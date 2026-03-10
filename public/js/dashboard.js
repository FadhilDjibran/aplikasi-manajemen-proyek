document.addEventListener("DOMContentLoaded", function () {
    if (!window.dashboardData) {
        return;
    }

    const data = window.dashboardData;

    const ctxSource = document.getElementById('sourceChart');
    if (ctxSource && data.sourceStats) {
        let filteredData = [];
        Object.entries(data.sourceStats).forEach(([key, value]) => {
            if (key && key.trim() !== '' && key.toLowerCase() !== 'null') {
                filteredData.push({ label: key, count: value });
            }
        });
        filteredData.sort((a, b) => b.count - a.count);

        new Chart(ctxSource, {
            type: 'bar',
            data: {
                labels: filteredData.map(item => item.label),
                datasets: [{
                    label: 'Jumlah Lead',
                    data: filteredData.map(item => item.count),
                    backgroundColor: '#07403A',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

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
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    const pieColors = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#f43f5e', '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#ec4899'];

    function createPieChart(canvasId, dataObject) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !dataObject) return;

        let tempData = [];
        Object.entries(dataObject).forEach(([key, value]) => {
            if (key && key.trim() !== '' && key.toLowerCase() !== 'null' && value > 0) {
                tempData.push({ label: key, value: value });
            }
        });

        tempData.sort((a, b) => b.value - a.value);

        let labels = tempData.map(item => item.label);
        let values = tempData.map(item => item.value);

        if (labels.length === 0) {
            labels = ['Belum ada data'];
            values = [1];
        }

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: labels[0] === 'Belum ada data' ? ['#e2e8f0'] : pieColors.slice(0, labels.length),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (labels[0] === 'Belum ada data') return ' Tidak ada data';
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let value = context.raw;
                                let percentage = ((value / total) * 100).toFixed(1) + '%';
                                return ` ${context.label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    }

    createPieChart('statusPieChart', data.statusStats);
    createPieChart('sourcePieChart', data.sourceStats);
    createPieChart('cityPieChart', data.cityStats);
    createPieChart('typePieChart', data.typeStats);
});
