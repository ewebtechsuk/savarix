document.addEventListener('DOMContentLoaded', () => {
    const statsEl = document.getElementById('stats-data');
    if (!statsEl) {
        return;
    }

    const stats = JSON.parse(statsEl.dataset.stats);

    const countsCtx = document.getElementById('modelCountsChart');
    if (countsCtx) {
        new Chart(countsCtx, {
            type: 'bar',
            data: {
                labels: ['Properties', 'Tenancies', 'Leads', 'Payments'],
                datasets: [{
                    label: 'Count',
                    data: [
                        stats.property_count,
                        stats.tenancy_count,
                        stats.lead_count,
                        stats.payment_count,
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            },
        });
    }

    const paymentsCtx = document.getElementById('paymentsChart');
    if (paymentsCtx && stats.monthly_payments) {
        const months = Object.keys(stats.monthly_payments);
        const totals = Object.values(stats.monthly_payments);

        new Chart(paymentsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Payments',
                    data: totals,
                    borderColor: '#0d6efd',
                    fill: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            },
        });
    }
});

