<?php
// View/LoginView.php

$Https_activo = false;
if (isset($_SERVER['HTTPS'])) {
	if ($_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off') {
		$Https_activo = true;
	}
}

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_name('XITICSESSID');
	session_set_cookie_params(
		array(
			'lifetime' => 0,
			'path' => '/',
			'domain' => '',
			'secure' => $Https_activo,
			'httponly' => true,
			'samesite' => 'Lax'
		)
	);
	session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

if (!isset($_SESSION['_csrf_token']) || $_SESSION['_csrf_token'] === '') {
	$_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION["_sesion_usuario"])) {
	header("Location:index.php?System=Dashboard");
	exit;
}

$Csrf_token = (string) $_SESSION['_csrf_token'];
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Xitic · Iniciar sesión</title>

	<link rel="icon" type="image/png" href="https://neto5.xitic.com.mx/Logo.png">
	<link rel="apple-touch-icon" href="https://neto5.xitic.com.mx/Logo.png">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

	<style>
		:root{
			--border: rgba(15, 23, 42, .10);
			--text: #0f172a;
			--muted: rgba(15, 23, 42, .62);
			--primary: #0b5ed7;
			--primary-hover: #0a55c6;
			--shadow: 0 18px 55px rgba(15, 23, 42, .14);
			--shadow-soft: 0 10px 26px rgba(15, 23, 42, .10);
		}

		html, body{ height: 100%; }

		body{
			font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
			min-height: 100vh;
			color: var(--text);
			background:
				radial-gradient(700px 420px at 15% 10%, rgba(11,94,215,.12), transparent 60%),
				radial-gradient(700px 420px at 85% 20%, rgba(11,94,215,.08), transparent 60%),
				linear-gradient(180deg, #f7f9fc 0%, #eef3fb 100%);
		}

		.auth-wrap{
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 26px 14px;
		}

		.auth-card{
			width: 100%;
			max-width: 460px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 20px;
			box-shadow: var(--shadow);
			overflow: hidden;
		}

		.auth-header{
			padding: 26px 22px 14px 22px;
			border-bottom: 1px solid rgba(15, 23, 42, .06);
			text-align: center;
			background: #fff;
		}

		.brand-logo{
			width: 150px;
			height: 150px;
			object-fit: contain;
			display: block;
			margin: 0 auto;
			filter: drop-shadow(0 14px 18px rgba(15, 23, 42, .12));
		}

		.auth-body{ padding: 22px; }

		.title{
			font-weight: 950;
			letter-spacing: -.03em;
			margin: 6px 0 4px 0;
			text-align: center;
			font-size: 1.25rem;
		}

		.subtitle{
			margin: 0 0 16px 0;
			text-align: center;
			color: var(--muted);
			font-size: .95rem;
		}

		.form-label{
			font-size: 12px;
			font-weight: 800;
			color: rgba(15, 23, 42, .72);
			margin-bottom: 6px;
		}

		.form-control{
			border-radius: 14px;
			border: 1px solid rgba(15, 23, 42, .16);
			padding: 11px 12px;
			height: 46px;
		}

		.form-control:focus{
			border-color: rgba(11, 94, 215, .45);
			box-shadow: 0 0 0 .25rem rgba(11, 94, 215, .12);
		}

		.input-icon{ position: relative; }
		.input-icon i{
			position: absolute;
			left: 12px;
			top: 50%;
			transform: translateY(-50%);
			color: rgba(15, 23, 42, .45);
			pointer-events: none;
		}
		.input-icon input{ padding-left: 40px; }

		.btn-login{
			border-radius: 14px;
			padding: 11px 12px;
			height: 46px;
			font-weight: 900;
			letter-spacing: -.01em;
			background: var(--primary) !important;
			border: 0 !important;
			color: #fff !important;
			box-shadow: var(--shadow-soft);
		}
		.btn-login:hover{ background: var(--primary-hover) !important; }
		.btn-login:active{ transform: translateY(1px); }

		.auth-footer{
			padding: 14px 22px;
			border-top: 1px solid rgba(15, 23, 42, .06);
			background: rgba(15, 23, 42, .02);
			color: var(--muted);
			font-size: .88rem;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			flex-wrap: wrap;
		}

		@media (max-width: 575.98px){
			.auth-card{ border-radius: 18px; }
			.auth-header{ padding: 22px 18px 12px 18px; }
			.auth-body{ padding: 18px; }
			.brand-logo{ width: 130px; height: 130px; }
		}
	</style>
</head>

<body>
	<div class="auth-wrap">
		<div class="auth-card">

			<div class="auth-header">
				<img src="https://neto5.xitic.com.mx/Logo.png" class="brand-logo" alt="Xitic">
			</div>

			<div class="auth-body">
				<h1 class="title">Iniciar sesión</h1>
				<p class="subtitle">Ingresa tus credenciales para continuar.</p>

				<div id="Login_alerta" class="alert alert-danger d-none" role="alert"></div>

				<form
					id="Login"
					name="Login"
					method="POST"
					autocomplete="off"
					data-action="index.php?System=Login&a=validar"
				>
					<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($Csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

					<div class="mb-3">
						<label class="form-label" for="email">Correo Electrónico</label>
						<div class="input-icon">
							<i class="fa-solid fa-envelope"></i>
							<input
								class="form-control"
								type="email"
								name="email"
								id="email"
								required="required"
								maxlength="100"
								autofocus
								autocomplete="off"
								placeholder="ejemplo@correo.com"
							>
						</div>
					</div>

					<div class="mb-4">
						<label class="form-label" for="pass">Contraseña</label>
						<div class="input-icon">
							<i class="fa-solid fa-lock"></i>
							<input
								class="form-control"
								type="password"
								name="pass"
								id="pass"
								required="required"
								maxlength="72"
								autocomplete="off"
								placeholder="Escribe tu contraseña"
							>
						</div>
					</div>

					<button id="Btn_login" type="submit" class="btn btn-login w-100">
						<i class="fa-solid fa-right-to-bracket me-1"></i>Entrar
					</button>
				</form>
			</div>

			<div class="auth-footer">
				<span>© <?php echo date('Y'); ?> Xitic</span>
				<span><i class="fa-solid fa-shield-halved me-1"></i>Acceso seguro</span>
			</div>

		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<script>
	(function () {
		var Form_login = document.getElementById('Login');
		var Alerta = document.getElementById('Login_alerta');
		var Boton = document.getElementById('Btn_login');

		function mostrarError(Mensaje) {
			Alerta.textContent = Mensaje;
			Alerta.classList.remove('d-none');
		}

		function ocultarError() {
			Alerta.textContent = '';
			Alerta.classList.add('d-none');
		}

		Form_login.addEventListener('submit', async function (Evento) {
			Evento.preventDefault();
			ocultarError();

			Boton.disabled = true;
			Boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validando...';

			var Url_validar = Form_login.getAttribute('data-action');
			var Datos = new FormData(Form_login);

			try {
				var Respuesta = await fetch(Url_validar, {
					method: 'POST',
					body: Datos,
					credentials: 'same-origin',
					headers: { 'Accept': 'application/json' }
				});

				var Json = null;
				try {
					Json = await Respuesta.json();
				} catch (e) {
					Json = null;
				}

				if (Json && parseInt(Json.success, 10) === 1) {
					if (Json.redirect) {
						window.location.href = Json.redirect;
						return;
					}
					window.location.href = 'index.php?System=Dashboard';
					return;
				}

				var Mensaje = 'Credenciales inválidas.';
				if (Json && Json.mensaje) {
					Mensaje = Json.mensaje;
				}
				mostrarError(Mensaje);

			} catch (e) {
				mostrarError('No se pudo conectar al servidor.');
			} finally {
				Boton.disabled = false;
				Boton.innerHTML = '<i class="fa-solid fa-right-to-bracket me-1"></i>Entrar';
			}
		});
	})();
	</script>
</body>
</html>
