<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>InventoryManager - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --surface: #ffffff;
            --ink: #0f1733;
            --ink-soft: #5f6782;
            --border: #dbe1f1;
            --success: #0f9960;
            --danger: #d93025;
            --primary: #1347a5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at 14% 18%, rgba(243, 179, 0, 0.16), transparent 32%),
                radial-gradient(circle at 82% 82%, rgba(255, 255, 255, 0.09), transparent 28%),
                linear-gradient(140deg, #061638 0%, #0c2f72 48%, #1347a5 100%);
            overflow-x: hidden;
        }

        .landing {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .landing::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 42px 42px;
            opacity: 0.12;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 22px 48px rgba(4, 14, 42, 0.35);
            padding: 36px 32px;
            color: var(--ink);
            backdrop-filter: blur(2px);
            text-align: left;
            position: relative;
            z-index: 1;
        }

        .login-card h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.6rem;
            line-height: 1.2;
            margin-bottom: 8px;
            text-align: center;
        }

        .login-card .subtitle {
            color: var(--ink-soft);
            margin-bottom: 24px;
            line-height: 1.5;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(19, 71, 165, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Space Grotesk', sans-serif;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 8px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(19, 71, 165, 0.25);
        }

        .message {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: left;
        }

        .error {
            color: var(--danger);
            background: #ffe8e6;
            border: 1px solid #f8bfbb;
        }

        .footer-note {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed #d7dced;
            color: #7e87a3;
            font-size: 13px;
            text-align: center;
        }

    </style>
</head>

<body>
    <main class="landing">
        <section class="login-card" aria-label="Acceso al sistema">
            <h2>InventoryManager</h2>
            <p class="subtitle">Ingresa tus credenciales para acceder al sistema</p>

            @if ($errors->any())
                <div class="message error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('custom.login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="submit-btn">
                    Iniciar sesión
                </button>
            </form>

            <p class="footer-note">Sistema de gestión de inventarios para laboratorios</p>
        </section>
    </main>
</body>

</html>
