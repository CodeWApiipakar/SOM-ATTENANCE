Biometric Attendance Integration – Laravel 12

A production-ready Laravel 12 backend that integrates directly with biometric attendance devices (e.g. ZKTeco U270 using Push SDK / ADMS).
It securely receives punch data, stores it in SQL Server, and provides a simple dashboard + REST API.


⚙️ Key Features

Secure /api/adms/callback endpoint for device push

IP allowlist + HMAC signature + per-device token

SQL Server support (sqlsrv)

Device registry and employee mapping

Idempotent punch ingestion (source_uid unique)

Optional Laravel Sanctum-based admin API

Minimal Blade dashboard for quick monitoring

Works with ZKTeco ADMS / Push SDK or any JSON-posting device


| Component      | Version                |
| -------------- | ---------------------- |
| PHP            | 8.2 +                  |
| Laravel        | 12 +                   |
| SQL Server     | 2019 / 2022            |
| PHP Extensions | `pdo_sqlsrv`, `sqlsrv` |





🚀 Installation & Setup
composer create-project laravel/laravel biometric-attendance
cd biometric-attendance
composer require laravel/sanctum
php artisan key:generate


app/
 ├── Http/Controllers/
 │    ├── AdmsPushController.php
 │    ├── DeviceController.php
 │    ├── PunchController.php
 │    └── DashboardController.php
 ├── Models/
 │    ├── Device.php
 │    ├── Employee.php
 │    └── Punch.php
config/
 ├── adms.php
database/
 ├── migrations/...
 └── seeders/DevDeviceSeeder.php
resources/views/dashboard.blade.php
routes/
 ├── api.php
 └── web.php

🔐 Security Layers

| Layer          | Purpose                              |
| -------------- | ------------------------------------ |
| HTTPS          | Encrypt transport (TLS)              |
| IP allowlist   | Restrict POSTs to trusted IPs        |
| HMAC Signature | Integrity check (`X-ADMS-Signature`) |
| Device Token   | Per-device secret in request body    |
| Auth (Sanctum) | Protect admin APIs                   |




📑 Migrations

-Devices

| Field         | Type     | Notes                  |
| ------------- | -------- | ---------------------- |
| id            | bigint   | PK                     |
| name          | varchar  | Device name            |
| serial_number | varchar  | Unique identifier (SN) |
| ip_address    | varchar  | Optional               |
| port          | smallint | Default 80             |
| model         | varchar  | Optional               |
| vendor        | varchar  | Default zkteco         |
| push_token    | varchar  | For `token` validation |
| is_active     | bit      | Default 1              |
| timestamps    | —        |                        |


-Employees

| Field      | Type           | Notes             |
| ---------- | -------------- | ----------------- |
| id         | bigint         |                   |
| enroll_id  | varchar unique | Device UserID/PIN |
| emp_code   | varchar        | HR code           |
| name       | varchar        |                   |
| department | varchar        |                   |
| timestamps |                |                   |


-Punches

| Field       | Type           | Notes       |
| ----------- | -------------- | ----------- |
| id          | bigint         |             |
| device_id   | FK devices     |             |
| employee_id | FK employees   |             |
| enroll_id   | varchar        |             |
| verify_mode | varchar        | FP/FACE/PIN |
| io_mode     | varchar        | IN/OUT      |
| punch_time  | datetime UTC   |             |
| work_code   | varchar        |             |
| source_uid  | varchar unique |             |
| raw_payload | json           |             |
| timestamps  |                |             |



🧠 Controller Logic Overview

-AdmsPushController@ingest

flowchart TD
A[Device POST /api/adms/callback] --> B[Check IP allowlist]
B --> C[Verify HMAC signature]
C --> D[Find device by serial_number]
D --> E[Validate device.push_token == token]
E --> F[Loop records -> normalize fields]
F --> G[Create/Find employee]
G --> H[Insert punch (unique source_uid)]
H --> I[Return JSON {ingested,duplicates,skipped}]


🌐 API Endpoints

| Method | Endpoint             | Description                 |
| ------ | -------------------- | --------------------------- |
| POST   | `/api/adms/callback` | Device push endpoint        |
| GET    | `/api/health`        | Simple health check         |
| GET    | `/api/devices`       | List devices (Sanctum auth) |
| POST   | `/api/devices`       | Register device             |
| GET    | `/api/punches`       | Paginated punch logs        |
| GET    | `/`                  | Dashboard UI                |


🖥️ Dashboard
-Browse / to see the latest 25 punches and registered devices.
| Column     | Meaning             |
| ---------- | ------------------- |
| Time (UTC) | Recorded punch time |
| Enroll ID  | Device user         |
| Employee   | Linked employee     |
| Device     | Source device       |
| Mode       | Verification method |
| IO         | In/Out flag         |


🧰 Testing
-Health
curl -s https://nidaam.somict.so/api/health

-Single punch
curl -X POST https://nidaam.somict.so/api/adms/callback \
 -H "Content-Type: application/json" \
 -d '{"sn":"U270-ABCD1234","enroll_id":"10012","timestamp":"2025-10-18 08:05:33","verify":"FP","io":"IN","rid":"245099"}'

With HMAC

SECRET=super-strong-random-string
BODY='{"sn":"U270-ABCD1234","token":"device-secret-123","records":[{"enroll_id":"10012","timestamp":"2025-10-18 08:05:33","verify":"FP","io":"IN","rid":"245099"}]}'
SIG=$(printf "%s" "$BODY" | openssl dgst -sha256 -hmac "$SECRET" -binary | xxd -p -c 256)

curl -X POST https://nidaam.somict.so/api/adms/callback \
 -H "Content-Type: application/json" \
 -H "X-ADMS-Signature: $SIG" \
 -d "$BODY"


🧱 Seed Example
php artisan make:seeder DevDeviceSeeder
php
Copy code


Device::updateOrCreate(
  ['serial_number'=>'U270-ABCD1234'],
  [
    'name'=>'ZKTeco U270 (Lab)',
    'vendor'=>'zkteco',
    'ip_address'=>'192.168.1.50',
    'port'=>80,
    'model'=>'U270',
    'push_token'=>'device-secret-123',
    'is_active'=>true,
  ]
);


🧱 Deployment Checklist
| Item                    | Status |
| ----------------------- | ------ |
| HTTPS enabled           | ✅      |
| APP_DEBUG=false         | ✅      |
| Non-`sa` DB user        | ✅      |
| Firewall restricts 1433 | ✅      |
| IP allowlist active     | ✅      |
| Queue worker running    | ✅      |
| Daily DB backups        | ✅      |


⚡ Troubleshooting

| Issue                    | Cause / Fix                                   |
| ------------------------ | --------------------------------------------- |
| 401 Invalid device token | Mismatch → update `devices.push_token`        |
| 401 Invalid signature    | HMAC mismatch → recalculate with same secret  |
| 403 IP not allowed       | Add IP to `ADMS_IP_WHITELIST`                 |
| 404 Unknown device SN    | Register device first                         |
| 422 Missing sn           | Device must send `sn` field                   |
| Duplicate entries        | Same record resent → ignored via `source_uid` |
| Wrong time               | Device clock / timezone issue → sync NTP      |



🧩 Extending

HR Integration – sync employees table from your HR system

Shift Logic – add shifts & attendance_summaries tables

Reports – CSV / Excel export controllers

Multi-Tenant – add company_id to core tables

UI – extend dashboard to Vue/React SPA




⚙ --------------------------------------------------------------- Device Configuration -----------------------------------------------------------


Device JSON Push – Configuration Guide (Direct to Laravel)

This guide explains how to configure biometric devices (e.g., ZKTeco/Hikvision/Suprema models that support custom cloud / HTTP JSON push) to send attendance logs directly to your Laravel app.

Your endpoint:
https://nidaam.somict.so/api/adms/callback (HTTPS, POST, JSON)



1) Prerequisites (Server Side)

Laravel app deployed and reachable at https://nidaam.somict.so/

The device is created in your devices table with the correct serial number (SN) and an optional push_token if you’ll enforce tokens.

.env security (recommended):

ADMS_IP_WHITELIST includes the public egress IP(s) the device will use (e.g., your site’s NAT IP)

If the device cannot send custom headers, do not enable HMAC; otherwise set ADMS_SHARED_SECRET and ensure the sender can add X-ADMS-Signature.

If the device can’t add headers or custom JSON fields, you can append ?token=... to the URL (see §6.2)




2) Device Network & Time

On the device (menu or web UI):

Network

IP Address: set static or use DHCP reservation

Subnet Mask, Gateway: set correctly

DNS: must resolve nidaam.somict.so (public DNS or your corporate DNS)

Time

Enable NTP

NTP server: pool.ntp.org

Timezone: Africa/Mogadishu (UTC+3)

Accurate time reduces clock-drift issues. Your server stores all punches in UTC.






3) Cloud / Server / HTTP Push Settings

In the device’s Cloud / Server / ADMS (exact names vary by vendor/firmware):

Server URL:
https://nidaam.somict.so/api/adms/callback

Port: 443

Protocol/Format: HTTPS, JSON payload (not legacy /iclock/cdata)

Method: POST

Push Mode: Real-time (best) or the smallest interval offered

Retry on Fail: Enabled (backoff between 5–60 seconds)

If the device lets you define the payload schema, configure this mapping:




| JSON Field  | Device Field / Description                       |
| ----------- | ------------------------------------------------ |
| `sn`        | Device serial number                             |
| `enroll_id` | UserID / PIN in the device                       |
| `timestamp` | Punch date-time (device local time is fine)      |
| `verify`    | Verify mode (e.g., `FP`, `FACE`, `CARD`, `PIN`)  |
| `io`        | In/Out flag (`IN`, `OUT`) if available           |
| `rid`       | Device log/record ID (stable if possible)        |
| `work_code` | (Optional) Work code / job code                  |
| `token`     | (Optional but recommended) constant device token |






















#============================================================================ Senario how project works ==============================================================
🔧 1. Overview

Your project is a Biometric Attendance Management API built on Laravel 12, connected to ZKTeco biometric devices (like U270) through ADMS (push/poll) communication.
It handles two main flows:

Attendance Data Push — devices send punches (check-in/out data) to your server.

Command Control — the server sends commands back to devices (reboot, clear logs, sync users, etc.).

Everything is managed through secure APIs, SQL Server database connectivity, and Nginx + PHP-FPM under HTTPS (https://nidaam.somict.so).






🧩 2. Core Components

| Component                      | Purpose                                                                          |
| ------------------------------ | -------------------------------------------------------------------------------- |
| **Laravel API**                | Central backend handling all device communication, admin APIs, and data storage. |
| **SQL Server**                 | Stores device details, punches, users, and commands.                             |
| **ZKTeco Devices (U270 etc.)** | Send punch data via ADMS push or poll for commands (reboot, sync user, etc.).    |
| **Nginx + PHP-FPM**            | Web server serving Laravel under HTTPS.                                          |
| **Sanctum Authentication**     | Protects admin APIs for device management.                                       |



📡 3. Communication Flows

== (A) Device → Server (Data Push)

Route: /api/adms/callback

-Device sends attendance logs as POST JSON.

-Laravel validates sn + token → stores punch data in punches table.

-Response: {ok:true}

Database tables involved:

-devices (for SN and token)

-punches (for check-in/out logs)


== (B) Server → Device (Command Control)

Admin can send commands like REBOOT, CLEAR_ATTLOG, or SYNC_USER.

1. Command Creation (Admin → Server)

Route: /api/devices/{id}/commands

-Admin (via Sanctum token) posts a command type + payload.

-Laravel creates a record in device_commands (status=pending).

2. Device Polls for Commands

Route: /api/adms/commands

-Device sends {sn, token}.

-Server returns pending commands for that device.

-Marks them as sent.

3. Device Executes & Acknowledges

Route: /api/adms/ack

-Device sends back ACK results (id, status, message).

-Server updates the command as ack or failed.


⚙️ 4. Command Types
| Command           | Description                             | Example Payload                      |
| ----------------- | --------------------------------------- | ------------------------------------ |
| `REBOOT`          | Restart the device                      | none                                 |
| `CLEAR_ATTLOG`    | Delete attendance logs                  | none                                 |
| `CLEAR_ALL_USERS` | Delete all enrolled users               | none                                 |
| `DELETE_USER`     | Remove specific user                    | `{"enroll_id":"1001"}`               |
| `SET_TIME`        | Set device time                         | `{"datetime":"2025-10-18 11:00:00"}` |
| `ENABLE`          | Enable device                           | none                                 |
| `DISABLE`         | Disable device                          | none                                 |
| `SYNC_USER`       | Upload new user (name, card, templates) | `{enroll_id,name,card,fingers,face}` |






🌐 5. Legacy ADMS (Optional)

Older ZKTeco firmware uses the /iclock/* routes:

| Endpoint             | Direction       | Purpose                          |
| -------------------- | --------------- | -------------------------------- |
| `/iclock/getrequest` | Device → Server | Polls for next command           |
| `/iclock/devicecmd`  | Device → Server | Sends result of previous command |




🔒 6. Security & Auth

Verified against the devices table.

Admin Authentication:
Admin APIs use Laravel Sanctum bearer tokens.

SSL / HTTPS:
All requests pass through https://nidaam.somict.so with Let’s Encrypt.

{ "sn": "U270-12345678", "token": "<device_push_token>" }




🧱 7. Database Summary
| Table                 | Purpose                                               |
| --------------------- | ----------------------------------------------------- |
| `devices`             | Registered biometric devices (SN, model, push_token). |
| `punches`             | Attendance logs from devices.                         |
| `device_commands`     | Queue of pending/sent/acknowledged device commands.   |
| `people`              | User records (enroll_id, name, card, privilege).      |
| `biometric_templates` | Base64 templates for finger/face linked to people.    |




🚀 8. Deployment & Server Setup
| Component         | Path / Port                                     | Notes                   |
| ----------------- | ----------------------------------------------- | ----------------------- |
| Laravel app       | `/var/www/html/SomApp/biometric-attendance`     | Main project            |
| PHP-FPM socket    | `/run/php/php8.2-fpm.sock`                      | Used by Nginx           |
| Nginx site config | `/etc/nginx/sites-enabled/biometric-attendance` | HTTPS + proxy           |
| SSL               | `/etc/letsencrypt/live/nidaam.somict.so/`       | Auto-managed by Certbot |
| Public endpoint   | `https://nidaam.somict.so`                      | Devices and admin API   |




🧭 9. Typical Operation Flow

[Admin]           [Laravel API]                     [Device]
   |                   |                               |
   | POST /commands →  |                               |
   |------------------>|  create pending command        |
   |                   |                               |
   |                   | ← POST /adms/commands          |
   |                   |  returns command payload       |
   |                   |------------------------------>|
   |                   |                               | execute
   |                   | ← POST /adms/ack               |
   |                   |  update command status         |
   |                   |                               |
   | GET /commands     | show ack/failed results        |
   |<------------------|                               |


🧾 10. Example: Reboot Command (Full Round Trip)

-Admin creates

POST /api/devices/1/commands
{ "type":"REBOOT", "confirm":true }

-Server stores pending command.
-Device polls 

POST /api/adms/commands
{ "sn":"U270-12345678", "token":"xyz" }

-Server responds

{ "ok":true, "commands":[{"id":77,"type":"REBOOT"}] }


-Device executes reboot, then ACKs
POST /api/adms/ack
{ "sn":"U270-12345678", "token":"xyz", "acks":[{"id":77,"status":"ack"}] }




🧰 11. Admin Tools / Maintenance
| Action            | Command                              |
| ----------------- | ------------------------------------ |
| Clear cache       | `php artisan optimize:clear`         |
| Run migrations    | `php artisan migrate`                |
| View logs         | `tail -f storage/logs/laravel.log`   |
| Test Nginx config | `nginx -t`                           |
| Restart services  | `systemctl restart nginx php8.2-fpm` |




✅ 14. Summary of How Everything Connects

┌───────────────┐      HTTPS      ┌────────────────────┐
│ ZKTeco Device │◄────────────────►│ Laravel API Server │
│ (U270 etc.)   │   Push/Poll API  │  /api/adms/*       │
└──────┬────────┘                  │  /iclock/*         │
       │                           ├────────────────────┤
       │                           │  SQL Server DB     │
       │                           │  devices, punches, │
       │                           │  device_commands   │
       │                           └────────────────────┘
       │
       ▼
┌────────────────────────────┐
│   Admin Panel / Postman    │
│ (Laravel Sanctum Auth)     │
│  Create + monitor commands │
└────────────────────────────┘


🛡️ License / Authorship

© 2025 SOM-ICT Technology Solution.
Manager: Eng. Abdullahi Yusuf (Muscab) — Director @ SOM-ICT.
Developer: Eng. Appiipakar mohamoud abdirahman  — Sofware Eng @ SOM-ICT.
