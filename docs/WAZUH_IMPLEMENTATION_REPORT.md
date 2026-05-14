# Wazuh Security Monitoring Implementation Report

Date: 2026-04-27
Host: Fedora 43 x86_64
Project: Receptionist System (Laravel)

## Scope completed

- Installed and started Wazuh manager on host.
- Added Laravel application security logging hooks for auth and receptionist forms.
- Connected Laravel log file to Wazuh log analysis.
- Added custom Wazuh rules for brute force, login success, form spam, SQL injection, and XSS.
- Enabled file integrity monitoring for project directory.
- Ran test simulations and verified alert generation in Wazuh alert logs.

## Important platform note

On this host, `wazuh-agent` conflicts with `wazuh-manager` package installation in the same RPM transaction. For single-host monitoring, manager-side local log collection was used and validated. If agent registration is strictly required, use a second host/VM for agent or separate manager and agent roles.

## Wazuh manager installation summary

Repository configured at `/etc/yum.repos.d/wazuh.repo` and manager installed with:

`sudo dnf install -y wazuh-manager --best --allowerasing --setopt=install_weak_deps=False`

Service status validated as `active`.

## Laravel integration summary

Security event generation in app:

- `LOGIN_FAILED` and `LOGIN_SUCCESS`
- `FORM_SUBMIT`
- `SQLI_DETECTED ' OR 1=1--`
- `XSS_DETECTED <script>alert(1)</script>`
- `FORM_SPAM_DETECTED`

Centralized detector service:

- `app/Services/SecurityMonitoringService.php`

Monitored submit/login entry points updated:

- `app/Livewire/Pages/Auth/Login.php`
- `app/Livewire/Pages/Receptionist/DocPackForm.php`
- `app/Livewire/Pages/Receptionist/Package.php`
- `app/Livewire/Pages/Receptionist/Guestbook.php`
- `app/Livewire/Pages/Receptionist/Documents.php`
- `app/Livewire/Pages/Receptionist/Bookingvehicle.php`

## Wazuh configuration applied

### ossec.conf

- Added localfile monitor for:
  `/home/clemoryn/Documents/GitHub/caps-test/storage/logs/laravel.log`
- Added syscheck directory:
  `/home/clemoryn/Documents/GitHub/caps-test`

### local_rules.xml

Custom rules deployed with IDs:

- 100100 Brute force login detected
- 100101 Login success
- 100102 Form spam detected
- 100103 SQL Injection attempt
- 100104 XSS attempt

Correlation helpers added for frequency logic:

- 100110 base LOGIN_FAILED
- 100120 base FORM_SUBMIT

## Test execution and evidence

Simulated log events were appended to `storage/logs/laravel.log` for:

1. 5 failed login attempts from `10.10.10.5`
2. 1 login success from `10.10.10.5`
3. 10 form submissions from `10.10.10.6`
4. SQLi payload `' OR 1=1--` from `10.10.10.7`
5. XSS payload `<script>alert(1)</script>` from `10.10.10.8`

Verified in `/var/ossec/logs/alerts/alerts.log`:

- Rule 100101 -> Login success
- Rule 100100 -> Brute force login detected
- Rule 100102 -> Form spam detected
- Rule 100103 -> SQL Injection attempt
- Rule 100104 -> XSS attempt

IPs were present in matching alert lines:

- `10.10.10.5`
- `10.10.10.6`
- `10.10.10.7`
- `10.10.10.8`

## Real-time verification command

`sudo tail -f /var/ossec/logs/alerts/alerts.log`

## Files created in repository

- `app/Services/SecurityMonitoringService.php`
- `local_rules.wazuh.xml`
- `docs/WAZUH_IMPLEMENTATION_REPORT.md`
