$f = "c:\laragon\www\KRB-System-Caps-main\Capstone-copy\resources\views\livewire\pages\receptionist\bookings-approval.blade.php"
$lines = [System.IO.File]::ReadAllLines($f, [System.Text.Encoding]::UTF8)
$script = @"

<script>
(function() {
    var errors = [];
    window.onerror = function(msg, src, line) { errors.push(msg + ' @ ' + (src||'').split('/').pop() + ':' + line); showBanner(); };
    function getBanner() {
        var b = document.getElementById('__kd__');
        if (!b) { b = document.createElement('div'); b.id='__kd__'; b.style.cssText='position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#0f172a;color:#7dd3fc;font-size:11px;font-family:monospace;padding:8px 12px;max-height:200px;overflow:auto;white-space:pre;line-height:1.6;border-top:2px solid #f87171;'; document.body.appendChild(b); }
        return b;
    }
    function showBanner() {
        var lw = window.Livewire ? 'YES' : 'NO'; var al = window.Alpine ? 'YES' : 'NO';
        var wi = document.querySelectorAll('[wire\\:id]').length;
        getBanner().textContent = 'LW:'+lw+' Alpine:'+al+' wire:id count:'+wi+'\n'+(errors.length?errors.join('\n'):'No JS errors');
    }
    document.addEventListener('click', function(e) {
        var el = document.elementFromPoint(e.clientX, e.clientY);
        var wc = null; var n = el; while(n&&n!==document.body){if(n.getAttribute&&n.getAttribute('wire:click')){wc=n.getAttribute('wire:click');break;}n=n.parentElement;}
        var lw = window.Livewire ? 'YES' : 'NO'; var al = window.Alpine ? 'YES' : 'NO';
        var wi = document.querySelectorAll('[wire\\:id]').length;
        getBanner().textContent = 'CLICK:'+el.tagName+' wire:click='+(wc||'NONE')+' LW:'+lw+' Al:'+al+' wire:id:'+wi+'\n'+(errors.length?errors.join('\n'):'No JS errors');
    }, true);
    var origFetch = window.fetch;
    window.fetch = function(url, opts) {
        var p = origFetch.apply(this, arguments);
        if (url && String(url).indexOf('livewire') !== -1) {
            p.then(function(r) {
                var b = getBanner();
                b.textContent += '\nLW XHR: ' + String(url).split('/').pop() + ' status=' + r.status;
            }).catch(function(err) {
                getBanner().textContent += '\nLW XHR ERROR: ' + err;
            });
        }
        return p;
    };
    window.addEventListener('load', showBanner);
})();
</script>
"@
$newLines = $lines + $script.Split("`n")
[System.IO.File]::WriteAllLines($f, $newLines, [System.Text.Encoding]::UTF8)
Write-Host "Done. Total lines: $($newLines.Length)"
