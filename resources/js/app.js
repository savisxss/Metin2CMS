import './bootstrap';
import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import Swal from 'sweetalert2';

// Register Chart.js components
Chart.register(...registerables);

// Make libraries available globally
window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

// Alpine.js components
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    open: false,
    show() {
        this.open = true;
    },
    hide() {
        this.open = false;
    }
}));

Alpine.data('tabs', () => ({
    activeTab: 0,
    setActive(tab) {
        this.activeTab = tab;
    },
    isActive(tab) {
        return this.activeTab === tab;
    }
}));

Alpine.data('playerRanking', () => ({
    players: [],
    loading: true,
    error: null,
    
    async init() {
        await this.fetchPlayers();
    },
    
    async fetchPlayers() {
        try {
            this.loading = true;
            const response = await fetch('/api/players');
            if (!response.ok) throw new Error('Failed to fetch players');
            this.players = await response.json();
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loading = false;
        }
    }
}));

Alpine.data('serverStatus', () => ({
    status: 'checking',
    playersOnline: 0,
    lastUpdated: null,
    
    async init() {
        await this.checkStatus();
        // Update every 30 seconds
        setInterval(() => this.checkStatus(), 30000);
    },
    
    async checkStatus() {
        try {
            const response = await fetch('/api/status');
            const data = await response.json();
            this.status = data.status;
            this.playersOnline = data.players_online;
            this.lastUpdated = new Date(data.timestamp);
        } catch (error) {
            this.status = 'offline';
        }
    }
}));

// Start Alpine
Alpine.start();

// Global functions for notifications
window.showSuccess = function(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
};

window.showError = function(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
};

window.showConfirm = function(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
};