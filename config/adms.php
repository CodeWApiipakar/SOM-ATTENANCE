<?php

return [
    // Optional HMAC secret for an extra header (X-ADMS-Signature)
    'shared_secret' => env('ADMS_SHARED_SECRET', null),

    // Optional comma-separated list of allowed IPs: "1.2.3.4,5.6.7.8"
    'ip_whitelist'  => env('ADMS_IP_WHITELIST', ''),
];
