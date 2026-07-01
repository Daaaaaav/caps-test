<?php

$layouts = [
    __DIR__ . '/resources/views/layouts/receptionist.blade.php',
    __DIR__ . '/resources/views/layouts/admin.blade.php',
    __DIR__ . '/resources/views/layouts/superadmin.blade.php',
];

foreach ($layouts as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // 1. Add hoverTimeout: null to x-data if not there
    if (strpos($content, 'hoverTimeout: null') === false) {
        $content = str_replace(
            "sidebarCollapsed: true,",
            "sidebarCollapsed: true,\n        hoverTimeout: null,",
            $content
        );
        $content = str_replace(
            "sidebarCollapsed: false,",
            "sidebarCollapsed: false,\n        hoverTimeout: null,",
            $content
        );
    }
    
    // 2. Replace sidebarLeave with debounce if not already done
    $oldLeave = <<<'JS'
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                this.sidebarCollapsed = true;
            }
        },
JS;
    $oldLeaveNoComma = <<<'JS'
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                this.sidebarCollapsed = true;
            }
        }
JS;

    $newLeave = <<<'JS'
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                clearTimeout(this.hoverTimeout);
                this.hoverTimeout = setTimeout(() => {
                    this.sidebarCollapsed = true;
                }, 150);
            }
        },
JS;

    $newLeaveNoComma = <<<'JS'
        sidebarLeave() {
            if (!this.sidebarLocked && !this.isMobile) {
                clearTimeout(this.hoverTimeout);
                this.hoverTimeout = setTimeout(() => {
                    this.sidebarCollapsed = true;
                }, 150);
            }
        }
JS;

    $content = str_replace($oldLeave, $newLeave, $content);
    // Be careful, only replace the no-comma version if the with-comma version wasn't found
    if (strpos($content, 'clearTimeout(this.hoverTimeout)') === false || strpos($path, 'receptionist') !== false) {
        // receptionist might already have it because of my previous multi_replace_file_content
    }
    
    // For admin and superadmin, replace enter to clear timeout
    $oldEnter = <<<'JS'
        sidebarEnter() {
            if (!this.sidebarLocked && !this.isMobile) {
                this.sidebarCollapsed = false;
            }
        },
JS;
    $newEnter = <<<'JS'
        sidebarEnter() {
            if (!this.sidebarLocked && !this.isMobile) {
                clearTimeout(this.hoverTimeout);
                this.sidebarCollapsed = false;
            }
        },
JS;
    $content = str_replace($oldEnter, $newEnter, $content);

    file_put_contents($path, $content);
}
echo "Done\n";
