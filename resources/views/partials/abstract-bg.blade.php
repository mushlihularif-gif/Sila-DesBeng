{{-- Custom Vector Abstract Background (Pasar Daerah & Kabar Daerah Wave Style) --}}
<div class="fixed inset-0 overflow-hidden z-0 pointer-events-none" id="premium-bg">
    <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
</div>

<script>
    // Canvas Vector Abstract Background Script
    (() => {
        // Global mouse & scroll tracking (attached once)
        if (!window.abstractBgState) {
            window.abstractBgState = {
                mouse: { x: -1000, y: -1000 },
                targetMouse: { x: -1000, y: -1000 },
                scrollY: window.scrollY || 0
            };
            document.addEventListener('mousemove', (e) => {
                window.abstractBgState.targetMouse.x = e.clientX;
                window.abstractBgState.targetMouse.y = e.clientY;
            });
            window.addEventListener('mouseout', () => {
                window.abstractBgState.targetMouse.x = -1000;
                window.abstractBgState.targetMouse.y = -1000;
            });
            window.addEventListener('scroll', () => {
                window.abstractBgState.scrollY = window.scrollY || 0;
            }, { passive: true });
        }

        const initCanvas = () => {
            // Cancel any existing animation loop to prevent stacking
            if (window.abstractBgAnimationId) {
                cancelAnimationFrame(window.abstractBgAnimationId);
                window.abstractBgAnimationId = null;
            }

            const canvas = document.getElementById('abstract-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width, height;
            let waves = [];

            class Wave {
                constructor(colorFunc, heightPercent, amplitude, speed, offset) {
                    this.colorFunc = colorFunc;
                    this.heightPercent = heightPercent;
                    this.amplitude = amplitude;
                    this.speed = speed;
                    this.offset = offset;
                    this.points = [];
                    this.time = 0;
                }
                init() {
                    this.points = [];
                    for (let i = 0; i <= width + 50; i += 50) {
                        this.points.push(i);
                    }
                }
                update() {
                    this.time += this.speed;
                }
                draw() {
                    const mouse = window.abstractBgState.mouse;
                    ctx.beginPath();
                    ctx.moveTo(0, height + 2000);
                    for (let i = 0; i < this.points.length; i++) {
                        let x = this.points[i];
                        let dx = x - mouse.x;
                        let dy = (height * this.heightPercent) - mouse.y;
                        let dist = Math.sqrt(dx * dx + dy * dy);
                        let repel = 0;

                        if (dist < 400) {
                            repel = (400 - dist) * 0.15;
                        }

                        let y = height * this.heightPercent
                              + Math.sin(this.time + (x * 0.005) + this.offset) * this.amplitude
                              + repel;

                        if (i === 0) {
                            ctx.lineTo(x, y);
                        } else {
                            ctx.lineTo(x, y);
                        }
                    }
                    ctx.lineTo(width, height + 2000);
                    ctx.closePath();
                    ctx.fillStyle = this.colorFunc(ctx, width, height);
                    ctx.fill();
                }
            }

            function initWaves() {
                waves = [
                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h * 0.5, 0, h);
                        grad.addColorStop(0, 'rgba(230, 240, 255, 1)');
                        grad.addColorStop(1, 'rgba(255, 255, 255, 1)');
                        return grad;
                    }, 0.65, 20, 0.005, 0),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h * 0.6, 0, h * 1.2);
                        grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                        grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                        return grad;
                    }, 0.75, 30, 0.003, 500),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h * 0.7, 0, h * 1.1);
                        grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)');
                        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
                        return grad;
                    }, 0.85, 45, 0.007, 700)
                ];
                waves.forEach(w => w.init());
            }

            function resize() {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }

            if (window.abstractBgResize) {
                window.removeEventListener('resize', window.abstractBgResize);
            }
            window.abstractBgResize = resize;
            window.addEventListener('resize', window.abstractBgResize);

            function animate() {
                const state = window.abstractBgState;
                state.mouse.x += (state.targetMouse.x - state.mouse.x) * 0.1;
                state.mouse.y += (state.targetMouse.y - state.mouse.y) * 0.1;

                ctx.fillStyle = '#e8eff5';
                ctx.fillRect(0, 0, width, height);

                ctx.save();
                ctx.translate(0, -state.scrollY * 0.4);

                let glowX = width * 0.15;
                let glowY = height * 0.4;
                let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
                gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)');
                gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
                ctx.fillStyle = gradGlow;
                ctx.beginPath();
                ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI * 2);
                ctx.fill();

                waves.forEach(w => {
                    w.update();
                    w.draw();
                });

                ctx.restore();
                window.abstractBgAnimationId = requestAnimationFrame(animate);
            }

            resize();
            animate();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCanvas);
        } else {
            initCanvas();
        }
        document.addEventListener('turbo:load', initCanvas);
        document.addEventListener('turbo:render', initCanvas);
    })();
</script>
