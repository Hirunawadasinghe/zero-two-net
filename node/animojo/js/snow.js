(() => {
    const canvas = document.getElementById('snow-canvas');
    const ctx = canvas.getContext('2d');

    let width, height, dpr;
    let flakes = [];
    const FLAKE_COUNT = Math.min(20, Math.floor(window.innerWidth / 100));
    const WIND_BASE = 0.2;

    function resize() {
        dpr = window.devicePixelRatio || 1;
        width = window.innerWidth;
        height = window.innerHeight;

        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';

        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function createFlake() {
        const depth = Math.random();
        return {
            x: Math.random() * width,
            y: Math.random() * height,
            r: 1 + depth * 2.5,
            vy: 0.5 + depth * 1.5,
            vx: WIND_BASE + depth * 0.6,
            opacity: 0.4 + depth * 0.6,
            depth
        };
    }

    function init() {
        flakes = [];
        for (let i = 0; i < FLAKE_COUNT; i++) {
            flakes.push(createFlake());
        }
    }

    let windTime = 0;

    function update() {
        ctx.clearRect(0, 0, width, height);

        windTime += 0.002;
        const wind = Math.sin(windTime) * 0.5;

        for (const f of flakes) {
            f.x += (f.vx + wind) * f.depth;
            f.y += f.vy;

            if (f.y > height) {
                f.y = -10;
                f.x = Math.random() * width;
            }

            if (f.x > width) f.x = 0;
            if (f.x < 0) f.x = width;

            ctx.beginPath();
            ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${f.opacity})`;
            ctx.fill();
        }

        requestAnimationFrame(update);
    }

    resize();
    init();
    update();

    window.addEventListener('resize', () => {
        resize();
        init();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            ctx.clearRect(0, 0, width, height);
        }
    });
})();