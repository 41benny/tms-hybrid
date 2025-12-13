@php($title = 'Masuk')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $title }} — Vintama Logic</title>

  {{-- Use local Vite assets instead of CDN --}}
  {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    :root{
      --gold1:#e7d08a;
      --gold2:#b8932e;

      /* Solid dove metal */
      --dove-solid: rgba(36, 40, 47, .96);
      --dove-solid-2: rgba(44, 49, 58, .96);

      --emboss-light: rgba(255,255,255,.16);
      --border-base: rgba(255,255,255,.10);
      --mx: 50%;
      --my: 50%;
    }

    body{
      min-height:100vh;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1200px 800px at 70% 10%, rgba(231,208,138,.12), transparent 60%),
        radial-gradient(900px 700px at 20% 85%, rgba(99,214,255,.10), transparent 55%),
        linear-gradient(rgba(6,10,18,.72), rgba(6,10,18,.72)),
        url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=2400&q=80') center/cover no-repeat;
      overflow-x:hidden;
    }

    .vignette{
      position:fixed; inset:0; pointer-events:none;
      background: radial-gradient(900px 600px at 50% 40%, transparent 40%, rgba(0,0,0,.45) 100%);
      mix-blend-mode:multiply;
    }

    /* Shimmer brand */
    .brand-anim{
      display:inline-block;
      background: linear-gradient(90deg,
        rgba(255,255,255,.92),
        rgba(231,208,138,.95),
        rgba(99,214,255,.92),
        rgba(255,255,255,.92)
      );
      background-size: 260% 120%; /* Increased height coverage */
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      animation: shimmer 5.5s ease-in-out infinite;
      text-shadow: 0 18px 50px rgba(0,0,0,.40);
      padding-bottom: 0.1em; /* Space for descenders */
      line-height: 1.1; /* Relaxed leading */
    }
    @keyframes shimmer{
      0%{ background-position: 0% 50%; }
      50%{ background-position: 100% 50%; }
      100%{ background-position: 0% 50%; }
    }

    /* Login card (white 95%) */
    .login-card{
      background: rgba(255,255,255,.95);
      border-radius: 26px;
      box-shadow:
        0 32px 90px rgba(0,0,0,.55),
        inset 0 1px 0 rgba(255,255,255,.65);
      border: 1px solid rgba(255,255,255,.55);
    }
    .input{
      width:100%;
      padding:12px 14px;
      border-radius:14px;
      border:1px solid rgba(15,23,42,.12);
      background: rgba(255,255,255,1);
    }
    .input:focus{
      outline:none;
      border-color: rgba(184,147,46,.55);
      box-shadow: 0 0 0 4px rgba(184,147,46,.18);
    }
    .btn-gold{
      background: linear-gradient(135deg, var(--gold1), var(--gold2));
      color:#1a1406;
      border-radius:14px;
      padding:12px 14px;
      font-weight: 800;
      box-shadow: 0 14px 30px rgba(184,147,46,.25);
      transition: transform .12s ease, filter .12s ease;
    }
    .btn-gold:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .btn-gold:active{ transform: translateY(0px); filter: brightness(.98); }

    /* Single quote card */
    .quote-card{
      position:relative;
      border-radius: 20px;
      background: linear-gradient(145deg, rgba(255,255,255,.05), rgba(0,0,0,.18)), var(--dove-solid);
      border: 1px solid var(--border-base);
      box-shadow:
        0 22px 60px rgba(0,0,0,.46),
        inset 1px 1px 0 var(--emboss-light),
        inset -1px -1px 0 rgba(0,0,0,.62),
        inset 0 1px 0 rgba(255,255,255,.06);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, background .16s ease, border-color .16s ease;
    }
    .quote-card::after{
      content:"";
      position:absolute; inset:0;
      border-radius: 20px;
      pointer-events:none;
      box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.06),
        inset 0 -14px 26px rgba(0,0,0,.22);
      opacity:.95;
    }
    .quote-card::before{
      content:"";
      position:absolute; inset:-2px;
      background: radial-gradient(320px 260px at var(--mx) var(--my),
        rgba(231,208,138,.44),
        rgba(231,208,138,.16) 36%,
        rgba(99,214,255,.10) 56%,
        transparent 76%
      );
      opacity: 0;
      transition: opacity .14s ease;
      pointer-events:none;
      filter: blur(1px);
      mix-blend-mode: screen;
    }
    .quote-card:hover{
      transform: translateY(-2px);
      background: linear-gradient(145deg, rgba(255,255,255,.06), rgba(0,0,0,.20)), var(--dove-solid-2);
      border-color: rgba(231,208,138,.22);
      box-shadow:
        0 28px 78px rgba(0,0,0,.54),
        0 18px 45px rgba(184,147,46,.14),
        inset 1px 1px 0 rgba(255,255,255,.18),
        inset -1px -1px 0 rgba(0,0,0,.66),
        inset 0 1px 0 rgba(255,255,255,.08);
    }
    .quote-card:hover::before{ opacity: 1; }

    .caption{
      color: rgba(255,255,255,.76);
      text-shadow: 0 8px 26px rgba(0,0,0,.65);
    }
    .quote-mark{
      color: rgba(231,208,138,.80);
    }

    /* Quote Navigation Dots */
    .quote-dots {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin-top: 16px;
    }
    .quote-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,.25);
      cursor: pointer;
      transition: all .3s ease;
      border: 1px solid rgba(255,255,255,.1);
    }
    .quote-dot:hover {
      background: rgba(231,208,138,.5);
      transform: scale(1.2);
    }
    .quote-dot.active {
      background: rgba(231,208,138,.85);
      width: 24px;
      border-radius: 4px;
      border-color: rgba(231,208,138,.4);
    }

    /* Entry Animations */
    @keyframes fadeSlideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .animate-entry {
      animation: fadeSlideUp 0.8s ease-out forwards;
    }
    .animate-entry-delayed {
      opacity: 0;
      animation: fadeSlideUp 0.8s ease-out 0.2s forwards;
    }

    /* Loading Button Spinner */
    .btn-loading {
      position: relative;
      color: transparent !important;
      pointer-events: none;
    }
    .btn-loading::after {
      content: "";
      position: absolute;
      width: 16px;
      height: 16px;
      top: 50%;
      left: 50%;
      margin-left: -8px;
      margin-top: -8px;
      border: 2px solid #1a1406;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spinner 0.6s linear infinite;
    }
    @keyframes spinner {
      to { transform: rotate(360deg); }
    }
  </style>
</head>

<body class="px-4">
  <div class="vignette"></div>

  <main class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-10 items-center">

      <!-- LEFT -->
      <section class="hidden lg:block animate-entry">
        <div class="max-w-xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 caption text-xs">
            Urban Skyline Night • Enterprise Theme
          </div>

          <h1 class="mt-5 text-5xl font-semibold tracking-tight text-white">
            <span class="brand-anim">Vintama Logic</span>
          </h1>

          <p class="mt-4 text-lg caption leading-relaxed">
            Enterprise Logistics & Accounting System.
          </p>

          <!-- DYNAMIC QUOTE CARD -->
          <div class="mt-8">
            <div id="quoteCard" class="quote-card p-5 text-white min-h-[160px] flex flex-col justify-center">
              <div class="flex items-start gap-3 relative">
                <div class="text-3xl leading-none quote-mark select-none">“</div>
                <div class="flex-1 transition-all duration-700 ease-in-out opacity-100 transform translate-y-0" id="quoteContent">
                  <div class="text-xs text-white/70 uppercase tracking-wide" id="quoteTitle">Signature principle</div>
                  <div class="mt-2 text-lg font-semibold leading-snug" id="quoteMain">
                    “Calm. Control. Stability. Depth of thought — the logic behind brilliant business decisions.”
                  </div>
                  <div class="mt-3 text-xs text-white/60" id="quoteSub">
                     Calm operations. Clear decisions. Strong accountability.
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Quote Navigation Dots -->
            <div class="quote-dots" id="quoteDots">
              <div class="quote-dot active" data-index="0"></div>
              <div class="quote-dot" data-index="1"></div>
              <div class="quote-dot" data-index="2"></div>
            </div>
          </div>
          
        </div>
      </section>

      <!-- RIGHT: LOGIN -->
      <section class="flex justify-center lg:justify-end animate-entry-delayed">
        <div class="w-full max-w-md scale-90 origin-center lg:origin-right">
          <div class="login-card p-6 sm:p-8">
            <div class="text-center">
              <div class="text-3xl font-semibold tracking-tight text-slate-900">
                Welcome
              </div>
              <div class="mt-1 text-sm text-slate-500">
                Enterprise Logistics & Accounting
              </div>
            </div>

            <!-- ERROR DISPLAY -->
            @if ($errors->any())
                <div class="mt-4 p-3 bg-red-50 text-red-600 rounded-lg text-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
              @csrf
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input @error('email') border-red-500 @enderror" placeholder="name@company.com" autocomplete="username" required autofocus />
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <input type="password" name="password" class="input @error('password') border-red-500 @enderror" placeholder="••••••••" autocomplete="current-password" required />
              </div>

              <div class="flex items-center justify-between pt-1">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 select-none cursor-pointer">
                  <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300" />
                  Remember me
                </label>
                {{-- <a href="#" class="text-sm font-semibold" style="color:#b8932e;">Forgot password?</a> --}}
              </div>

              <button type="submit" id="loginBtn" class="btn-gold w-full">Login</button>
              
              <div class="pt-6 mt-6 border-t border-slate-200 text-center">
                <div class="text-xs text-slate-500">© {{ date('Y') }} Vintama Logic. All rights reserved.</div>
              </div>
            </form>
          </div>

          <div class="lg:hidden mt-6 text-center caption text-sm">
            <span class="brand-anim font-semibold">Vintama Logic</span> • calm enterprise
          </div>
        </div>
      </section>

    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Quote Navigation Dots
      const card = document.getElementById("quoteCard");
      const dots = document.querySelectorAll('.quote-dot');
      
      if(card) {
          const setVars = (e) => {
            const r = card.getBoundingClientRect();
            const x = ((e.clientX - r.left) / r.width) * 100;
            const y = ((e.clientY - r.top) / r.height) * 100;
            card.style.setProperty("--mx", x.toFixed(2) + "%");
            card.style.setProperty("--my", y.toFixed(2) + "%");
          };
          
          card.addEventListener("mousemove", setVars);
          card.addEventListener("mouseenter", setVars);
      }

      // Dynamic Quotes Logic
      const quotes = [
        {
          title: "Signature Principle",
          main: "“Calm. Control. Stability. Depth of thought — the logic behind brilliant business decisions.”",
          sub: "Calm operations. Clear decisions. Strong accountability."
        },
        {
          title: "Operational Excellence",
          main: "“Efficiency is not just about speed, but about the seamless integration of every moving part.”",
          sub: "Streamlined workflows. Optimized resources. Maximum output."
        },
        {
          title: "Trusted Reliability",
          main: "“True reliability is the quiet confidence that everything is exactly where it needs to be.”",
          sub: "Trusted systems. Precise tracking. Unwavering consistency."
        }
      ];

      let currentQuoteIndex = 0;
      let quoteInterval;
      const quoteContent = document.getElementById('quoteContent');
      const quoteTitle = document.getElementById('quoteTitle');
      const quoteMain = document.getElementById('quoteMain');
      const quoteSub = document.getElementById('quoteSub');

      function updateDots() {
        dots.forEach((dot, index) => {
          if (index === currentQuoteIndex) {
            dot.classList.add('active');
          } else {
            dot.classList.remove('active');
          }
        });
      }

      function rotateQuote(targetIndex = null) {
        // Fade out
        quoteContent.style.opacity = '0';
        quoteContent.style.transform = 'translateY(10px)';

        setTimeout(() => {
            if (targetIndex !== null) {
              currentQuoteIndex = targetIndex;
            } else {
              currentQuoteIndex = (currentQuoteIndex + 1) % quotes.length;
            }
            const q = quotes[currentQuoteIndex];
            
            // Update text
            quoteTitle.textContent = q.title;
            quoteMain.textContent = q.main;
            quoteSub.textContent = q.sub;

            // Update dots
            updateDots();

            // Fade in
            quoteContent.style.opacity = '1';
            quoteContent.style.transform = 'translateY(0)';
        }, 700);
      }

      // Dot click handlers
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          if (index !== currentQuoteIndex) {
            // Reset interval when user manually clicks
            clearInterval(quoteInterval);
            rotateQuote(index);
            // Restart auto-rotation
            quoteInterval = setInterval(() => rotateQuote(), 6000);
          }
        });
      });

      // Auto-rotate every 6 seconds
      quoteInterval = setInterval(() => rotateQuote(), 6000);

      // Login Button Loading State
      const loginForm = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginBtn');
      
      if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function(e) {
          // Don't prevent default - let form submit normally
          // Just add loading state
          loginBtn.classList.add('btn-loading');
          loginBtn.disabled = true;
        });
      }
    });
  </script>
</body>
</html>
