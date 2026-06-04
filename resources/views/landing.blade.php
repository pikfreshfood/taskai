<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task AI — Hacker Command Center</title>
    <meta name="description" content="Task AI is a black-screen AI command center for Windows desktop automation, local task execution, and controlled account access.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900|jetbrains-mono:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#0a0a0a; color:#c4ffc4; font-family:'Jetbrains Mono',monospace; overflow-x:hidden; }
        .scanlines {
            position:fixed; inset:0; z-index:9998; pointer-events:none;
            background:repeating-linear-gradient(0deg,rgba(0,255,0,0.03) 0px,rgba(0,255,0,0.03) 2px,transparent 2px,transparent 5px);
        }
        #matrix-canvas { position:fixed; inset:0; z-index:0; opacity:0.25; }
        .glitch { position:relative; display:inline-block; }
        .glitch::before,.glitch::after {
            content:attr(data-text); position:absolute; top:0; left:0; width:100%; height:100%;
            background:transparent; overflow:hidden;
        }
        .glitch::before { left:2px; text-shadow:-2px 0 #ff00c1; clip:rect(44px,450px,56px,0); animation:glitch1 2.5s infinite linear alternate-reverse; }
        .glitch::after { left:-2px; text-shadow:2px 0 #00fff9; clip:rect(24px,450px,34px,0); animation:glitch2 2s infinite linear alternate-reverse; }
        @keyframes glitch1 { 0%{clip:rect(32px,9999px,54px,0)} 20%{clip:rect(12px,9999px,88px,0)} 40%{clip:rect(70px,9999px,22px,0)} 60%{clip:rect(44px,9999px,14px,0)} 80%{clip:rect(64px,9999px,48px,0)} 100%{clip:rect(28px,9999px,72px,0)} }
        @keyframes glitch2 { 0%{clip:rect(70px,9999px,88px,0)} 20%{clip:rect(44px,9999px,24px,0)} 40%{clip:rect(16px,9999px,62px,0)} 60%{clip:rect(50px,9999px,36px,0)} 80%{clip:rect(30px,9999px,74px,0)} 100%{clip:rect(58px,9999px,12px,0)} }
        .typing { overflow:hidden; border-right:2px solid #0f0; white-space:nowrap; animation:typing 3.5s steps(40,end), blink-caret 0.75s step-end infinite; }
        @keyframes typing { from{width:0} to{width:100%} }
        @keyframes blink-caret { from,to{border-color:transparent} 50%{border-color:#0f0} }
        .btn-download {
            position:relative; display:inline-flex; align-items:center; gap:10px;
            padding:16px 40px; font-family:'Jetbrains Mono',monospace; font-size:14px;
            font-weight:800; text-transform:uppercase; letter-spacing:3px;
            color:#fff; border:none; cursor:pointer; overflow:hidden; z-index:1;
            background:linear-gradient(135deg,#00ff88,#00ccff,#ff00e4,#00ff88);
            background-size:300% 300%; animation:gradientShift 3s ease infinite;
            border-radius:4px; box-shadow:0 0 30px rgba(0,255,136,0.3);
            transition:transform 0.2s, box-shadow 0.2s;
        }
        .btn-download:hover { transform:scale(1.05); box-shadow:0 0 50px rgba(0,255,136,0.5); }
        .btn-download:active { transform:scale(0.98); }
        @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        .btn-download .icon { display:inline-block; animation:bounceDown 2s ease infinite; }
        @keyframes bounceDown { 0%,100%{transform:translateY(0)} 50%{transform:translateY(6px)} }
        .btn-download::before {
            content:''; position:absolute; inset:2px; background:#0a0a0a; z-index:-1; border-radius:2px;
        }
        .btn-download span { position:relative; z-index:1; }
        .pulse-dot { display:inline-block; width:10px; height:10px; border-radius:50%; background:#0f0; animation:pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(0,255,0,0.7)} 50%{box-shadow:0 0 0 10px rgba(0,255,0,0)} }
        .hacker-grid {
            position:fixed; inset:0; z-index:1; pointer-events:none;
            background-image:
                linear-gradient(rgba(0,255,0,0.05) 1px,transparent 1px),
                linear-gradient(90deg,rgba(0,255,0,0.05) 1px,transparent 1px);
            background-size:60px 60px;
        }
        .terminal-border { border:1px solid rgba(0,255,0,0.25); box-shadow:0 0 30px rgba(0,255,0,0.08),inset 0 0 30px rgba(0,255,0,0.04); }
        .text-shadow-neon { text-shadow:0 0 10px rgba(0,255,0,0.5),0 0 40px rgba(0,255,0,0.2); }
        .text-shadow-cyan { text-shadow:0 0 10px rgba(0,255,255,0.5),0 0 40px rgba(0,255,255,0.2); }
        .text-shadow-magenta { text-shadow:0 0 10px rgba(255,0,228,0.5),0 0 40px rgba(255,0,228,0.2); }
        .crt { animation:crtFlicker 0.15s infinite; }
        @keyframes crtFlicker { 0%{opacity:0.98} 50%{opacity:1} 100%{opacity:0.99} }
        .corner-decoration { position:absolute; width:20px; height:20px; border-color:#0f0; }
        .corner-tl { top:-1px; left:-1px; border-top:2px solid #0f0; border-left:2px solid #0f0; }
        .corner-tr { top:-1px; right:-1px; border-top:2px solid #0f0; border-right:2px solid #0f0; }
        .corner-bl { bottom:-1px; left:-1px; border-bottom:2px solid #0f0; border-left:2px solid #0f0; }
        .corner-br { bottom:-1px; right:-1px; border-bottom:2px solid #0f0; border-right:2px solid #0f0; }
        .glow-card { transition:box-shadow 0.3s; }
        .glow-card:hover { box-shadow:0 0 40px rgba(0,255,0,0.15); }
        .status-led { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:6px; animation:pulse 1.5s ease-in-out infinite; }
    </style>
</head>
<body class="crt">

<canvas id="matrix-canvas"></canvas>
<div class="hacker-grid"></div>
<div class="scanlines"></div>

<div class="relative" style="z-index:10; min-height:100vh; display:flex; flex-direction:column;">

    <header style="position:fixed; inset:0 0 auto 0; z-index:100; border-bottom:1px solid rgba(0,255,0,0.15); background:rgba(10,10,10,0.85); backdrop-filter:blur(20px);">
        <nav style="max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:16px 24px;">
            <a href="{{ route('home') }}" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                <span style="font-size:24px; font-weight:800; color:#0f0; text-shadow:0 0 20px rgba(0,255,0,0.4);">TASK_AI</span>
                <span style="color:rgba(0,255,0,0.4); font-size:12px;">v3.1.7</span>
            </a>
            <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:rgba(0,255,0,0.6);">
                <span class="status-led" style="background:#0f0; box-shadow:0 0 10px #0f0;"></span>
                <span>SYSTEM :: ONLINE</span>
            </div>
            <div style="display:flex; gap:16px; align-items:center;">
                <a href="#protocol" style="color:rgba(0,255,0,0.7); text-decoration:none; font-size:12px; letter-spacing:2px; text-transform:uppercase; transition:color 0.2s;" onmouseover="this.style.color='#0f0'" onmouseout="this.style.color='rgba(0,255,0,0.7)'">Protocol</a>
                <a href="#arsenal" style="color:rgba(0,255,0,0.7); text-decoration:none; font-size:12px; letter-spacing:2px; text-transform:uppercase; transition:color 0.2s;" onmouseover="this.style.color='#0f0'" onmouseout="this.style.color='rgba(0,255,0,0.7)'">Arsenal</a>
                <a href="#access" style="color:rgba(0,255,0,0.7); text-decoration:none; font-size:12px; letter-spacing:2px; text-transform:uppercase; transition:color 0.2s;" onmouseover="this.style.color='#0f0'" onmouseout="this.style.color='rgba(0,255,0,0.7)'">Access</a>
            </div>
        </nav>
    </header>

    <main style="flex:1;">

        <section style="min-height:100vh; display:flex; align-items:center; position:relative; padding-top:80px;">
            <div style="max-width:1280px; margin:0 auto; padding:0 24px; width:100%;">
                <div style="max-width:900px;">
                    <div style="display:flex; gap:16px; align-items:center; margin-bottom:24px; font-size:12px;">
                        <span style="display:flex; align-items:center; gap:8px; padding:6px 14px; border:1px solid rgba(0,255,0,0.3); color:#0f0; font-weight:700; letter-spacing:2px;">
                            <span class="pulse-dot" style="width:6px; height:6px;"></span>
                            BOOT SEQUENCE COMPLETE
                        </span>
                        <span style="color:rgba(0,255,255,0.6); letter-spacing:2px;">[ AI_KERNEL_ACTIVE ]</span>
                    </div>

                    <h1 class="glitch" data-text="TASK AI — COMMAND CENTER" style="font-size:clamp(2.5rem,8vw,5.5rem); font-weight:900; line-height:1; text-transform:uppercase; letter-spacing:4px; color:#fff; margin-bottom:12px;">
                        TASK AI — COMMAND CENTER
                    </h1>
                    <h2 style="font-size:clamp(1rem,2.5vw,1.5rem); font-weight:400; color:rgba(0,255,255,0.6); letter-spacing:6px; text-transform:uppercase; margin-bottom:24px;">
                        $ Blackbox AI for Desktop Command_
                    </h2>

                    <p style="font-size:16px; line-height:1.8; color:rgba(200,255,200,0.75); max-width:680px; margin-bottom:32px;">
                        <span style="color:#0f0;">root@task-ai:~$</span> Task AI turns your Windows workspace into a controlled command center: screen review, local app actions, file work, automation, and account-gated access from one portal. No bloat. No dashboard fluff. Just raw terminal-grade control.
                    </p>

                    <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:48px;">
                        <a class="btn-download" href="{{ url('/taskai/download?source=landing-hero') }}">
                            <span class="icon">&darr;</span>
                            <span>Download Task AI</span>
                        </a>
                        <a href="#protocol" style="display:inline-flex; align-items:center; gap:8px; padding:16px 32px; border:1px solid rgba(0,255,255,0.3); color:rgba(0,255,255,0.8); text-decoration:none; font-size:13px; letter-spacing:2px; text-transform:uppercase; transition:all 0.2s;" onmouseover="this.style.background='rgba(0,255,255,0.08)';this.style.borderColor='rgba(0,255,255,0.6)'" onmouseout="this.style.background='transparent';this.style.borderColor='rgba(0,255,255,0.3)'">
                            [ Read Protocol ]
                        </a>
                    </div>

                    <div class="terminal-border" style="padding:20px; max-width:560px; background:rgba(0,20,0,0.4);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid rgba(0,255,0,0.15); font-size:11px; letter-spacing:2px; color:rgba(0,255,0,0.5);">
                            <span>taskai://neural-terminal</span>
                            <span style="color:rgba(0,255,255,0.5);">root</span>
                        </div>
                        <div style="font-size:13px; line-height:2; color:rgba(0,255,0,0.7);">
                            <p><span style="color:#0f0;">$</span> boot task-ai --mode command</p>
                            <p style="color:rgba(200,255,200,0.5);">loading local automation kernel <span style="color:#0f0;">[  OK  ]</span></p>
                            <p style="color:rgba(200,255,200,0.5);">watching windows, files, commands <span style="color:#0ff;">[ ACTIVE ]</span></p>
                            <p style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap;">
                                <span style="border:1px solid rgba(0,255,0,0.2); padding:4px 10px; font-size:11px; color:rgba(0,255,0,0.7);">TASKS: LIVE</span>
                                <span style="border:1px solid rgba(0,255,255,0.2); padding:4px 10px; font-size:11px; color:rgba(0,255,255,0.7);">FILES: MAPPED</span>
                                <span style="border:1px solid rgba(255,200,0,0.2); padding:4px 10px; font-size:11px; color:rgba(255,200,0,0.7);">ACTIONS: QUEUED</span>
                            </p>
                            <p><span style="color:#0f0;">$</span> <span class="typing" style="display:inline-block;">operator prompt ready_</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="protocol" style="border-top:1px solid rgba(0,255,0,0.1); border-bottom:1px solid rgba(0,255,0,0.1); padding:80px 0;">
            <div style="max-width:1280px; margin:0 auto; padding:0 24px;">
                <div style="max-width:600px; margin-bottom:48px;">
                    <p style="font-size:12px; letter-spacing:4px; color:#0f0; font-weight:700; margin-bottom:16px;">// PROTOCOL</p>
                    <h2 style="font-size:clamp(1.8rem,4vw,3rem); font-weight:900; color:#fff; text-transform:uppercase; letter-spacing:2px; margin-bottom:16px;">Built like a field terminal.</h2>
                    <p style="color:rgba(200,255,200,0.6); line-height:1.8; font-size:15px;">The page, portal, and desktop assistant are shaped around direct action: authenticate, inspect the screen, execute local work, report the outcome.</p>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:20px;">
                    <div class="glow-card" style="position:relative; border:1px solid rgba(0,255,0,0.2); background:rgba(0,20,0,0.15); padding:32px;">
                        <span class="corner-decoration corner-tl"></span>
                        <span class="corner-decoration corner-tr"></span>
                        <span class="corner-decoration corner-bl"></span>
                        <span class="corner-decoration corner-br"></span>
                        <p style="font-size:11px; letter-spacing:3px; color:#0f0; font-weight:700; margin-bottom:16px;">01 / OBSERVE</p>
                        <h3 style="font-size:22px; font-weight:900; color:#fff; margin-bottom:12px;">Screen intelligence</h3>
                        <p style="color:rgba(200,255,200,0.5); line-height:1.7; font-size:14px;">Read desktop state, active windows, and open files without forcing the user into a browser-only workflow.</p>
                    </div>
                    <div class="glow-card" style="position:relative; border:1px solid rgba(0,255,255,0.2); background:rgba(0,20,30,0.15); padding:32px;">
                        <span class="corner-decoration corner-tl" style="border-color:#0ff;"></span>
                        <span class="corner-decoration corner-tr" style="border-color:#0ff;"></span>
                        <span class="corner-decoration corner-bl" style="border-color:#0ff;"></span>
                        <span class="corner-decoration corner-br" style="border-color:#0ff;"></span>
                        <p style="font-size:11px; letter-spacing:3px; color:#0ff; font-weight:700; margin-bottom:16px;">02 / EXECUTE</p>
                        <h3 style="font-size:22px; font-weight:900; color:#fff; margin-bottom:12px;">Local operations</h3>
                        <p style="color:rgba(200,255,255,0.5); line-height:1.7; font-size:14px;">Launch tools, run commands, edit assets, and handle repetitive Windows tasks through a controlled assistant loop.</p>
                    </div>
                    <div class="glow-card" style="position:relative; border:1px solid rgba(255,200,0,0.2); background:rgba(30,20,0,0.15); padding:32px;">
                        <span class="corner-decoration corner-tl" style="border-color:#fc0;"></span>
                        <span class="corner-decoration corner-tr" style="border-color:#fc0;"></span>
                        <span class="corner-decoration corner-bl" style="border-color:#fc0;"></span>
                        <span class="corner-decoration corner-br" style="border-color:#fc0;"></span>
                        <p style="font-size:11px; letter-spacing:3px; color:#fc0; font-weight:700; margin-bottom:16px;">03 / REPORT</p>
                        <h3 style="font-size:22px; font-weight:900; color:#fff; margin-bottom:12px;">Operator output</h3>
                        <p style="color:rgba(255,230,200,0.5); line-height:1.7; font-size:14px;">Return concise next steps, account status, payment state, and task results through the Laravel portal.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="arsenal" style="padding:80px 0; background:rgba(0,10,0,0.3);">
            <div style="max-width:1280px; margin:0 auto; padding:0 24px; display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:48px; align-items:center;">
                <div>
                    <p style="font-size:12px; letter-spacing:4px; color:#0ff; font-weight:700; margin-bottom:16px;">// ARSENAL</p>
                    <h2 style="font-size:clamp(1.8rem,4vw,3rem); font-weight:900; color:#fff; text-transform:uppercase; letter-spacing:2px; margin-bottom:16px;">A black-screen AI workspace with real controls.</h2>
                    <p style="color:rgba(200,255,200,0.6); line-height:1.8; font-size:15px;">This is not a brochure skin. The interface points users toward the actual portal actions: login, admin review, account control, and desktop assistant access.</p>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="border:1px solid rgba(0,255,0,0.15); background:rgba(0,0,0,0.5); padding:20px;">
                        <p style="font-size:11px; letter-spacing:2px; color:#0f0; font-weight:700; margin-bottom:8px;">AI assistant</p>
                        <p style="color:rgba(200,255,200,0.5); font-size:13px; line-height:1.6;">Understand user requests, inspect the workspace, and turn instructions into clear next actions.</p>
                    </div>
                    <div style="border:1px solid rgba(0,255,255,0.15); background:rgba(0,0,0,0.5); padding:20px;">
                        <p style="font-size:11px; letter-spacing:2px; color:#0ff; font-weight:700; margin-bottom:8px;">Desktop control</p>
                        <p style="color:rgba(200,255,255,0.5); font-size:13px; line-height:1.6;">Open software, manage files, run commands, and automate local task sequences.</p>
                    </div>
                    <div style="border:1px solid rgba(255,200,0,0.15); background:rgba(0,0,0,0.5); padding:20px;">
                        <p style="font-size:11px; letter-spacing:2px; color:#fc0; font-weight:700; margin-bottom:8px;">User gateway</p>
                        <p style="color:rgba(255,230,200,0.5); font-size:13px; line-height:1.6;">Login, subscription checks, device records, and upgrade flows for controlled access.</p>
                    </div>
                    <div style="border:1px solid rgba(255,0,100,0.15); background:rgba(0,0,0,0.5); padding:20px;">
                        <p style="font-size:11px; letter-spacing:2px; color:#f06; font-weight:700; margin-bottom:8px;">Admin console</p>
                        <p style="color:rgba(255,200,230,0.5); font-size:13px; line-height:1.6;">Payment approval, revenue visibility, account oversight, and operational status.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="access" style="border-top:1px solid rgba(0,255,0,0.1); padding:80px 0;">
            <div style="max-width:1280px; margin:0 auto; padding:0 24px; display:grid; grid-template-columns:repeat(auto-fit,minmax(350px,1fr)); gap:48px; align-items:center;">
                <div class="terminal-border" style="padding:0; background:rgba(0,10,0,0.3); overflow:hidden;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid rgba(0,255,0,0.15); font-size:11px; letter-spacing:2px; color:rgba(0,255,0,0.5);">
                        <span>TASK AI CORE</span>
                        <span style="color:#0f0;">ONLINE</span>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:320px; padding:40px; text-align:center;">
                        <div style="width:80px; height:80px; border:2px solid rgba(0,255,0,0.4); display:flex; align-items:center; justify-content:center; margin-bottom:20px; border-radius:50%; box-shadow:0 0 40px rgba(0,255,0,0.2);">
                            <span style="font-size:36px; color:#0f0;">AI</span>
                        </div>
                        <p style="font-size:20px; font-weight:900; letter-spacing:4px; color:#fff; text-transform:uppercase; margin-bottom:12px;">Task AI</p>
                        <p style="color:rgba(200,255,200,0.5); font-size:13px; line-height:1.6; max-width:300px;">Local assistant engine, portal access, automation control, and account management in one black-screen command center.</p>
                        <div style="margin-top:24px;">
                            <a class="btn-download" style="padding:12px 28px; font-size:12px;" href="{{ url('/taskai/download?source=landing-footer') }}">
                                <span class="icon">&darr;</span>
                                <span>Download Now</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div>
                    <p style="font-size:12px; letter-spacing:4px; color:#0f0; font-weight:700; margin-bottom:16px;">// ACCESS</p>
                    <h2 style="font-size:clamp(1.8rem,4vw,3rem); font-weight:900; color:#fff; text-transform:uppercase; letter-spacing:2px; margin-bottom:16px;">Controlled entry, clean command path.</h2>
                    <p style="color:rgba(200,255,200,0.6); line-height:1.8; font-size:15px; margin-bottom:32px;">Task AI keeps the hacker look sharp, but the business logic remains practical: authenticated users, admin approvals, payment callbacks, and device-aware assistant access.</p>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border:1px solid rgba(0,255,0,0.12); background:rgba(0,255,0,0.03);">
                            <span style="color:rgba(200,255,200,0.7); font-size:14px;">auth.session</span>
                            <span style="color:#0f0; font-size:12px; letter-spacing:2px;">VERIFIED</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border:1px solid rgba(0,255,255,0.12); background:rgba(0,255,255,0.03);">
                            <span style="color:rgba(200,255,255,0.7); font-size:14px;">payment.callback</span>
                            <span style="color:#0ff; font-size:12px; letter-spacing:2px;">ARMED</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border:1px solid rgba(255,200,0,0.12); background:rgba(255,200,0,0.03);">
                            <span style="color:rgba(255,230,200,0.7); font-size:14px;">admin.approval</span>
                            <span style="color:#fc0; font-size:12px; letter-spacing:2px;">MANUAL</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer style="border-top:1px solid rgba(0,255,0,0.1); padding:24px; background:rgba(0,5,0,0.5);">
        <div style="max-width:1280px; margin:0 auto; padding:0 24px; display:flex; justify-content:space-between; align-items:center; font-size:11px; letter-spacing:2px; color:rgba(0,255,0,0.3);">
            <span>TASK AI :: COMMAND PORTAL v3.1.7</span>
            <span>[ root@task-ai ]</span>
        </div>
    </footer>
</div>

<script>
(function(){
    var canvas = document.getElementById('matrix-canvas');
    var ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    var chars = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン0123456789ABCDEF<>/{}[]|&^%$#@!';
    var fontSize = 14;
    var columns = canvas.width / fontSize;
    var drops = [];
    for(var x=0; x<columns; x++) drops[x] = Math.floor(Math.random() * canvas.height / fontSize);

    function draw() {
        ctx.fillStyle = 'rgba(10,10,10,0.06)';
        ctx.fillRect(0,0,canvas.width,canvas.height);
        ctx.fillStyle = '#0f0';
        ctx.font = fontSize + 'px monospace';
        for(var i=0; i<drops.length; i++) {
            var text = chars[Math.floor(Math.random() * chars.length)];
            ctx.fillText(text, i*fontSize, drops[i]*fontSize);
            if(drops[i]*fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i]++;
        }
    }
    setInterval(draw, 35);
    window.addEventListener('resize', function(){
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        columns = canvas.width / fontSize;
        drops = [];
        for(var x=0; x<columns; x++) drops[x] = Math.floor(Math.random() * canvas.height / fontSize);
    });
})();
</script>

</body>
</html>
