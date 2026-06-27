    /* Unit Carousel Styles */
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
