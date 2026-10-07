<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - System Facturation</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        /* =====================================================
           BACKGROUND NOIR PROFOND AVEC EFFET AURORA
        ===================================================== */

        body {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: "Segoe UI", -apple-system, Arial, sans-serif;

            background: #000000;

            overflow: hidden;

            position: relative;
        }


        /* AURORA BACKGROUND */

        .aurora {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .aurora::before,
        .aurora::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
        }

        .aurora::before {
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, #0044cc, transparent 70%);
            top: -250px;
            left: -250px;
            animation: aurora1 18s ease-in-out infinite alternate;
        }

        .aurora::after {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #00aaff, transparent 70%);
            bottom: -200px;
            right: -200px;
            animation: aurora2 22s ease-in-out infinite alternate;
        }

        @keyframes aurora1 {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(200px, 150px) scale(1.2); }
            100% { transform: translate(100px, 300px) scale(0.9); }
        }

        @keyframes aurora2 {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(-180px, -120px) scale(1.15); }
            100% { transform: translate(-80px, -250px) scale(0.95); }
        }


        /* =====================================================
           GRILLE LUMINEUSE SUBTILE
        ===================================================== */

        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;

            background-image:
                linear-gradient(rgba(0, 102, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 102, 255, 0.05) 1px, transparent 1px);

            background-size: 60px 60px;

            mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 80%);
        }


        /* =====================================================
           PETITS CERCLES BLEUS AMÉLIORÉS
        ===================================================== */

        .background-circles {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 2;
            pointer-events: none;
        }


        .circle {
            position: absolute;
            border-radius: 50%;
            background: #00aaff;
            box-shadow:
                0 0 12px #00aaff,
                0 0 25px rgba(0, 170, 255, 0.6),
                0 0 40px rgba(0, 102, 255, 0.3);
            animation: floatCircle linear infinite;
        }

        .circle:nth-child(1)  { width: 7px;  height: 7px;  left: 8%;  top: 20%; animation-duration: 12s; animation-delay: -3s;  }
        .circle:nth-child(2)  { width: 4px;  height: 4px;  left: 18%; top: 75%; animation-duration: 17s; animation-delay: -8s;  }
        .circle:nth-child(3)  { width: 9px;  height: 9px;  left: 28%; top: 15%; animation-duration: 20s; animation-delay: -10s; }
        .circle:nth-child(4)  { width: 5px;  height: 5px;  left: 38%; top: 85%; animation-duration: 14s; animation-delay: -5s;  }
        .circle:nth-child(5)  { width: 6px;  height: 6px;  left: 48%; top: 10%; animation-duration: 18s; animation-delay: -7s;  }
        .circle:nth-child(6)  { width: 4px;  height: 4px;  left: 58%; top: 80%; animation-duration: 15s; animation-delay: -4s;  }
        .circle:nth-child(7)  { width: 8px;  height: 8px;  left: 68%; top: 25%; animation-duration: 22s; animation-delay: -12s; }
        .circle:nth-child(8)  { width: 5px;  height: 5px;  left: 78%; top: 70%; animation-duration: 16s; animation-delay: -6s;  }
        .circle:nth-child(9)  { width: 10px; height: 10px; left: 88%; top: 15%; animation-duration: 19s; animation-delay: -9s;  }
        .circle:nth-child(10) { width: 4px;  height: 4px;  left: 93%; top: 85%; animation-duration: 13s; animation-delay: -2s;  }
        .circle:nth-child(11) { width: 6px;  height: 6px;  left: 13%; top: 45%; animation-duration: 21s; animation-delay: -11s; }
        .circle:nth-child(12) { width: 5px;  height: 5px;  left: 83%; top: 50%; animation-duration: 17s; animation-delay: -5s;  }


        @keyframes floatCircle {
            0%   { transform: translate(0, 0) scale(1);        opacity: 0.2; }
            25%  { transform: translate(60px, -80px) scale(1.3);  opacity: 0.8; }
            50%  { transform: translate(-40px, -150px) scale(0.8); opacity: 0.35; }
            75%  { transform: translate(90px, -60px) scale(1.2);  opacity: 0.7; }
            100% { transform: translate(0, 0) scale(1);        opacity: 0.2; }
        }


        /* =====================================================
           LOGIN CARD - GLASSMORPHISM MODERNE
        ===================================================== */

        .login-card {

            position: relative;
            z-index: 10;

            width: 100%;
            max-width: 460px;

            padding: 42px 42px 32px;

            border-radius: 24px;

            /* Effet verre dépoli */
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);

            border: 1px solid rgba(56, 189, 248, 0.18);

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.7),
                0 0 0 1px rgba(255, 255, 255, 0.03) inset,
                0 0 60px rgba(0, 102, 255, 0.15);

            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);

            overflow: hidden;
        }


        /* Ligne lumineuse en haut de la carte */

        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(0, 170, 255, 0.8),
                rgba(0, 102, 255, 0.8),
                transparent
            );
        }


        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
                filter: blur(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }


        /* =====================================================
           LOGO MODERNE
        ===================================================== */

        .logo {

            width: 72px;
            height: 72px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 20px;

            font-size: 34px;

            background: linear-gradient(135deg, #0066cc 0%, #00aaff 100%);

            box-shadow:
                0 10px 30px rgba(0, 102, 204, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset,
                0 0 40px rgba(0, 170, 255, 0.35);

            position: relative;

            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { box-shadow: 0 10px 30px rgba(0, 102, 204, 0.5), 0 0 0 1px rgba(255,255,255,0.1) inset, 0 0 40px rgba(0,170,255,0.35); }
            50%      { box-shadow: 0 10px 30px rgba(0, 102, 204, 0.7), 0 0 0 1px rgba(255,255,255,0.15) inset, 0 0 60px rgba(0,170,255,0.55); }
        }


        /* =====================================================
           TITRE
        ===================================================== */

        .login-title {
            font-size: 24px;
            font-weight: 700;

            color: #ffffff;

            margin-bottom: 8px;
            text-align: center;

            letter-spacing: -0.3px;

            background: linear-gradient(135deg, #ffffff, #a5d8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }


        .login-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.5;
        }


        /* =====================================================
           LABEL
        ===================================================== */

        .form-label {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }


        /* =====================================================
           INPUT - STYLE SOMBRE MODERNE
        ===================================================== */

        .input-wrapper {
            position: relative;
        }


        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            z-index: 3;
            opacity: 0.7;
            pointer-events: none;
            transition: 0.25s;
        }


        .form-control {

            height: 52px;

            border-radius: 12px;

            border: 1px solid rgba(56, 189, 248, 0.15);

            background: rgba(2, 6, 23, 0.6);

            padding-left: 46px;
            padding-right: 46px;

            font-size: 14px;
            color: #ffffff;

            transition: all 0.25s ease;

            outline: none;
        }


        .form-control::placeholder {
            color: #64748b;
            opacity: 1;
        }


        .form-control:focus {

            border-color: rgba(0, 170, 255, 0.6);

            background: rgba(2, 6, 23, 0.85);

            box-shadow:
                0 0 0 4px rgba(0, 170, 255, 0.12),
                0 0 20px rgba(0, 170, 255, 0.15);

            color: #ffffff;
        }


        .form-control:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            opacity: 1;
            filter: drop-shadow(0 0 6px rgba(0, 170, 255, 0.8));
        }


        /* =====================================================
           PASSWORD TOGGLE
        ===================================================== */

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);

            border: none;
            background: transparent;
            cursor: pointer;

            font-size: 16px;
            z-index: 5;

            color: #64748b;

            padding: 4px;

            transition: 0.25s;

            border-radius: 6px;
        }


        .password-toggle:hover {
            color: #00aaff;
            background: rgba(0, 170, 255, 0.1);
        }


        /* =====================================================
           BOUTON - DÉGRADÉ LUMINEUX
        ===================================================== */

        .login-button {

            height: 52px;
            width: 100%;

            border: none;
            border-radius: 12px;

            background: linear-gradient(135deg, #0055a4 0%, #0088ee 50%, #00aaff 100%);

            background-size: 200% 200%;

            color: white;

            font-size: 14px;
            font-weight: 700;

            letter-spacing: 0.5px;

            box-shadow:
                0 10px 25px rgba(0, 136, 238, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;

            transition: all 0.3s ease;

            cursor: pointer;

            position: relative;

            overflow: hidden;
        }


        .login-button::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.25),
                transparent
            );
            transition: 0.6s;
        }


        .login-button:hover {
            transform: translateY(-2px);

            background-position: 100% 50%;

            box-shadow:
                0 16px 35px rgba(0, 136, 238, 0.55),
                0 0 30px rgba(0, 170, 255, 0.35),
                0 0 0 1px rgba(255, 255, 255, 0.15) inset;
        }


        .login-button:hover::after {
            left: 100%;
        }


        .login-button:active {
            transform: translateY(0);
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            margin-top: 26px;
            padding-top: 20px;

            text-align: center;

            font-size: 11px;
            line-height: 1.7;

            color: #475569;

            border-top: 1px solid rgba(56, 189, 248, 0.08);

            letter-spacing: 0.3px;
        }


        /* =====================================================
           ALERTE MODERNE
        ===================================================== */

        .alert {

            border-radius: 12px;

            font-size: 12.5px;

            padding: 12px 16px;

            margin-bottom: 20px;

            border: 1px solid rgba(239, 68, 68, 0.3);

            background: rgba(239, 68, 68, 0.1);

            color: #fca5a5;

            backdrop-filter: blur(10px);
        }


        .alert .btn-close {
            filter: invert(1);
            opacity: 0.6;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 550px) {

            .login-card {
                max-width: 92%;
                padding: 32px 24px 24px;
                border-radius: 20px;
            }

            .login-title {
                font-size: 21px;
            }

            .logo {
                width: 64px;
                height: 64px;
                font-size: 30px;
            }

            .form-control,
            .login-button {
                height: 50px;
            }

        }

    </style>

</head>


<body>

    <!-- Aurora background -->
    <div class="aurora"></div>

    <!-- Grid overlay -->
    <div class="grid-overlay"></div>

    <!-- Cercles animés -->
    <div class="background-circles">
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
        <span class="circle"></span>
    </div>


    <!-- =====================================================
         LOGIN CARD
    ===================================================== -->

    <div class="login-card">

        <!-- Logo + Titre -->
        <div class="text-center">

            <div class="logo">🧾</div>

            <h3 class="login-title">
                Gestion des Factures SAV
            </h3>

            <p class="login-subtitle">
                🔒 Espace sécurisé<br>
                Connectez-vous pour accéder au système
            </p>

        </div>


        <!-- ERREUR -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>
        @endif


        <!-- FORMULAIRE -->
        <form action="{{ route('login.submit') }}" method="POST">

            @csrf

            <!-- Nom utilisateur -->
            <div class="mb-3">

                <label for="username" class="form-label">
                    Nom d'utilisateur
                </label>

                <div class="input-wrapper">

                    <input
                        type="text"
                        name="username"
                        id="username"
                        class="form-control"
                        placeholder="Entrez votre nom d'utilisateur"
                        required
                        autofocus
                    >

                    <span class="input-icon">👤</span>

                </div>

            </div>


            <!-- Mot de passe -->
            <div class="mb-4">

                <label for="password" class="form-label">
                    Mot de passe / Code
                </label>

                <div class="input-wrapper">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >

                    <span class="input-icon">🔒</span>

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        aria-label="Afficher le mot de passe"
                    >
                        👁️
                    </button>

                </div>

            </div>


            <!-- Bouton -->
            <div class="d-grid">

                <button type="submit" class="login-button">
                    🔑 &nbsp; Se connecter
                </button>

            </div>

        </form>


        <!-- Footer -->
        <div class="footer">
            © 2026 Gestion des Factures SAV<br>
            Tous droits réservés
        </div>

    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Show / Hide password -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const button = document.querySelector(".password-toggle");

            if (password.type === "password") {
                password.type = "text";
                button.textContent = "🙈";
            } else {
                password.type = "password";
                button.textContent = "👁️";
            }
        }
    </script>

</body>

</html>