$f = "c:\laragon\www\KRB-System-Caps-main\Capstone-copy\resources\views\livewire\pages\receptionist\bookings-approval.blade.php"
$lines = [System.IO.File]::ReadAllLines($f, [System.Text.Encoding]::UTF8)
$script = @"

<script>
(function() {
    function getBanner() {
        var b = document.getElementById('__kd__');
        if (!b) {
            b = document.createElement('div');
            b.id = '__kd__';
            b.style.cssText = 'position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#0f172a;color:#86efac;font-size:11px;font-family:monospace;padding:8px 12px;max-height:220px;overflow:auto;white-space:pre;line-height:1.6;border-top:2px solid #4ade80;';
            document.body.appendChild(b);
        }
        return b;
    }

    function wireIds() {
        var els = document.querySelectorAll('[wire\\:id]');
        var out = [];
        els.forEach(function(el) {
            out.push(el.tagName + ' wire:id=' + el.getAttribute('wire:id') + ' component=' + (el.getAttribute('wire:id') || '?'));
        });
        return out;
    }

    window.addEventListener('load', function() {
        var ids = wireIds();
        getBanner().textContent = '=== PAGE LOAD ===\nwire:id elements (' + ids.length + '):\n' + ids.join('\n') + '\n\nLivewire components registered: ' + (window.Livewire ? Object.keys(window.Livewire.__instance_cache || {}).length : 'N/A');
    });

    document.addEventListener('click', function(e) {
        var el = document.elementFromPoint(e.clientX, e.clientY);
        // Find closest wire:id ancestor
        var wireEl = null;
        var n = el;
        while (n && n !== document.documentElement) {
            if (n.hasAttribute && n.hasAttribute('wire:id')) { wireEl = n; break; }
            n = n.parentElement;
        }
        // Find wire:click
        var wc = null;
        n = el;
        while (n && n !== document.body) {
            if (n.getAttribute && n.getAttribute('wire:click')) { wc = n.getAttribute('wire:click'); break; }
            n = n.parentElement;
        }
        var ids = wireIds();
        var msg = [
            '=== CLICK on ' + el.tagName + ' ===',
            'wire:click found: ' + (wc || 'NONE'),
            'closest wire:id ancestor: ' + (wireEl ? wireEl.getAttribute('wire:id') : 'NONE - button is OUTSIDE all components!'),
            'all wire:id on page (' + ids.length + '): ' + ids.map(function(s){return s.split(' ')[2];}).join(', '),
        ];
        // Try calling Livewire directly
        if (wireEl && window.Livewire) {
            try {
                var comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
                msg.push('Livewire.find() result: ' + (comp ? 'FOUND component ' + comp.id : 'NULL - component not registered!'));
            } catch(err) {
                msg.push('Livewire.find() ERROR: ' + err.message);
            }
        } else if (!wireEl) {
            msg.push('ERROR: clicked button has NO wire:id ancestor - click will be ignored by Livewire!');
        }
        getBanner().textContent = msg.join('\n');
    }, true);
})();
"@
$newLines = $lines + $script.Split("`n")
[System.IO.File]::WriteAllLines($f, $newLines, [System.Text.Encoding]::UTF8)
Write-Host "Done. Lines: $($newLines.Length)"
