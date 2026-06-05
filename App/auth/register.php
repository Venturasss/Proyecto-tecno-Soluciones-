<section class="auth">
    <h1>Registro de usuario</h1>
    <?php if (!empty($error)): ?><p class="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    
    <form method="post" action="<?= BASE_URL ?>registro" class="form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <label>Nombre
            <input type="text" name="name" required autocomplete="name">
        </label>    
        <label>Email
            <input type="email" name="email" required autocomplete="email">
        </label>  
        <label>Clave
            <input type="password" name="password" required minlength="8" autocomplete="new-password">
        </label> 
        <button type="submit">Registrar</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="<?= BASE_URL ?>login">Ingresar</a></p>
</section>