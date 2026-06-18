# 🚀 START HERE: SQL Injection Testing for Wazuh

## Quick Guide to Testing Your Security Monitoring

This guide will help you test if Wazuh is properly detecting SQL injection attempts in your KRB System.

---

## ⚡ Fastest Way to Test (2 minutes)

### Step 1: Run the automated test

```powershell
# Open PowerShell in project directory
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy

# Run the test (replace with your actual ngrok URL)
.\test-sqli-detection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"
```

### Step 2: Check the results

You should see output like:
```
Testing: 1. Classic OR-based SQLi
  ✅ PASSED: Blocked with 403 Forbidden

Testing: 2. Comment-based SQLi
  ✅ PASSED: Blocked with 403 Forbidden
...

Total Tests: 11
Passed: 11 ✅
Failed: 0
```

### Step 3: Verify logs

```powershell
# Check Laravel detected the attacks
type storage\logs\laravel.log | findstr /i "SQLI_DETECTED"
```

**Done!** If all tests pass and logs show detections, your Wazuh monitoring is working correctly! ✅

---

## 📱 Alternative: Manual Browser Test (3 minutes)

### Step 1: Open your login page
Navigate to: `https://your-ngrok-url.ngrok.io/login`

### Step 2: Try a SQL injection
- **Email:** `admin' OR '1'='1`
- **Password:** `anything`
- Click **Login**

### Step 3: Check the result
**Expected:** You should see a **403 Forbidden** page

**✅ If you see 403**: Security is working!  
**❌ If you login**: Security monitoring has issues!

---

## 🎯 What You're Testing

When you send `admin' OR '1'='1`, here's what should happen:

```
Your Browser
    ↓ (sends SQL injection)
Laravel Application
    ↓ (middleware intercepts)
WazuhSecurityMonitor
    ↓ (detects malicious pattern)
    ├─→ Blocks request (403 Forbidden)
    └─→ Logs to Laravel log
         ↓
    Wazuh Agent reads log
         ↓
    Wazuh Manager creates alert
         ↓
    AI Security Reports shows attack
```

---

## ✅ Verification Checklist

After testing, verify these four things:

- [ ] **Browser**: Saw 403 Forbidden error
- [ ] **Laravel Log**: Contains SQLI_DETECTED entries
- [ ] **Wazuh Dashboard**: Shows Level 12 security alerts
- [ ] **AI Reports Page**: Displays the attack attempts

### Check Laravel Logs
```powershell
type storage\logs\laravel.log | findstr /i "SQLI_DETECTED"
```

### Check Wazuh Dashboard
1. Open Wazuh dashboard
2. Go to **Security Events**
3. Filter by **Level 12**
4. Look for recent "SQLI_DETECTED" alerts

### Check AI Security Reports
1. Login as **superadmin**
2. Click **AI Security Reports** in sidebar
3. Verify attacks are listed
4. Check timestamps match your test time

---

## 🛠️ Common Issues & Quick Fixes

### Issue 1: "Command not found" when running script

**Solution:**
```powershell
# Make sure you're in the project directory
cd c:\laragon\www\KRB-System-Caps-main\Capstone-copy

# Check the file exists
dir test-sqli-detection.ps1
```

### Issue 2: Connection error

**Solution:**
```powershell
# Make sure your app is running
php artisan serve

# Or check if Laragon is running
# Open Laragon control panel and verify services are started
```

### Issue 3: All tests fail with connection errors

**Problem:** Application not accessible

**Solutions:**
- Verify ngrok is running: Check ngrok terminal window
- Test localhost first: `.\test-sqli-detection.ps1 -BaseUrl "http://localhost:8000"`
- Check firewall isn't blocking requests

### Issue 4: Tests pass but no logs

**Problem:** Logging not working

**Solution:**
```powershell
# Check log file permissions
icacls storage\logs

# Clear cache
php artisan config:clear
php artisan cache:clear

# Verify .env has APP_DEBUG=true
```

### Issue 5: No Wazuh alerts

**Problem:** Wazuh not monitoring Laravel logs

**Solution:**
1. Check Wazuh agent is running
2. Verify `ossec.conf` includes Laravel log path
3. Restart Wazuh agent

---

## 📚 Full Documentation

For detailed information, see:

| Document | Use Case |
|----------|----------|
| `docs/QUICK_TEST_CHECKLIST.md` | 5-minute quick reference |
| `docs/PENETRATION_TEST_GUIDE.md` | Complete testing methodology |
| `docs/SECURITY_TESTING_README.md` | Overview of all resources |
| `docs/manual-test-page.html` | Browser-based testing tool |

---

## 🎓 Test Payloads to Try

Copy and paste these into the login form:

### SQL Injection
```
admin' OR '1'='1          ← Classic SQLi
admin'--                  ← Comment-based
admin' OR 1=1--           ← Boolean-based
' UNION SELECT NULL--     ← UNION-based
admin'; DROP TABLE x--    ← Destructive attempt
```

### XSS (Cross-Site Scripting)
```
<script>alert('XSS')</script>
<img src=x onerror=alert(1)>
javascript:alert(1)
```

**Expected Result for ALL:** `403 Forbidden`

---

## 📊 Understanding Success

### ✅ GOOD - All tests passing
```
Total Tests: 11
Passed: 11
Failed: 0
Errors: 0
```
**Meaning:** Security monitoring is working perfectly!

### ⚠️ MIXED - Some failures
```
Total Tests: 11
Passed: 8
Failed: 3
Errors: 0
```
**Meaning:** Some attack patterns not detected. Review WazuhSecurityMonitor patterns.

### ❌ BAD - All tests failing
```
Total Tests: 11
Passed: 0
Failed: 11
Errors: 0
```
**Meaning:** Middleware not active or security monitoring disabled. Check configuration.

### 🔌 CONNECTION - Error connecting
```
Total Tests: 11
Passed: 0
Failed: 0
Errors: 11
```
**Meaning:** Application not accessible. Check if server is running.

---

## 🎯 Next Steps After Testing

### If all tests pass ✅
1. Document your results
2. Schedule regular testing
3. Monitor Wazuh dashboard daily
4. Test with different payload variations

### If some tests fail ⚠️
1. Review detection patterns in `WazuhSecurityMonitor.php`
2. Add missing patterns to regex
3. Re-run tests to verify fixes
4. Document new patterns

### If no tests pass ❌
1. Verify middleware is registered
2. Check routes have middleware applied
3. Clear all caches
4. Review error logs
5. Consult troubleshooting guide

---

## 🔐 Important Reminders

1. **Only test your own systems** - Don't test systems you don't own!
2. **Document your testing** - Keep records of what you test
3. **Test regularly** - Security monitoring can break with updates
4. **Monitor alerts** - Check Wazuh dashboard daily
5. **Update patterns** - Add new attack patterns as they emerge

---

## 📞 Getting Help

If you're stuck:

1. **Check logs first:**
   ```powershell
   type storage\logs\laravel.log | Select-Object -Last 20
   ```

2. **Review middleware:**
   ```powershell
   type app\Http\Middleware\WazuhSecurityMonitor.php
   ```

3. **Check Wazuh agent:**
   ```powershell
   Get-Service WazuhSvc  # Windows
   ```

4. **Read full documentation:**
   - `docs/PENETRATION_TEST_GUIDE.md`
   - `docs/SECURITY_TESTING_README.md`

---

## ⏱️ Time Estimates

| Activity | Time |
|----------|------|
| Automated test (PowerShell) | 2 minutes |
| Manual browser test | 3 minutes |
| Full verification (all 4 checks) | 5 minutes |
| Complete documentation review | 30 minutes |
| Troubleshooting issues | 15-60 minutes |

---

## 🎉 Quick Win Checklist

For a complete test in 10 minutes:

1. ⏱️ **2 min** - Run PowerShell script
2. ⏱️ **2 min** - Check Laravel logs
3. ⏱️ **3 min** - Verify Wazuh dashboard
4. ⏱️ **3 min** - Check AI Security Reports page

**Total: 10 minutes to full confidence in your security monitoring!**

---

## 🚀 Ready to Start?

Choose your path:

### Path A: Automated (Recommended)
```powershell
.\test-sqli-detection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"
```

### Path B: Manual
1. Open login page
2. Enter: `admin' OR '1'='1`
3. Verify 403 error

### Path C: Interactive
1. Open `docs/manual-test-page.html` in browser
2. Click test buttons
3. View results

**Pick one and start testing now!** 🎯

---

**Questions?** Check `docs/SECURITY_TESTING_README.md` for complete documentation.

**Document Version:** 1.0  
**Last Updated:** June 18, 2026
