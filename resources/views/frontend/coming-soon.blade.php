<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming Soon - {{ config('app.name') }}</title>
    <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Albert Sans', system-ui, sans-serif;
            overflow: hidden;
        }
        .coming-soon-wrapper {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        .logo { margin-bottom: 2rem; }
        .logo img { max-height: 60px; }
        h1 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .subtitle {
            font-size: clamp(1rem, 3vw, 1.25rem);
            color: rgba(255,255,255,0.7);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        .countdown {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }
        .countdown-item {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            min-width: 90px;
        }
        .countdown-item .num {
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 700;
            color: #fff;
            display: block;
            line-height: 1;
        }
        .countdown-item .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.5);
            margin-top: 0.25rem;
            display: block;
        }
        .email-form {
            display: flex;
            gap: 0.75rem;
            max-width: 420px;
            margin: 0 auto;
        }
        .email-form input {
            flex: 1;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.06);
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .email-form input::placeholder { color: rgba(255,255,255,0.35); }
        .email-form input:focus { border-color: rgba(255,255,255,0.4); }
        .email-form button {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            background: #e94560;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .email-form button:hover { background: #d63851; }
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        .social-links a {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.2s;
        }
        .social-links a:hover {
            background: #e94560;
            border-color: #e94560;
            color: #fff;
        }
        .particles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            width: 4px; height: 4px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            animation: float linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }
        @media (max-width: 480px) {
            .countdown { gap: 0.75rem; }
            .countdown-item { min-width: 70px; padding: 0.75rem 1rem; }
            .email-form { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    <canvas id="blast" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9998; pointer-events:none;"></canvas>
    <div id="flash" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; opacity:0; z-index:9999; pointer-events:none; transition:opacity 0.25s;"></div>
    <div id="launchMsg" style="display:none; position:fixed; bottom:8%; left:0; right:0; text-align:center; z-index:9998; color:#fff; font-size:1.25rem; font-weight:600; letter-spacing:0.02em; text-shadow:0 2px 12px rgba(0,0,0,0.6);">{{ __t("We are live! Opening the store...") }}</div>
    <div class="coming-soon-wrapper">
        <div class="logo">
            <img src="{{ App\Helpers\ImageHelper::getWebsiteLogo($siteLogoDark ?? null) }}" alt="{{ $siteName ?? config('app.name') }}" onerror="this.style.display='none'">
        </div>
        <h1>{{ __t('Coming Soon') }}</h1>
        <p class="subtitle">{{ __t('We are working on something amazing. Stay tuned for our launch!') }}</p>

        <div class="countdown" id="countdown">
            <div class="countdown-item"><span class="num" id="days">00</span><span class="label">{{ __t('Days') }}</span></div>
            <div class="countdown-item"><span class="num" id="hours">00</span><span class="label">{{ __t('Hours') }}</span></div>
            <div class="countdown-item"><span class="num" id="minutes">00</span><span class="label">{{ __t('Minutes') }}</span></div>
            <div class="countdown-item"><span class="num" id="seconds">00</span><span class="label">{{ __t('Seconds') }}</span></div>
        </div>

        <form class="email-form" id="notifyForm">
            @csrf
            <input type="email" name="email" placeholder="{{ __t('Enter your email') }}" required>
            <button type="submit">{{ __t('Notify Me') }}</button>
        </form>

        <div class="social-links">
            <a href="#" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
            <a href="#" aria-label="Twitter"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
            <a href="#" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
        </div>
    </div>

    <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
    <script>
        // Particles
        (function() {
            var container = document.getElementById('particles');
            for (var i = 0; i < 50; i++) {
                var p = document.createElement('div');
                p.className = 'particle';
                p.style.left = Math.random() * 100 + '%';
                p.style.width = p.style.height = (2 + Math.random() * 4) + 'px';
                p.style.animationDuration = (15 + Math.random() * 25) + 's';
                p.style.animationDelay = (Math.random() * 20) + 's';
                container.appendChild(p);
            }
        })();

        // Countdown to July 20, 2026
        (function() {
            var target = new Date('2026-08-01T09:00:00');
            var launched = false;

            function update() {
                var now = new Date();
                var diff = target - now;

                if (diff <= 0) {
                    document.getElementById('days').textContent = '00';
                    document.getElementById('hours').textContent = '00';
                    document.getElementById('minutes').textContent = '00';
                    document.getElementById('seconds').textContent = '00';

                    if (!launched) {
                        launched = true;
                        triggerBlast();
                    }
                    return;
                }
                var d = Math.floor(diff / (1000 * 60 * 60 * 24));
                var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var s = Math.floor((diff % (1000 * 60)) / 1000);
                document.getElementById('days').textContent = String(d).padStart(2, '0');
                document.getElementById('hours').textContent = String(h).padStart(2, '0');
                document.getElementById('minutes').textContent = String(m).padStart(2, '0');
                document.getElementById('seconds').textContent = String(s).padStart(2, '0');
            }
            update();
            setInterval(update, 1000);
        })();

        // Bomb blast / fireworks effect, then open the home page
        function triggerBlast() {
            var canvas = document.getElementById('blast');
            var ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            canvas.style.display = 'block';

            var cx = canvas.width / 2;
            var cy = canvas.height / 2;
            var particles = [];
            var colors = ['#ff5722', '#ff9800', '#ffeb3b', '#f44336', '#ffc107', '#ffffff'];
            var TOTAL_BLASTS = 5;

            function boom(x, y, count, power) {
                for (var i = 0; i < count; i++) {
                    var angle = Math.random() * Math.PI * 2;
                    var speed = (Math.random() * power) + power * 0.3;
                    particles.push({
                        x: x, y: y,
                        vx: Math.cos(angle) * speed,
                        vy: Math.sin(angle) * speed,
                        life: 1,
                        decay: Math.random() * 0.008 + 0.004,
                        size: Math.random() * 3 + 1.5,
                        color: colors[Math.floor(Math.random() * colors.length)]
                    });
                }
            }

            function flashScreen() {
                // screen flash disabled
            }

            // Show the launch message
            var msg = document.getElementById('launchMsg');
            if (msg) { msg.style.display = 'block'; }

            // Fire exactly 5 blasts within 2 seconds
            for (var k = 0; k < TOTAL_BLASTS; k++) {
                (function(idx) {
                    setTimeout(function() {
                        var x = (idx === 0)
                            ? cx
                            : cx + (Math.random() - 0.5) * canvas.width * 0.7;
                        var y = (idx === 0)
                            ? cy
                            : cy + (Math.random() - 0.5) * canvas.height * 0.7;
                        boom(x, y, idx === 0 ? 220 : 140, idx === 0 ? 14 : 11);
                        flashScreen();
                    }, idx * 450);
                })(k);
            }

            // Auto-redirect to the home page 2 seconds after the countdown ends
            setTimeout(function() {
                try { sessionStorage.setItem('aariva_launch_blast', '1'); } catch (e) {}
                window.location.href = '{{ route("frontend.home") }}';
            }, 2000);

            var frame = 0;
            function render() {
                frame++;
                ctx.fillStyle = 'rgba(0,0,0,0.18)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                for (var i = particles.length - 1; i >= 0; i--) {
                    var p = particles[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += 0.08;
                    p.vx *= 0.98;
                    p.vy *= 0.98;
                    p.life -= p.decay;
                    if (p.life <= 0) { particles.splice(i, 1); continue; }
                    ctx.globalAlpha = Math.max(p.life, 0);
                    ctx.fillStyle = p.color;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;

                if (frame < 400) {
                    requestAnimationFrame(render);
                }
            }
            render();
        }

        // Notify form
        $('#notifyForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button');
            var orig = btn.text();
            btn.prop('disabled', true).text('{{ __t("Subscribing...") }}');
            $.ajax({
                url: '{{ route("coming.soon.notify") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function() {
                    btn.text('{{ __t("You are on the list!") }}');
                    setTimeout(function() { btn.prop('disabled', false).text(orig); }, 3000);
                },
                error: function() {
                    btn.text('{{ __t("Something went wrong") }}');
                    setTimeout(function() { btn.prop('disabled', false).text(orig); }, 3000);
                }
            });
        });
    </script>
</body>
</html>
