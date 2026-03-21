<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua | Login</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ab1155",
                        secondary: "#ab2c5d",
                        surface: "#fbf9fb",
                        "surface-container": "#efedef",
                        "surface-container-high": "#e9e7e9",
                        "surface-container-highest": "#e3e2e4",
                        "surface-container-low": "#f5f3f5",
                        "surface-container-lowest": "#ffffff",
                        "primary-container": "#cc326e",
                        "primary-fixed": "#ffd9e1",
                        "primary-fixed-dim": "#ffb1c5",
                        "secondary-container": "#fd6c9c",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                        outline: "#8e6f74",
                        "outline-variant": "#e3bdc3"
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"],
                        label: ["Manrope"]
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        lg: "2rem",
                        xl: "3rem",
                        full: "9999px"
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
            vertical-align: middle;
        }
        body {
            background-color: #fbf9fb;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(216, 27, 96, 0.04) 0%, transparent 35%),
                radial-gradient(circle at 90% 10%, rgba(171, 17, 85, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 80% 80%, rgba(204, 50, 110, 0.05) 0%, transparent 35%);
        }
        .lunar-glass {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(24px);
        }
        .signature-glow {
            background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%);
        }
    </style>
</head>
<body class="font-body text-on-surface antialiased">
    <nav class="fixed top-0 z-50 flex h-20 w-full items-center justify-between bg-[#D81B60] px-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
        <div class="flex items-center gap-12">
            <a class="text-2xl font-black italic tracking-tighter text-white" href="/">SexyLua</a>
            <div class="hidden items-center gap-8 md:flex">
                <a class="font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="/">Live Cam</a>
                <a class="font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="/explore">Explorar</a>
                <a class="font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="/register">Criar Conta</a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="rounded-full border-b-2 border-white pb-1 font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-wide text-white">Login</span>
            <a class="rounded-full bg-white px-6 py-2 font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-wide text-primary transition-transform duration-200 hover:scale-105" href="/register">Registro</a>
        </div>
    </nav>

    <main class="mx-auto flex min-h-screen max-w-7xl items-center px-4 pb-16 pt-32 md:px-8">
        <div class="grid w-full gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="relative overflow-hidden rounded-[2rem] bg-white/50 p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-10">
                <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-primary/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-secondary-container/20 blur-3xl"></div>
                <div class="relative space-y-8">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5">
                        <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings: 'FILL' 1;">nights_stay</span>
                        <span class="font-label text-xs font-bold uppercase tracking-widest text-primary">Acesso Lunar</span>
                    </div>
                    <div class="space-y-4">
                        <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface md:text-5xl">Entre na atmosfera SexyLua</h1>
                        <p class="max-w-2xl text-lg leading-relaxed text-on-surface-variant">Acesse sua conta para navegar entre as areas publicas, painel do criador, area do assinante e administracao, tudo mantendo o mesmo visual do prototipo.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="lunar-glass rounded-2xl border border-white/70 p-4 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Admin</p>
                            <p class="mt-3 text-sm font-bold">admin@sexylua.local</p>
                            <p class="text-sm text-on-surface-variant">admin123</p>
                        </div>
                        <div class="lunar-glass rounded-2xl border border-white/70 p-4 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Criador</p>
                            <p class="mt-3 text-sm font-bold">maria@sexylua.local</p>
                            <p class="text-sm text-on-surface-variant">creator123</p>
                        </div>
                        <div class="lunar-glass rounded-2xl border border-white/70 p-4 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Assinante</p>
                            <p class="mt-3 text-sm font-bold">assinante@sexylua.local</p>
                            <p class="text-sm text-on-surface-variant">subscriber123</p>
                        </div>
                    </div>
                    <div class="rounded-[2rem] signature-glow p-6 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15">
                                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">diamond</span>
                            </div>
                            <div class="space-y-2">
                                <h2 class="font-headline text-xl font-bold">Fluxo sem mudar o layout</h2>
                                <p class="text-sm leading-relaxed text-white/85">Essas telas de acesso foram criadas no mesmo idioma visual das telas publicas, para manter consistencia enquanto o backend vai ficando 100% funcional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lunar-glass rounded-[2rem] border border-white/70 p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.08)] lg:p-10">
                <div class="mb-8 space-y-3">
                    <p class="font-label text-xs font-bold uppercase tracking-[0.2em] text-primary">Login</p>
                    <h2 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Entrar</h2>
                    <p class="text-on-surface-variant">Use suas credenciais para acessar a area correspondente ao seu perfil.</p>
                </div>

                <form action="/login" class="space-y-5" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

                    <label class="block space-y-2">
                        <span class="px-1 text-sm font-semibold text-on-surface-variant">E-mail</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="email" placeholder="voce@sexylua.local" required type="email">
                    </label>

                    <label class="block space-y-2">
                        <span class="px-1 text-sm font-semibold text-on-surface-variant">Senha</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="password" placeholder="******" required type="password">
                    </label>

                    <button class="signature-glow flex w-full items-center justify-center gap-2 rounded-full px-8 py-4 font-headline text-lg font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)] transition-transform duration-200 hover:scale-[1.02]" type="submit">
                        <span class="material-symbols-outlined">login</span>
                        Entrar Agora
                    </button>
                </form>

                <div class="mt-8 rounded-2xl bg-white/70 p-5">
                    <p class="text-sm text-on-surface-variant">Ainda nao tem conta? <a class="font-bold text-primary hover:underline" href="/register">Criar conta</a></p>
                    <p class="mt-2 text-xs uppercase tracking-[0.2em] text-outline">Depois do login o sistema redireciona automaticamente para Publico, Criador, Assinante ou Admin.</p>
                </div>
            </section>
        </div>
    </main>

    <footer class="flex w-full flex-col items-center gap-6 bg-[#D81B60] px-10 py-12">
        <div class="flex flex-col items-center gap-4">
            <span class="text-lg font-bold text-white">SexyLua</span>
            <div class="flex gap-8">
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/">Inicio</a>
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/explore">Explorar</a>
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/register">Registro</a>
            </div>
        </div>
        <p class="font-['Manrope'] text-center text-xs uppercase tracking-widest text-white/50">© 2026 SexyLua. Acesso e experiencia no mesmo universo visual.</p>
    </footer>
</body>
</html>
