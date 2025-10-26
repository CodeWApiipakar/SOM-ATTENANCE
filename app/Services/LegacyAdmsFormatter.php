<?php
// app/Services/LegacyAdmsFormatter.php
namespace App\Services;

class LegacyAdmsFormatter
{
    public static function toCommandLine(string $type, ?array $payload = null): string
    {
        $p = $payload ?? [];
        switch (strtoupper($type)) {
            case 'REBOOT':
                return "C:1:REBOOT";
            case 'CLEAR_ATTLOG':
                return "C:1:CLEAR LOG";
            case 'CLEAR_ALL_USERS':
                return "C:1:CLEAR DATA";
            case 'DELETE_USER':
                // Some firmwares want PIN=xxx; others USERID=xxx
                $pin = $p['enroll_id'] ?? $p['pin'] ?? '';
                return "C:1:DELETE USER PIN={$pin}";
            case 'SET_TIME':
                // Device may expect "YYYY-MM-DD HH:MM:SS"
                $dt = $p['datetime'] ?? '';
                return "C:1:SET TIME {$dt}";
            case 'ENABLE':
                return "C:1:ENABLE";
            case 'DISABLE':
                return "C:1:DISABLE";
            default:
                // Fallback: send as note for debugging
                return "C:1:NOTE UNSUPPORTED {$type}";
        }
    }
}
