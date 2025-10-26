<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceCommandController extends Controller
{
   // List commands for a device (admin)
   public function index(Device $device, Request $req) {
    $cmds = DeviceCommand::where('device_id', $device->id)
        ->latest()->paginate(25);
    return response()->json($cmds);
}
    
// Create a command for a device (admin)
public function create(Device $device, Request $req) {
    $data = $req->validate([
        'type' => ['required', Rule::in([
            'REBOOT','CLEAR_ATTLOG','CLEAR_ALL_USERS','DELETE_USER','SET_TIME',
            'ENABLE','DISABLE','SYNC_USER'
        ])],
        'payload'   => ['nullable','array'],
        'transport' => ['nullable', Rule::in(['json-poll','adms','wdms','sdk'])],
        'confirm'   => ['required','boolean'],
    ]);

    if (in_array($data['type'], ['CLEAR_ATTLOG','CLEAR_ALL_USERS','DELETE_USER']) && !$data['confirm']) {
        return response()->json(['error'=>'Confirmation required for destructive command'], 422);
    }
    if ($data['type'] === 'DELETE_USER') {
        $req->validate(['payload.enroll_id' => 'required|string']);
    }
    if ($data['type'] === 'SET_TIME') {
        $req->validate(['payload.datetime' => 'required|date']);
    }
    

    $cmd = new DeviceCommand();
    $cmd->device_id = $device->id;
    $cmd->type      = $data['type'];
    $cmd->payload   = $data['payload'] ?? null;
    $cmd->transport = $data['transport'] ?? $this->guessTransport($device);
    $cmd->status    = 'pending';
    $cmd->created_by= optional($req->user())->id;
    $cmd->save();

    // Optional: dispatch job to push via WDMS/SDK immediately.
    // DeliverDeviceCommand::dispatch($cmd->id);

    return response()->json(['ok'=>true,'command'=>$cmd], 201);
}

// Device polls for commands (JSON polling)
public function pullForDevice(Request $req) {
    $sn    = $req->input('sn');
    $token = $req->input('token');
   
    if (!$sn) return response()->json(['error'=>'Missing sn'], 422);

    $device = Device::where('serial_number', $sn)->first();
    if (!$device) return response()->json(['error'=>'Unknown device'], 404);
    if ($device->push_token && $device->push_token !== $token) {
        return response()->json(['error'=>'Invalid device token'], 401);
    }

    $cmds = DeviceCommand::where('device_id', $device->id)
        ->where('status','pending')
        ->orderBy('id')
        ->limit(5)->get();

    foreach ($cmds as $c) {
        $c->status   = 'sent';
        $c->sent_at  = now();
        $c->attempts = $c->attempts + 1;
        $c->save();
    }

    return response()->json([
        'ok'       => true,
        'sn'       => $sn,
        'commands' => $cmds->map(fn($c)=>[
            'id'      => $c->id,
            'type'    => $c->type,
            'payload' => $c->payload,
        ])->values(),
    ]);
}

// Device acknowledges execution result
public function ack(Request $req) {
    $sn    = $req->input('sn');
    $token = $req->input('token');
    $acks  = (array)$req->input('acks');

    $device = Device::where('serial_number', $sn)->first();
    if (!$device) return response()->json(['error'=>'Unknown device'], 404);
    if ($device->push_token && $device->push_token !== $token) {
        return response()->json(['error'=>'Invalid device token'], 401);
    }

    $updated = 0;
    foreach ($acks as $ack) {
        $cmd = DeviceCommand::where('id', $ack['id'] ?? null)
            ->where('device_id', $device->id)->first();
        if (!$cmd) continue;

        $status = strtolower($ack['status'] ?? '');
        $cmd->status     = in_array($status, ['ack','ok','success','done']) ? 'ack' : 'failed';
        $cmd->ack_at     = now();
        $cmd->last_error = ($cmd->status === 'failed') ? ($ack['message'] ?? 'device_error') : null;
        $cmd->save();
        $updated++;
    }

    return response()->json(['ok'=>true,'updated'=>$updated]);
}

public function cancel(DeviceCommand $command) {
    if ($command->isFinal()) return response()->json(['error'=>'Cannot cancel finalized command'], 422);
    $command->status = 'cancelled';
    $command->save();
    return response()->json(['ok'=>true]);
}

private function guessTransport(Device $device): string {
    // Simple heuristic; you can persist a column on devices table instead.
    return $device->vendor === 'zkteco' ? 'json-poll' : 'wdms';
}

}
