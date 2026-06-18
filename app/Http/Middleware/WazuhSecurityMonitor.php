<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WazuhSecurityMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        $ip = $request->ip() ?? '127.0.0.1';
        $location = $request->path();
        
        // 1. Check for SQL Injection
        $sqliPattern = '/(union.*select|select.*from|insert.*into|drop.*table|update.*set|delete.*from|\bor\b\s+1\s*=\s*1|--\s*$)/i';
        if ($this->detectPattern($input, $sqliPattern)) {
            Log::info("level 12 srcip: {$ip} location: /{$location} -> SQLI_DETECTED");
            abort(403, 'Forbidden: Malicious activity detected.');
        }

        // 2. Check for XSS
        $xssPattern = '/(<script|javascript:|onerror=|onload=|eval\(|document\.cookie)/i';
        if ($this->detectPattern($input, $xssPattern)) {
            Log::info("level 12 srcip: {$ip} location: /{$location} -> XSS_DETECTED");
            abort(403, 'Forbidden: Malicious activity detected.');
        }

        // 3. Check for Command Injection
        $cmdPattern = '/(\||;|&|`|\$|\|\|)\s*(ls|cat|whoami|id|pwd|wget|curl|echo|ping|bash|sh|php)/i';
        if ($this->detectPattern($input, $cmdPattern)) {
            Log::info("level 12 srcip: {$ip} location: /{$location} -> COMMAND_INJECTION");
            abort(403, 'Forbidden: Malicious activity detected.');
        }

        // 4. Check for File Upload Attacks
        foreach ($request->allFiles() as $file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    if ($this->isFileMalicious($f)) {
                        Log::info("level 12 srcip: {$ip} location: /{$location} -> FILE_UPLOAD_ATTACK");
                        abort(403, 'Forbidden: Malicious file upload detected.');
                    }
                }
            } else {
                if ($this->isFileMalicious($file)) {
                    Log::info("level 12 srcip: {$ip} location: /{$location} -> FILE_UPLOAD_ATTACK");
                    abort(403, 'Forbidden: Malicious file upload detected.');
                }
            }
        }

        return $next($request);
    }

    private function detectPattern(array $input, string $pattern): bool
    {
        $detected = false;
        array_walk_recursive($input, function ($item) use ($pattern, &$detected) {
            if (is_string($item) && preg_match($pattern, $item)) {
                $detected = true;
            }
        });
        return $detected;
    }

    private function isFileMalicious($file): bool
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $maliciousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'sh', 'exe', 'bat', 'cmd', 'cgi', 'pl'];

        return in_array($extension, $maliciousExtensions);
    }
}
