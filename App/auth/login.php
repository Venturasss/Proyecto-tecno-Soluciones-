<style>
.login-split {
    display: flex;
    min-height: 100vh;
    align-items: stretch;
}

/* IZQUIERDA OSCURA PREMIUM */
.login-left {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px;
    background: #020617;
    border-right: 1px solid rgba(255,255,255,0.06);
    position: relative;
    overflow: hidden;
    animation: fadeUp 0.7s ease;
}

.login-left::before {
    content: "";
    position: absolute;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: rgba(37,99,235,0.18);
    filter: blur(90px);
    top: -100px; left: -100px;
    pointer-events: none;
}

.login-left::after {
    content: "";
    position: absolute;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: rgba(20,184,166,0.12);
    filter: blur(90px);
    bottom: -80px; right: -60px;
    pointer-events: none;
}

.brand-line {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 36px;
}

.brand-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #2563eb;
}

.brand-name {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.45);
    letter-spacing: 2.5px;
    text-transform: uppercase;
}

.hero-title {
    font-size: 38px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 14px;
}

.hero-title span { color: #60a5fa; }

.hero-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.38);
    line-height: 1.7;
    margin-bottom: 44px;
    max-width: 340px;
}

.login-features {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.login-feature {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    transition: 0.3s ease;
}

.login-feature:hover {
    background: rgba(37,99,235,0.10);
    border-color: rgba(96,165,250,0.25);
    transform: translateX(5px);
}

.login-feature .icon {
    width: 40px; height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(37,99,235,0.2);
    border-radius: 10px;
    flex-shrink: 0;
    font-size: 20px;
}

.login-feature .feat-text strong {
    display: block;
    color: rgba(255,255,255,0.88);
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 2px;
}

.login-feature .feat-text span {
    color: rgba(255,255,255,0.32);
    font-size: 12px;
}

.trust-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 36px;
}

.trust-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #14b8a6;
}

.trust-txt {
    font-size: 12px;
    color: rgba(255,255,255,0.25);
}

/* DERECHA FORMULARIO */
.login-right {
    width: 460px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 48px;
    background: rgba(15,23,42,0.4);
}

.login-right .auth {
    width: 100%;
    margin: 0;
    background: transparent;
    border: none;
    box-shadow: none;
    backdrop-filter: none;
    padding: 0;
    animation: fadeUp 0.9s ease;
}

.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    background: rgba(20,184,166,0.12);
    border: 1px solid rgba(20,184,166,0.2);
    color: #14b8a6;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 22px;
}

.auth h2 {
    font-size: 30px !important;
    font-weight: 800 !important;
    margin-bottom: 6px !important;
}

.auth .form-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 28px;
}

/* RESPONSIVE */
@media (max-width: 920px) {
    .login-split { flex-direction: column; }
    .login-left { padding: 40px 28px; }
    .hero-title { font-size: 28px; }
    .login-right { width: 100%; padding: 40px 24px; }
}
</style>

<div class="login-split">

    <!-- IZQUIERDA -->
    <div class="login-left">
        <div class="brand-line">
            <div class="brand-dot"></div>
            <div class="brand-name">tecnoSoluciones S.A</div>
        </div>

        <div class="hero-title">Gestión empresarial<br><span>inteligente y moderna</span></div>
        <p class="hero-sub">Centraliza clientes, proyectos y reportes en una sola plataforma diseñada para crecer contigo.</p>

        <div class="login-features">
            <div class="login-feature">
                <div class="icon">👥</div>
                <div class="feat-text">
                    <strong>Clientes organizados</strong>
                    <span>Historial y contactos en un clic</span>
                </div>
            </div>
            <div class="login-feature">
                <div class="icon">📊</div>
                <div class="feat-text">
                    <strong>Proyectos bajo control</strong>
                    <span>Estados, fechas y presupuestos</span>
                </div>
            </div>
            <div class="login-feature">
                <div class="icon">📄</div>
                <div class="feat-text">
                    <strong>Reportes PDF al instante</strong>
                    <span>Exporta con un solo clic</span>
                </div>
            </div>
            <div class="login-feature">
                <div class="icon">🔒</div>
                <div class="feat-text">
                    <strong>Acceso seguro</strong>
                    <span>Sistema protegido con autenticación</span>
                </div>
            </div>
        </div>

        <div class="trust-bar">
            <div class="trust-dot"></div>
            <div class="trust-txt">Plataforma segura · Datos protegidos · Acceso 24/7</div>
        </div>
    </div>

    <!-- DERECHA -->
    <div class="login-right">
        <section class="auth">
            <div class="verified-badge">✔ Acceso verificado</div>
            <h2>Bienvenido de vuelta</h2>
            <p class="form-subtitle">Ingresa tus credenciales para continuar</p>

            <?php if (!empty($error)): ?>
                <p class="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>login" class="form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

                <label>Email 📧
                    <input type="email" name="email" required autocomplete="email">
                </label>

                <label>Clave 🔑
                    <input type="password" name="password" required autocomplete="current-password">
                </label>

                <button type="submit">Ingresar al sistema</button>
            </form>

            <p style="margin-top:18px; color:#475569; font-size:13px; text-align:center;">
                ¿Primera vez? <a href="<?= BASE_URL ?>registro" style="color:#60a5fa; font-weight:600;">Crear cuenta gratis</a>
            </p>
        </section>
    </div>

</div>