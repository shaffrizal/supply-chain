<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In | Supply Chain Intelligence</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root{color-scheme:dark;--blue:#1684ff;--line:#1b3953;--muted:#7890aa}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,"Segoe UI",sans-serif;background:#050e19;color:#e6f0fa}
        .auth-shell{display:grid;grid-template-columns:1.15fr .85fr;min-height:100vh;background:radial-gradient(circle at 18% 18%,#103454 0,#071522 38%,#040c16 78%)}
        .auth-brand{position:relative;display:flex;justify-content:center;flex-direction:column;padding:8vw;overflow:hidden}
        .auth-brand:after{content:"";position:absolute;width:650px;height:650px;left:-300px;bottom:-350px;border:1px solid rgba(59,151,239,.13);border-radius:50%}
        .auth-logo{display:grid;place-items:center;width:58px;height:58px;border:1px solid #245277;border-radius:16px;background:#0d2e4a;color:#56aaff;font-size:23px}
        .eyebrow{display:block;margin-top:23px;color:#3d92ea;font-size:9px;font-weight:850;letter-spacing:1.7px}
        .auth-brand h1{max-width:600px;margin:8px 0 12px;font-size:42px;line-height:1.08;letter-spacing:-1.8px}
        .auth-brand p{max-width:520px;margin:0;color:var(--muted);font-size:14px;line-height:1.7}
        .auth-features{display:flex;gap:20px;margin-top:34px;color:#8ea5ba;font-size:10px}.auth-features i{margin-right:6px;color:#3fdd90}
        .auth-panel{display:grid;place-items:center;padding:35px;border-left:1px solid #173049;background:rgba(6,18,31,.72);backdrop-filter:blur(18px)}
        .login-card{width:min(400px,100%);padding:32px;border:1px solid var(--line);border-radius:18px;background:linear-gradient(145deg,#0d2035,#071524);box-shadow:0 28px 65px rgba(0,0,0,.35)}
        .login-card h2{margin:6px 0;color:#edf6ff;font-size:25px}.login-card>p{margin:0 0 24px;color:#718aa2;font-size:11px}
        .field{margin-bottom:14px}.field label{display:block;margin-bottom:6px;color:#91a8bc;font-size:10px;font-weight:750}
        .field div{display:flex;align-items:center;height:44px;padding:0 12px;border:1px solid #1c3955;border-radius:9px;background:#081725}
        .field i{width:20px;color:#52718d}.field input{width:100%;border:0;outline:0;background:transparent;color:#e5eff8}
        .remember{display:flex;align-items:center;gap:7px;margin:3px 0 18px;color:var(--muted);font-size:10px}
        .login-button{width:100%;height:44px;border:0;border-radius:9px;background:linear-gradient(135deg,var(--blue),#0f5ac5);color:#fff;font-weight:800;cursor:pointer;box-shadow:0 9px 23px rgba(22,132,255,.25)}
        .login-button:hover{filter:brightness(1.08)}.login-error{display:flex;gap:8px;margin-bottom:15px;padding:10px;border:1px solid #6c2931;border-radius:8px;background:#32171d;color:#ff9299;font-size:10px}
        .back-link{display:block;margin-top:18px;color:#6db6ff;font-size:10px;text-align:center;text-decoration:none}.back-link:hover{color:#9bd0ff}
        .security-note{display:flex;gap:9px;margin-top:20px;padding-top:16px;border-top:1px solid #183149;color:#607992;font-size:9px;line-height:1.5}.security-note i{color:#43d58a}
        @media(max-width:850px){.auth-shell{grid-template-columns:1fr}.auth-brand{display:none}.auth-panel{border:0;padding:20px}}
    </style>
</head>
<body>
<main class="auth-shell">
    <section class="auth-brand">
        <div class="auth-logo"><i class="fas fa-globe"></i></div>
        <span class="eyebrow">SUPPLY CHAIN INTELLIGENCE</span>
        <h1>Secure global risk operations.</h1>
        <p>Role-based access for analysts, viewers, and administrators monitoring global supply-chain exposure.</p>
        <div class="auth-features">
            <span><i class="fas fa-check-circle"></i>Protected session</span>
            <span><i class="fas fa-check-circle"></i>CSRF security</span>
            <span><i class="fas fa-check-circle"></i>Role-based access</span>
        </div>
    </section>
    <section class="auth-panel">
        <form class="login-card" method="POST" action="{{ route('login.store') }}">
            @csrf
            <span class="eyebrow">AUTHORIZED PLATFORM ACCESS</span>
            <h2>Welcome back</h2>
            <p>Sign in with your analyst, viewer, or administrator credentials.</p>
            @if($errors->any())
                <div class="login-error"><i class="fas fa-exclamation-circle"></i><span>{{ $errors->first() }}</span></div>
            @endif
            <div class="field"><label for="email">Email address</label><div><i class="fas fa-envelope"></i><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div></div>
            <div class="field"><label for="password">Password</label><div><i class="fas fa-lock"></i><input id="password" type="password" name="password" autocomplete="current-password" required></div></div>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in on this device</label>
            <button class="login-button" type="submit"><i class="fas fa-sign-in-alt"></i> Sign in securely</button>
            <a class="back-link" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> New to the platform? Create an account</a>
            <div class="security-note"><i class="fas fa-shield-alt"></i><span>Permissions follow your assigned role. Administrative data changes remain restricted to administrators.</span></div>
        </form>
    </section>
</main>
</body>
</html>
