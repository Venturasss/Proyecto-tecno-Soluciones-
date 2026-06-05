<?php $isLoggedIn = !empty($_SESSION['user']); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(($title ?? 'Sistema') . ' - ' . APP_NAME) ?></title>

<link rel="stylesheet" href="/styles.css">

</head>
<body>

<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?>dashboard"><?= APP_NAME ?></a>
  
  <?php if ($isLoggedIn): ?>
    <nav>
      <a href="<?= BASE_URL ?>dashboard">Panel</a>
      <a href="<?= BASE_URL ?>clientes">Clientes</a>
      <a href="<?= BASE_URL ?>proyectos">Proyectos</a>
      <a href="<?= BASE_URL ?>logout">Salir</a>
    </nav>
  <?php endif; ?>
</header>

<main class="container">