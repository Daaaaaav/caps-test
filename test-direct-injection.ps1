# Direct SQL Injection Testing (Bypasses Browser Form Validation)
# This script sends raw HTTP requests directly to the server

param(
    [Parameter(Mandatory=$false)]
    [string]$BaseUrl = "http://localhost:8000"
)

Write-Host ""
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host " Direct HTTP Request Testing" -ForegroundColor Cyan
Write-Host " Bypassing Browser Form Validation" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Target: $BaseUrl" -ForegroundColor White
Write-Host "Method: Direct POST requests (no browser)" -ForegroundColor White
Write-Host ""

# Get CSRF token first (if needed)
Write-Host "Step 1: Getting CSRF token..." -ForegroundColor Yellow
try {
    $loginPage = Invoke-WebRequest -Uri "$BaseUrl/login" -SessionVariable session -UseBasicParsing
    $csrfToken = ""
    
    # Try to extract CSRF token from response
    $pattern = 'name="_token"\s+value="([^"]+)"'
    if ($loginPage.Content -match $pattern) {
        $csrfToken = $matches[1]
        Write-Host "  ✓ CSRF token obtained" -ForegroundColor Green
    } else {
        Write-Host "  ! No CSRF token found (may not be required)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ! Could not get CSRF token" -ForegroundColor Yellow
    $session = $null
}

Write-Host ""
Write-Host "Step 2: Sending SQL Injection Payloads..." -ForegroundColor Yellow
Write-Host ""

$testCases = @(
    @{
        name = "1. Classic OR-based SQLi"
        email = "admin' OR '1'='1"
        password = "test123"
        description = "Most common SQL injection pattern"
    },
    @{
        name = "2. Comment-based SQLi"
        email = "admin'--"
        password = "anything"
        description = "Using SQL comments to bypass logic"
    },
    @{
        name = "3. UNION SELECT injection"
        email = "admin' UNION SELECT NULL,NULL,NULL--"
        password = "test"
        description = "Attempting to extract data"
    },
    @{
        name = "4. Boolean-based blind SQLi"
        email = "admin' OR 1=1--"
        password = "test"
        description = "True/false condition testing"
    },
    @{
        name = "5. DROP TABLE attempt"
        email = "admin'; DROP TABLE users--"
        password = "test"
        description = "Destructive SQL command"
    },
    @{
        name = "6. Stacked queries"
        email = "admin'; UPDATE users SET role='admin'--"
        password = "test"
        description = "Multiple SQL statements"
    },
    @{
        name = "7. Time-based blind SQLi"
        email = "admin' AND SLEEP(5)--"
        password = "test"
        description = "Time delay detection"
    },
    @{
        name = "8. SELECT FROM injection"
        email = "admin' OR 1=1; SELECT * FROM users--"
        password = "test"
        description = "Data extraction attempt"
    },
    @{
        name = '9. XSS in email field'
        email = '<script>alert("XSS")</script>'
        password = 'test'
        description = 'Cross-site scripting test'
    },
    @{
        name = '10. SQLi in password field'
        email = 'admin@test.com'
        password = "' OR '1'='1"
        description = 'Testing password field for SQLi'
    },
    @{
        name = "11. Command injection attempt"
        email = "admin; ls -la"
        password = "test"
        description = "OS command injection test"
    }
)

$passed = 0
$failed = 0
$errors = 0

foreach ($test in $testCases) {
    Write-Host "Testing: $($test.name)" -ForegroundColor Cyan
    Write-Host "  Description: $($test.description)" -ForegroundColor Gray
    Write-Host "  Email: $($test.email)" -ForegroundColor Gray
    Write-Host "  Password: $($test.password)" -ForegroundColor Gray
    
    # Build request body
    $body = @{
        email = $test.email
        password = $test.password
    }
    
    if ($csrfToken -ne "") {
        $body["_token"] = $csrfToken
    }
    
    try {
        $params = @{
            Uri = "$BaseUrl/login"
            Method = "POST"
            Body = $body
            ContentType = "application/x-www-form-urlencoded"
            UseBasicParsing = $true
            ErrorAction = "Stop"
            TimeoutSec = 10
        }
        
        if ($session) {
            $params["WebSession"] = $session
        }
        
        $response = Invoke-WebRequest @params
        
        Write-Host "  Status: $($response.StatusCode)" -ForegroundColor Yellow
        
        if ($response.StatusCode -eq 200) {
            Write-Host "  ❌ FAIL: Request succeeded (expected to be blocked)" -ForegroundColor Red
            $failed++
        } elseif ($response.StatusCode -eq 302) {
            Write-Host "  ⚠️  REDIRECT: Might indicate successful login (check logs)" -ForegroundColor Yellow
            $failed++
        } else {
            Write-Host "  ⚠️  Unexpected success response" -ForegroundColor Yellow
            $errors++
        }
        
    } catch {
        $statusCode = $null
        
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode
        }
        
        if ($statusCode -eq 403) {
            Write-Host "  ✅ PASS: Blocked with 403 Forbidden" -ForegroundColor Green
            $passed++
        } elseif ($statusCode -eq 422) {
            Write-Host "  ⚠️  Validation Error (422) - Check if pattern detected" -ForegroundColor Yellow
            $errors++
        } elseif ($statusCode -eq 419) {
            Write-Host "  ⚠️  CSRF Token Error (419) - Expected without token" -ForegroundColor Yellow
            $errors++
        } else {
            Write-Host "  ⚠️  Connection Error: $($_.Exception.Message)" -ForegroundColor Yellow
            $errors++
        }
    }
    
    Write-Host ""
    Start-Sleep -Milliseconds 500
}

# Summary
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host " Test Results Summary" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total Tests: $($passed + $failed + $errors)" -ForegroundColor White
Write-Host "Passed (Blocked): $passed" -ForegroundColor Green
Write-Host "Failed (Not Blocked): $failed" -ForegroundColor Red
Write-Host "Errors/Warnings: $errors" -ForegroundColor Yellow
Write-Host ""

if ($failed -eq 0 -and $errors -lt ($passed + $failed + $errors)) {
    Write-Host "✅ Security monitoring appears to be working!" -ForegroundColor Green
} elseif ($failed -gt 0) {
    Write-Host "⚠️  Some attacks were NOT blocked!" -ForegroundColor Red
} else {
    Write-Host "⚠️  Tests completed with warnings." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host " Verification Steps" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "1. Check Laravel logs for detections:" -ForegroundColor White
Write-Host "   type storage\logs\laravel.log | findstr /i SQLI_DETECTED" -ForegroundColor Gray
Write-Host ""

Write-Host "2. View last 20 log entries:" -ForegroundColor White
Write-Host "   Get-Content storage\logs\laravel.log -Tail 20" -ForegroundColor Gray
Write-Host ""

Write-Host "3. Watch logs in real-time (open new terminal):" -ForegroundColor White
Write-Host "   Get-Content storage\logs\laravel.log -Wait -Tail 10" -ForegroundColor Gray
Write-Host ""

Write-Host "4. Check Wazuh Dashboard:" -ForegroundColor White
Write-Host "   - Navigate to Security Events" -ForegroundColor Gray
Write-Host "   - Filter by Level 12" -ForegroundColor Gray
Write-Host "   - Search for SQLI_DETECTED" -ForegroundColor Gray
Write-Host ""

Write-Host "5. Check AI Security Reports:" -ForegroundColor White
Write-Host "   - Login as superadmin" -ForegroundColor Gray
Write-Host "   - Navigate to AI Security Reports page" -ForegroundColor Gray
Write-Host "   - Verify attacks are listed" -ForegroundColor Gray
Write-Host ""

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# Offer to check logs immediately
$checkLogs = Read-Host "View Laravel logs now? (y/n)"
if ($checkLogs -eq "y" -or $checkLogs -eq "Y") {
    Write-Host ""
    Write-Host "Recent SQLI_DETECTED entries:" -ForegroundColor Cyan
    Write-Host ""
    
    if (Test-Path "storage\logs\laravel.log") {
        $sqliLogs = Select-String -Path "storage\logs\laravel.log" -Pattern "SQLI_DETECTED" -SimpleMatch | Select-Object -Last 10
        
        if ($sqliLogs) {
            $sqliLogs | ForEach-Object { Write-Host $_.Line -ForegroundColor Yellow }
        } else {
            Write-Host "No SQLI_DETECTED entries found in logs." -ForegroundColor Red
            Write-Host "This might indicate:" -ForegroundColor Yellow
            Write-Host "  - Middleware blocked before logging" -ForegroundColor Gray
            Write-Host "  - SecurityMonitoringService not triggered" -ForegroundColor Gray
            Write-Host "  - Patterns not matching detection rules" -ForegroundColor Gray
        }
    } else {
        Write-Host "Log file not found at storage\logs\laravel.log" -ForegroundColor Red
    }
    
    Write-Host ""
}
