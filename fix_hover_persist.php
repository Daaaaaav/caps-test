<?php

$layouts = [
    __DIR__ . '/resources/views/layouts/receptionist.blade.php',
    __DIR__ . '/resources/views/layouts/admin.blade.php',
    __DIR__ . '/resources/views/layouts/superadmin.blade.php',
];

foreach ($layouts as $path) {
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // First, let's remove the previous matches(':hover') logic
    $oldHoverLogic = <<<'JS'
            // Check if cursor is already hovering over sidebar upon load (page transitions)
            setTimeout(() => {
                const sidebar = document.querySelector('.sidebar-unified');
                if (sidebar && sidebar.matches(':hover') && !this.sidebarLocked && !this.isMobile) {
                    this.sidebarCollapsed = false;
                }
            }, 100);
JS;
    $content = str_replace($oldHoverLogic, "", $content);

    // Now, update init() to handle sessionStorage
    $searchInit = <<<'JS'
        init() {
            // Restore lock state from localStorage
            const saved = localStorage.getItem(
JS;

    // We can't do a simple replace on localStorage name since it varies per layout (receptionist-sidebar-locked vs superadmin-sidebar-locked).
    // Instead we can inject it right after setting the initial state from localStorage.
    // Let's find:
    //                 if (val) this.sidebarCollapsed = false;
    //                 if (!val) this.sidebarCollapsed = true;
    //             });

    $searchWatchEnd = <<<'JS'
                if (!val) this.sidebarCollapsed = true;
            });
JS;

    $replacementWatchEnd = <<<'JS'
                if (!val) this.sidebarCollapsed = true;
            });

            // Check if we just navigated from a sidebar link
            if (!this.sidebarLocked && !this.isMobile) {
                if (sessionStorage.getItem('sidebar-navigated') === 'true') {
                    this.sidebarCollapsed = false;
                    sessionStorage.removeItem('sidebar-navigated');
                }
            }

            // Bind click listener to sidebar links to set the flag
            this.$nextTick(() => {
                const sidebar = document.querySelector('.sidebar-unified');
                if (sidebar) {
                    sidebar.addEventListener('click', (e) => {
                        if (e.target.closest('a')) {
                            sessionStorage.setItem('sidebar-navigated', 'true');
                        }
                    });
                }
            });
JS;

    if (strpos($content, $searchWatchEnd) !== false) {
        $content = str_replace($searchWatchEnd, $replacementWatchEnd, $content);
        file_put_contents($path, $content);
    }
}
echo "Done\n";
