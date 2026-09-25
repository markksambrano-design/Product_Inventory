<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bg:#0d131d; --panel:#151d29; --line:rgba(255,255,255,.12); --muted:#929aaa; --blue:#315cff; --violet:#7147f7; }
        * { box-sizing:border-box; }
        html, body { width:100%; min-height:100%; }
        body { min-width:1100px; min-height:100vh; margin:0; padding:0; color:#f8fafc; background:#050a13; font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; overflow-x:auto; }
        .shell { width:100%; min-width:1100px; min-height:100vh; margin:0; display:flex; overflow:hidden; border:0; border-radius:0; background:#0b1423; box-shadow:none; }
        .brand-side { position:relative; width:52%; flex:0 0 52%; min-width:520px; min-height:100vh; overflow:hidden; display:flex; justify-content:center; padding:5.5vh 46px 50px; background-color:#020817; background-image:linear-gradient(180deg,#020817 0%,rgba(2,8,23,.78) 23%,rgba(2,8,23,.06) 52%,rgba(3,7,18,.38) 100%),url('{{ asset('images/inventory-login-warehouse-v5.png') }}'); background-size:cover,cover; background-position:center,center 125px; background-repeat:no-repeat,no-repeat; }
        .brand-side::before { content:""; position:absolute; inset:0; background:radial-gradient(circle at 50% 24%,rgba(65,72,255,.13),transparent 25%),linear-gradient(90deg,rgba(1,5,16,.3),transparent 18% 82%,rgba(1,5,16,.3)); }
        .brand-side::after { content:""; position:absolute; inset:auto 0 0; height:32%; background:linear-gradient(transparent,rgba(3,6,16,.12)); }
        .brand-content { position:relative; z-index:4; width:100%; max-width:520px; height:max-content; text-align:center; }
        .cube { width:76px; height:76px; margin:0 auto 13px; filter:drop-shadow(0 10px 25px rgba(57,79,255,.45)); }
        .brand-title { margin:0; font-size:2.35rem; letter-spacing:.055em; font-weight:800; text-shadow:0 4px 20px rgba(0,0,0,.5); }
        .brand-subtitle { margin-top:2px; color:#7682ff; letter-spacing:.24em; font-weight:700; font-size:1rem; }
        .accent-line { width:210px; height:1px; margin:18px auto 14px; background:linear-gradient(90deg,transparent,#7948ff,#2e67ff,transparent); }
        .tagline { margin:auto; max-width:390px; color:#c5cad5; line-height:1.5; font-size:.92rem; text-shadow:0 2px 12px #020617; }
        .tagline strong { display:block; color:#fff; font-size:1.08rem; margin-bottom:3px; }
        .warehouse-art { display:none; }
        /* Match the background image's 1400x1154 cover plane so zoom keeps
           the animated chart locked to the monitor artwork. */
        .dashboard-motion { position:absolute; z-index:3; left:50%; top:125px; width:max(100%,121.318vh); aspect-ratio:1400/1154; transform:translateX(-50%); pointer-events:none; }
        .motion-donut { position:absolute; left:49.65%; top:43.55%; width:9.3%; aspect-ratio:1; border-radius:50%; background:#09172e; box-shadow:0 0 0 2px #09172e,0 0 14px rgba(34,89,210,.18); }
        .motion-donut::before { content:""; position:absolute; inset:2%; border-radius:50%; background:conic-gradient(#4bd7ae 0 24%,#7048ed 24% 49%,#2479e7 49% 74%,#18366d 74% 100%); -webkit-mask:radial-gradient(circle,transparent 0 45%,#000 47%); mask:radial-gradient(circle,transparent 0 45%,#000 47%); animation:donutSpin 7s linear infinite; }
        .motion-donut::after { content:""; position:absolute; inset:31%; border-radius:50%; background:radial-gradient(circle at 40% 35%,#102344,#071225 72%); box-shadow:inset 0 0 10px rgba(0,0,0,.45); }
        .motion-bars { position:absolute; left:77.55%; top:46.65%; width:12.4%; height:7.4%; display:flex; align-items:flex-end; justify-content:space-between; padding:0 .3%; }
        .motion-bars i { display:block; width:3.8%; min-width:2px; height:38%; border-radius:3px 3px 0 0; background:linear-gradient(#7657ff,#296aff); transform-origin:bottom; animation:barMove 2.2s ease-in-out infinite alternate; }
        .motion-bars i:nth-child(2){height:64%;animation-delay:.15s}.motion-bars i:nth-child(3){height:48%;animation-delay:.3s}.motion-bars i:nth-child(4){height:82%;animation-delay:.45s}.motion-bars i:nth-child(5){height:70%;animation-delay:.6s}.motion-bars i:nth-child(6){height:76%;animation-delay:.75s}.motion-bars i:nth-child(7){height:61%;animation-delay:.9s}.motion-bars i:nth-child(8){height:43%;animation-delay:1.05s}
        .motion-nav { position:absolute; left:36.5%; top:32.25%; width:9.25%; height:27.5%; display:grid; grid-template-rows:repeat(9,1fr); gap:1.2%; }
        .motion-nav i { position:relative; display:flex; align-items:center; gap:8%; padding:0 8%; border-radius:6px; }
        .motion-nav i:nth-child(2) { background:linear-gradient(100deg,rgba(109,47,255,.88),rgba(55,26,210,.92)); box-shadow:0 0 13px rgba(93,54,255,.35); animation:navActive 2.8s ease-in-out infinite; }
        .motion-nav i::before { content:""; flex:0 0 8%; aspect-ratio:1; border:1px solid #a9b7d2; border-radius:35%; transform:rotate(45deg); animation:navIcon 2.4s ease-in-out infinite; animation-delay:calc(var(--row) * .13s); }
        .motion-nav i::after { content:""; width:29%; height:8%; min-height:2px; border-radius:10px; background:linear-gradient(90deg,#7c8bb0,#4e5878); transform-origin:left; animation:navLine 2.4s ease-in-out infinite alternate; animation-delay:calc(var(--row) * .13s); }
        .motion-nav i:first-child::after { background:linear-gradient(90deg,#65e4ff,#3188ff); box-shadow:0 0 6px rgba(55,174,255,.55); }
        .motion-nav i:nth-child(2)::before { border-color:#e2ddff; box-shadow:0 0 5px rgba(255,255,255,.4); }
        .motion-nav i:nth-child(2)::after { width:40%; background:linear-gradient(90deg,#fff,#bdc3ff); }
        .motion-cards { position:absolute; left:47.65%; top:34.45%; width:44.3%; height:6.5%; display:grid; grid-template-columns:repeat(3,1fr); gap:2%; }
        .motion-card { position:relative; }
        .motion-card::before,.motion-card::after { content:""; position:absolute; left:8%; height:7%; border-radius:8px; background:linear-gradient(90deg,#8995bd,#4c5778); animation:dataLine 2.5s ease-in-out infinite alternate; }
        .motion-card::before { top:28%; width:40%; }.motion-card::after { top:58%; width:30%; animation-delay:.45s; }
        .motion-card i { position:absolute; right:8%; top:26%; width:16%; aspect-ratio:1; border-radius:50%; background:radial-gradient(circle at 45% 40%,#258cff,#5239c9 58%,#142451 62%); box-shadow:0 0 10px #315cff66; animation:orbPulse 2.2s ease-in-out infinite; }
        .motion-card:nth-child(2) i{animation-delay:.35s}.motion-card:nth-child(3) i{animation-delay:.7s}
        .motion-legend { position:absolute; left:62.55%; top:44.35%; width:9%; height:9%; display:grid; grid-template-rows:repeat(4,1fr); }
        .motion-legend i,.motion-table i { display:flex; align-items:center; gap:9%; }
        .motion-legend b,.motion-table b { width:7%; aspect-ratio:1; border-radius:50%; background:var(--dot); box-shadow:0 0 5px color-mix(in srgb,var(--dot),transparent 40%); animation:dotPulse 1.8s ease-in-out infinite; }
        .motion-legend span,.motion-table span { width:38%; height:8%; min-height:2px; border-radius:8px; background:linear-gradient(90deg,#7f8cad,#4a5574); animation:dataLine 2.1s ease-in-out infinite alternate; }
        .motion-panel-title { position:absolute; left:77.5%; top:42.35%; width:10%; height:3%; display:flex; align-items:center; gap:9%; }
        .motion-panel-title::before { content:""; width:9%; aspect-ratio:1; border:1px solid #bdc9df; transform:rotate(30deg); animation:titleSpin 4s linear infinite; }
        .motion-panel-title::after { content:""; width:38%; height:7%; min-height:2px; border-radius:8px; background:linear-gradient(90deg,#8996be,#4d5877); animation:dataLine 2s ease-in-out infinite alternate; }
        .motion-table { position:absolute; left:47.7%; top:58.25%; width:44%; height:16.6%; padding:1.2% 2%; display:grid; grid-template-rows:.65fr repeat(4,1fr); }
        .motion-table .head { width:14%; height:5%; min-height:2px; border-radius:8px; background:#536180; animation:dataLine 2s ease-in-out infinite alternate; }
        .motion-table i { position:relative; border-top:1px solid rgba(92,111,153,.12); }
        .motion-table i span { width:14%; margin-right:9%; }
        .motion-table i span:nth-of-type(2){width:12%}.motion-table i span:nth-of-type(3){width:8%}
        .motion-table em { margin-left:auto; width:9%; height:28%; background:linear-gradient(135deg,transparent 38%,#445dca 40% 48%,transparent 50% 62%,#445dca 64% 72%,transparent 74%); animation:spark 2s ease-in-out infinite; }
        .motion-table small { width:3%; aspect-ratio:1; margin-left:6%; border-radius:50%; background:var(--status); box-shadow:0 0 5px var(--status); animation:dotPulse 1.6s ease-in-out infinite; }
        .cube { animation:cubeFloat 4s ease-in-out infinite; }
        @keyframes barMove { 0%{transform:scaleY(.82);filter:brightness(.85)}100%{transform:scaleY(1.08);filter:brightness(1.4)} }
        @keyframes donutSpin { to{transform:rotate(360deg)} }
        @keyframes navActive { 0%,100%{filter:brightness(.9);transform:translateX(0)}50%{filter:brightness(1.18);transform:translateX(2%)} }
        @keyframes navIcon { 0%,100%{opacity:.55;transform:rotate(45deg) scale(.82)}50%{opacity:1;transform:rotate(225deg) scale(1)} }
        @keyframes navLine { from{opacity:.48;transform:scaleX(.72)}to{opacity:1;transform:scaleX(1.08)} }
        @keyframes dataLine { from{opacity:.45;transform:scaleX(.72)}to{opacity:1;transform:scaleX(1)} }
        @keyframes orbPulse { 0%,100%{filter:brightness(.75);transform:scale(.88)}50%{filter:brightness(1.4);transform:scale(1.08)} }
        @keyframes dotPulse { 0%,100%{opacity:.55;transform:scale(.82)}50%{opacity:1;transform:scale(1.18)} }
        @keyframes titleSpin { to{transform:rotate(390deg)} }
        @keyframes spark { 0%,100%{opacity:.4;transform:translateY(8%)}50%{opacity:1;transform:translateY(-8%)} }
        @keyframes cubeFloat { 0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)} }
        @media (prefers-reduced-motion:reduce) { .dashboard-motion *,.dashboard-motion *::before,.dashboard-motion *::after,.cube { animation:none !important; } }
        .screen { width:58%; height:190px; margin-left:29%; padding:13px; border:8px solid #080d1a; border-radius:11px; background:linear-gradient(145deg,#111a2e,#060b17); box-shadow:0 25px 38px rgba(0,0,0,.55); transform:perspective(700px) rotateY(-4deg); }
        .screen-head { display:flex; justify-content:space-between; color:#c8ceda; font-size:.55rem; border-bottom:1px solid rgba(255,255,255,.08); padding-bottom:8px; }
        .screen-body { display:grid; grid-template-columns:1fr 1fr; align-items:center; height:135px; }
        .donut { width:92px; height:92px; margin:auto; display:grid; place-items:center; border-radius:50%; background:conic-gradient(#4fd2a2 0 24%,#7048ef 24% 48%,#2379d8 48% 73%,#19233b 73%); position:relative; }
        .donut::after { content:"320\A Total Items"; white-space:pre; display:grid; place-items:center; position:absolute; inset:18px; border-radius:50%; background:#0c1424; font-size:.63rem; }
        .bars span { display:block; height:5px; margin:13px 0; border-radius:5px; background:linear-gradient(90deg,#49caa0 45%,#263047 45%); }
        .bars span:nth-child(2){background:linear-gradient(90deg,#efad53 25%,#263047 25%)} .bars span:nth-child(3){background:linear-gradient(90deg,#e56e67 12%,#263047 12%)}
        .boxes { position:absolute; left:4%; bottom:-8px; display:flex; align-items:flex-end; gap:5px; }
        .box { width:70px; height:65px; border-radius:3px; background:linear-gradient(145deg,#b36d42,#6e3b28); box-shadow:inset 0 1px rgba(255,255,255,.2),0 12px 22px rgba(0,0,0,.3); }
        .box:nth-child(2){width:54px;height:52px}.box::after{content:"";display:block;width:20px;height:12px;margin:8px;background:#c38a69}
        .login-side { position:relative; isolation:isolate; width:48%; flex:1 1 48%; min-width:0; min-height:100vh; overflow:hidden; display:flex; align-items:center; justify-content:center; padding:48px clamp(28px,4vw,72px); background:#081221; }
        .login-side::before { content:""; position:absolute; z-index:-2; inset:-18px; background-image:url('{{ asset('images/inventory-login-warehouse-v5.png') }}'); background-size:cover; background-position:62% center; opacity:.25; filter:blur(4px) saturate(.8); transform:scale(1.04) scaleX(-1); }
        .login-side::after { content:""; position:absolute; z-index:-1; inset:0; background-image:radial-gradient(circle at 50% 45%,rgba(12,32,68,.55),rgba(4,11,23,.82) 64%,rgba(3,8,17,.94)),radial-gradient(#4a72dd 1.3px,transparent 1.3px); background-size:auto,15px 15px; background-position:center,calc(100% - 35px) 30px; background-repeat:no-repeat,repeat; }
        .login-card { position:relative; width:100%; max-width:400px; overflow:hidden; border:1px solid rgba(107,131,190,.28); border-radius:15px; background:linear-gradient(150deg,rgba(13,28,52,.88),rgba(7,18,35,.95)); box-shadow:0 34px 90px rgba(0,0,0,.5),inset 0 1px rgba(255,255,255,.04),0 0 70px rgba(35,73,170,.08); backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px); }
        .card-main { padding:20px 28px 17px; }
        .lock-ring { width:52px; height:52px; display:grid; place-items:center; margin:0 auto 9px; border:1px solid rgba(255,255,255,.1); border-radius:50%; color:#647bff; background:radial-gradient(circle,rgba(65,89,255,.11),transparent 68%); box-shadow:inset 0 0 25px rgba(65,89,255,.04); }
        .lock-ring svg { width:27px; height:27px; }
        h1 { text-align:center; font-size:1.4rem; font-weight:750; margin-bottom:3px; }
        .lead-copy { text-align:center; color:var(--muted); margin-bottom:16px; font-size:.8rem; }
        .form-label { font-size:.82rem; font-weight:650; margin-bottom:7px; }
        .input-wrap { position:relative; }
        .field-icon { position:absolute; left:17px; top:50%; transform:translateY(-50%); color:#8992a3; }
        .form-control { height:42px; padding:0 40px 0 42px; font-size:.88rem; color:#f8fafc; caret-color:#7c8cff; border:1px solid rgba(255,255,255,.16); border-radius:8px; background:#141d2a; transition:.2s; }
        .form-control::placeholder { color:#8e96a6; }.form-control:focus { color:#fff; background:rgba(12,18,28,.28); border-color:#586cff; box-shadow:0 0 0 3px rgba(76,95,255,.12); }
        .form-control:-webkit-autofill,.form-control:-webkit-autofill:hover,.form-control:-webkit-autofill:focus { -webkit-text-fill-color:#f8fafc !important; caret-color:#f8fafc; -webkit-box-shadow:0 0 0 1000px #141d2a inset !important; box-shadow:0 0 0 1000px #141d2a inset !important; border-color:#586cff; transition:background-color 9999s ease-out 0s; }
        .eye { position:absolute; right:11px; top:50%; transform:translateY(-50%); padding:9px; color:#9aa2b1; border:0; background:transparent; }
        .form-check-input { background-color:transparent; border-color:#657083; }.form-check-input:checked{background-color:#5269ff;border-color:#5269ff}
        .forgot { color:#7184ff; text-decoration:none; font-size:.88rem; }.forgot:hover{color:#9aa7ff}
        .sign-in { height:42px; border:0; border-radius:8px; color:#fff; font-size:.9rem; font-weight:700; background:linear-gradient(100deg,#7447f6 0%,#4e4df7 48%,#315dff 100%); box-shadow:0 13px 30px rgba(53,75,246,.28); transition:.2s; }.sign-in:hover{color:#fff;filter:brightness(1.1);transform:translateY(-1px)}
        .alert { border:1px solid rgba(239,68,68,.28); background:rgba(127,29,29,.22); color:#fecaca; }
        .card-footer-custom { padding:11px; text-align:center; color:#8e96a4; border-top:1px solid rgba(255,255,255,.08); font-size:.7rem; }
        /* Browser zoom changes viewport width, so use the physical device width
           for the mobile switch. Desktop layout therefore stays side-by-side. */
        @media(max-device-width:767px){body{min-width:0;padding:0;overflow-x:hidden}.shell{width:100%;min-width:0;min-height:100vh;border:0;border-radius:0;display:block}.brand-side{display:none}.login-side{width:100%;min-height:100vh;padding:14px}.login-card{max-width:400px}.card-main{padding:20px 22px 17px}.lock-ring{width:52px;height:52px}h1{font-size:1.4rem}.lead-copy{font-size:.8rem}.login-card{border-radius:14px}}
    </style>
</head>
<body>
<main class="shell">
    <section class="brand-side">
        <div class="brand-content">
            <svg class="cube" viewBox="0 0 100 100"><defs><linearGradient id="a" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#8b4dff"/><stop offset="1" stop-color="#394cff"/></linearGradient><linearGradient id="b" x1="0" x2="1"><stop stop-color="#7145ff"/><stop offset="1" stop-color="#214de6"/></linearGradient></defs><path fill="url(#a)" d="M50 5 92 29 50 54 8 29Z"/><path fill="url(#b)" d="M8 35 47 58v38L8 73Z"/><path fill="#315bfb" d="m53 58 39-23v38L53 96Z"/></svg>
            <h2 class="brand-title">INVENTORY</h2>
            <div class="brand-subtitle">MANAGEMENT SYSTEM</div>
            <div class="accent-line"></div>
            <p class="tagline"><strong>Track. Manage. Organize.</strong>A smarter way to manage your inventory in one secure and efficient system.</p>
        </div>
        <div class="warehouse-art" aria-hidden="true">
            <div class="screen"><div class="screen-head"><span>STOCK OVERVIEW</span><span>◻ ◻</span></div><div class="screen-body"><div class="donut"></div><div class="bars"><span></span><span></span><span></span></div></div></div>
            <div class="boxes"><div class="box"></div><div class="box"></div></div>
        </div>
        <div class="dashboard-motion" aria-hidden="true">
            <span class="motion-nav"><i style="--row:0"></i><i style="--row:1"></i><i style="--row:2"></i><i style="--row:3"></i><i style="--row:4"></i><i style="--row:5"></i><i style="--row:6"></i><i style="--row:7"></i><i style="--row:8"></i></span>
            <span class="motion-cards"><i class="motion-card"><i></i></i><i class="motion-card"><i></i></i><i class="motion-card"><i></i></i></span>
            <span class="motion-donut"></span>
            <span class="motion-legend"><i style="--dot:#2585ff"><b></b><span></span></i><i style="--dot:#7048ed"><b></b><span></span></i><i style="--dot:#e89343"><b></b><span></span></i><i style="--dot:#df4149"><b></b><span></span></i></span>
            <span class="motion-panel-title"></span>
            <span class="motion-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></span>
            <span class="motion-table"><b class="head"></b><i style="--dot:#2585ff;--status:#35c69c"><b></b><span></span><span></span><span></span><em></em><small></small></i><i style="--dot:#7048ed;--status:#df4149"><b></b><span></span><span></span><span></span><em></em><small></small></i><i style="--dot:#e89343;--status:#df4149"><b></b><span></span><span></span><span></span><em></em><small></small></i><i style="--dot:#35c69c;--status:#35c69c"><b></b><span></span><span></span><span></span><em></em><small></small></i></span>
        </div>
    </section>

    <section class="login-side">
        <div class="login-card">
            <div class="card-main">
                <div class="lock-ring"><svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/></svg></div>
                <h1>Welcome Back!</h1>
                <p class="lead-copy">Sign in to continue to your inventory system</p>

                @if($errors->any())<div class="alert py-2 px-3 mb-3">{{ $errors->first() }}</div>@endif

                <form action="{{ route('login.submit') }}" method="POST">@csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrap"><svg class="field-icon" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter your email" autocomplete="email" required autofocus></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrap"><svg class="field-icon" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/></svg><input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required><button id="togglePassword" type="button" class="eye" aria-label="Show password"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label small text-secondary" for="remember">Remember me</label></div><a class="forgot" href="{{ route('password.request') }}">Forgot password?</a></div>
                    <button class="btn sign-in w-100" type="submit"><svg class="me-2" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>Sign In</button>
                </form>
            </div>
            <div class="card-footer-custom">© {{ date('Y') }} Inventory Management System. All rights reserved.</div>
        </div>
    </section>
</main>
<script>
    const password = document.getElementById('password');
    document.getElementById('togglePassword').addEventListener('click', function () { password.type = password.type === 'password' ? 'text' : 'password'; });
</script>
</body>
</html>
