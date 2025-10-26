<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Punch;
use App\Services\LegacyAdmsFormatter;

class LegacyAdmsController extends Controller
{
    // Device polls for next command
    public function getRequest(Request $req)
    {
        $sn = $req->query('SN') ?: $req->input('SN');
    
        if (!$sn) return response("OK", 200); // no SN, return harmless OK

        $device = Device::where('serial_number', $sn)->first();
        if (!$device) return response("OK", 200); // unknown device → no-op

        $cmd = DeviceCommand::where('device_id', $device->id)
            ->where('transport', 'adms')
            ->where('status', 'pending')
            ->orderBy('id')->first();

        if (!$cmd) return response("OK", 200); // nothing to send

        // Format legacy command string (e.g., "C:1:REBOOT")
        $line = LegacyAdmsFormatter::toCommandLine($cmd->type, $cmd->payload);

        // Mark sent
        $cmd->status = 'sent';
        $cmd->sent_at = now();
        $cmd->attempts++;
        $cmd->save();

        // ADMS expects plain text body
        return response($line, 200)->header('Content-Type', 'text/plain');
    }

    // Device reports command result
    public function postResult(Request $req)
    {
        // Some firmwares call /iclock/devicecmd with different forms;
        // Accept both JSON and form text.
        $sn   = $req->input('SN') ?? $req->input('sn');
        $id   = $req->input('ID') ?? $req->input('id');     // internal id we sent (if you embed it)
        $ret  = strtolower($req->input('Return') ?? $req->input('ret') ?? 'ok');
        $msg  = $req->input('Message') ?? $req->input('msg');

        if (!$sn || !$id) return response("OK", 200);

        $device = Device::where('serial_number', $sn)->first();
        if (!$device) return response("OK", 200);

        $cmd = DeviceCommand::where('id', $id)->where('device_id', $device->id)->first();
        if (!$cmd) return response("OK", 200);

        $cmd->status     = in_array($ret, ['ok', 'success', 'ack']) ? 'ack' : 'failed';
        $cmd->ack_at     = now();
        $cmd->last_error = ($cmd->status === 'failed') ? ($msg ?: 'device_error') : null;
        $cmd->save();

        return response("OK", 200);
    }

    public function cdata(Request $req) {
    // U270 usually sends lines like:
    // "POST /iclock/cdata?SN=U270-XXXX&table=ATTLOG&Stamp=...&OpStamp=..."
    // Body contains records like: "PIN=10012\tDateTime=2025-10-18 08:05:33\tVerifyType=FP\tStatus=0\tWorkCode=0"
    $sn = $req->query('SN') ?? $req->input('SN');
    if (!$sn) return response('OK', 200);

    $device = Device::where('serial_number', $sn)->first();
    if (!$device) return response('OK', 200);

    $raw = $req->getContent();              // multi-line text
    foreach (preg_split("/\r\n|\n|\r/", $raw) as $line) {
        if (!trim($line)) continue;
        // parse "key=value\tkey=value..."
        $pairs = [];
        foreach (preg_split("/\t+/", $line) as $kv) {
            [$k,$v] = array_pad(explode('=', $kv, 2), 2, null);
            if ($k !== null) $pairs[$k] = $v;
        }
        // map & save
        if (($pairs['PIN'] ?? null) && ($pairs['DateTime'] ?? null)) {
            Punch::create([
                'device_id'  => $device->id,
                'enroll_id'  => $pairs['PIN'],
                'timestamp'  => $pairs['DateTime'],
                'verify'     => $pairs['VerifyType'] ?? null,
                'io'         => isset($pairs['Status']) ? (string)$pairs['Status'] : null,
                'rid'        => $pairs['RecordID'] ?? null,
                'raw'        => $line,
            ]);
        }
    }
    // ADMS expects plain-text "OK" to advance the stamp
    return response('OK', 200)->header('Content-Type','text/plain');
}
}
