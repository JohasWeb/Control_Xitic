<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias - <?= htmlspecialchars($Encuesta['titulo']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
<?php
// Configuración de Tema (Copy-Paste consistencia con responder.php)
$Theme = 'light';
$CustomColor = '#4f46e5'; 

if (!empty($Encuesta['configuracion_json'])) {
    $Conf = json_decode($Encuesta['configuracion_json'], true);
    if ($Conf) {
        if (isset($Conf['tema'])) $Theme = $Conf['tema'];
        if (isset($Conf['color'])) $CustomColor = $Conf['color'];
    }
}

// Define Theme Colors
$ThemesMap = [
    'light' => ['bg' => '#f8fafc', 'card' => '#ffffff', 'text' => '#1e293b', 'border' => '#e2e8f0'],
    'navy' => ['bg' => '#0f172a', 'card' => '#1e293b', 'text' => '#f8fafc', 'border' => '#334155'],
    'dark' => ['bg' => '#000000', 'card' => '#121212', 'text' => '#ffffff', 'border' => '#27272a']
];
$CurrentTheme = $ThemesMap[$Theme] ?? $ThemesMap['light'];
?>
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($CustomColor) ?>;
            --bg-color: <?= $CurrentTheme['bg'] ?>;
            --card-bg: <?= $CurrentTheme['card'] ?>;
            --text-main: <?= $CurrentTheme['text'] ?>;
            --border-color: <?= $CurrentTheme['border'] ?>;
            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --font-display: 'Outfit', system-ui, -apple-system, sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: var(--font-sans);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .thank-you-card {
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            padding: 2.5rem 2rem 3rem 2rem;
            max-width: 480px;
            width: 90%;
            text-align: center;
            position: relative;
            margin-top: 60px; /* Space for popped logo */
        }

        .brand-logo-container {
            width: auto;
            max-width: 200px;
            height: auto;
            min-height: 80px;
            background: #fff;
            border-radius: 20px;
            padding: 10px;
            margin: -70px auto 2rem auto; /* Pop out effect */
            box-shadow: 0 8px 24px rgba(0,0,0,0.08); /* Softer shadow */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 10;
        }
        
        .brand-logo {
            max-height: 80px;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        .check-icon {
            width: 72px;
            height: 72px;
            background-color: #22c55e;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 16px rgba(34, 197, 94, 0.25);
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        h1 {
            font-family: var(--font-display);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        p { opacity: 0.8; }

        @keyframes popIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
        
        .anime-enter {
            animation: fadeUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="thank-you-card anime-enter">
        <!-- Logo -->
        <?php if (!empty($Encuesta['imagen_header'])): ?>
            <div class="brand-logo-container">
                <img src="<?= htmlspecialchars($Encuesta['imagen_header']) ?>" alt="Logo" class="brand-logo">
            </div>
        <?php endif; ?>

        <!-- Check -->
        <div class="check-icon">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1>¡Gracias!</h1>
        <p class="mb-0">Tus respuestas han sido registradas correctamente.</p>
        
        <div class="mt-4 text-muted small">
            <?= htmlspecialchars($Encuesta['titulo']) ?>
        </div>
    </div>
    
    <div class="mt-4 text-muted small opacity-50">
        Powered by Xitic Control
    </div>

</body>
</html>
