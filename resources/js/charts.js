import { Chart, DoughnutController, BarController, CategoryScale, LinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';

Chart.register(DoughnutController, BarController, CategoryScale, LinearScale, ArcElement, BarElement, Tooltip, Legend);

function safeJson(val) { try { return JSON.parse(val || '[]'); } catch { return []; } }

const colors = ['#6b7280', '#3b82f6', '#eab308', '#22c55e'];

const statusChart = document.getElementById('tasksStatusChart');
if (statusChart) {
    new Chart(statusChart, {
        type: 'doughnut',
        data: {
            labels: safeJson(statusChart.dataset.labels),
            datasets: [{
                data: safeJson(statusChart.dataset.values),
                backgroundColor: colors,
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } },
            },
        },
    });
}

const servicesChart = document.getElementById('servicesTypeChart');
if (servicesChart) {
    const labels = safeJson(servicesChart.dataset.labels);
    const values = safeJson(servicesChart.dataset.values);
    const serviceColors = ['#3b82f6', '#22c55e', '#eab308', '#f97316', '#a855f7', '#ec4899', '#06b6d4', '#6366f1'];

    new Chart(servicesChart, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: serviceColors.slice(0, labels.length),
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } },
                y: { ticks: { font: { size: 11 } } },
            },
        },
    });
}

const assetsChart = document.getElementById('assetsStatusChart');
if (assetsChart) {
    new Chart(assetsChart, {
        type: 'doughnut',
        data: {
            labels: safeJson(assetsChart.dataset.labels),
            datasets: [{
                data: safeJson(assetsChart.dataset.values),
                backgroundColor: ['#22c55e', '#3b82f6', '#ef4444', '#6b7280'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, boxWidth: 12 } },
            },
        },
    });
}

const renewalsChart = document.getElementById('renewalsExpiryChart');
if (renewalsChart) {
    new Chart(renewalsChart, {
        type: 'bar',
        data: {
            labels: safeJson(renewalsChart.dataset.labels),
            datasets: [{
                data: safeJson(renewalsChart.dataset.values),
                backgroundColor: '#eab308',
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
            },
        },
    });
}
