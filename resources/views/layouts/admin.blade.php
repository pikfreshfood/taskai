<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task AI Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900|jetbrains-mono:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #020602;
            color: #c4ffc4;
            font-family: 'Jetbrains Mono', monospace;
            overflow-x: hidden;
        }
        #matrix-canvas { position: fixed; inset: 0; z-index: 0; opacity: 0.18; }
        .admin-grid {
            position: fixed; inset: 0; z-index: 1; pointer-events: none;
            background-image:
                linear-gradient(rgba(0,255,0,0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,255,0,0.045) 1px, transparent 1px);
            background-size: 58px 58px;
        }
        .scanlines {
            position: fixed; inset: 0; z-index: 50; pointer-events: none;
            background: repeating-linear-gradient(0deg, rgba(0,255,0,0.028) 0, rgba(0,255,0,0.028) 2px, transparent 2px, transparent 5px);
        }
        .admin-shell { position: relative; z-index: 10; min-height: 100vh; }
        .terminal-header {
            border-bottom: 1px solid rgba(0,255,102,0.22);
            background: rgba(2, 8, 2, 0.88);
            backdrop-filter: blur(18px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.25);
        }
        .terminal-label { letter-spacing: .24em; text-transform: uppercase; }
        .pulse-dot {
            display: inline-block; width: 8px; height: 8px; border-radius: 999px;
            background: #00ff66; box-shadow: 0 0 14px #00ff66;
            animation: pulse 1.6s ease-in-out infinite;
        }
        .admin-sidebar {
            border-right: 1px solid rgba(0,255,102,0.18);
            background: rgba(2, 8, 2, 0.82);
            backdrop-filter: blur(18px);
        }
        @keyframes pulse {
            0%, 100% { opacity: .65; transform: scale(.9); }
            50% { opacity: 1; transform: scale(1.15); }
        }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="admin-grid"></div>
    <div class="scanlines"></div>

    <div class="admin-shell">
        <header class="terminal-header sticky top-0 z-40">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4">
                    <span class="text-xl font-black tracking-[0.18em] text-[#00ff66]" style="text-shadow:0 0 22px rgba(0,255,102,.4);">TASK_AI</span>
                    <span class="hidden border-l border-green-500/25 pl-4 text-xs text-green-300/65 terminal-label sm:inline">admin console</span>
                </a>

                <div class="flex items-center gap-3">
                    <div class="hidden items-center gap-2 text-xs text-green-300/80 sm:flex">
                        <span class="pulse-dot"></span>
                        ONLINE
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="border border-red-400/35 bg-red-500/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-red-300 transition hover:bg-red-500/14 hover:text-red-100">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="mx-auto flex max-w-7xl flex-col md:flex-row">
            <aside class="admin-sidebar md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:w-72 md:shrink-0 md:overflow-y-auto">
                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'code' => 'SYS', 'description' => 'Overview and quick status'],
                        ['route' => 'admin.users', 'label' => 'Users', 'code' => 'USR', 'description' => 'Accounts and subscription state'],
                        ['route' => 'admin.updates', 'label' => 'App Updates', 'code' => 'UPD', 'description' => 'Push desktop releases'],
                        ['route' => 'admin.payments', 'label' => 'Payments', 'code' => 'PAY', 'description' => 'Approve and review orders'],
                        ['route' => 'admin.plans', 'label' => 'Plans', 'code' => 'PLN', 'description' => 'Manage subscription plans'],
                        ['route' => 'admin.authorization', 'label' => 'Auth JSON', 'code' => 'AUT', 'description' => 'Edit Codex and OpenCode JSON files'],
                    ];
                @endphp
                <div class="p-4">
                    <div class="mb-4 border border-green-500/20 bg-green-500/5 p-4">
                        <p class="terminal-label text-[10px] font-bold text-green-400/70">left menu</p>
                        <p class="mt-2 text-sm font-black uppercase tracking-[0.14em] text-white">Admin Nodes</p>
                    </div>
                    <nav class="grid gap-2">
                        @foreach($adminLinks as $link)
                            @php
                                $active = request()->routeIs($link['route']);
                            @endphp
                            <a href="{{ route($link['route']) }}" class="{{ $active ? 'border-green-400 bg-green-500/14 text-green-100 shadow-[0_0_24px_rgba(0,255,102,0.12)]' : 'border-green-500/18 bg-black/30 text-green-100/62 hover:border-green-400/45 hover:bg-green-500/8 hover:text-green-100' }} group border p-4 transition">
                                <div class="flex items-start gap-3">
                                    <span class="{{ $active ? 'border-green-300 text-green-100' : 'border-green-500/25 text-green-400/65 group-hover:text-green-200' }} border px-2 py-1 text-[10px] font-black tracking-[0.18em]">{{ $link['code'] }}</span>
                                    <span>
                                        <span class="block text-sm font-black uppercase tracking-[0.12em]">{{ $link['label'] }}</span>
                                        <span class="mt-1 block text-xs leading-5 text-green-100/42">{{ $link['description'] }}</span>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

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
            ctx.fillStyle = 'rgba(2, 6, 2, 0.18)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = '14px Jetbrains Mono, monospace';
            columns.forEach((drop, index) => {
                const char = chars[Math.floor(Math.random() * chars.length)];
                const x = index * 18;
                const y = drop * 18;
                ctx.fillStyle = Math.random() > 0.972 ? '#9cffbd' : '#008f36';
                ctx.fillText(char, x, y);
                columns[index] = y > canvas.height + Math.random() * 420 ? Math.random() * -35 : drop + 1;
            });
            requestAnimationFrame(drawMatrix);
        }
        window.addEventListener('resize', resizeMatrix);
        resizeMatrix();
        drawMatrix();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function ajaxLoad(url, wrapperId) {
                const wrapper = document.getElementById(wrapperId);
                if (!wrapper) return;
                wrapper.innerHTML = '<div class="px-5 py-10 text-center text-sm text-green-100/42">Loading...</div>';
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Request failed');
                    return r.text();
                })
                .then(html => { wrapper.innerHTML = html; bindPagination(); bindSearch(); })
                .catch(() => { wrapper.innerHTML = '<div class="px-5 py-10 text-center text-sm text-red-100/62">Failed to load.</div>'; });
            }

            function bindPagination() {
                document.querySelectorAll('.pagination a').forEach(a => {
                    a.addEventListener('click', function (e) {
                        const wrapper = this.closest('[id$="-table-wrapper"]');
                        if (!wrapper) return;
                        e.preventDefault();
                        ajaxLoad(this.href, wrapper.id);
                    });
                });
            }

            function bindSearch() {
                const searchInput = document.querySelector('input[name="search"]');
                if (!searchInput) return;
                const form = searchInput.closest('form');
                if (!form) return;

                let timeout;
                searchInput.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const params = new URLSearchParams(new FormData(form));
                        const url = form.getAttribute('action') + '?' + params.toString();
                        const wrapper = document.getElementById('users-table-wrapper');
                        if (wrapper) ajaxLoad(url, 'users-table-wrapper');
                    }, 350);
                });
            }

            bindPagination();
            bindSearch();
        });
    </script>
</body>
</html>
