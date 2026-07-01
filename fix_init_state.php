<?php

$layouts = [
    __DIR__ . '/resources/views/layouts/receptionist.blade.php',
    __DIR__ . '/resources/views/layouts/admin.blade.php',
    __DIR__ . '/resources/views/layouts/superadmin.blade.php',
];

foreach ($layouts as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Change sidebarCollapsed: false to sidebarCollapsed: true
    // Need to be careful not to replace it inside methods
    $content = preg_replace(
        "/sidebarCollapsed:\s*false,\s*hoverTimeout:/",
        "sidebarCollapsed: true,\n        hoverTimeout:",
        $content
    );

    file_put_contents($path, $content);
}
echo "Done\n";
