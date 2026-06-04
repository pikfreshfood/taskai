import './bootstrap';

import Alpine from 'alpinejs';
import 'trix';

// Dark mode persistence
const theme = localStorage.getItem('theme');
if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('toast', () => ({
        show: false,
        message: '',
        type: 'success',
        timeout: null,
        init() {
            window.addEventListener('toast', (e) => {
                this.message = e.detail.message;
                this.type = e.detail.type || 'success';
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => { this.show = false; }, 4000);
            });
        },
        dismiss() {
            this.show = false;
            clearTimeout(this.timeout);
        }
    }));
});

Alpine.start();
