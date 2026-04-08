<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
$wallet = $data['wallet'] ?? [];
$stats = $data['stats'] ?? [];
$recentTransactions = $data['recent_transactions'] ?? [];
$verification = $data['verification'] ?? [];
$verificationStatus = (string) ($verification['status'] ?? 'pending');
$identityDocument = is_array($verification['identity_document'] ?? null) ? $verification['identity_document'] : null;
$avatarUrl = media_url((string) ($subscriber['avatar_url'] ?? ''));
$coverUrl = media_url((string) ($subscriber['cover_url'] ?? ''));
$subscriberHandle = user_handle($subscriber, 'assinante');
$subscriberAvatarLabel = user_avatar_label($subscriber, 'AS');
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Configuracoes do Assinante</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ab1155",
                        background: "#fbf9fb",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f5f3f5",
                        "surface-container-high": "#e9e7e9",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"],
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        lg: "2rem",
                        xl: "3rem",
                        full: "9999px",
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
        @media (max-width: 768px) {
            .settings-mobile-wrap {
                background: transparent !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .settings-mobile-card {
                border-radius: 1.75rem;
                background: #ffffff !important;
                padding: 1.15rem !important;
                box-shadow: 0 12px 32px rgba(27, 28, 29, 0.08);
            }
        }
    </style>
</head>
<body class="min-h-screen">
<?php
$subscriberTopbarUser = $subscriber;
$subscriberTopbarAction = ['href' => '/subscriber', 'label' => 'Painel'];
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
$subscriberSidebarCurrent = 'settings';
ob_start();
?>
<div class="rounded-3xl bg-white p-5 shadow-sm">
    <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Saldo atual</p>
    <div class="mt-3 text-3xl font-extrabold"><?= luacoin_amount_html((int) ($wallet['balance'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-8 w-8 shrink-0') ?></div>
    <p class="mt-2 text-sm text-on-surface-variant">Atualize seu perfil e mantenha a conta pronta para novas assinaturas, mensagens e recargas.</p>
</div>
<?php
$subscriberSidebarFooter = (string) ob_get_clean();
require BASE_PATH . '/templates/partials/subscriber_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Perfil do assinante</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Configuracoes da <span class="italic text-primary">Conta</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Ajuste imagem, bio, cidade e senha da sua conta sem sair do fluxo visual do assinante.</p>
        </div>
        <div class="signature-glow rounded-3xl px-6 py-5 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Conta ativa</p>
            <p class="mt-2 text-2xl font-extrabold" data-username-preview="subscriber"><?= e($subscriberHandle) ?></p>
            <p class="mt-1 text-sm text-white/80"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($stats['subscriptions'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Favoritos</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($stats['favorites'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Salvos</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($stats['saved'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saldo</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($stats['balance'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
    </section>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.05fr_0.95fr]">
        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Dados principais</p>
                <h3 class="mt-3 text-3xl font-extrabold">Meu perfil</h3>
            </div>

            <form action="/subscriber/settings/update" class="space-y-6" method="post" enctype="multipart/form-data">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-slate-500 shadow-sm focus:ring-0" name="name" readonly type="text" value="<?= e((string) ($subscriber['name'] ?? '')) ?>">
                        <span class="block text-xs text-on-surface-variant">Somente o admin pode alterar esse nome.</span>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" data-username-input data-username-target="subscriber" name="username" spellcheck="false" type="text" value="<?= e((string) ($subscriber['username'] ?? '')) ?>">
                        <span class="block text-xs text-on-surface-variant" data-username-feedback="subscriber">Use apenas letras, numeros, ponto e underscore.</span>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cidade</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text" value="<?= e((string) ($subscriber['city'] ?? '')) ?>">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface-variant shadow-sm" readonly type="email" value="<?= e((string) ($subscriber['email'] ?? '')) ?>">
                    </label>
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Headline</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($subscriber['headline'] ?? '')) ?>">
                    </label>
                </div>

                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bio</span>
                    <textarea class="min-h-36 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"><?= e((string) ($subscriber['bio'] ?? '')) ?></textarea>
                </label>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL do avatar</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_url" type="text" value="<?= e((string) ($subscriber['avatar_url'] ?? '')) ?>">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do avatar</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL da capa</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" type="text" value="<?= e((string) ($subscriber['cover_url'] ?? '')) ?>">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload da capa</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </label>
                </div>

                <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                    <div class="mb-5">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Verificacao</p>
                        <h4 class="mt-2 text-2xl font-extrabold">Documento de identidade</h4>
                        <p class="mt-2 text-sm text-on-surface-variant">Enquanto a documentacao nao for aprovada, o saque continua bloqueado.</p>
                    </div>
                    <div class="grid gap-5">
                        <div class="settings-mobile-card rounded-3xl bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface-variant">Status atual</p>
                                    <p class="mt-2 text-lg font-extrabold <?= $verificationStatus === 'approved' ? 'text-emerald-600' : ($verificationStatus === 'rejected' ? 'text-rose-600' : 'text-amber-600') ?>">
                                        <?= e($verificationStatus === 'approved' ? 'Aprovado' : ($verificationStatus === 'rejected' ? 'Reenviar documento' : 'Pendente')) ?>
                                    </p>
                                </div>
                                <?php if ($identityDocument): ?>
                                    <a class="inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-3 text-sm font-bold text-on-surface-variant" href="<?= e('/messages/asset?scope=identity&id=' . (int) ($subscriber['id'] ?? 0)) ?>" target="_blank">
                                        <span class="material-symbols-outlined text-base">id_card</span>
                                        Ver enviado
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <label class="settings-mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm">
                            <span class="text-sm font-semibold text-on-surface-variant">Enviar novo documento</span>
                            <input accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="identity_document_file" type="file">
                            <span class="block text-xs text-on-surface-variant">Frente, verso ou PDF. O admin revisa em ate 48h.</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nova senha</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password" type="password">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Confirmar senha</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password_confirmation" type="password">
                    </label>
                </div>

                <button class="rounded-full bg-slate-900 px-8 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar meu perfil</button>
            </form>
        </section>

        <section class="space-y-6">
            <div class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
                <?php if ($coverUrl !== ''): ?>
                    <img alt="Capa do assinante" class="h-48 w-full object-cover" src="<?= e($coverUrl) ?>">
                <?php else: ?>
                    <div class="flex h-48 w-full items-center justify-center bg-gradient-to-br from-[#ab1155] via-[#D81B60] to-[#f57c91] text-lg font-bold text-white">Subscriber Club</div>
                <?php endif; ?>
                <div class="p-6">
                    <div class="-mt-16 flex items-end gap-4">
                        <?php if ($avatarUrl !== ''): ?>
                            <img alt="Avatar do assinante" class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-lg" src="<?= e($avatarUrl) ?>">
                        <?php else: ?>
                            <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-primary text-2xl font-bold text-white shadow-lg"><?= e($subscriberAvatarLabel) ?></div>
                        <?php endif; ?>
                        <div class="pb-3">
                            <p class="text-xl font-bold" data-username-preview="subscriber"><?= e($subscriberHandle) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant">Nome visivel controlado pelo admin</p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($subscriber['headline'] ?? '')) ?></p>
                        </div>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed text-on-surface-variant"><?= e(excerpt((string) ($subscriber['bio'] ?? 'Seu perfil de assinante pronto para novas experiencias.'), 200)) ?></p>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Ultimas movimentacoes</h3>
                <div class="mt-6 space-y-4">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <?php $isIn = (string) ($transaction['direction'] ?? 'in') === 'in'; ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold"><?= e((string) ($transaction['note'] ?? 'Movimentacao')) ?></p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
                                <strong class="<?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?>"><?= $isIn ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($recentTransactions === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Sem movimentacoes recentes no momento.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
    (() => {
        const bindUsernameValidation = (key) => {
            const input = document.querySelector(`[data-username-target="${key}"]`);
            const feedback = document.querySelector(`[data-username-feedback="${key}"]`);
            const previews = document.querySelectorAll(`[data-username-preview="${key}"]`);
            if (!input || !feedback) {
                return;
            }

            const original = String(input.value || '').trim();
            let timer = null;

            const setFeedback = (message, tone = 'neutral') => {
                feedback.textContent = message;
                feedback.classList.remove('text-on-surface-variant', 'text-emerald-600', 'text-rose-600', 'text-amber-600');
                feedback.classList.add(
                    tone === 'success'
                        ? 'text-emerald-600'
                        : (tone === 'error' ? 'text-rose-600' : (tone === 'warning' ? 'text-amber-600' : 'text-on-surface-variant'))
                );
            };

            const syncPreview = (value) => {
                const handle = value ? `@${value}` : '@usuario';
                previews.forEach((node) => {
                    node.textContent = handle;
                });
            };

            const validate = async () => {
                const value = String(input.value || '').trim();
                syncPreview(value);

                if (value === '') {
                    setFeedback('Informe um @usuario para o seu perfil.', 'warning');
                    return;
                }

                try {
                    const response = await fetch(`/auth/check-username?username=${encodeURIComponent(value)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json();
                    const normalized = String(payload.normalized || '').trim();
                    if (normalized && normalized !== value) {
                        input.value = normalized;
                        syncPreview(normalized);
                    }

                    if (normalized === original) {
                        setFeedback('Este @usuario ja esta reservado para a sua conta.', 'success');
                        return;
                    }

                    if (payload.state === 'available') {
                        setFeedback('Este @usuario esta disponivel.', 'success');
                        return;
                    }

                    if (payload.state === 'taken') {
                        setFeedback('Este @usuario ja esta em uso.', 'error');
                        return;
                    }

                    setFeedback(String(payload.message || 'Digite um @usuario valido.'), payload.state === 'invalid' ? 'error' : 'warning');
                } catch {
                    setFeedback('Nao foi possivel validar o @usuario agora.', 'warning');
                }
            };

            input.addEventListener('input', () => {
                if (timer) window.clearTimeout(timer);
                timer = window.setTimeout(validate, 260);
            });

            syncPreview(original);
            validate();
        };

        bindUsernameValidation('subscriber');
    })();
</script>
</body>
</html>
