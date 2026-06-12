import { Chart, DoughnutController, BarController, CategoryScale, LinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';

Chart.register(DoughnutController, BarController, CategoryScale, LinearScale, ArcElement, BarElement, Tooltip, Legend);

const statusChart = document.getElementById('tasksStatusChart');
if (statusChart) {
    new Chart(statusChart, {
        type: 'doughnut',
        data: {
            labels: JSON.parse(statusChart.dataset.labels),
            datasets: [{
                data: JSON.parse(statusChart.dataset.values),
                backgroundColor: ['#6b7280', '#3b82f6', '#eab308', '#22c55e'],
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
    const labels = JSON.parse(servicesChart.dataset.labels);
    const values = JSON.parse(servicesChart.dataset.values);
    const colors = ['#3b82f6', '#22c55e', '#eab308', '#f97316', '#a855f7', '#ec4899', '#06b6d4', '#6366f1'];

    new Chart(servicesChart, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
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
