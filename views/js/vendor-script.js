// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('salesChart')) {
        initSalesChart();
    }
    if (document.getElementById('stockChart')) {
        initStockChart();
    }
    if (document.getElementById('categoryChart')) {
        initCategoryChart();
    }
});

let salesChart, stockChart, categoryChart;

function initSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const months = JSON.parse(document.getElementById('chart-data').dataset.months || '[]');
    const sales = JSON.parse(document.getElementById('chart-data').dataset.sales || '[]');
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales Amount ($)',
                data: sales,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Amount ($)' } } }
        }
    });
}

function initStockChart() {
    const ctx = document.getElementById('stockChart').getContext('2d');
    const inStock = parseInt(document.getElementById('stock-data').dataset.inStock || 0);
    const lowStock = parseInt(document.getElementById('stock-data').dataset.lowStock || 0);
    const outStock = parseInt(document.getElementById('stock-data').dataset.outStock || 0);
    stockChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['In Stock (≥10)', 'Low Stock (<10)', 'Out of Stock'],
            datasets: [{ data: [inStock, lowStock, outStock], backgroundColor: ['#28a745', '#ffc107', '#dc3545'] }]
        }
    });
}

function initCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const categories = JSON.parse(document.getElementById('category-data').dataset.categories || '[]');
    const counts = JSON.parse(document.getElementById('category-data').dataset.counts || '[]');
    categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Number of Medicines',
                data: counts,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Count' } } }
        }
    });
}