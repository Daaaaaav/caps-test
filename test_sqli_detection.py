#!/usr/bin/env python3
"""
SQL Injection Detection Test Script
Tests Wazuh monitoring effectiveness for the KRB System

Usage:
    python test_sqli_detection.py https://your-ngrok-url.ngrok.io
    python test_sqli_detection.py  # defaults to http://localhost:8000
"""

import sys
import requests
from typing import List, Dict, Tuple

# ANSI color codes
class Colors:
    CYAN = '\033[96m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    GRAY = '\033[90m'
    WHITE = '\033[97m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

def print_header(title: str):
    """Print a formatted header"""
    print(f"\n{Colors.CYAN}{'='*60}")
    print(f" {title}")
    print(f"{'='*60}{Colors.RESET}\n")

def print_test(name: str, description: str, payload: str):
    """Print test information"""
    print(f"{Colors.YELLOW}Testing: {name}{Colors.RESET}")
    print(f"{Colors.GRAY}  Description: {description}{Colors.RESET}")
    print(f"{Colors.GRAY}  Payload: {payload}{Colors.RESET}")

def run_test(base_url: str, email: str, password: str) -> Tuple[bool, int, str]:
    """
    Run a single injection test
    
    Returns:
        (success, status_code, message)
    """
    try:
        response = requests.post(
            f"{base_url}/login",
            data={
                "email": email,
                "password": password
            },
            allow_redirects=False,
            timeout=10
        )
        
        if response.status_code == 403:
            return (True, 403, "Blocked with 403 Forbidden")
        else:
            return (False, response.status_code, f"Expected 403, got {response.status_code}")
            
    except requests.exceptions.RequestException as e:
        return (None, 0, f"Connection error: {str(e)}")

def test_sql_injections(base_url: str) -> Dict[str, int]:
    """Test SQL injection detection"""
    print_header("SQL Injection Detection Tests")
    print(f"{Colors.WHITE}Target URL: {base_url}{Colors.RESET}\n")
    
    test_cases = [
        {
            "name": "1. Classic OR-based SQLi",
            "email": "admin' OR '1'='1",
            "password": "test123",
            "description": "Tests basic boolean-based SQL injection"
        },
        {
            "name": "2. Comment-based SQLi",
            "email": "admin'--",
            "password": "anything",
            "description": "Tests SQL comment injection"
        },
        {
            "name": "3. UNION-based SQLi",
            "email": "admin' UNION SELECT NULL,NULL,NULL--",
            "password": "test",
            "description": "Tests UNION query injection"
        },
        {
            "name": "4. Boolean SQLi with spaces",
            "email": "admin' OR 1=1--",
            "password": "test",
            "description": "Tests boolean with numeric comparison"
        },
        {
            "name": "5. DROP TABLE attempt",
            "email": "admin'; DROP TABLE users--",
            "password": "test",
            "description": "Tests destructive SQL command"
        },
        {
            "name": "6. SELECT FROM injection",
            "email": "admin' OR 1=1; SELECT * FROM users--",
            "password": "test",
            "description": "Tests data extraction attempt"
        },
        {
            "name": "7. Time-based blind SQLi",
            "email": "admin' AND SLEEP(5)--",
            "password": "test",
            "description": "Tests time-based blind injection"
        },
        {
            "name": "8. Stacked queries",
            "email": "admin'; UPDATE users SET role='admin'--",
            "password": "test",
            "description": "Tests privilege escalation via stacked queries"
        },
        {
            "name": "9. String concatenation SQLi",
            "email": "admin' || '1'='1",
            "password": "test",
            "description": "Tests string concatenation injection"
        },
        {
            "name": "10. WHERE clause injection",
            "email": "admin' AND '1'='1",
            "password": "test",
            "description": "Tests WHERE clause modification"
        }
    ]
    
    results = {"passed": 0, "failed": 0, "errors": 0}
    
    for test in test_cases:
        print_test(test["name"], test["description"], test["email"])
        
        success, status_code, message = run_test(
            base_url, 
            test["email"], 
            test["password"]
        )
        
        if success is True:
            print(f"  {Colors.GREEN}✅ PASSED: {message}{Colors.RESET}\n")
            results["passed"] += 1
        elif success is False:
            print(f"  {Colors.RED}❌ FAILED: {message}{Colors.RESET}")
            print(f"  {Colors.RED}Risk: Injection not detected by security monitor!{Colors.RESET}\n")
            results["failed"] += 1
        else:
            print(f"  {Colors.YELLOW}⚠️  ERROR: {message}{Colors.RESET}\n")
            results["errors"] += 1
    
    return results

def test_xss_attacks(base_url: str) -> Dict[str, int]:
    """Test XSS detection"""
    print_header("XSS Detection Tests (Bonus)")
    
    test_cases = [
        {
            "name": "Script tag injection",
            "email": "<script>alert('XSS')</script>",
            "password": "test",
            "description": "Tests basic script tag injection"
        },
        {
            "name": "Event handler injection",
            "email": "<img src=x onerror=alert(1)>",
            "password": "test",
            "description": "Tests event handler XSS"
        },
        {
            "name": "JavaScript protocol",
            "email": "javascript:alert(document.cookie)",
            "password": "test",
            "description": "Tests JavaScript protocol XSS"
        },
        {
            "name": "SVG-based XSS",
            "email": "<svg onload=alert(1)>",
            "password": "test",
            "description": "Tests SVG element XSS"
        }
    ]
    
    results = {"passed": 0, "failed": 0, "errors": 0}
    
    for test in test_cases:
        print_test(test["name"], test["description"], test["email"])
        
        success, status_code, message = run_test(
            base_url, 
            test["email"], 
            test["password"]
        )
        
        if success is True:
            print(f"  {Colors.GREEN}✅ PASSED: {message}{Colors.RESET}\n")
            results["passed"] += 1
        elif success is False:
            print(f"  {Colors.RED}❌ FAILED: {message}{Colors.RESET}\n")
            results["failed"] += 1
        else:
            print(f"  {Colors.YELLOW}⚠️  ERROR: {message}{Colors.RESET}\n")
            results["errors"] += 1
    
    return results

def print_summary(sql_results: Dict[str, int], xss_results: Dict[str, int]):
    """Print test summary"""
    print_header("Test Summary")
    
    total_passed = sql_results["passed"] + xss_results["passed"]
    total_failed = sql_results["failed"] + xss_results["failed"]
    total_errors = sql_results["errors"] + xss_results["errors"]
    total_tests = total_passed + total_failed + total_errors
    
    print(f"{Colors.WHITE}Total Tests: {total_tests}{Colors.RESET}")
    print(f"{Colors.GREEN}Passed: {total_passed}{Colors.RESET}")
    print(f"{Colors.RED}Failed: {total_failed}{Colors.RESET}")
    print(f"{Colors.YELLOW}Errors: {total_errors}{Colors.RESET}\n")
    
    if total_failed == 0 and total_errors == 0:
        print(f"{Colors.GREEN}✅ All tests passed! Security monitoring is working correctly.{Colors.RESET}")
    elif total_failed > 0:
        print(f"{Colors.RED}⚠️  Some injections were NOT blocked. Review security configuration.{Colors.RESET}")
    else:
        print(f"{Colors.YELLOW}⚠️  Some tests encountered errors. Check application availability.{Colors.RESET}")

def print_next_steps():
    """Print next steps for verification"""
    print_header("Next Steps")
    
    print(f"{Colors.WHITE}1. Check Laravel logs:{Colors.RESET}")
    print(f"{Colors.GRAY}   type storage\\logs\\laravel.log | findstr /i SQLI_DETECTED{Colors.RESET}\n")
    
    print(f"{Colors.WHITE}2. Check Wazuh dashboard:{Colors.RESET}")
    print(f"{Colors.GRAY}   - Navigate to Security Events{Colors.RESET}")
    print(f"{Colors.GRAY}   - Filter by level 12{Colors.RESET}")
    print(f"{Colors.GRAY}   - Search for 'SQLI_DETECTED'{Colors.RESET}\n")
    
    print(f"{Colors.WHITE}3. Check AI Security Reports page:{Colors.RESET}")
    print(f"{Colors.GRAY}   - Login as superadmin{Colors.RESET}")
    print(f"{Colors.GRAY}   - Navigate to AI Security Reports{Colors.RESET}")
    print(f"{Colors.GRAY}   - Verify alerts are displayed{Colors.RESET}\n")
    
    print(f"{Colors.CYAN}{'='*60}{Colors.RESET}\n")

def main():
    """Main entry point"""
    # Get base URL from command line or use default
    if len(sys.argv) > 1:
        base_url = sys.argv[1].rstrip('/')
    else:
        base_url = "http://localhost:8000"
        print(f"{Colors.YELLOW}No URL provided. Using default: {base_url}{Colors.RESET}")
        print(f"{Colors.GRAY}Usage: python test_sqli_detection.py <base_url>{Colors.RESET}")
    
    # Run tests
    sql_results = test_sql_injections(base_url)
    xss_results = test_xss_attacks(base_url)
    
    # Print summary
    print_summary(sql_results, xss_results)
    print_next_steps()

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print(f"\n{Colors.YELLOW}Test interrupted by user{Colors.RESET}")
        sys.exit(1)
    except Exception as e:
        print(f"\n{Colors.RED}Unexpected error: {str(e)}{Colors.RESET}")
        sys.exit(1)
