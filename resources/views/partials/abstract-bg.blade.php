{{-- Custom Vector Abstract Background --}}
<div class="fixed inset-0 overflow-hidden z-0 pointer-events-none" id="premium-bg">
    <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
</div>

<script>
    // Canvas Vector Abstract Background Script
    (() => {
        const initCanvas = () => {
        const canvas = document.getElementById('abstract-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        
        let width, height;
        let mouse = { x: -1000, y: -1000 };
        let targetMouse = { x: -1000, y: -1000 };

        function resize() {
            if (width !== window.innerWidth || height !== window.innerHeight) {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }
        }

        window.addEventListener('resize', resize);

        window.addEventListener('mousemove', (e) => {
            targetMouse.x = e.clientX;
            targetMouse.y = e.clientY;
        });
        window.addEventListener('mouseout', () => {
            targetMouse.x = -1000;
            targetMouse.y = -1000;
        });

        let scrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            scrollY = window.scrollY;
        });

        class Wave {
            constructor(getGradient, yOffset, amplitude, speed, wavelength) {
                this.getGradient = getGradient;
                this.yOffset = yOffset; 
                this.amplitude = amplitude; 
                this.speed = speed; 
                this.wavelength = wavelength; 
                this.points = [];
                this.time = Math.random() * 100;
            }

            init() {
                this.points = [];
                let numPoints = Math.ceil(width / 25) + 2; // Resolusi tinggi agar kursor presisi
                for(let i = 0; i < numPoints; i++) {
                    let startX = (i - 1) * 25;
                    let startBaseY = height * this.yOffset;
                    let startY = startBaseY + Math.sin(this.time + startX / this.wavelength) * this.amplitude;
                    this.points.push({
                        x: startX,
                        baseY: startBaseY,
                        y: startY,
                        vy: 0,
                        spring: 0.05, 
                        friction: 0.90 
                    });
                }
            }

            update() {
                this.time += this.speed;
                for(let i = 0; i < this.points.length; i++) {
                    let pt = this.points[i];
                    
                    // Gerakan gelombang natural
                    let targetY = pt.baseY + Math.sin(this.time + pt.x / this.wavelength) * this.amplitude;
                    
                    // Interaksi Kursor: Menyebar saat disentuh
                    let dx = mouse.x - pt.x;
                    let dy = mouse.y - targetY;
                    let distance = Math.sqrt(dx*dx + dy*dy);
                    
                    if (distance < 200) {
                        let force = Math.pow((200 - distance) / 200, 2); 
                        let pushDir = (dy > 0) ? -1 : 1; 
                        targetY += pushDir * force * 60; // Dorongan diperhalus agar tidak terlalu liar
                    }
                    
                    let forceY = (targetY - pt.y) * pt.spring;
                    pt.vy += forceY;
                    pt.vy *= pt.friction;
                    pt.y += pt.vy;
                }
            }

            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                
                for(let i = 0; i < this.points.length - 1; i++) {
                    let cx = (this.points[i].x + this.points[i+1].x) / 2;
                    let cy = (this.points[i].y + this.points[i+1].y) / 2;
                    ctx.quadraticCurveTo(this.points[i].x, this.points[i].y, cx, cy);
                }
                
                let last = this.points[this.points.length - 1];
                ctx.lineTo(last.x, last.y);
                // Gambar ekstra jauh ke bawah agar saat di-scroll ke atas tidak terpotong bolong
                ctx.lineTo(width, height * 2 + scrollY);
                ctx.lineTo(0, height * 2 + scrollY);
                ctx.closePath();
                
                ctx.fillStyle = this.getGradient(ctx, width, height);
                ctx.fill();
            }
        }

        let waves = [];

        function initWaves() {
            waves = [
                // 1. Biru Muda (Diturunkan dan diperlambat agar lebih tenang)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.5, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(140, 190, 250, 0.7)');
                    grad.addColorStop(1, 'rgba(180, 215, 255, 0.1)');
                    return grad;
                }, 0.65, 40, 0.005, 600),

                // 2. Putih Solid (Pemisah)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.6, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                    grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                    return grad;
                }, 0.75, 30, 0.003, 500),

                // 3. Kuning Amber (Lebih pudar dan gradasi halus ke putih transparan)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.7, 0, h*1.1);
                    grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)'); // Agak pudar di puncak
                    grad.addColorStop(1, 'rgba(255, 255, 255, 0)'); // Pudar sempurna ke transparan
                    return grad;
                }, 0.85, 45, 0.007, 700)
            ];
            waves.forEach(w => w.init());
        }

        function animate() {
            // Lerp mouse
            mouse.x += (targetMouse.x - mouse.x) * 0.1;
            mouse.y += (targetMouse.y - mouse.y) * 0.1;

            // Background layer solid (agar saat parallax tidak bolong)
            ctx.fillStyle = '#e8eff5'; 
            ctx.fillRect(0, 0, width, height);

            ctx.save();
            // Terapkan Parallax Scrolling (Background bergerak 40% kecepatan scroll content)
            ctx.translate(0, -scrollY * 0.4); 

            // Cahaya Matahari Halus (Kiri) - Diperhalus
            let glowX = width * 0.15;
            let glowY = height * 0.4;
            let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
            gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)'); // Opasitas diturunkan
            gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
            ctx.fillStyle = gradGlow;
            ctx.beginPath();
            ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI*2);
            ctx.fill();

            // Gambar ombak-ombak
            waves.forEach(w => {
                w.update();
                w.draw();
            });

            // Ikon Wajik (Kanan Atas) - Dibuat lebih kecil & pudar agar tidak mendominasi
            ctx.save();
            ctx.translate(width * 0.9, height * 0.08);
            
            // Parallax menjauh dari kursor
            let dxD = mouse.x - (width * 0.9);
            let dyD = mouse.y - (height * 0.08);
            let distD = Math.sqrt(dxD*dxD + dyD*dyD);
            if(distD < 300) {
                let f = (300 - distD)/300;
                ctx.translate(-(dxD/distD)*f*20, -(dyD/distD)*f*20);
            }

            ctx.rotate(Math.PI / 4);
            
            ctx.fillStyle = 'rgba(74, 144, 226, 0.4)';
            ctx.fillRect(-15, -15, 30, 30);
            
            ctx.fillStyle = 'rgba(120, 175, 240, 0.3)';
            ctx.fillRect(5, 5, 25, 25);
            
            ctx.strokeStyle = 'rgba(150, 190, 250, 0.4)';
            ctx.lineWidth = 1.5;
            ctx.strokeRect(20, 20, 15, 15);

            ctx.restore(); // Restore efek rotasi wajik
            ctx.restore(); // Restore efek Parallax Scroll

            requestAnimationFrame(animate);
        }

        resize();
        animate();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCanvas);
    } else {
        initCanvas();
    }
})();
</script>
