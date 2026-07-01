<?php

$layouts = [
    __DIR__ . '/resources/views/layouts/receptionist.blade.php',
    __DIR__ . '/resources/views/layouts/admin.blade.php',
    __DIR__ . '/resources/views/layouts/superadmin.blade.php',
];

foreach ($layouts as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Check if hoverTimeout is already there
    if (strpos($content, 'hoverTimeout:') === false) {
        $content = str_replace(
            "sidebarCollapsed: false,",
            "sidebarCollapsed: false,\n        hoverTimeout: null,",
            $content
        );
        
        $oldMethods = <<<'JS'
        sidebarEnter() {
            if (!this.sidebarLocked && !this.isMobile) {
                this.sidebarCollapsed = false;
            }
        },
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                this.sidebarCollapsed = true;
            }
        },
JS;

        $newMethods = <<<'JS'
        sidebarEnter() {
            if (!this.sidebarLocked && !this.isMobile) {
                clearTimeout(this.hoverTimeout);
                this.sidebarCollapsed = false;
            }
        },
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                clearTimeout(this.hoverTimeout);
                this.hoverTimeout = setTimeout(() => {
                    this.sidebarCollapsed = true;
                }, 150);
            }
        },
JS;

        $content = str_replace($oldMethods, $newMethods, $content);
        file_put_contents($path, $content);
    }
}
echo "Done\n";
