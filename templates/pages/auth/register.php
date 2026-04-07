<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua | Registro</title>
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
                radial-gradient(circle at 15% 15%, rgba(216, 27, 96, 0.05) 0%, transparent 35%),
                radial-gradient(circle at 85% 12%, rgba(108, 62, 175, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 75% 80%, rgba(204, 50, 110, 0.05) 0%, transparent 35%);
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
    <?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>

    <main class="mx-auto flex min-h-screen max-w-7xl items-start px-4 pb-16 pt-28 md:px-8">
        <div class="grid w-full gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="order-2 relative overflow-hidden rounded-[2rem] bg-white/50 p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:order-2 lg:p-10">
                <div class="absolute -left-16 top-10 h-48 w-48 rounded-full bg-secondary-container/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-44 w-44 rounded-full bg-primary/10 blur-3xl"></div>
                <div class="relative space-y-8">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5">
                        <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                        <span class="font-label text-xs font-bold uppercase tracking-widest text-primary">Nova Conta</span>
                    </div>
                    <div class="space-y-4">
                        <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface md:text-5xl">Crie seu perfil no ecossistema SexyLua</h1>
                        <p class="max-w-2xl text-lg leading-relaxed text-on-surface-variant">Cadastre-se como assinante para consumir conteudo e interagir com criadores, ou como criador para publicar planos, lives e colecoes usando a base visual ja aprovada.</p>
                    </div>
                    <div class="grid gap-4">
                        <div class="lunar-glass rounded-2xl border border-white/70 p-5 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Assinante</p>
                            <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Ideal para quem quer assinar planos, favoritar criadores, conversar, dar gorjetas e acompanhar as lives.</p>
                        </div>
                        <div class="lunar-glass rounded-2xl border border-white/70 p-5 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Criador</p>
                            <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Ideal para quem vai gerenciar conteudo, assinaturas, transmissao ao vivo, carteira e toda a operacao criativa.</p>
                        </div>
                    </div>
                    <div class="rounded-[2rem] bg-white/70 p-6 shadow-sm">
                        <h2 class="font-headline text-xl font-bold text-on-surface">Conta pronta para validar</h2>
                        <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Depois do envio, o perfil ja nasce funcional e a documentacao segue para revisao administrativa.</p>
                    </div>
                </div>
            </section>

            <section class="order-1 lunar-glass rounded-[2rem] border border-white/70 p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.08)] lg:order-1 lg:p-10">
                <div class="mb-8 space-y-3">
                    <p class="font-label text-xs font-bold uppercase tracking-[0.2em] text-primary">Registro</p>
                    <h2 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Criar conta</h2>
                    <p class="text-on-surface-variant">Preencha os dados abaixo para entrar no sistema ja com o seu perfil configurado.</p>
                </div>

                <form action="/register" class="space-y-5" enctype="multipart/form-data" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="px-1 text-sm font-semibold text-on-surface-variant">Nome</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="name" placeholder="Seu nome" required type="text">
                        </label>

                        <label class="block space-y-2">
                            <span class="px-1 text-sm font-semibold text-on-surface-variant">Usuario</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="username" placeholder="seuusuario" required type="text">
                        </label>
                    </div>

                    <label class="block space-y-2">
                        <span class="px-1 text-sm font-semibold text-on-surface-variant">E-mail</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="email" placeholder="voce@sexylua.local" required type="email">
                    </label>

                    <div class="grid gap-5 md:grid-cols-3">
                        <label class="block space-y-2">
                            <span class="px-1 text-sm font-semibold text-on-surface-variant">Cidade</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="city" placeholder="Brasil" type="text" value="Brasil">
                        </label>

                        <label class="block space-y-2">
                            <span class="px-1 text-sm font-semibold text-on-surface-variant">Idade</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" max="120" min="18" name="age" placeholder="18" required type="number">
                        </label>

                        <label class="block space-y-2">
                            <span class="px-1 text-sm font-semibold text-on-surface-variant">Tipo de conta</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="role">
                                <option value="subscriber">Assinante</option>
                                <option value="creator">Criador</option>
                            </select>
                        </label>
                    </div>

                    <label class="block space-y-2">
                        <span class="px-1 text-sm font-semibold text-on-surface-variant">Senha</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="password" placeholder="Minimo de 6 caracteres" required type="password">
                    </label>

                    <label class="block space-y-2">
                        <span class="px-1 text-sm font-semibold text-on-surface-variant">Documento de identidade</span>
                        <input accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface shadow-sm focus:ring-2 focus:ring-primary/20" name="identity_document_file" required type="file">
                        <span class="block px-1 text-xs text-on-surface-variant">Envie frente, verso ou PDF do documento para validação administrativa.</span>
                    </label>

                    <label class="flex items-start gap-3 rounded-2xl bg-surface-container-low px-5 py-4 text-sm text-on-surface-variant">
                        <input class="mt-1 rounded border-none text-primary focus:ring-primary/20" name="terms_accepted" required type="checkbox" value="1">
                        <span>Li e aceito os <a class="font-bold text-primary underline" href="/terms" target="_blank">termos de uso</a> e a <a class="font-bold text-primary underline" href="/privacy" target="_blank">política de privacidade</a>.</span>
                    </label>

                    <button class="signature-glow flex w-full items-center justify-center gap-2 rounded-full px-8 py-4 font-headline text-lg font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)] transition-transform duration-200 hover:scale-[1.02]" type="submit">
                        <span class="material-symbols-outlined">person_add</span>
                        Criar Conta
                    </button>
                </form>

                <?php if (!empty($google_auth_enabled)): ?>
                    <div class="my-6 flex items-center gap-3">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <span class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">ou continue com</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    <a class="flex w-full items-center justify-center gap-3 rounded-full border border-slate-200 bg-white px-6 py-4 font-headline text-base font-bold text-on-surface shadow-sm transition hover:border-primary/30 hover:text-primary" data-google-register href="<?= e((string) ($google_auth_url ?? '/auth/google?intent=register&role=subscriber')) ?>">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24">
                            <path d="M21.8 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.5c-.2 1.2-.9 2.3-1.9 3v2.5h3.1c1.8-1.7 3.1-4.2 3.1-7.3z" fill="#4285F4"/>
                            <path d="M12 22c2.7 0 4.9-.9 6.6-2.5l-3.1-2.5c-.9.6-2 .9-3.4.9-2.6 0-4.9-1.8-5.7-4.2H3.1v2.6C4.8 19.7 8.1 22 12 22z" fill="#34A853"/>
                            <path d="M6.3 13.7c-.2-.6-.3-1.1-.3-1.7s.1-1.2.3-1.7V7.7H3.1C2.4 9 2 10.5 2 12s.4 3 1.1 4.3l3.2-2.6z" fill="#FBBC05"/>
                            <path d="M12 6.1c1.5 0 2.8.5 3.8 1.5l2.8-2.8C16.9 3.2 14.7 2 12 2 8.1 2 4.8 4.3 3.1 7.7l3.2 2.6c.8-2.4 3.1-4.2 5.7-4.2z" fill="#EA4335"/>
                        </svg>
                        Criar conta com Google
                    </a>
                <?php endif; ?>

                <div class="mt-8 rounded-2xl bg-white/70 p-5">
                    <p class="text-sm text-on-surface-variant">Ja possui conta? <a class="font-bold text-primary hover:underline" href="/login">Entrar agora</a></p>
                </div>
            </section>
        </div>
    </main>

    <footer class="mt-8 flex w-full flex-col items-center gap-6 bg-[#D81B60] px-10 py-12">
        <div class="flex flex-col items-center gap-4">
            <?= brand_logo_white('h-8 w-auto') ?>
            <div class="flex gap-8">
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/">Inicio</a>
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/explore">Explorar</a>
                <a class="font-['Manrope'] text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/login">Login</a>
            </div>
        </div>
        <p class="font-['Manrope'] text-center text-xs uppercase tracking-widest text-white/50">© 2026 SexyLua. Cadastro integrado ao mesmo universo visual do prototipo.</p>
    </footer>
    <?php if (!empty($google_auth_enabled)): ?>
        <script>
            (() => {
                const googleButton = document.querySelector('[data-google-register]');
                const roleSelect = document.querySelector('select[name=\"role\"]');
                if (!googleButton || !roleSelect) {
                    return;
                }

                const updateHref = () => {
                    const role = roleSelect.value === 'creator' ? 'creator' : 'subscriber';
                    googleButton.href = '/auth/google?intent=register&role=' + encodeURIComponent(role);
                };

                updateHref();
                roleSelect.addEventListener('change', updateHref);
            })();
        </script>
    <?php endif; ?>
</body>
</html>
