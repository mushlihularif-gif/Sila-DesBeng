<style>
        #unit-carousel-container { scroll-margin-top: 5rem; }
        .unit-nav-wrapper { position: relative; z-index: 70 !important; isolation: isolate; }
        .unit-nav-controls { position: relative; z-index: 71; }
        .unit-nav-button { position: relative; z-index: 72; display: inline-flex; width: 3rem; height: 3rem; flex-shrink: 0; align-items: center; justify-content: center; border: 1px solid #e8edf2; border-radius: 999px; background: #fff; padding: 0; color: #334155; box-shadow: 0 5px 14px rgba(15, 23, 42, .1); transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease; }
        .unit-nav-button svg { width: 1.25rem; height: 1.25rem; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .unit-nav-button:hover { transform: translateY(-1px); border-color: #bfdbeb; background: #f8fbfd; color: #115789; }
        .unit-nav-button:focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }

        /* --- UNIT CAROUSEL STYLES (4 VISIBLE ITEMS) --- */
        .unit-stage-wrapper {
            height: 440px;
        }

        .unit-card {
            width: 280px;
            height: 280px;
            position: absolute;
            top: 45%;
            transform-origin: center center;
            /* Transisi halus saat tukar tempat */
            transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1);
            will-change: transform, left, opacity;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .unit-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.15));
        }

        /* POSISI 0: KIRI */
        .state-0 {
            left: 15% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.8;
            z-index: 20;
            filter: grayscale(10%);
        }

        /* POSISI 1: (TENGAH FOKUS) */
        .state-1 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(1.5) !important;
            opacity: 1;
            z-index: 50;
            filter: grayscale(0%) drop-shadow(0 25px 35px rgba(0, 0, 0, 0.25));
        }

        /* POSISI 2: KANAN  */
        .state-2 {
            left: 80% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.8;
            z-index: 20;
            filter: grayscale(10%);
        }

        /* POSISI 3: KANAN UJUNG (HIDDEN) */
        .state-3 {
            left: 100% !important;
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
        }

        /* POSISI 4: KIRI UJUNG (HIDDEN) */
        .state-4 {
            left: 0% !important;
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
        }

        /* POSISI 5: TERSEMBUNYI */
        .state-5 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(0.1) !important;
            opacity: 0;
            z-index: 5;
            pointer-events: none;
        }

        /* RESPONSIVE MOBILE - 3 COLUMN LAYOUT (CENTER FOCUS) */
        @media (max-width: 768px) {
            #unit-carousel-container {
                padding-top: 1rem !important;
                padding-bottom: 1.5rem !important;
            }
            .unit-stage-wrapper {
                height: 210px !important;
            }
            .unit-card {
                width: clamp(88px, 26vw, 110px) !important;
                height: clamp(88px, 26vw, 110px) !important;
                top: 43% !important;
            }
            .unit-card img {
                width: 96% !important;
                height: 96% !important;
                filter: drop-shadow(0 8px 14px rgba(15, 23, 42, 0.16)) !important;
            }

            /* Slot Kiri (Background Preview) */
            .state-0 {
                left: 18% !important;
                transform: translate(-50%, -50%) scale(0.64) !important;
                opacity: 0.58 !important;
                z-index: 20 !important;
                filter: grayscale(20%) !important;
            }

            /* Slot Tengah (Focus Terpusat, Proporsional dan Rapi) */
            .state-1 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(1.12) !important;
                opacity: 1 !important;
                z-index: 50 !important;
                filter: grayscale(0%) drop-shadow(0 8px 16px rgba(0,0,0,0.18)) !important;
            }

            /* Slot Kanan (Background Preview) */
            .state-2 {
                left: 82% !important;
                transform: translate(-50%, -50%) scale(0.64) !important;
                opacity: 0.58 !important;
                z-index: 20 !important;
                filter: grayscale(20%) !important;
            }

            /* Antrian (Hidden) */
            .state-3 {
                left: 120% !important;
                transform: translate(-50%, -50%) scale(0.3) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            .state-4 {
                left: -20% !important;
                transform: translate(-50%, -50%) scale(0.3) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            .state-5 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(0.1) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }

            /* Navigasi Tombol Geser di Bawah */
            .unit-nav-wrapper {
                position: relative !important;
                bottom: auto !important;
                left: auto !important;
                right: auto !important;
                width: 100% !important;
                margin-top: 0.25rem !important;
                margin-bottom: 0.75rem !important;
                padding: 0 8px !important;
                gap: 8px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .unit-nav-button { width: 2.5rem; height: 2.5rem; }
            .unit-nav-button svg { width: 1.1rem; height: 1.1rem; }
            .unit-title-box {
                min-width: 0 !important;
                max-width: 180px !important;
                flex: 1 !important;
            }
            #unit-title {
                font-size: 0.95rem !important;
                line-height: 1.25 !important;
            }

        }

        @media (max-width: 420px) {
            #unit-carousel-container {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .unit-stage-wrapper {
                height: 190px !important;
            }
            .unit-card {
                width: 92px !important;
                height: 92px !important;
            }
            .unit-card img {
                width: 94% !important;
                height: 94% !important;
            }
            .unit-title-box {
                max-width: 150px !important;
            }
            #unit-title {
                font-size: 0.9rem !important;
            }
            #unit-speech-box {
                padding: 0.75rem 0.85rem !important;
            }
        }

        @media (prefers-reduced-motion: reduce) { .unit-card { transition: none; } }
</style>
