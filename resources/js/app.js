import './bootstrap';
import rover from '@sheaf/rover';
import './globals/modals';

document.addEventListener('alpine:init', () => {
	if (!window.Alpine) return;
	window.Alpine.plugin(rover);
	import('./components/select');
});
