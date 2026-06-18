# Bypassing Browser Form Validation for Security Testing

## 🎯 Purpose

This guide shows you how to send SQL injection payloads **directly to the server**, bypassing browser-side validation and form constraints. This is essential for testing if your **server-side security monitoring (Wazuh)** actually detects attacks.

## ⚠️ Why Bypass Browser Forms?

When you type `admin'--` in a browser login form:
- ❌ HTML5 validation blocks it (invalid email format)
- ❌ JavaScript validation might block it
- ❌ reCAPTCHA might block submission
- ❌ **The request never reaches the server!**

**Result:** You can't test if Wazuh detects the attack because the attack never arrives!

## ✅ Solution: Direct HTTP Requests

Send raw HTTP POST requests directly to the server, completely bypassing the browser form.

---

## 🚀 Method 1: PowerShell (Easiest - Windows)

### Quick Test (Single Command)

```powershell
Invoke-WebRequest -Uri "https://your-ngrok-url.ngrok.io/login" `
  -Method POST `
  -Body @{email="admin' OR '1'='1"; password="test"} `
  -UseBasicParsing
```

### Automated Test Suite

```powershell
# Navigate to project
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy

# Run comprehensive tests
.\test-direct-injection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"
```

**What this does:**
- ✅ Sends 11 different SQL injection payloads
- ✅ Bypasses all browser validation
- ✅ Shows which attacks are blocked (403)
- ✅ Offers to view logs immediately

### Manual PowerShell Examples

```powershell
# Classic SQL Injection
Invoke-WebRequest -Uri "http://localhost:8000/login" `
  -Method POST `
  -Body @{
    email = "admin' OR '1'='1"
    password = "test123"
  }

# DROP TABLE Attempt
Invoke-WebRequest -Uri "http://localhost:8000/login" `
  -Method POST `
  -Body @{
    email = "admin'; DROP TABLE users--"
    password = "test"
  }

# UNION SELECT Injection
Invoke-WebRequest -Uri "http://localhost:8000/login" `
  -Method POST `
  -Body @{
    email = "admin' UNION SELECT NULL,NULL,NULL--"
    password = "test"
  }

# XSS Test
Invoke-WebRequest -Uri "http://localhost:8000/login" `
  -Method POST `
  -Body @{
    email = "<script>alert('XSS')</script>"
    password = "test"
  }
```

---

## 🚀 Method 2: cURL (Cross-platform)

### Quick Test

```bash
curl -X POST "https://your-ngrok-url.ngrok.io/login" \
  -d "email=admin' OR '1'='1" \
  -d "password=test123"
```

### Automated Test Suite

```batch
REM Windows batch file
test-direct-injection.bat https://your-ngrok-url.ngrok.io
```

### Manual cURL Examples

```bash
# Classic SQL Injection
curl -X POST "http://localhost:8000/login" \
  -d "email=admin' OR '1'='1" \
  -d "password=test"

# With verbose output
curl -v -X POST "http://localhost:8000/login" \
  -d "email=admin'--" \
  -d "password=test"

# Show only HTTP status code
curl -w "\nHTTP Status: %{http_code}\n" \
  -X POST "http://localhost:8000/login" \
  -d "email=admin' UNION SELECT NULL--" \
  -d "password=test" \
  -s -o /dev/null

# Multiple tests in sequence
curl -X POST "http://localhost:8000/login" -d "email=admin' OR '1'='1" -d "password=test"
curl -X POST "http://localhost:8000/login" -d "email=admin'--" -d "password=test"
curl -X POST "http://localhost:8000/login" -d "email=admin' UNION SELECT NULL--" -d "password=test"
```

---

## 🚀 Method 3: Postman (GUI - User Friendly)

### Setup Steps

1. **Download Postman** (free)
   - Visit: https://www.postman.com/downloads/
   - Install for Windows

2. **Create New Request**
   - Click **New** → **HTTP Request**
   - Set method to **POST**
   - Enter URL: `https://your-ngrok-url.ngrok.io/login`

3. **Configure Body**
   - Click **Body** tab
   - Select **x-www-form-urlencoded**
   - Add parameters:
     - Key: `email`, Value: `admin' OR '1'='1`
     - Key: `password`, Value: `test123`

4. **Send Request**
   - Click **Send** button
   - View response (should be 403 if blocked)

5. **Create Test Collection**
   - Save multiple requests with different SQLi payloads
   - Run entire collection at once

### Postman Collection (Import This)

Save as `KRB-SQLi-Tests.postman_collection.json`:

```json
{
  "info": {
    "name": "KRB SQL Injection Tests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "SQLi - Classic OR",
      "request": {
        "method": "POST",
        "header": [],
        "body": {
          "mode": "urlencoded",
          "urlencoded": [
            {"key": "email", "value": "admin' OR '1'='1", "type": "text"},
            {"key": "password", "value": "test123", "type": "text"}
          ]
        },
        "url": {
          "raw": "{{base_url}}/login",
          "host": ["{{base_url}}"],
          "path": ["login"]
        }
      }
    },
    {
      "name": "SQLi - Comment Based",
      "request": {
        "method": "POST",
        "header": [],
        "body": {
          "mode": "urlencoded",
          "urlencoded": [
            {"key": "email", "value": "admin'--", "type": "text"},
            {"key": "password", "value": "test", "type": "text"}
          ]
        },
        "url": {
          "raw": "{{base_url}}/login",
          "host": ["{{base_url}}"],
          "path": ["login"]
        }
      }
    },
    {
      "name": "SQLi - UNION SELECT",
      "request": {
        "method": "POST",
        "header": [],
        "body": {
          "mode": "urlencoded",
          "urlencoded": [
            {"key": "email", "value": "admin' UNION SELECT NULL,NULL,NULL--", "type": "text"},
            {"key": "password", "value": "test", "type": "text"}
          ]
        },
        "url": {
          "raw": "{{base_url}}/login",
          "host": ["{{base_url}}"],
          "path": ["login"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    }
  ]
}
```

---

## 🚀 Method 4: Browser DevTools Console (No Extra Software)

### Steps

1. **Open your login page** in browser
2. **Press F12** to open Developer Tools
3. **Go to Console tab**
4. **Paste and execute this JavaScript:**

```javascript
// Function to send SQL injection payload
async function testSQLi(email, password) {
  try {
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: new URLSearchParams({
        email: email,
        password: password
      })
    });
    
    console.log(`Status: ${response.status} ${response.statusText}`);
    
    if (response.status === 403) {
      console.log('✅ BLOCKED - Security monitoring working!');
    } else {
      console.log('⚠️ Not blocked - check logs');
    }
    
    return response;
  } catch (error) {
    console.error('Error:', error);
  }
}

// Run tests
console.log('🧪 Testing SQL Injection Detection...\n');

// Test 1
await testSQLi("admin' OR '1'='1", "test");

// Test 2
await testSQLi("admin'--", "test");

// Test 3
await testSQLi("admin' UNION SELECT NULL--", "test");

// Test 4
await testSQLi("<script>alert('XSS')</script>", "test");

console.log('\n✅ Tests complete! Check Network tab for details.');
```

### Quick One-Liner Tests

```javascript
// Single test
fetch('/login', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'email=admin%27+OR+%271%27%3D%271&password=test'
}).then(r => console.log(r.status));
```

---

## 🚀 Method 5: Python Requests (Programming)

### Simple Python Script

Save as `test_sqli.py`:

```python
import requests

BASE_URL = "http://localhost:8000"

payloads = [
    {"email": "admin' OR '1'='1", "password": "test"},
    {"email": "admin'--", "password": "test"},
    {"email": "admin' UNION SELECT NULL--", "password": "test"},
    {"email": "<script>alert('XSS')</script>", "password": "test"},
]

print("🧪 Testing SQL Injection Detection\n")

for i, payload in enumerate(payloads, 1):
    print(f"Test {i}: {payload['email']}")
    
    try:
        response = requests.post(f"{BASE_URL}/login", data=payload)
        
        if response.status_code == 403:
            print(f"  ✅ BLOCKED (403)")
        elif response.status_code == 422:
            print(f"  ⚠️  Validation Error (422)")
        else:
            print(f"  ❌ Status: {response.status_code}")
    except Exception as e:
        print(f"  ❌ Error: {e}")
    
    print()

print("✅ Tests complete!")
```

Run with:
```bash
python test_sqli.py
```

---

## 🚀 Method 6: Burp Suite (Professional Penetration Testing)

### Setup

1. **Download Burp Suite Community** (free)
   - https://portswigger.net/burp/communitydownload

2. **Configure Proxy**
   - Burp: Proxy → Options → Listen on 127.0.0.1:8080
   - Browser: Set proxy to 127.0.0.1:8080

3. **Intercept Request**
   - Burp: Proxy → Intercept → Intercept is on
   - Browser: Try to login normally
   - Burp: Request appears in Intercept tab

4. **Modify Request**
   - Change email field to: `admin' OR '1'='1`
   - Click **Forward**

5. **Automated Testing**
   - Right-click request → Send to Intruder
   - Set attack positions (email field)
   - Load SQL injection payload list
   - Start attack

---

## 📊 Real-Time Monitoring Setup

### 3-Terminal Setup (Recommended)

**Terminal 1: Run Tests**
```powershell
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy
.\test-direct-injection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"
```

**Terminal 2: Watch Laravel Logs**
```powershell
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy
Get-Content storage\logs\laravel.log -Wait -Tail 20
```

**Terminal 3: Monitor ngrok**
- Keep ngrok terminal visible
- Watch for incoming POST /login requests
- Note status codes (403 = blocked, 422 = validation error)

---

## ✅ Expected Results

### Successful Detection

```
Testing: Classic OR-based SQLi
  Email: admin' OR '1'='1
  ✅ PASS: Blocked with 403 Forbidden
```

**Laravel Log:**
```
[2026-06-18 14:23:45] local.WARNING: SQLI_DETECTED ' OR 1=1-- 
{"ip":"192.168.1.100","path":"login","event":"login","field":"email","payload":"admin' OR '1'='1"}
```

**Wazuh Dashboard:**
- Alert appears
- Level: 12 (High severity)
- Description: SQLI_DETECTED
- Source IP: Your IP

**AI Security Reports:**
- Attack listed in dashboard
- Timestamp matches test time
- Payload visible

---

## 🎯 Quick Reference: Copy-Paste Commands

### PowerShell (Windows)
```powershell
# Single test
Invoke-WebRequest -Uri "http://localhost:8000/login" -Method POST -Body @{email="admin' OR '1'='1"; password="test"}

# Full suite
.\test-direct-injection.ps1
```

### cURL (Windows CMD)
```batch
curl -X POST "http://localhost:8000/login" -d "email=admin' OR '1'='1" -d "password=test"
```

### cURL (Linux/Mac)
```bash
curl -X POST "http://localhost:8000/login" \
  -d "email=admin' OR '1'='1" \
  -d "password=test"
```

### Browser Console
```javascript
fetch('/login', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'email=admin%27+OR+%271%27%3D%271&password=test'}).then(r => console.log(r.status))
```

---

## 🔍 Verification Checklist

After sending direct requests, verify:

- [ ] Terminal shows response (403, 422, or other)
- [ ] Laravel log contains SQLI_DETECTED
- [ ] Wazuh dashboard shows new alert
- [ ] AI Security Reports displays the attempt
- [ ] Normal login still works with valid credentials

### Check Commands

```powershell
# Check Laravel logs
type storage\logs\laravel.log | findstr /i "SQLI_DETECTED"

# View last 20 log entries
Get-Content storage\logs\laravel.log -Tail 20

# Count detections
(Select-String -Path storage\logs\laravel.log -Pattern "SQLI_DETECTED").Count
```

---

## 🎓 Understanding the Flow

### With Browser Form (Doesn't Work)
```
You type: admin'--
    ↓
Browser validates: ❌ Invalid email
    ↓
JavaScript blocks: ❌ Not sent
    ↓
❌ Server never sees the attack!
❌ Wazuh has nothing to detect!
```

### With Direct HTTP Request (Works!)
```
PowerShell/cURL sends: admin'--
    ↓
Server receives: ✅ Direct to Laravel
    ↓
WazuhSecurityMonitor: ✅ Detects pattern
    ↓
Logs to file: ✅ SQLI_DETECTED
    ↓
Wazuh agent reads: ✅ Creates alert
    ↓
Dashboard shows: ✅ Attack visible
```

---

## ❓ FAQ

### Q: Why do I get 419 errors?
**A:** CSRF token missing/invalid. This is expected for direct requests. The middleware should still detect SQL injection patterns.

### Q: Why do I get 422 errors?
**A:** Validation errors (invalid email format). This is Laravel's validation layer working.

### Q: Should all tests return 403?
**A:** Not necessarily. Some might return 422 (validation) or 419 (CSRF). The important thing is checking if **SQLI_DETECTED appears in logs**.

### Q: How do I know if Wazuh actually detected it?
**A:** Check three places:
1. Laravel log: `SQLI_DETECTED` entry
2. Wazuh dashboard: New Level 12 alert
3. AI Security Reports: Attack listed

### Q: Can I test on production?
**A:** ⚠️ **NO!** Only test on:
- Your local development environment
- Staging/testing environment
- With proper authorization

---

## 🚨 Security Reminder

**Only test systems you own or have explicit permission to test!**

This guide is for:
- ✅ Educational purposes
- ✅ Testing your own application
- ✅ Authorized security assessments
- ✅ Verifying security monitoring

Not for:
- ❌ Attacking third-party websites
- ❌ Unauthorized penetration testing
- ❌ Malicious purposes
- ❌ Production systems without approval

---

**Document Version:** 1.0  
**Last Updated:** June 18, 2026  
**Purpose:** Security testing education - Authorized testing only
