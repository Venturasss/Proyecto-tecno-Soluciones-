<?php
use App\Core\Controller;
$flash = Controller::getFlash();
?>

<?php if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>" id="flashMsg">
    <span><?= htmlspecialchars($flash['message']) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<section class="page-head">
    <h1>Proyectos</h1>
    <div class="actions">
        <div class="search-box">
            <span class="search-icon">⌕</span>
            <input type="text" id="searchInput" placeholder="Buscar proyecto..." autocomplete="off">
            <button class="search-clear" id="searchClear" onclick="clearSearch()">✕</button>
        </div>
        <div class="filter-pills" id="filterPills">
            <button class="pill active" data-status="">Todos</button>
            <button class="pill" data-status="En progreso">En progreso</button>
            <button class="pill" data-status="Planificado">Planificado</button>
            <button class="pill" data-status="En pausa">En pausa</button>
            <button class="pill" data-status="Finalizado">Finalizado</button>
            <button class="pill" data-status="Cancelado">Cancelado</button>
        </div>
        <a class="button" href="<?= BASE_URL ?>proyectos/nuevo">+ Nuevo proyecto</a>
        <a class="button secondary" href="<?= BASE_URL ?>reportes/proyectos">↓ PDF</a>
    </div>
</section>

<div class="table-wrap">
    <table id="projectsTable">
        <thead>
            <tr>
                <th>Proyecto</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th style="width:180px"></th>
            </tr>
        </thead>
        <tbody id="projectsBody">
            <?php foreach ($projects as $project): ?>
                <?php
                    $status = $project['status'];
                    $cls = match($status) {
                        'Finalizado'  => 'badge-active',
                        'En progreso' => 'badge-pending',
                        'En pausa'    => 'badge-pause',
                        'Cancelado'   => 'badge-danger',
                        default       => 'badge-inactive',
                    };
                ?>
                <tr data-status="<?= htmlspecialchars($status) ?>">
                    <td><?= htmlspecialchars($project['name']) ?></td>
                    <td><?= htmlspecialchars($project['client_name']) ?></td>
                    <td><span class="badge <?= $cls ?>"><?= htmlspecialchars($status) ?></span></td>
                    <td><?= htmlspecialchars((string) $project['start_date']) ?></td>
                    <td><?= htmlspecialchars((string) $project['end_date']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn-edit" href="<?= BASE_URL ?>proyectos/editar?id=<?= (int) $project['id'] ?>">Editar</a>
                            <a class="btn-pdf" href="<?= BASE_URL ?>reportes/proyecto?id=<?= (int) $project['id'] ?>" title="Descargar PDF">↓ PDF</a>
                            <a class="btn-invoice" href="<?= BASE_URL ?>facturas/guardar?proyecto=<?= (int) $project['id'] ?>" title="Generar factura">⬡ Factura</a>
                            <button class="btn-delete" onclick="confirmDelete(<?= (int) $project['id'] ?>, '<?= htmlspecialchars(addslashes($project['name'])) ?>')">Eliminar</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="no-results" id="noResults" style="display:none">
        <span>⊘</span> No se encontraron proyectos para "<span id="noResultsTerm"></span>"
    </div>
    <div class="results-count" id="resultsCount"></div>
</div>

<!-- Modal de confirmación -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠</div>
        <h3 class="modal-title">¿Eliminar proyecto?</h3>
        <p class="modal-desc" id="modalDesc">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
            <form method="POST" id="deleteForm" action="<?= BASE_URL ?>proyectos/eliminar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn-confirm-delete">Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
const searchInput   = document.getElementById('searchInput');
const searchClear   = document.getElementById('searchClear');
const tbody         = document.getElementById('projectsBody');
const noResults     = document.getElementById('noResults');
const noResultsTerm = document.getElementById('noResultsTerm');
const resultsCount  = document.getElementById('resultsCount');
const allRows       = Array.from(tbody.querySelectorAll('tr'));
const total         = allRows.length;
let activeStatus    = '';

function applyFilters() {
    const q = searchInput.value.trim().toLowerCase();
    searchClear.style.display = q ? 'flex' : 'none';
    let visible = 0;

    allRows.forEach(row => {
        const text   = row.textContent.toLowerCase();
        const status = row.dataset.status;
        const matchQ = !q || text.includes(q);
        const matchS = !activeStatus || status === activeStatus;
        const show   = matchQ && matchS;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const hasFilter = q || activeStatus;
    noResults.style.display    = visible === 0 && hasFilter ? 'flex' : 'none';
    noResultsTerm.textContent  = searchInput.value.trim() || activeStatus;
    resultsCount.textContent   = hasFilter ? `${visible} de ${total} proyecto${total !== 1 ? 's' : ''}` : '';
    resultsCount.style.display = hasFilter ? 'block' : 'none';
}

searchInput.addEventListener('input', applyFilters);

document.getElementById('filterPills').addEventListener('click', function(e) {
    const pill = e.target.closest('.pill');
    if (!pill) return;
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    activeStatus = pill.dataset.status;
    applyFilters();
});

function clearSearch() {
    searchInput.value = '';
    applyFilters();
    searchInput.focus();
}

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('modalDesc').textContent = 'Vas a eliminar "' + name + '". Esta acción no se puede deshacer.';
    document.getElementById('deleteModal').classList.add('active');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.style.opacity = '0', 4000);
</script>