@php($title = 'Masuk')
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &mdash; {{ config('app.name', 'Laravel') }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes blob-bounce {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }
        @keyframes blob-move-horizontal {
            0% { transform: translateX(0) scale(1); }
            50% { transform: translateX(50px) scale(1.2); }
            100% { transform: translateX(0) scale(1); }
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        .animate-blob {
            animation: blob-bounce 10s infinite ease-in-out;
        }
        .animate-blob-reverse {
            animation: blob-bounce 15s infinite ease-in-out reverse;
        }
        .animate-blob-horizontal {
            animation: blob-move-horizontal 20s infinite ease-in-out;
        }
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite ease-in-out;
        }
    </style>
</head>
<body class="h-full bg-slate-900 overflow-hidden font-sans antialiased selection:bg-cyan-400 selection:text-white">
    <div class="min-h-screen relative flex items-center justify-center p-4">

        {{-- BACKGROUND: MAX VIBRANCY + ANIMATION --}}
        <div class="absolute inset-0 z-0 overflow-hidden">
            {{-- Base Image --}}
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="
                    background-image: url('{{ asset('images/bg-login-nebula.jpg') }}');
                    filter: brightness(1.1) saturate(1.2);
                "
            ></div>

            {{-- Animated Aurora Blobs --}}
            <div class="absolute inset-0 opacity-60">
                {{-- Purple Blob --}}
                <div class="absolute top-0 -left-40 w-[500px] h-[500px] bg-purple-600/40 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
                {{-- Cyan Blob --}}
                <div class="absolute bottom-0 -right-40 w-[500px] h-[500px] bg-cyan-500/40 rounded-full mix-blend-screen filter blur-[100px] animate-blob-reverse"></div>
                {{-- Pink Blob --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-pink-600/30 rounded-full mix-blend-screen filter blur-[120px] animate-blob-horizontal"></div>
            </div>

            {{-- Twinkling Stars --}}
            <div class="absolute inset-0">
                <div class="star w-1 h-1 top-10 left-10" style="animation-delay: 0s;"></div>
                <div class="star w-0.5 h-0.5 top-20 left-1/4" style="animation-delay: 1s;"></div>
                <div class="star w-1 h-1 top-1/3 right-20" style="animation-delay: 2s;"></div>
                <div class="star w-0.5 h-0.5 bottom-20 left-20" style="animation-delay: 0.5s;"></div>
                <div class="star w-1 h-1 bottom-1/4 right-1/4" style="animation-delay: 1.5s;"></div>
                <div class="star w-0.5 h-0.5 top-1/2 right-10" style="animation-delay: 2.5s;"></div>
                <div class="star w-1 h-1 top-5 right-1/2" style="animation-delay: 3s;"></div>
                {{-- More random stars --}}
                <div class="star w-0.5 h-0.5 top-[15%] left-[80%]" style="animation-delay: 0.2s;"></div>
                <div class="star w-0.5 h-0.5 top-[85%] left-[40%]" style="animation-delay: 1.8s;"></div>
                <div class="star w-1 h-1 top-[40%] left-[10%]" style="animation-delay: 2.2s;"></div>
            </div>

            {{-- Vignette --}}
            <div class="absolute inset-0 bg-radial-gradient from-transparent to-black/40"></div>
        </div>

        {{-- MAIN GLASS CARD --}}
        <div
            class="relative z-10 w-full max-w-lg
                   rounded-[24px]
                   border border-white/60
                   bg-gradient-to-br from-white/20 via-white/5 to-transparent
                   backdrop-blur-xl
                   shadow-[0_0_25px_rgba(255,255,255,0.3),0_0_60px_rgba(255,255,255,0.1)]
                   p-6 md:p-10
                   flex flex-col items-center justify-center
                   overflow-hidden"
        >
            {{-- GLOSS SHINE (Top Right) --}}
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/20 rounded-full blur-[80px] pointer-events-none mix-blend-overlay"></div>
            {{-- GLOSS SHINE (Bottom Left) --}}
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-cyan-400/20 rounded-full blur-[80px] pointer-events-none mix-blend-overlay"></div>

            {{-- CONTENT WRAPPER --}}
            <div class="relative w-full max-w-sm mx-auto space-y-6 text-center">
                
                {{-- HEADER --}}
                <div class="space-y-1.5">
                    <h1 class="text-2xl md:text-3xl font-bold text-white drop-shadow-[0_2px_10px_rgba(0,0,0,0.3)] tracking-tight">
                        Selamat Datang
                    </h1>
                    <p class="text-white text-xs tracking-wide font-medium drop-shadow-md">
                        Silakan masuk ke akun Anda.
                    </p>
                </div>

                {{-- ERROR ALERT --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200/50 bg-red-500/30 text-white text-xs p-3 backdrop-blur-md text-left shadow-lg">
                        <ul class="list-disc pl-4 space-y-1 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('login.store') }}" class="space-y-5 text-left">
                    @csrf

                    <div class="grid grid-cols-1 gap-5">
                        {{-- Email --}}
                        <div class="group relative">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder=" "
                                class="peer w-full rounded-lg border-[1.5px] border-white/30
                                       bg-white/5
                                       px-4 py-3 text-sm text-white font-medium
                                       placeholder-transparent
                                       focus:border-white/60 focus:bg-white/10 focus:ring-0
                                       focus:shadow-[0_0_20px_rgba(255,255,255,0.3)]
                                       transition-all duration-300"
                            >
                            <label
                                for="email"
                                class="absolute left-4 -top-6 text-white text-xs font-medium transition-all duration-300 drop-shadow-sm pointer-events-none
                                       peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-placeholder-shown:text-white/80
                                       peer-focus:-top-6 peer-focus:text-xs peer-focus:text-white"
                            >
                                Email Address
                            </label>
                        </div>

                        {{-- Password --}}
                        <div class="group relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                placeholder=" "
                                class="peer w-full rounded-lg border-[1.5px] border-white/30
                                       bg-white/5
                                       px-4 py-3 text-sm text-white font-medium
                                       placeholder-transparent
                                       focus:border-white/60 focus:bg-white/10 focus:ring-0
                                       focus:shadow-[0_0_20px_rgba(255,255,255,0.3)]
                                       transition-all duration-300"
                            >
                            <label
                                for="password"
                                class="absolute left-4 -top-6 text-white text-xs font-medium transition-all duration-300 drop-shadow-sm pointer-events-none
                                       peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-placeholder-shown:text-white/80
                                       peer-focus:-top-6 peer-focus:text-xs peer-focus:text-white"
                            >
                                Kata Sandi
                            </label>
                            
                            {{-- Toggle Password Button --}}
                            <button 
                                type="button" 
                                id="password-toggle"
                                class="absolute right-4 top-3 text-white/70 hover:text-white focus:outline-none transition-colors z-20 cursor-pointer"
                            >
                                {{-- Eye Icon (Show) --}}
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{-- Eye Slash Icon (Hide) --}}
                                <svg id="eye-slash-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const toggleBtn = document.getElementById('password-toggle');
                            const passwordInput = document.getElementById('password');
                            const eyeIcon = document.getElementById('eye-icon');
                            const eyeSlashIcon = document.getElementById('eye-slash-icon');

                            if (toggleBtn && passwordInput) {
                                toggleBtn.addEventListener('click', function() {
                                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                                    passwordInput.setAttribute('type', type);
                                    
                                    if (type === 'text') {
                                        eyeIcon.classList.add('hidden');
                                        eyeSlashIcon.classList.remove('hidden');
                                    } else {
                                        eyeIcon.classList.remove('hidden');
                                        eyeSlashIcon.classList.add('hidden');
                                    }
                                });
                            }
                        });
                    </script>

                    {{-- Remember Only --}}
                    <div class="flex items-center justify-start text-xs pt-1 font-medium">
                        <label class="inline-flex items-center gap-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-white/60 bg-white/10 text-cyan-400 focus:ring-offset-0 focus:ring-cyan-400/50 h-3.5 w-3.5"
                            >
                            <span class="text-white group-hover:text-cyan-200 transition-colors drop-shadow-sm">Ingat Saya</span>
                        </label>
                    </div>

                    {{-- BUTTON --}}
                    <div class="pt-4 flex justify-center">
                        <button
                            type="submit"
                            class="group relative w-full md:w-auto px-16 py-3 rounded-full
                                   bg-white/10
                                   backdrop-blur-md
                                   border border-white/50
                                   text-white font-bold tracking-widest uppercase text-xs
                                   shadow-[0_0_20px_rgba(255,255,255,0.1)]
                                   hover:bg-white/20
                                   hover:border-white
                                   hover:shadow-[0_0_35px_rgba(255,255,255,0.4)]
                                   hover:scale-105
                                   transition-all duration-300 ease-out
                                   overflow-hidden"
                        >
                            <span class="relative z-10">Masuk</span>
                            {{-- Shine effect on hover --}}
                            <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 ease-in-out"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
