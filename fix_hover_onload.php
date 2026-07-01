<?php

$layouts = [
    __DIR__ . '/resources/views/layouts/receptionist.blade.php',
    __DIR__ . '/resources/views/layouts/admin.blade.php',
    __DIR__ . '/resources/views/layouts/superadmin.blade.php',
];

foreach ($layouts as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    $oldInit = <<<'JS'
        init() {
            // Restore lock state from localStorage
JS;

    // Use regex to find init() because different layouts might have slight variations, 
    // but they all have `init() {` followed by setting up listener or localStorage.

    // Actually, I can just replace `if (saved === 'true') { ... }` with the appended timeout.
    $replacement = <<<'JS'
                if (!val) this.sidebarCollapsed = true;
            });

            // Check if cursor is already hovering over sidebar upon load (page transitions)
            setTimeout(() => {
                const sidebar = document.querySelector('.sidebar-unified');
                if (sidebar && sidebar.matches(':hover') && !this.sidebarLocked && !this.isMobile) {
                    this.sidebarCollapsed = false;
                }
            }, 100);
        },
JS;

    // The end of the init method in all 3 files is:
    //                 if (val) this.sidebarCollapsed = false;
    //                 if (!val) this.sidebarCollapsed = true;
    //             });
    //         },
    $search = <<<'JS'
                if (!val) this.sidebarCollapsed = true;
            });
        },
JS;

    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replacement, $content);
        file_put_contents($path, $content);
    }
}
echo "Done\n";
