<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Food Ordering</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand: #f97316;
            --brand-dark: #ea580c;
            --text: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Open Sans", sans-serif;
            color: var(--text);
            background: #020617;
            overflow-x: hidden;
        }

        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px 16px;
            background:
                linear-gradient(rgba(2, 6, 23, 0.55), rgba(2, 6, 23, 0.7)),
                radial-gradient(1200px 500px at 80% -10%, rgba(249, 115, 22, 0.25), transparent),
                radial-gradient(900px 420px at -10% 110%, rgba(22, 163, 74, 0.22), transparent),
                url('/images/landing_page.jpg') center/cover no-repeat;
        }

        .hero-inner {
            max-width: 960px;
            position: relative;
            z-index: 2;
        }

        .kicker {
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            letter-spacing: 0.18em;
            color: #fdba74;
            text-transform: uppercase;
            margin-bottom: 14px;
            font-weight: 700;
        }

        .title {
            margin: 0;
            color: #fff;
            font-family: "Poppins", sans-serif;
            font-size: clamp(2rem, 5vw, 4rem);
            line-height: 1.08;
            font-weight: 800;
            text-wrap: balance;
        }

        .subtitle {
            margin: 18px auto 0;
            color: rgba(255, 255, 255, 0.9);
            max-width: 700px;
            font-size: clamp(1rem, 2.1vw, 1.2rem);
        }

        .actions {
            margin-top: 34px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-family: "Poppins", sans-serif;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #16a34a, #15803d); 
            box-shadow: 0 10px 24px rgba(22, 163, 74, 0.35);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .btn-ghost {
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        #footer {
            background: #0b1220;
            color: #94a3b8;
            text-align: center;
            font-size: 14px;
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        #footer strong {
            color: #e2e8f0;
        }

        .snow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 3;
        }

        .flake {
            position: absolute;
            top: -5vh;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            animation-name: fall;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }

        @keyframes fall {
            0% { transform: translateY(-5vh) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(110vh) translateX(32px); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="snow" id="snowLayer"></div>

    <section class="hero">
        <div class="hero-inner">
            <div class="kicker">Welcome To</div>
            <h1 class="title">F&B Ordering System</h1>
            <p class="subtitle">
               Better customer experience from one modern food platform.
            </p>

            <div class="actions">
                <a href="{{ route('home') }}" class="btn btn-primary">Order Now</a>
               
            </div>
        </div>
    </section>

    <footer id="footer">
        &copy; {{ now()->year }} <strong>F&B</strong> Ordering System | All Rights Reserved
    </footer>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        (function () {
            const snowLayer = document.getElementById('snowLayer');
            const symbols = ['❄', '✦', '✶'];
            const count = 55;

            for (let i = 0; i < count; i++) {
                const flake = document.createElement('span');
                flake.className = 'flake';
                flake.textContent = symbols[Math.floor(Math.random() * symbols.length)];
                flake.style.left = Math.random() * 100 + 'vw';
                flake.style.animationDuration = (6 + Math.random() * 10).toFixed(2) + 's';
                flake.style.animationDelay = (Math.random() * 10).toFixed(2) + 's';
                flake.style.fontSize = (10 + Math.random() * 20).toFixed(0) + 'px';
                snowLayer.appendChild(flake);
            }
        })();
    </script>
</body>
</html>

