# ⚡ QUICK TEST - SQL Injection Detection

## 🚀 Fastest Way to Test (30 seconds)

### Option 1: PowerShell (Recommended)

```powershell
# Step 1: Navigate to project
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy

# Step 2: Run automated tests
.\test-direct-injection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"

# Step 3: Check logs
type storage\logs\laravel.log | findstr /i "SQLI_DETECTED"
```

### Option 2: Single cURL Command

```bash
curl -X POST "https://your-ngrok-url.ngrok.io/login" ^
  -d "email=admin' OR '1'='1" ^
  -d "password=test"
```

---

## ✅ What Should Happen

### If Security is Working:

**1. Command Response:**
```
Status Code: 403 Forbidden
```

**2. Laravel Log Shows:**
```
[timestamp] local.WARNING: SQLI_DETECTED ' OR 1=1--
```

**3. Wazuh Dashboard:**
- New Level 12 alert appears
- Contains "SQLI_DETECTED"

**4. AI Security Reports:**
- Attack listed with IP and timestamp

---

## 📝 Copy-Paste Test Commands

### Test 1: Classic SQL Injection
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/login" -Method POST -Body @{email="admin' OR '1'='1"; password="test"}
```

### Test 2: DROP TABLE Attempt
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/login" -Method POST -Body @{email="admin'; DROP TABLE users--"; password="test"}
```

### Test 3: UNION SELECT
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/login" -Method POST -Body @{email="admin' UNION SELECT NULL--"; password="test"}
```

---

## 🔍 Quick Verification

```powershell
# Check if attacks were logged
type storage\logs\laravel.log | findstr /i "SQLI_DETECTED"

# View last 10 log entries
powershell "Get-Content storage\logs\laravel.log -Tail 10"

# Count total detections
powershell "(Select-String -Path storage\logs\laravel.log -Pattern 'SQLI_DETECTED').Count"
```

---

## ⚠️ Troubleshooting

### Problem: Connection refused
**Solution:** Make sure your app is running
```bash
php artisan serve
```

### Problem: No logs appear
**Solution:** Check file permissions
```bash
icacls storage\logs /grant Everyone:(OI)(CI)F /T
```

### Problem: 419 CSRF error
**Solution:** This is expected for direct requests. Check logs anyway - pattern might still be detected.

---

## 📚 Full Documentation

- **Quick Start:** `START_HERE.md`
- **Bypass Browser Forms:** `docs/BYPASS_BROWSER_TESTING.md`
- **Comprehensive Guide:** `docs/PENETRATION_TEST_GUIDE.md`
- **Advanced Tests:** `docs/ADVANCED_SQLI_TESTS.md`

---

## 🎯 Replace Your ngrok URL

Before running, replace `https://your-ngrok-url.ngrok.io` with your actual URL:

1. Check your ngrok terminal for the URL
2. Example: `https://abc123.ngrok-free.app`
3. Update commands with your actual URL

---

**Testing Time:** 30 seconds  
**Documentation Time:** 2 minutes  
**Full Verification:** 5 minutes
