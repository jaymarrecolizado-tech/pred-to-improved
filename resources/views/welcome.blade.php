<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DICT R2 Travel Order System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/DICT_logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-light: #3b82f6;
            --accent: #06b6d4;
            --bg: #f0f4ff;
            --panel-bg: rgba(255,255,255,0.85);
            --text: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.38;
            z-index: 0;
            animation: blobFloat 12s ease-in-out infinite;
        }
        .blob-1 { width: 500px; height: 500px; background: radial-gradient(circle, #93c5fd, #3b82f6); top: -100px; left: -100px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: radial-gradient(circle, #67e8f9, #06b6d4); bottom: -80px; right: -80px; animation-delay: -4s; }
        .blob-3 { width: 300px; height: 300px; background: radial-gradient(circle, #c7d2fe, #818cf8); top: 40%; left: 60%; animation-delay: -8s; }

        @keyframes blobFloat {
            0%, 100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(20px,-20px) scale(1.05); }
            66% { transform: translate(-15px,15px) scale(0.97); }
        }

        #binaryCanvas {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.45;
            pointer-events: none;
        }

        .page-wrapper {
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 960px;
            width: 100%;
            min-height: 520px;
            margin: 20px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(59,130,246,0.15),
                0 25px 80px rgba(15,23,42,0.12),
                0 0 60px rgba(59,130,246,0.08);
        }

        .brand-panel {
            background: linear-gradient(145deg, #1e3a8a 0%, #1d4ed8 45%, #0369a1 100%);
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before { content: ''; position: absolute; top: -60px; right: -60px; width: 250px; height: 250px; border-radius: 50%; background: rgba(255,255,255,0.05); }
        .brand-panel::after  { content: ''; position: absolute; bottom: -40px; left: -40px; width: 200px; height: 200px; border-radius: 50%; background: rgba(6,182,212,0.12); }

        .brand-corner-tl { position: absolute; top: 20px; left: 20px; width: 40px; height: 40px; border-top: 2px solid rgba(6,182,212,0.6); border-left: 2px solid rgba(6,182,212,0.6); }
        .brand-corner-br { position: absolute; bottom: 20px; right: 20px; width: 40px; height: 40px; border-bottom: 2px solid rgba(6,182,212,0.6); border-right: 2px solid rgba(6,182,212,0.6); }

        .scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(6,182,212,0.4), transparent); animation: scanMove 6s linear infinite; pointer-events: none; }
        @keyframes scanMove { 0% { top: 0%; opacity: 0; } 5% { opacity: 1; } 95% { opacity: 1; } 100% { top: 100%; opacity: 0; } }

        /* Logo — landscape full width */
        .brand-logo-wrap {
            display: flex;
            flex-direction: column;
            gap: 0px;
            position: relative;
        }
        .brand-logo-wrap img {
            width: 100%;
            max-width: 500px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 14px rgba(6,182,212,0.6)) brightness(1.15);
        }
        .brand-office-label {
            font-family: 'Rajdhani', sans-serif;
            font-size: 30px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .brand-region-sub {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: rgba(6,182,212,0.8);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .system-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(6,182,212,0.15); border: 1px solid rgba(6,182,212,0.35); border-radius: 4px; padding: 4px 10px; margin-bottom: 20px; }
        .system-tag span { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: #67e8f9; letter-spacing: 0.1em; text-transform: uppercase; }
        .system-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #06b6d4; animation: pulse 2s ease-in-out infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(6,182,212,0.6); }
            50% { opacity: 0.7; box-shadow: 0 0 0 4px rgba(6,182,212,0); }
        }

        .brand-headline { font-family: 'Rajdhani', sans-serif; font-size: 38px; font-weight: 700; color: #fff; line-height: 1.1; letter-spacing: 0.02em; margin-bottom: 12px; }
        .brand-headline span { color: #67e8f9; }
        .brand-desc { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 1.6; max-width: 240px; }
        .brand-footer-text { font-family: 'Share Tech Mono', monospace; font-size: 15px; color: #10b981; }
        .brand-region { font-family: 'Rajdhani', sans-serif; font-size: 12px; font-weight: 600; color: rgba(103,232,249,0.7); letter-spacing: 0.12em; text-transform: uppercase; margin-top: 4px; }

        .login-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid rgba(59,130,246,0.12);
        }

        .login-eyebrow { font-family: 'Share Tech Mono', monospace; font-size: 20px; letter-spacing: 0.15em; color: var(--primary-light); text-transform: uppercase; margin-bottom: 8px; }
        .login-title { font-family: 'Rajdhani', sans-serif; font-size: 28px; font-weight: 700; color: var(--text); letter-spacing: 0.02em; line-height: 1.1; }
        .login-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 6px; }
        .divider-line { width: 40px; height: 3px; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 2px; margin: 14px 0 0; }

        .info-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 28px; margin-top: 24px; }
        .info-card { background: rgba(239,246,255,0.8); border: 1px solid rgba(59,130,246,0.15); border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; }
        .info-card-icon { width: 32px; height: 32px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .info-card-icon svg { width: 16px; height: 16px; color: #1d4ed8; }
        .card-label { font-size: 10px; color: var(--text-muted); letter-spacing: 0.05em; text-transform: uppercase; font-weight: 500; }
        .card-value { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.2; }

        .signin-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #0891b2 100%); color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; border: none; border-radius: 10px; cursor: pointer; position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(37,99,235,0.35); text-decoration: none; }
        .signin-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(37,99,235,0.45); }
        .signin-btn:active { transform: translateY(0); }
        .btn-icon { width: 18px; height: 18px; flex-shrink: 0; }
        .btn-spinner { display: none; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .status-bar { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(59,130,246,0.1); }
        .status-indicator { display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #10b981; animation: pulse 2s ease-in-out infinite; }
        .status-text { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: #10b981; letter-spacing: 0.08em; }
        .copyright-text { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: var(--text-muted); }

        @media (max-width: 640px) {
            .page-wrapper { grid-template-columns: 1fr; max-width: 420px; }
            .brand-panel { display: none; }
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <canvas id="binaryCanvas"></canvas>

    <div class="page-wrapper">
        <div class="brand-panel">
            <div class="brand-corner-tl"></div>
            <div class="brand-corner-br"></div>
            <div class="scan-line"></div>

            <div class="brand-logo-wrap">
                <img src="{{ asset('images/DICT_logo3.png') }}" alt="DICT Logo">
                <div class="brand-office-label">DICT Regional Office II</div>
                <div class="brand-region-sub"> </div>
            </div>

            <div>
                <div class="system-tag">
                    <div class="system-tag-dot"></div>
                    <span>Secure Access Portal</span>
                </div>
                <div class="brand-headline">TRAVEL<br><span>ORDER</span><br>SYSTEM</div>
                <p class="brand-desc">Centralized travel authorization and routing platform for DICT Region II employees.</p>
            </div>

            <div>
                <div class="brand-footer-text"> · v2.2.1 · </div>
                <div class="brand-region"> </div>
            </div>
        </div>

        <div class="login-panel">
            <div class="login-eyebrow">// System Login</div>
            <div class="login-title">Welcome Back</div>
            <div class="login-subtitle">Sign in with your DICT credentials to continue.</div>
            <div class="divider-line"></div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="card-label">Access</div>
                        <div class="card-value">Authorized<br>Users Only</div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="card-label">System</div>
                        <div class="card-value">Travel Order<br>Management</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('filament.DICT.auth.login') }}" id="loginBtn" class="signin-btn">
                <svg class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" id="btnIcon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <div class="btn-spinner" id="btnSpinner"></div>
                <span id="btnText">Sign In</span>
            </a>

            <div class="status-bar">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span class="status-text">Server Online</span>
                </div>
                <span class="copyright-text">&copy; {{ date('Y') }} DICT</span>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('binaryCanvas');
        const ctx    = canvas.getContext('2d');

        function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        const fontSize = 13;
        const cols     = () => Math.floor(canvas.width / fontSize);
        let drops      = [];

        function initDrops() { drops = Array.from({ length: cols() }, () => Math.random() * -100); }
        initDrops();
        window.addEventListener('resize', initDrops);

        function draw() {
            ctx.fillStyle = 'rgba(240, 244, 255, 0.03)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = fontSize + 'px "Share Tech Mono", monospace';
            const c = cols();
            for (let i = 0; i < c; i++) {
                const char = Math.random() > 0.5 ? '1' : '0';
                ctx.fillStyle = Math.random() > 0.88
                    ? 'rgba(6, 182, 212, 0.95)'
                    : 'rgba(29, 78, 216, 0.65)';
                ctx.fillText(char, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i] += 0.4;
            }
        }
        setInterval(draw, 60);

        const btn        = document.getElementById('loginBtn');
        const btnText    = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const btnIcon    = document.getElementById('btnIcon');
        const href       = btn.getAttribute('href');

        window.addEventListener('pageshow', () => {
            btnSpinner.style.display = 'none';
            btnIcon.style.display    = 'block';
            btnText.textContent      = 'Sign In';
            btn.style.pointerEvents  = 'auto';
        });

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            btnIcon.style.display    = 'none';
            btnSpinner.style.display = 'block';
            btnText.textContent      = 'Redirecting...';
            btn.style.pointerEvents  = 'none';
            setTimeout(() => { window.location.href = href; }, 400);
        });
    </script>
</body>
</html>