

document.addEventListener("DOMContentLoaded", function () {

    if (!window.dashboardData) {
        return;
    }

    const data = window.dashboardData;

    // --- GRAFIK 1: SUMBER LEAD (VERTICAL BAR) ---
    const ctxSource = document.getElementById('sourceChart');
    if (ctxSource && data.sourceStats) {

        const sourceLabels = Object.keys(data.sourceStats);
        const sourceValues = Object.values(data.sourceStats);

        new Chart(ctxSource, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Jumlah Lead',
                    data: sourceValues,
                    backgroundColor: '#3b82f6', // Biru
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // --- GRAFIK 2: ALASAN GAGAL (HORIZONTAL BAR) ---
    const ctxFail = document.getElementById('failReasonChart');
    if (ctxFail && data.failStats) {

        const failLabels = Object.keys(data.failStats);
        const failValues = Object.values(data.failStats);

        const hasData = failLabels.length > 0;

        new Chart(ctxFail, {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: hasData ? failLabels : ['Belum ada data'],
                datasets: [{
                    label: 'Jumlah Lead',
                    data: hasData ? failValues : [0],
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
