<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Manya Footwear POS</title>
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="shortcut icon" href="/images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0d0d0d;
            overflow: hidden;
        }

        /* ── Left panel ── */
        .left-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at 60% 40%, #1a1200 0%, #0d0d0d 70%);
            position: relative;
            padding: 3rem;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(180,140,20,.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(200,160,30,.06) 0%, transparent 50%);
        }
        .left-logo {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .left-logo img {
            width: 280px;
            max-width: 80%;
            filter: drop-shadow(0 0 40px rgba(200,160,20,.4));
            animation: glow 3s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(200,160,20,.3)); }
            to   { filter: drop-shadow(0 0 50px rgba(200,160,20,.6)); }
        }
        .left-tagline {
            color: rgba(200,160,20,.6);
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 1.5rem;
        }
        .gold-line {
            width: 60px; height: 1px;
            background: linear-gradient(90deg, transparent, #c8a014, transparent);
            margin: 1rem auto;
        }
        .left-sub {
            color: rgba(255,255,255,.25);
            font-size: .75rem;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* ── Right panel ── */
        .right-panel {
            width: 440px;
            flex-shrink: 0;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            border-left: 1px solid rgba(200,160,20,.15);
            position: relative;
        }
        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c8a014, transparent);
        }

        .login-box { width: 100%; }

        .login-box h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.8rem;
            font-weight: 500;
            margin-bottom: .4rem;
        }
        .login-box .subtitle {
            color: rgba(255,255,255,.35);
            font-size: .85rem;
            margin-bottom: 2.5rem;
            letter-spacing: .5px;
        }

        .form-group { margin-bottom: 1.4rem; }
        .form-group label {
            display: block;
            color: rgba(200,160,20,.9);
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: .6rem;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,.25);
            font-size: .9rem;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            padding: .85rem 1rem .85rem 2.8rem;
            outline: none;
            transition: border-color .2s, background .2s;
        }
        .input-wrap input::placeholder { color: rgba(255,255,255,.2); }
        .input-wrap input:focus {
            background: rgba(200,160,20,.07);
            border-color: rgba(200,160,20,.5);
        }
        .input-wrap input.is-invalid {
            border-color: #e74c3c;
        }

        .btn-login {
            width: 100%;
            padding: .95rem;
            background: linear-gradient(135deg, #c8a014, #a07810);
            color: #000;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: .95rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: .5rem;
            transition: opacity .2s, transform .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }
        .btn-login:hover  { opacity: .9; }
        .btn-login:active { transform: scale(.98); }

        .alert-box {
            background: rgba(231,76,60,.12);
            border: 1px solid rgba(231,76,60,.4);
            border-radius: 8px;
            color: #e74c3c;
            font-size: .85rem;
            padding: .75rem 1rem;
            margin-bottom: 1.5rem;
        }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { padding: 2.5rem 1.5rem; min-height: 40vh; }
            .left-logo img { width: 180px; }
            .right-panel { width: 100%; border-left: none; border-top: 1px solid rgba(200,160,20,.15); padding: 2.5rem 1.5rem 3rem; }
        }
    </style>
</head>
<body>

    {{-- LEFT: Brand panel --}}
    <div class="left-panel">
        <div class="left-logo">
            <img src="/images/logo.jpeg" alt="Manya Footwear">
            <div class="gold-line"></div>
            <div class="left-tagline">Point of Sale</div>
            <div class="gold-line"></div>
            <div class="left-sub">Premium Footwear Management</div>
        </div>
    </div>

    {{-- RIGHT: Login form --}}
    <div class="right-panel">
        <div class="login-box">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            @if(session('error'))
            <div class="alert-box"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="alert-box"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                            required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password"
                            placeholder="Enter your password"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                            required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
        </div>
    </div>

</body>
</html>
