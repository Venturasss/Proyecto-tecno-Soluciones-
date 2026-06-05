<section class="page-head">
    <h1>Panel</h1>
    <div class="actions">
        <a class="button" href="<?= BASE_URL ?>clientes/nuevo">+ Nuevo cliente</a>
        <a class="button" href="<?= BASE_URL ?>proyectos/nuevo">+ Nuevo proyecto</a>
    </div>
</section>

<!-- Stats -->
<section class="stats">
    <article>
        <strong><?= (int)($clientsCount ?? 0) ?></strong>
        <span>Clientes registrados</span>
    </article>
    <article>
        <strong><?= (int)($projectsCount ?? 0) ?></strong>
        <span>Proyectos totales</span>
    </article>
    <article>
        <strong>$<?= number_format((float)($totalBudget ?? 0), 0, '.', ',') ?></strong>
        <span>Presupuesto total</span>
    </article>
</section>

<!-- Gráficos -->
<div class="charts-grid">

    <!-- Donut: proyectos por estado -->
    <div class="chart-card">
        <h3 class="chart-title">Proyectos por estado</h3>
        <div class="chart-container" style="max-width:260px; margin:0 auto;">
            <canvas id="chartStatus"></canvas>
        </div>
        <div class="chart-legend" id="legendStatus"></div>
    </div>

    <!-- Barras: proyectos por mes -->
    <div class="chart-card">
        <h3 class="chart-title">Proyectos por mes</h3>
        <div class="chart-container">
            <canvas id="chartMonths"></canvas>
        </div>
    </div>

</div>

<!-- Reportes -->
<section class="reports">
    <h2>Reportes PDF</h2>
    <a href="<?= BASE_URL ?>reportes/clientes">↓ Descargar clientes</a>
    <a href="<?= BASE_URL ?>reportes/proyectos">↓ Descargar proyectos</a>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const statusLabels = <?= json_encode(array_keys($byStatus ?? [])) ?>;
const statusData   = <?= json_encode(array_values($byStatus ?? [])) ?>;
const monthLabels  = <?= json_encode(array_keys($byMonth ?? [])) ?>;
const monthData    = <?= json_encode(array_values($byMonth ?? [])) ?>;

// Planificado | En progreso | En pausa | Finalizado | Cancelado
const colors = ['#4a9ad8', '#22c984', '#f59e0b', '#10b981', '#ef4444'];

const isDark    = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
const textColor = isDark ? 'rgba(200,225,248,0.6)' : 'rgba(60,80,110,0.7)';
const gridColor = isDark ? 'rgba(80,140,200,0.08)' : 'rgba(80,140,200,0.12)';

Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size   = 12;

// ── Donut ──────────────────────────────────────────────────────────────────
const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusData,
            backgroundColor: colors,
            borderColor: isDark ? '#0a1628' : '#fff',
            borderWidth: 3,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(6,14,32,0.92)',
                titleColor: '#fff',
                bodyColor: 'rgba(200,225,248,0.75)',
                borderColor: 'rgba(80,140,200,0.2)',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    label: ctx => ` ${ctx.parsed} proyecto${ctx.parsed !== 1 ? 's' : ''}`
                }
            }
        }
    }
});

// Leyenda manual del donut
const legendEl = document.getElementById('legendStatus');
statusLabels.forEach((label, i) => {
    if (statusData[i] === 0) return;
    legendEl.innerHTML += `
        <div class="legend-item">
            <span class="legend-dot" style="background:${colors[i]}"></span>
            <span class="legend-label">${label}</span>
            <span class="legend-val">${statusData[i]}</span>
        </div>`;
});

// ── Barras ─────────────────────────────────────────────────────────────────
const ctxMonths = document.getElementById('chartMonths').getContext('2d');

const gradient = ctxMonths.createLinearGradient(0, 0, 0, 220);
gradient.addColorStop(0, 'rgba(74,154,216,0.55)');
gradient.addColorStop(1, 'rgba(74,154,216,0.04)');

new Chart(ctxMonths, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Proyectos',
            data: monthData,
            backgroundColor: gradient,
            borderColor: '#4a9ad8',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(6,14,32,0.92)',
                titleColor: '#fff',
                bodyColor: 'rgba(200,225,248,0.75)',
                borderColor: 'rgba(80,140,200,0.2)',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y} proyecto${ctx.parsed.y !== 1 ? 's' : ''}`
                }
            }
        },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { color: textColor },
                border: { color: gridColor }
            },
            y: {
                beginAtZero: true,
                ticks: { color: textColor, stepSize: 1, precision: 0 },
                grid: { color: gridColor },
                border: { color: gridColor }
            }
        }
    }
});
</script>