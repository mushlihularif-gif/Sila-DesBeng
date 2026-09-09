<style>
    /* Unit Carousel Styles */
    .unit-stage-wrapper {
        height: 440px;
    }

    .unit-card {
        width: 280px;
        height: 280px;
        position: absolute;
        top: 45%;
        transform-origin: center center;
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

    .state-0 {
        left: 15% !important;
        transform: translate(-50%, -50%) scale(0.65) !important;
        opacity: 0.8;
        z-index: 20;
        filter: grayscale(10%);
    }

    .state-1 {
        left: 50% !important;
        transform: translate(-50%, -50%) scale(1.5) !important;
        opacity: 1;
        z-index: 50;
        filter: grayscale(0%) drop-shadow(0 25px 35px rgba(0, 0, 0, 0.25));
    }

    .state-2 {
        left: 80% !important;
        transform: translate(-50%, -50%) scale(0.65) !important;
        opacity: 0.8;
        z-index: 20;
        filter: grayscale(10%);
    }

    .state-3 {
        left: 100% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0.6;
        z-index: 10;
        filter: grayscale(30%);
    }

    .state-4 {
        left: 0% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0.6;
        z-index: 10;
        filter: grayscale(30%);
    }

    .state-5 {
        left: -20% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0;
        z-index: 5;
    }

    /* RESPONSIVE MOBILE */
    @media (max-width: 768px) {
        #unit-carousel-container {
            padding-top: 1rem !important;
            padding-bottom: 2rem !important;
        }
        .unit-stage-wrapper {
            height: 200px !important;
        }
        .unit-card {
            width: 90px !important;
            height: 90px !important;
            top: 36% !important;
        }

        /* Slot Kiri (Background Preview) */
        .state-0 {
            left: 15% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.5 !important;
            z-index: 20 !important;
            filter: grayscale(20%) !important;
        }

        /* Slot Tengah (Focus Terpusat, Proporsional dan Rapi) */
        .state-1 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(1.15) !important;
            opacity: 1 !important;
            z-index: 50 !important;
            filter: grayscale(0%) drop-shadow(0 8px 16px rgba(0,0,0,0.18)) !important;
        }

        /* Slot Kanan (Background Preview) */
        .state-2 {
            left: 85% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.5 !important;
            z-index: 20 !important;
            filter: grayscale(20%) !important;
        }

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
            bottom: 4px !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            padding: 0 16px !important;
            gap: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #unit-prev, #unit-next {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;
        }
        #unit-prev svg, #unit-next svg {
            width: 18px !important;
            height: 18px !important;
        }
        .unit-title-box {
            min-width: 0 !important;
            max-width: 210px !important;
            flex: 1 !important;
        }
        #unit-title {
            font-size: 0.95rem !important;
            line-height: 1.25 !important;
        }
    }
</style>
