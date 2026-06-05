<?php $editing = is_array($client ?? null); ?>
<div class="page-head">
    <h1><?= $editing ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
</div>

<form method="post" action="<?= BASE_URL . ($editing ? 'clientes/actualizar' : 'clientes/guardar') ?>" class="form wide">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($client['id'] ?? '') ?>">
    <?php endif; ?>

    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($client['name'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Empresa</label>
        <input type="text" name="company" value="<?= htmlspecialchars($client['company'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($client['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Teléfono</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Dirección</label>
        <textarea name="address"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
    </div>

    <button type="submit">Guardar</button>
</form>