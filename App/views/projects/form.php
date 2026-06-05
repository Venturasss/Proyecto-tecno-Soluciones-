<?php $editing = is_array($project); ?>

<section class="page-head">
    <h1><?= $editing ? 'Editar proyecto' : 'Nuevo proyecto' ?></h1>
</section>

<form method="post" action="<?= BASE_URL . ($editing ? 'proyectos/actualizar' : 'proyectos/guardar') ?>" class="form wide">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
    <?php endif; ?>
   
    <label>Cliente
        <select name="client_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?= (int) $client['id'] ?>" <?= ((int) ($project['client_id'] ?? 0) === (int) $client['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($client['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Nombre del proyecto
        <input type="text" name="name" required value="<?= htmlspecialchars($project['name'] ?? '') ?>">
    </label>

    <label>Estado
        <select name="status">
            <?php foreach (['Planificado', 'En progreso', 'En pausa', 'Finalizado'] as $status): ?>
                <option value="<?= $status ?>" <?= (($project['status'] ?? '') === $status) ? 'selected' : '' ?>>
                    <?= $status ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Fecha de inicio
        <input type="date" name="start_date" value="<?= htmlspecialchars((string) ($project['start_date'] ?? '')) ?>">
    </label>

    <label>Fecha de fin
        <input type="date" name="end_date" value="<?= htmlspecialchars((string) ($project['end_date'] ?? '')) ?>">
    </label>

    <label>Presupuesto
        <input type="number" step="0.01" name="budget" value="<?= htmlspecialchars((string) ($project['budget'] ?? '')) ?>">
    </label>

    <label>Descripción
        <textarea name="description"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
    </label>

    <button type="submit">Guardar</button>
</form>