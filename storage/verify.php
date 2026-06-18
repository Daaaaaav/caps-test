<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test SQLi
$request = Illuminate\Http\Request::create('/login', 'POST', ['username' => "admin' OR 1=1 --"]);
$response = $kernel->handle($request);
echo "SQLi Response: " . $response->getStatusCode() . "\n";

// Test XSS
$request = Illuminate\Http\Request::create('/login', 'POST', ['username' => "<script>alert(1)</script>"]);
$response = $kernel->handle($request);
echo "XSS Response: " . $response->getStatusCode() . "\n";

// Test Command Injection
$request = Illuminate\Http\Request::create('/login', 'POST', ['username' => "; cat /etc/passwd"]);
$response = $kernel->handle($request);
echo "Command Injection Response: " . $response->getStatusCode() . "\n";

// Test File Upload
file_put_contents(__DIR__.'/test.php', '<?php echo "shell";');
$file = new Illuminate\Http\UploadedFile(
    __DIR__.'/test.php', // path
    'shell.php', // original name
    'application/x-httpd-php', // mime
    null, // error
    true // test mode
);
$request = Illuminate\Http\Request::create('/login', 'POST', [], [], ['file' => $file]);
$response = $kernel->handle($request);
echo "File Upload Response: " . $response->getStatusCode() . "\n";

// Dispatch Failed Event
$event = new Illuminate\Auth\Events\Failed('web', null, []);
for ($i=0; $i<6; $i++) {
    event($event);
}

// Read log file
echo "\nLast 10 lines of laravel.log:\n";
$logFile = __DIR__.'/logs/laravel.log';
if (file_exists($logFile)) {
    $log = file_get_contents($logFile);
    $lines = explode("\n", trim($log));
    echo implode("\n", array_slice($lines, -10));
}
