<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task AI Admin Access</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900|jetbrains-mono:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #020602;
            color: #c4ffc4;
            font-family: 'Jetbrains Mono', monospace;
            overflow-x: hidden;
        }
        #matrix-canvas { position: fixed; inset: 0; z-index: 0; opacity: 0.28; }
        .admin-grid {
            position: fixed; inset: 0; z-index: 1; pointer-events: none;
            background-image:
                linear-gradient(rgba(0,255,0,0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,255,0,0.055) 1px, transparent 1px);
            background-size: 56px 56px;
        }
        .scanlines {
            position: fixed; inset: 0; z-index: 50; pointer-events: none;
            background: repeating-linear-gradient(0deg, rgba(0,255,0,0.035) 0, rgba(0,255,0,0.035) 2px, transparent 2px, transparent 5px);
        }
        .terminal-panel {
            border: 1px solid rgba(0,255,102,0.32);
            background: rgba(0, 16, 4, 0.84);
            box-shadow: 0 0 42px rgba(0,255,102,0.12), inset 0 0 32px rgba(0,255,102,0.045);
            backdrop-filter: blur(16px);
        }
        .terminal-label {
            letter-spacing: 0.28em;
            text-transform: uppercase;
        }
        .corner { position: absolute; width: 18px; height: 18px; border-color: #00ff66; }
        .corner.tl { top: -1px; left: -1px; border-top: 2px solid; border-left: 2px solid; }
        .corner.tr { top: -1px; right: -1px; border-top: 2px solid; border-right: 2px solid; }
        .corner.bl { bottom: -1px; left: -1px; border-bottom: 2px solid; border-left: 2px solid; }
        .corner.br { bottom: -1px; right: -1px; border-bottom: 2px solid; border-right: 2px solid; }
        .pulse-dot {
            display: inline-block; width: 8px; height: 8px; border-radius: 999px;
            background: #00ff66; box-shadow: 0 0 16px #00ff66;
            animation: pulse 1.6s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.65; transform: scale(0.9); }
            50% { opacity: 1; transform: scale(1.15); }
        }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="admin-grid"></div>
    <div class="scanlines"></div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-5xl grid gap-8 lg:grid-cols-[1fr_440px] items-center">
            <section class="hidden lg:block">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-decoration-none">
                    <span class="text-3xl font-black text-[#00ff66] tracking-[0.18em]" style="text-shadow:0 0 22px rgba(0,255,102,.45);">TASK_AI</span>
                    <span class="text-xs text-cyan-300/70 terminal-label">admin node</span>
                </a>
                <div class="mt-10 terminal-panel relative p-6">
                    <span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span>
                    <div class="flex items-center justify-between border-b border-green-500/20 pb-4 mb-5">
                        <span class="text-xs text-green-400/70 terminal-label">taskai://secure-admin</span>
                        <span class="text-xs text-cyan-300/80">root</span>
                    </div>
                    <div class="space-y-3 text-sm leading-7">
                        <p><span class="text-[#00ff66]">$</span> authenticate operator --scope admin</p>
                        <p class="text-green-100/55">verifying dashboard gateway <span class="text-[#00ff66]">[ OK ]</span></p>
                        <p class="text-green-100/55">payments, subscriptions, updates <span class="text-cyan-300">[ LIVE ]</span></p>
                        <p class="flex flex-wrap gap-2 pt-2">
                            <span class="border border-green-500/30 px-3 py-1 text-xs text-green-300">USERS: TRACKED</span>
                            <span class="border border-cyan-400/30 px-3 py-1 text-xs text-cyan-300">PAYMENTS: READY</span>
                            <span class="border border-amber-300/30 px-3 py-1 text-xs text-amber-200">UPDATES: ARMED</span>
                        </p>
                    </div>
                </div>
            </section>

            <section class="terminal-panel relative w-full p-6 sm:p-8">
                <span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span>
                {{ $slot }}
            </section>
        </div>
    </main>

    <script>
        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');
        const chars = '01$#@TASKAI{}[]<>/=+-_';
        let columns = [];
        function resizeMatrix() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            columns = Array.from({ length: Math.ceil(canvas.width / 18) }, () => Math.random() * -80);
        }
        function drawMatrix() {
            ctx.fillStyle = 'rgba(2, 6, 2, 0.16)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = '14px Jetbrains Mono, monospace';
            columns.forEach((drop, index) => {
                const char = chars[Math.floor(Math.random() * chars.length)];
                const x = index * 18;
                const y = drop * 18;
                ctx.fillStyle = Math.random() > 0.965 ? '#8effb1' : '#00a63e';
                ctx.fillText(char, x, y);
                columns[index] = y > canvas.height + Math.random() * 400 ? Math.random() * -35 : drop + 1;
            });
            requestAnimationFrame(drawMatrix);
        }
        window.addEventListener('resize', resizeMatrix);
        resizeMatrix();
        drawMatrix();
    </script>
</body>
</html>
