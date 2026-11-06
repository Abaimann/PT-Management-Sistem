import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.Chart = Chart;
window.lucide = { createIcons, icons };

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

document.addEventListener('alpine:init', () => {
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('erp_sidebar_collapsed') === 'true',
        mobileOpen: false,
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('erp_sidebar_collapsed', this.collapsed);
        },
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        },
        closeMobile() {
            this.mobileOpen = false;
        }
    });

    Alpine.store('notification', {
        items: [],
        add(message, type = 'info', timeout = 4000) {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            if (timeout) {
                setTimeout(() => this.remove(id), timeout);
            }
        },
        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        }
    });
});

Alpine.start();
