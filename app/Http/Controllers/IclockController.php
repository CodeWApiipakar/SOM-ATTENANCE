<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IclockController extends Controller
{
    /* ============================================================
     |  Helpers (no new tables; only files under storage/app)
     * ============================================================ */

    private function commandsDir(): string
    {
        $dir = storage_path('app/iclock/commands');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        return $dir;
    }

    private function stateDir(): string
    {
        $dir = storage_path('app/iclock/state');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        return $dir;
    }

    private function queueCmd(string $sn, string $line): void
    {
        $f = $this->commandsDir() . "/{$sn}.txt";
        file_put_contents($f, rtrim($line) . "\r\n", FILE_APPEND);
    }

    private function queueMany(string $sn, array $lines): void
    {
        foreach ($lines as $l) $this->queueCmd($sn, $l);
    }

    private function popCmd(?string $sn): ?string
    {
        if (!$sn) return null;
        $f = $this->commandsDir() . "/{$sn}.txt";
        if (!is_file($f)) return null;

        $lines = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            @unlink($f);
            return null;
        }

        $cmd = array_shift($lines);
        if ($lines) file_put_contents($f, implode("\n", $lines) . "\n");
        else @unlink($f);

        return $cmd;
    }

    private function getToggle(string $sn): string
    {
        $f = $this->stateDir() . "/{$sn}.toggle";
        if (!is_file($f)) {
            file_put_contents($f, 'ATTLOG');
            return 'ATTLOG';
        }
        $cur = trim(file_get_contents($f)) ?: 'ATTLOG';
        $next = $cur === 'ATTLOG' ? 'USER' : 'ATTLOG';
        file_put_contents($f, $next);
        return $cur;
    }

    // Save public IP & ensure device record exists (your schema)
    private function ensureDevice(?string $sn, string $publicIp): ?object
    {
        if (!$sn) return null;

        $row = DB::table('devices')->where('serial_number', $sn)->first();
        if ($row) {
            DB::table('devices')->where('id', $row->id)->update([
                'ip_address' => $publicIp,
                'updated_at' => now(),
            ]);
            return DB::table('devices')->where('id', $row->id)->first();
        }

        $id = DB::table('devices')->insertGetId([
            'name'          => $sn,
            'serial_number' => $sn,
            'ip_address'    => $publicIp,
            'port'          => 80,
            'model'         => null,
            'vendor'        => 'zkteco',
            'push_token'    => null,
            'is_active'     => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        return DB::table('devices')->where('id', $id)->first();
    }

    /* ============================================================
     |  Device endpoints
     * ============================================================ */

    // Device fetches options – force full upload by zeroing stamps
    public function getoption(Request $req)
    {
        $sn = $req->query('SN') ?: $req->input('SN');
        $this->ensureDevice($sn, $req->ip());

        $lines = [
            'Stamp=0',
            'OpStamp=0',
            'ATTLOGStamp=0',
            'USERStamp=0',
            'ErrorDelay=30',
            'Delay=5',
            'TransTimes=00:00;23:59',
            'TransInterval=1',
            'Realtime=1',
        ];
        $body = implode("\r\n", $lines) . "\r\n";

        Log::info('ICLOCK GETOPTION', ['sn' => $sn, 'ip' => $req->ip(), 'qs' => $req->query()]);
        return response($body, 200)->header('Content-Type', 'text/plain');
    }

    // Device polls for commands
    // public function getrequest(Request $req)
    // {
    //     $sn = $req->query('SN') ?: $req->input('SN');
    //     $this->ensureDevice($sn, $req->ip());

    //     Log::info('ICLOCK META', [
    //         'ip' => $req->ip(),
    //         'ua' => $req->header('User-Agent'),
    //         'qs' => $req->query(),
    //     ]);

    //     // Admin-queued commands take priority
    //     if ($sn && ($cmd = $this->popCmd($sn))) {
    //         Log::info('ICLOCK CMD OUT', ['sn' => $sn, 'cmd' => $cmd]);
    //         return response($cmd."\r\n", 200)->header('Content-Type', 'text/plain');
    //     }

    //     // Alternate between ATTLOG & USER pulls
    //     $which = $this->getToggle($sn ?: 'unknown');
    //     if ($which === 'ATTLOG') {
    //         $cmd = "C:1:DATA QUERY ATTLOG\tStartTime=2000-01-01 00:00:00\tEndTime=2099-12-31 23:59:59";
    //     } else {
    //         $cmd = "C:1:DATA QUERY USER";
    //     }

    //     Log::info('ICLOCK CMD OUT', ['sn' => $sn, 'cmd' => $cmd]);
    //     return response($cmd."\r\n", 200)->header('Content-Type', 'text/plain');
    // }

    public function getrequest(Request $req)
    {
        $sn = $req->query('SN') ?: $req->input('SN');
        // $this->ensureDevice($sn, $req->ip());

        Log::info('ICLOCK META', [
            'ip' => $req->ip(),
            'ua' => $req->header('User-Agent'),
            'qs' => $req->query(),
        ]);

        // 1) Admin-queued commands (FIFO pop: removes exactly one line)
        if ($sn && ($cmd = $this->popCmd($sn))) {
            Log::info('ICLOCK CMD OUT', ['sn' => $sn, 'cmd' => $cmd]);
            return response($cmd . "\r\n", 200)->header('Content-Type', 'text/plain');
        }

        // 2) Alternate between ATTLOG and USERINFO
        $which = $this->getToggle($sn ?: 'unknown'); // must persist per-SN
        if ($which === 'ATTLOG') {
            // Use SPACES (not tabs) + explicit PIN=All (helps on some firmware)
            // Huge window is OK for first sync; later you can narrow it.
            // $cmd = "C:1:DATA QUERY ATTLOG PIN=All StartTime=2000-01-01 00:00:00 EndTime=2099-12-31 23:59:59";
            $cmd = "C:1:DATA QUERY ATTLOG";
        } else {
            // Ask for USERINFO (more universally accepted than USER)
            $cmd = "C:1:DATA QUERY USERINFO";
        }

        Log::info('ICLOCK CMD OUT', ['sn' => $sn, 'cmd' => $cmd]);
        return response($cmd . "\r\n", 200)->header('Content-Type', 'text/plain');
    }


    // Device uploads data (ATTLOG / USER / INFO / mixed)
    public function cdata(Request $req)
    {
        $sn    = $req->query('SN') ?: $req->input('SN');
        $table = strtoupper((string)($req->query('table') ?: $req->input('table')));
        $body  = $req->getContent() ?? '';
        Log::info("----------Table----------" . $table);
        $this->ensureDevice($sn, $req->ip());

        Log::info('ICLOCK CDATA', [
            'sn'    => $sn,
            'table' => $table ?: null,
            'ip'    => $req->ip(),
            'len'   => strlen($body),
            'peek'  => mb_substr($body, 0, 150),
        ]);

        // Direct route when table is explicit
        if ($table === 'ATTLOG') {
            $this->storeATTLOG($sn, $body);
            return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
        }
        if ($table === 'USER' || $table === 'USERINFO') {
            $this->storeUSER($sn, $body);
            return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
        }
        if ($table === 'INFO') {
            $this->storeINFO($sn, $body);
            return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
        }

        // Fallback: content-aware routing (mixed or missing table)
        $attLines  = [];
        $userLines = [];
        $infoLines = [];

        foreach (preg_split('/\r\n|\r|\n/', $body) as $ln) {
            $line = trim($ln);
            if ($line === '') continue;

            // Identify ATTLOG lines
            if (
                stripos($line, 'ATTLOG') === 0
                || preg_match('/^\S+\s+\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $line)
            ) {
                $attLines[] = $line;
                continue;
            }

            // Identify USER lines
            if (
                stripos($line, 'USER') === 0
                || preg_match('/^\d+\s+Name=|^\d+\s+\S+/', $line)
            ) {
                $userLines[] = $line;
                continue;
            }

            // INFO-like key=value pairs (optional)
            if (str_contains($line, '=')) {
                $infoLines[] = $line;
                continue;
            }
        }

        if ($userLines) $this->storeUSER($sn, implode("\n", $userLines));
        if ($attLines)  $this->storeATTLOG($sn, implode("\n", $attLines));
        if ($infoLines) $this->storeINFO($sn, implode("\n", $infoLines));

        return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
    }

    /* ============================================================
     |  Parsers: ATTLOG / USER / INFO  (write only to your schema)
     * ============================================================ */

    // STRICT: do not create employees from ATTLOG
    private function storeATTLOG(?string $sn, string $body): int
    {
        if (!$sn) return 0;

        $device = DB::table('devices')->where('serial_number', $sn)->first();
        if (!$device) return 0;

        $lines = preg_split('/\r\n|\r|\n/', $body);
        $count = 0;

        Log::debug('---- ATT BODY ----', ['body' => $body]);

        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;

            // Strip optional ATTLOG prefix
            if (stripos($ln, 'ATTLOG') === 0) {
                $ln = trim(substr($ln, 6));
            }

            // Expect: PIN <space/tab> YYYY-mm-dd <space/T> HH:ii:ss [rest...]
            if (!preg_match(
                '/^(\S+)\s+(\d{4}-\d{2}-\d{2})[ T]+(\d{2}:\d{2}:\d{2})(?:\s+(.*))?$/',
                $ln,
                $m
            )) {
                Log::warning('ATTLOG line did not match expected pattern', ['line' => $ln]);
                continue;
            }

            $pin  = $m[1];
            $ts   = $m[2] . ' ' . $m[3]; // full timestamp with time
            $rest = isset($m[4]) ? trim($m[4]) : '';

            // Split remaining fields on tabs or spaces and ignore empties
            $restParts = preg_split('/[\t ]+/', $rest, -1, PREG_SPLIT_NO_EMPTY);

            $verify   = $restParts[0] ?? null;
            $io       = $restParts[1] ?? null;
            $workcode = $restParts[2] ?? null;

            if (!$pin || !$ts) continue;

            $verify_mode = $this->mapVerify((string) $verify);
            $io_mode     = $this->mapIO((string) $io);

            // If device time != DB TZ, convert here
            // $tsUtc = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ts, 'Africa/Mogadishu')->utc()->toDateTimeString();
            $tsToStore = $ts;

            $source_uid = md5("SN:$sn|PIN:$pin|TS:$tsToStore");

            if (DB::table('punches')->where('source_uid', $source_uid)->exists()) continue;

            // STRICT: require existing employee
            $emp = DB::table('employees')->where('enroll_id', $pin)->first();
            if (!$emp) {
                Log::warning('Skipping punch: unknown employee', ['sn' => $sn, 'pin' => $pin, 'ts' => $ts]);
                continue;
            }

            DB::table('punches')->insert([
                'device_id'    => $device->id,
                'employee_id'  => $emp->id,
                'enroll_id'    => (string) $pin,
                'verify_mode'  => $verify_mode,
                'io_mode'      => $io_mode,
                'punch_time'   => $tsToStore,
                'work_code'    => $workcode,
                'source_uid'   => $source_uid,
                'raw_payload'  => json_encode(['raw' => $ln], JSON_UNESCAPED_UNICODE),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function mapVerify(string $v): ?string
    {
        // quick mapping; adjust as needed
        return match ($v) {
            '0' => 'FP',
            '1' => 'FP',
            '2' => 'CARD',
            '3' => 'PIN',
            default => null,
        };
    }

    private function mapIO(string $v): ?string
    {
        return match ($v) {
            '0' => 'IN',
            '1' => 'OUT',
            default => null,
        };
    }

    private function storeUSER(?string $sn, string $body): int
    {
        Log::debug('-----------USER BODY ----------', ['body' => $body]);
        if (!$sn) return 0;

        $count = 0;
        $lines = preg_split('/\r\n|\r|\n/', $body);

        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;

            // Drop leading "USER"
            if (stripos($ln, 'USER') === 0) {
                $ln = trim(substr($ln, 4));
            }

            // ---- Extract PIN (digits only) ----
            $pin = null;

            // Prefer explicit key: PIN=...
            if (preg_match('/\bPIN=([^\s]+)/i', $ln, $m)) {
                $pin = $m[1];
            } else {
                // Otherwise, allow leading numeric token as PIN
                if (preg_match('/^\s*(\d+)/', $ln, $m)) {
                    $pin = $m[1];
                }
            }

            if ($pin === null) continue;

            // Keep ONLY digits; skip if none found
            $pin = preg_replace('/\D+/', '', (string)$pin);
            if ($pin === '') continue;

            // ---- Extract Name, Department/Dept (optional) ----
            // Capture up to the next key or end-of-line; names with spaces will still be caught if the device sends "Name=First_Last" or similar.
            $name = null;
            if (preg_match('/\bName=([^\t\r\n]*)/i', $ln, $m)) {
                $name = trim($m[1]);
            }

            $dept = null;
            if (preg_match('/\bDepartment=([^\s\t\r\n]*)/i', $ln, $m)) {
                $dept = trim($m[1]);
            } elseif (preg_match('/\bDept=([^\s\t\r\n]*)/i', $ln, $m)) {
                $dept = trim($m[1]);
            }

            // ---- Upsert employee by enroll_id (numeric string) ----
            $emp = DB::table('employees')->where('enroll_id', $pin)->first();

            if ($emp) {
                DB::table('employees')->where('id', $emp->id)->update([
                    'name'       => $name ?? $emp->name,
                    'departmentId' => $dept ?? $emp->departmentId,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('employees')->insert([
                    'enroll_id'  => $pin,   // numeric string only
                    'emp_code'   => null,
                    'name'       => $name,
                    'departmentId' => $dept,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $count++;
        }

        return $count;
    }


    private function storeINFO(?string $sn, string $body): void
    {
        Log::debug('---- INFO BODY ----', ['body' => $body]);
        if (!$sn) return;

        $kv = [];
        foreach (preg_split('/\r\n|\r|\n/', $body) as $ln) {
            $ln = trim($ln);
            if ($ln === '' || !str_contains($ln, '=')) continue;
            [$k, $v] = array_map('trim', explode('=', $ln, 2));
            $kv[strtolower($k)] = $v;
        }

        $update = [];
        if (!empty($kv['producttype']))     $update['model'] = $kv['producttype'];
        if (!empty($kv['firmwareversion'])) $update['vendor'] = 'zkteco';
        if (!empty($kv['ipaddress']))       $update['ip_address'] = $kv['ipaddress'];

        if ($update) {
            DB::table('devices')->where('serial_number', $sn)->update($update + ['updated_at' => now()]);
        }

        Log::info('ICLOCK INFO', ['sn' => $sn, 'kv' => $kv, 'applied' => $update]);
    }

    /* ============================================================
     |  Admin commands (file-queue, no DB tables)
     * ============================================================ */

    // REBOOT
    public function cmdReboot(string $sn)
    {
        $this->queueCmd($sn, 'C:1:REBOOT');
        return response()->json(['ok' => true, 'queued' => 'REBOOT', 'sn' => $sn]);
    }

    // CLEAR ATTENDANCE LOG
    public function cmdClearAttlog(string $sn)
    {
        $this->queueCmd($sn, 'C:1:CLEAR LOG');
        return response()->json(['ok' => true, 'queued' => 'CLEAR LOG', 'sn' => $sn]);
    }

    // CLEAR ALL DATA (users + logs)
    public function cmdClearAll(string $sn)
    {
        $this->queueCmd($sn, 'C:1:CLEAR DATA');
        return response()->json(['ok' => true, 'queued' => 'CLEAR DATA', 'sn' => $sn]);
    }

    // Force re-upload of everything (ATTLOG + USER)
    public function cmdPullAll(string $sn)
    {
        $this->queueMany($sn, [
            "C:1:DATA QUERY USERINFO",
            "C:1:DATA QUERY ATTLOG\tStartTime=2000-01-01 00:00:00\tEndTime=2099-12-31 23:59:59",
        ]);
        return response()->json(['ok' => true, 'queued' => ['ATTLOG', 'USER'], 'sn' => $sn]);
    }

    // Push a single user to device
    // Body JSON: { "enroll_id":"10012", "name":"Asha", "privilege":0, "pin":"", "card":"" }
    public function cmdSyncUser(string $sn, Request $r)
    {
        $p = $r->validate([
            'enroll_id' => 'required|string',
            'name'      => 'nullable|string',
            'privilege' => 'nullable|integer',
            'pin'       => 'nullable|string',
            'card'      => 'nullable|string',
        ]);

        $kv = [];
        $kv[] = 'PIN=' . $p['enroll_id'];
        if (!empty($p['name']))      $kv[] = 'Name=' . preg_replace('/[\t\r\n]+/', ' ', $p['name']);
        $kv[] = 'Pri=' . (string)($p['privilege'] ?? 0);
        if (!empty($p['pin']))       $kv[] = 'Passwd=' . $p['pin'];
        if (!empty($p['card']))      $kv[] = 'Card=' . $p['card'];

        $cmd = 'C:1:DATA UPDATE USER ' . implode("\t", $kv);
        $this->queueCmd($sn, $cmd);

        return response()->json(['ok' => true, 'queued' => $cmd, 'sn' => $sn]);
    }

    // Device acknowledgements for commands
    public function devicecmd(Request $req)
    {
        // Typical device posts: SN=XXXXXX&ID=123&Return=OK&Message=...
        $sn  = $req->input('SN') ?? $req->input('sn');
        $id  = $req->input('ID') ?? $req->input('id');
        $ret = (string)($req->input('Return') ?? $req->input('ret') ?? 'OK');
        $msg = (string)($req->input('Message') ?? $req->input('msg') ?? '');

        // keep device record fresh
        $this->ensureDevice($sn, $req->ip());

        Log::info('ICLOCK DEVICECMD', [
            'ip'  => $req->ip(),
            'sn'  => $sn,
            'id'  => $id,
            'ret' => $ret,
            'msg' => $msg,
            'all' => $req->all(),
        ]);

        // ADMS expects plain OK
        return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
    }

    /* ============================================================
     |  Read-only inspectors
     * ============================================================ */

    public function listDevices()
    {
        $rows = DB::table('devices')->orderByDesc('updated_at')->get();
        return response()->json(['ok' => true, 'count' => $rows->count(), 'items' => $rows]);
    }

    public function listPunches(Request $r)
    {
        $rows = DB::table('punches')->orderByDesc('id')->limit((int)$r->query('limit', 50))->get();
        return response()->json(['ok' => true, 'count' => $rows->count(), 'items' => $rows]);
    }

    public function listEmployees(Request $r)
    {
        $rows = DB::table('employees')->orderByDesc('id')->limit((int)$r->query('limit', 50))->get();
        return response()->json(['ok' => true, 'count' => $rows->count(), 'items' => $rows]);
    }
}
