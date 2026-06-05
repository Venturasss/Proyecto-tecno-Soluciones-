<?php
$flash = App\Core\Controller::getFlash();
?>

<?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>

<div class="page-header">
  <h2>Facturas</h2>
</div>

<?php if (empty($invoices)): ?>
  <p>No hay facturas generadas aún. Ve a <a href="/proyectos">Proyectos</a> y haz clic en "Factura".</p>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th>N° Factura</th>
      <th>Proyecto</th>
      <th>Cliente</th>
      <th>Emisión</th>
      <th>Vence</th>
      <th>Estado</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($invoices as $inv): ?>
    <tr>
      <td><?= htmlspecialchars($inv['number']) ?></td>
      <td><?= htmlspecialchars($inv['project_name']) ?></td>
      <td><?= htmlspecialchars($inv['client_name']) ?></td>
      <td><?= date('d/m/Y', strtotime($inv['issued_at'])) ?></td>
      <td><?= $inv['due_at'] ? date('d/m/Y', strtotime($inv['due_at'])) : '—' ?></td>
      <td><?= htmlspecialchars($inv['status']) ?></td>
      <td>
        <a href="/facturas/pdf?id=<?= $inv['id'] ?>">Ver PDF</a>

        <form method="POST" action="/facturas/eliminar" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="id" value="<?= $inv['id'] ?>">
          <button type="submit">Eliminar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>