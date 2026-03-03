

document.addEventListener("DOMContentLoaded", function () {

    if (!window.dashboardData) {
        return;
    }

    const data = window.dashboardData;

    // --- GRAFIK 1: SUMBER LEAD (VERTICAL BAR) ---
    const ctxSource = document.getElementById('sourceChart');
    if (ctxSource && data.sourceStats) {

        let filteredData = [];
        Object.entries(data.sourceStats).forEach(([key, value]) => {
            if (key && key.trim() !== '' && key.toLowerCase() !== 'null') {
                filteredData.push({ label: key, count: value });
            }
        });

        filteredData.sort((a, b) => b.count - a.count);

        const sourceLabels = filteredData.map(item => item.label);
        const sourceValues = filteredData.map(item => item.count);

        new Chart(ctxSource, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Jumlah Lead',
                    data: sourceValues,
                    backgroundColor: '#07403A',
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
