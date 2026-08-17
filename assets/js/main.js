/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Global JavaScript Utility & Theme Switcher Engine
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Dark/Light Theme Toggle Engine with LocalStorage Persistence
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    function syncThemeUI(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('grand_theme', theme);
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-sun text-warning';
                themeToggleBtn.setAttribute('title', 'Switch to Light Mode');
            } else {
                themeIcon.className = 'fas fa-moon text-warning';
                themeToggleBtn.setAttribute('title', 'Switch to Dark Mode');
            }
        }
    }

    const currentSavedTheme = localStorage.getItem('grand_theme') || 'light';
    syncThemeUI(currentSavedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = htmlElement.getAttribute('data-bs-theme') || 'light';
            const nextTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            syncThemeUI(nextTheme);
        });
    }

    // 2. Real-time Live Filter for Bootstrap Tables
    const tableSearchInputs = document.querySelectorAll('.table-search-input');
    tableSearchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const targetTableId = this.getAttribute('data-target');
            const query = this.value.toLowerCase();
            const table = document.getElementById(targetTableId);
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 3. Automated Check-In / Check-Out Night & Price Calculator
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    const nightsDisplay = document.getElementById('calculated_nights');
    const priceDisplay = document.getElementById('calculated_total_price');

    if (checkInInput && checkOutInput) {
        function updatePricing() {
            const d1 = new Date(checkInInput.value);
            const d2 = new Date(checkOutInput.value);
            
            if (d1 && d2 && d2 > d1) {
                const diffTime = Math.abs(d2 - d1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (nightsDisplay) nightsDisplay.textContent = diffDays + ' Night(s)';
                
                const roomPriceElem = document.getElementById('room_price_val');
                if (roomPriceElem && priceDisplay) {
                    const pricePerNight = parseFloat(roomPriceElem.value || 0);
                    const total = diffDays * pricePerNight;
                    priceDisplay.textContent = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
                }
            }
        }

        checkInInput.addEventListener('change', updatePricing);
        checkOutInput.addEventListener('change', updatePricing);
    }

    // 4. Confirm Action Modals
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const msg = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

});
