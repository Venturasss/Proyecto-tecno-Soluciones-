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
    <h1>Clientes</h1>
    <div class="actions">
        <div class="search-box">
            <span class="search-icon">⌕</span>
            <input type="text" id="searchInput" placeholder="Buscar cliente..." autocomplete="off">
            <button class="search-clear" id="searchClear" onclick="clearSearch()">✕</button>
        </div>
        <a class="button" href="<?= BASE_URL ?>clientes/nuevo">+ Nuevo cliente</a>
        <a class="button secondary" href="<?= BASE_URL ?>reportes/clientes">↓ PDF</a>
    </div>
</section>

<div class="table-wrap">
    <table id="clientsTable">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th style="width:120px"></th>
            </tr>
        </thead>
        <tbody id="clientsBody">
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['name']) ?></td>
                    <td><?= htmlspecialchars($client['company']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= htmlspecialchars($client['phone']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn-edit" href="<?= BASE_URL ?>clientes/editar?id=<?= (int) $client['id'] ?>">Editar</a>
                            <a class="btn-pdf" href="<?= BASE_URL ?>reportes/cliente?id=<?= (int) $client['id'] ?>" title="Descargar PDF">↓ PDF</a>
                            <button class="btn-delete" onclick="confirmDelete(<?= (int) $client['id'] ?>, '<?= htmlspecialchars(addslashes($client['name'])) ?>')">Eliminar</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="no-results" id="noResults" style="display:none">
        <span>⊘</span> No se encontraron clientes para "<span id="noResultsTerm"></span>"
    </div>
    <div class="results-count" id="resultsCount"></div>
</div>

<!-- Modal de confirmación -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠</div>
        <h3 class="modal-title">¿Eliminar cliente?</h3>
        <p class="modal-desc" id="modalDesc">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
            <form method="POST" id="deleteForm" action="<?= BASE_URL ?>clientes/eliminar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn-confirm-delete">Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const searchClear = document.getElementById('searchClear');
const tbody       = document.getElementById('clientsBody');
const noResults   = document.getElementById('noResults');
const noResultsTerm = document.getElementById('noResultsTerm');
const resultsCount  = document.getElementById('resultsCount');
const allRows     = Array.from(tbody.querySelectorAll('tr'));
const total       = allRows.length;

searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    searchClear.style.display = q ? 'flex' : 'none';
    let visible = 0;

    allRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = !q || text.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    noResults.style.display   = visible === 0 && q ? 'flex' : 'none';
    noResultsTerm.textContent = searchInput.value.trim();
    resultsCount.textContent  = q ? `${visible} de ${total} cliente${total !== 1 ? 's' : ''}` : '';
    resultsCount.style.display = q ? 'block' : 'none';
});

function clearSearch() {
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input'));
    searchInput.focus();
}

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('modalDesc').textContent = 'Vas a eliminar a "' + name + '". Esta acción no se puede deshacer.';
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