/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Chart.js Dynamic Dashboard Visualizations Engine
 */

function renderAdminCharts(revenueData, occupancyData, bookingsData, roomTypeData) {
    
    // 1. Monthly Revenue Line Chart
    const revenueCtx = document.getElementById('monthlyRevenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenueData.values || [45000, 52000, 68000, 74000, 89000, 95000, 112000, 105000, 98000, 115000, 130000, 145000],
                    borderColor: '#c5a059',
                    backgroundColor: 'rgba(197, 160, 89, 0.15)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d1b2a'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Room Occupancy Rate Doughnut Chart
    const occupancyCtx = document.getElementById('occupancyDoughnutChart');
    if (occupancyCtx) {
        new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: occupancyData.labels || ['Available', 'Occupied', 'Reserved', 'Cleaning', 'Maintenance'],
                datasets: [{
                    data: occupancyData.values || [40, 30, 15, 10, 5],
                    backgroundColor: ['#198754', '#dc3545', '#fd7e14', '#0dcaf0', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Monthly Bookings Bar Chart
    const bookingsCtx = document.getElementById('bookingsBarChart');
    if (bookingsCtx) {
        new Chart(bookingsCtx, {
            type: 'bar',
            data: {
                labels: bookingsData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Total Reservations',
                    data: bookingsData.values || [12, 19, 25, 22, 30, 35, 42],
                    backgroundColor: '#1b263b',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 4. Room Type Popularity Polar Chart
    const roomTypeCtx = document.getElementById('roomTypePolarChart');
    if (roomTypeCtx) {
        new Chart(roomTypeCtx, {
            type: 'polarArea',
            data: {
                labels: roomTypeData.labels || ['Executive Suite', 'Presidential Villa', 'Deluxe Double', 'Standard Classic'],
                datasets: [{
                    data: roomTypeData.values || [35, 15, 30, 20],
                    backgroundColor: ['#c5a059', '#0d1b2a', '#415a77', '#778da9']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}
