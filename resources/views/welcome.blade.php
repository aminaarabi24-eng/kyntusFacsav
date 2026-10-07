<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- FontAwesome Icons -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100vh;
            overflow: hidden;
            background: #000000;
            font-family: 'Segoe UI', -apple-system, Tahoma, sans-serif;
            position: relative;
        }


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
            filter: blur(110px);
            opacity: 0.55;
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

        .circle:nth-child(1)  { width: 6px;  height: 6px;  left: 8%;  top: 20%; animation-duration: 12s; animation-delay: -3s;  }
        .circle:nth-child(2)  { width: 4px;  height: 4px;  left: 18%; top: 75%; animation-duration: 17s; animation-delay: -8s;  }
        .circle:nth-child(3)  { width: 8px;  height: 8px;  left: 28%; top: 15%; animation-duration: 20s; animation-delay: -10s; }
        .circle:nth-child(4)  { width: 5px;  height: 5px;  left: 38%; top: 85%; animation-duration: 14s; animation-delay: -5s;  }
        .circle:nth-child(5)  { width: 6px;  height: 6px;  left: 48%; top: 10%; animation-duration: 18s; animation-delay: -7s;  }
        .circle:nth-child(6)  { width: 4px;  height: 4px;  left: 58%; top: 80%; animation-duration: 15s; animation-delay: -4s;  }
        .circle:nth-child(7)  { width: 7px;  height: 7px;  left: 68%; top: 25%; animation-duration: 22s; animation-delay: -12s; }
        .circle:nth-child(8)  { width: 5px;  height: 5px;  left: 78%; top: 70%; animation-duration: 16s; animation-delay: -6s;  }
        .circle:nth-child(9)  { width: 9px;  height: 9px;  left: 88%; top: 15%; animation-duration: 19s; animation-delay: -9s;  }
        .circle:nth-child(10) { width: 4px;  height: 4px;  left: 93%; top: 85%; animation-duration: 13s; animation-delay: -2s;  }

        @keyframes floatCircle {
            0%   { transform: translate(0, 0) scale(1);           opacity: 0.2; }
            25%  { transform: translate(60px, -80px) scale(1.3);  opacity: 0.8; }
            50%  { transform: translate(-40px, -150px) scale(0.8); opacity: 0.35; }
            75%  { transform: translate(90px, -60px) scale(1.2);  opacity: 0.7; }
            100% { transform: translate(0, 0) scale(1);           opacity: 0.2; }
        }


        .navbar-custom {

            position: relative;
            z-index: 100;

            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);

            border-bottom: 1px solid rgba(56, 189, 248, 0.15);

            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);

            padding: 12px 24px !important;
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-brand .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(135deg, #0066cc 0%, #00aaff 100%);

            box-shadow:
                0 6px 20px rgba(0, 136, 238, 0.45),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;

            font-size: 18px;
            color: #ffffff;
        }

        .navbar-brand .brand-text {
            background: linear-gradient(135deg, #ffffff, #a5d8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;

            padding: 8px 18px;
            border-radius: 50px;

            font-size: 13px;
            font-weight: 600;

            transition: all 0.25s ease;

            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.6);
            color: #ffffff;

            box-shadow: 0 0 20px rgba(239, 68, 68, 0.35);
            transform: translateY(-1px);
        }


        .card-custom {

            position: relative;

            border: 1px solid rgba(56, 189, 248, 0.18);
            border-radius: 24px;

            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.7),
                0 0 0 1px rgba(255, 255, 255, 0.03) inset,
                0 0 60px rgba(0, 102, 255, 0.15);

            overflow: hidden;
        }

        .card-custom::before {
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


        .dashboard-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.3px;

            background: linear-gradient(135deg, #ffffff, #a5d8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;

            margin-bottom: 6px;
        }

        .dashboard-subtitle {
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.5;
        }


        .file-upload-box {

            border: 1.5px dashed rgba(56, 189, 248, 0.25);
            border-radius: 14px;

            padding: 16px;

            background: rgba(2, 6, 23, 0.5);

            transition: all 0.3s ease;

            height: 100%;

            position: relative;
        }

        .file-upload-box:hover {
            border-color: rgba(0, 170, 255, 0.6);
            background: rgba(2, 6, 23, 0.75);
            box-shadow: 0 0 25px rgba(0, 170, 255, 0.12);
            transform: translateY(-2px);
        }


        .file-upload-label {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-upload-label .badge-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            width: 22px;
            height: 22px;

            border-radius: 7px;

            font-size: 11px;
            font-weight: 700;

            background: rgba(0, 170, 255, 0.15);
            color: #00aaff;

            border: 1px solid rgba(0, 170, 255, 0.3);
        }

        .file-upload-label .optional {
            color: #64748b;
            font-size: 11px;
            font-weight: 400;
            margin-left: auto;
        }


        .form-control {

            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(56, 189, 248, 0.2);
            color: #e2e8f0;

            border-radius: 10px;

            font-size: 12.5px;

            padding: 8px 12px;

            transition: all 0.25s ease;
        }

        .form-control::file-selector-button {
            background: linear-gradient(135deg, #0055a4, #0088ee);
            color: white;
            border: none;
            border-radius: 7px;
            padding: 6px 14px;
            font-size: 11.5px;
            font-weight: 600;
            margin-right: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .form-control::file-selector-button:hover {
            background: linear-gradient(135deg, #0066cc, #00aaff);
            box-shadow: 0 0 15px rgba(0, 170, 255, 0.4);
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.95) !important;
            border-color: rgba(0, 170, 255, 0.6);
            box-shadow: 0 0 0 4px rgba(0, 170, 255, 0.12);
            color: #ffffff;
        }


        .btn-primary-custom {

            background: linear-gradient(135deg, #0055a4 0%, #0088ee 50%, #00aaff 100%);
            background-size: 200% 200%;

            border: none;
            color: #ffffff;

            padding: 11px 26px;

            border-radius: 50px;

            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.3px;

            box-shadow:
                0 10px 25px rgba(0, 136, 238, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;

            transition: all 0.3s ease;

            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom::after {
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

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            background-position: 100% 50%;
            color: #ffffff;
            box-shadow:
                0 16px 35px rgba(0, 136, 238, 0.55),
                0 0 30px rgba(0, 170, 255, 0.35),
                0 0 0 1px rgba(255, 255, 255, 0.15) inset;
        }

        .btn-primary-custom:hover::after {
            left: 100%;
        }


        .btn-reset {

            background: transparent;
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #94a3b8;

            padding: 9px 20px;
            border-radius: 50px;

            font-size: 12.5px;
            font-weight: 600;

            transition: all 0.25s ease;
        }

        .btn-reset:hover {
            background: rgba(148, 163, 184, 0.1);
            border-color: rgba(148, 163, 184, 0.6);
            color: #e2e8f0;
        }


        /* =====================================================
           ALERTE MODERNE - ERREUR
        ===================================================== */

        .alert-modern {
            border-radius: 12px;
            font-size: 13px;
            padding: 12px 16px;
            margin-bottom: 20px;

            border: 1px solid rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;

            backdrop-filter: blur(10px);
        }

        .alert-modern .btn-close {
            filter: invert(1);
            opacity: 0.6;
        }


        /* =====================================================
           ALERTE MODERNE - SUCCÈS
        ===================================================== */

        .alert-success-modern {

            border-radius: 12px;
            font-size: 13px;
            padding: 12px 16px;
            margin-bottom: 20px;

            border: 1px solid rgba(34, 197, 94, 0.35);
            background: rgba(34, 197, 94, 0.1);
            color: #86efac;

            backdrop-filter: blur(10px);

            animation: successAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1);

            box-shadow:
                0 0 20px rgba(34, 197, 94, 0.15),
                0 0 0 1px rgba(34, 197, 94, 0.05) inset;
        }

        .alert-success-modern .btn-close {
            filter: invert(1);
            opacity: 0.6;
        }

        .alert-success-modern i {
            color: #22c55e;
            filter: drop-shadow(0 0 6px rgba(34, 197, 94, 0.6));
        }


        @keyframes successAppear {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
                filter: blur(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }


        /* =====================================================
           LOADING OVERLAY
        ===================================================== */

        #loading-overlay {

            display: none;

            position: fixed;
            top: 0;
            left: 0;

            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.85);

            z-index: 9999;

            justify-content: center;
            align-items: center;
            flex-direction: column;

            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        #loading-overlay .spinner-border {
            width: 3.5rem;
            height: 3.5rem;

            color: #00aaff !important;

            border-width: 3px;

            filter: drop-shadow(0 0 20px rgba(0, 170, 255, 0.8));

            margin-bottom: 20px;
        }

        #loading-overlay h6 {
            color: #ffffff;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.3px;
        }

        #loading-overlay p {
            color: #94a3b8;
            font-size: 13px;
        }


        /* =====================================================
           SCROLLBAR
        ===================================================== */

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 170, 255, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 170, 255, 0.5);
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 768px) {

            .navbar-custom {
                padding: 10px 16px !important;
            }

            .navbar-brand .brand-text {
                font-size: 14px;
            }

            .dashboard-title {
                font-size: 21px;
            }

            .card-custom {
                border-radius: 20px;
            }

        }

    </style>

</head>


<body class="d-flex flex-column">


    <!-- =========================================
         AURORA + GRID + CERCLES
    ========================================== -->

    <div class="aurora"></div>
    <div class="grid-overlay"></div>

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
    </div>


    <!-- =========================================
         LOADER
    ========================================== -->

    <div id="loading-overlay">

        <div class="spinner-border" role="status"></div>

        <h6>Traitement des fichiers en cours...</h6>

        <p class="mb-0">Veuillez patienter quelques instants</p>

    </div>


    <!-- =========================================
         NAVBAR
    ========================================== -->

    <nav class="navbar navbar-custom">

        <div class="container-fluid">

            <span class="navbar-brand">

                <div class="brand-icon">
                    <i class="fa-solid fa-file-excel"></i>
                </div>

                <span class="brand-text">
                    Gestion des Factures SAV
                </span>

            </span>


            <form
                action="{{ route('logout') }}"
                method="POST"
                class="d-inline m-0"
            >

                @csrf

                <button type="submit" class="btn-logout">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Déconnexion

                </button>

            </form>

        </div>

    </nav>


    <!-- =========================================
         CONTENU PRINCIPAL
    ========================================== -->

    <div class="container my-auto position-relative" style="z-index: 10;">

        <div class="row justify-content-center">

            <div class="col-lg-10 col-xl-9">

                <div class="card card-custom p-4 p-md-5">


                    <!-- TITRE -->

                    <div class="text-center mb-4">

                        <h3 class="dashboard-title">
                            Tableau de Bord
                        </h3>

                        <p class="dashboard-subtitle mb-0">
                            Importez votre fichier principal.<br>
                            Les fichiers complémentaires sont optionnels.
                        </p>

                    </div>


                    <!-- =========================================
                         MESSAGE ERREUR
                    ========================================== -->

                    @if(session('error'))

                        <div
                            class="alert alert-modern alert-dismissible fade show"
                            role="alert"
                        >

                            <i class="fa-solid fa-triangle-exclamation me-2"></i>

                            {{ session('error') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close"
                            ></button>

                        </div>

                    @endif


                    <!-- =========================================
                         MESSAGE SUCCÈS
                    ========================================== -->

                    @if(session('success'))

                        <div
                            class="alert alert-success-modern alert-dismissible fade show"
                            role="alert"
                        >

                            <i class="fa-solid fa-circle-check me-2"></i>

                            {{ session('success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close"
                            ></button>

                        </div>

                    @endif


                    <!-- =========================================
                         FORMULAIRE
                    ========================================== -->

                    <form
                        id="upload-form"
                        action="{{ route('process.excel') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        <!-- =====================================
                             LES 3 FICHIERS
                        ====================================== -->

                        <div class="row g-3 mb-4">


                            <!-- 1. FICHIER PRINCIPAL -->

                            <div class="col-md-4">

                                <div class="file-upload-box">

                                    <label
                                        for="excel_file"
                                        class="file-upload-label"
                                    >

                                        <span class="badge-num">1</span>

                                        Factures

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input
                                        type="file"
                                        name="excel_file"
                                        id="excel_file"
                                        class="form-control form-control-sm"
                                        required
                                        accept=".xlsx,.xls,.csv"
                                    >

                                </div>

                            </div>


                            <!-- 2. SAV MC (optionnel) -->

                            <div class="col-md-4">

                                <div class="file-upload-box">

                                    <label
                                        for="sav_mc_file"
                                        class="file-upload-label"
                                    >

                                        <span class="badge-num">2</span>

                                        SAV MC

                                        <span class="optional">optionnel</span>

                                    </label>


                                    <input
                                        type="file"
                                        name="sav_mc_file"
                                        id="sav_mc_file"
                                        class="form-control form-control-sm"
                                        accept=".xlsx,.xls,.csv"
                                    >

                                </div>

                            </div>


                            <!-- 3. PBO PHOTO NACELLE (optionnel) -->

                            <div class="col-md-4">

                                <div class="file-upload-box">

                                    <label
                                        for="third_file"
                                        class="file-upload-label"
                                    >

                                        <span class="badge-num">3</span>

                                        PBO Photo Nacelle

                                        <span class="optional">optionnel</span>

                                    </label>


                                    <input
                                        type="file"
                                        name="third_file"
                                        id="third_file"
                                        class="form-control form-control-sm"
                                        accept=".xlsx,.xls,.csv"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- =========================================
                             BOUTONS
                        ========================================== -->

                        <div
                            class="d-flex justify-content-between align-items-center pt-3"
                            style="border-top: 1px solid rgba(56, 189, 248, 0.1);"
                        >


                            <!-- RESET -->

                            <button
                                type="reset"
                                id="reset-btn"
                                class="btn-reset"
                            >

                                <i class="fa-solid fa-rotate-left me-1"></i>

                                Réinitialiser

                            </button>


                            <!-- SUBMIT -->

                            <button
                                type="submit"
                                id="submit-btn"
                                class="btn-primary-custom"
                            >

                                <i class="fa-solid fa-gears me-1"></i>

                                Traiter et Télécharger

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <!-- Bootstrap 5 JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- =========================================
         SCRIPT DE TRAITEMENT
    ========================================== -->

    <script>

        const uploadForm = document.getElementById('upload-form');
        const loadingOverlay = document.getElementById('loading-overlay');
        const submitBtn = document.getElementById('submit-btn');
        const resetBtn = document.getElementById('reset-btn');


        /* =====================================================
           FONCTION TOAST SUCCÈS
        ===================================================== */

        function showSuccessToast(message) {

            /* Créer le conteneur si inexistant */
            let container = document.getElementById('toast-container');

            if (!container) {

                container = document.createElement('div');
                container.id = 'toast-container';

                container.style.cssText = `
                    position: fixed;
                    top: 90px;
                    right: 24px;
                    z-index: 99999;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    pointer-events: none;
                `;

                document.body.appendChild(container);
            }


            /* Créer le toast */
            const toast = document.createElement('div');

            toast.style.cssText = `
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(34, 197, 94, 0.4);
                border-left: 4px solid #22c55e;
                border-radius: 14px;
                padding: 14px 20px;
                color: #e2e8f0;
                font-size: 13.5px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 300px;
                box-shadow:
                    0 15px 40px rgba(0, 0, 0, 0.6),
                    0 0 25px rgba(34, 197, 94, 0.2);
                pointer-events: auto;
                opacity: 0;
                transform: translateX(120%);
                transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            `;

            toast.innerHTML = `
                <i class="fa-solid fa-circle-check"
                   style="color:#22c55e;font-size:18px;
                          filter:drop-shadow(0 0 8px rgba(34,197,94,0.8));"
                ></i>
                <span>${message}</span>
            `;

            container.appendChild(toast);


            /* Animation d'entrée */
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            });


            /* Animation de sortie */
            setTimeout(() => {

                toast.style.opacity = '0';
                toast.style.transform = 'translateX(120%)';

                setTimeout(() => toast.remove(), 500);

            }, 4000);
        }


        /* =====================================================
           SOUMISSION DU FORMULAIRE
        ===================================================== */

        uploadForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            loadingOverlay.style.display = 'flex';

            submitBtn.disabled = true;

            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Traitement...
            `;


            try {

                const formData = new FormData(uploadForm);

                const response = await fetch(
                    uploadForm.action,
                    {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );


                const contentType = response.headers.get('content-type') || '';


                /* Si Laravel retourne une page HTML */

                if (contentType.includes('text/html')) {

                    const html = await response.text();

                    document.open();
                    document.write(html);
                    document.close();

                    return;
                }


                if (!response.ok) {

                    throw new Error(
                        'Une erreur est survenue pendant le traitement.'
                    );
                }


                const blob = await response.blob();


                const contentDisposition =
                    response.headers.get('Content-Disposition');


                let fileName = 'facture_complete.xlsx';


                if (contentDisposition) {

                    const match = contentDisposition.match(
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
                    );

                    if (match && match[1]) {

                        fileName = match[1].replace(/['"]/g, '');
                    }
                }


                const url = window.URL.createObjectURL(blob);

                const downloadLink = document.createElement('a');

                downloadLink.href = url;
                downloadLink.download = fileName;

                document.body.appendChild(downloadLink);

                downloadLink.click();

                downloadLink.remove();

                window.URL.revokeObjectURL(url);


                /* =========================================
                   ✅ AFFICHER LE TOAST DE SUCCÈS
                ========================================= */

                showSuccessToast(
                    'Fichier traité avec succès — Téléchargement lancé !'
                );


            } catch (error) {

                console.error(error);

                alert(
                    'Une erreur est survenue pendant le traitement du fichier.'
                );


            } finally {

                loadingOverlay.style.display = 'none';

                submitBtn.disabled = false;

                submitBtn.innerHTML = `
                    <i class="fa-solid fa-gears me-1"></i>
                    Traiter et Télécharger
                `;

            }

        });


        /* =====================================================
           BOUTON RÉINITIALISER
        ===================================================== */

        resetBtn.addEventListener('click', function() {

            loadingOverlay.style.display = 'none';

            submitBtn.disabled = false;

            submitBtn.innerHTML = `
                <i class="fa-solid fa-gears me-1"></i>
                Traiter et Télécharger
            `;

        });

    </script>


</body>

</html>