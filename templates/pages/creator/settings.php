<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$wallet = $data['wallet'] ?? [];
$platform = $data['platform'] ?? [];
$verification = $data['verification'] ?? [];
$verificationStatus = (string) ($verification['status'] ?? 'pending');
$identityDocument = is_array($verification['identity_document'] ?? null) ? $verification['identity_document'] : null;
$activeSubscribers = (int) ($data['active_subscribers'] ?? 0);
$liveDefaults = $data['live_defaults'] ?? [];
$priorityTipTiers = $liveDefaults['priority_tip_tiers'] ?? [1, 10, 25, 50, 100, 150];
$priorityTipMessages = $liveDefaults['priority_tip_messages'] ?? [];
$avatarUrl = media_url((string) ($creator['avatar_url'] ?? ''));
$coverUrl = media_url((string) ($creator['cover_url'] ?? ''));
$publicProfileUrl = '/profile?slug=' . (string) ($creator['slug'] ?? 'criador');
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Configurações do Criador</title>
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
                        "surface-container": "#efedef",
                        "surface-container-low": "#f5f3f5",
                        "surface-container-lowest": "#ffffff",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044"
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"]
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
        }
        body {
            font-family: "Manrope", sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        @media (max-width: 768px) {
            .settings-mobile-wrap {
                background: transparent !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .settings-mobile-card {
                border-radius: 1.75rem;
                background: #ffffff;
                padding: 1.25rem;
                box-shadow: 0 12px 32px rgba(27, 28, 29, 0.08);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-background text-on-surface">
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'settings';
$creatorTopbarLabel = 'Configurações do Criador';
$creatorTopbarAction = ['href' => '/creator/wallet', 'label' => 'Carteira'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="min-h-screen pt-20 lg:pl-64">
    <div class="mx-auto max-w-7xl px-8 py-12">
        <header class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="mb-2 text-4xl font-extrabold tracking-tight">Configurações do Criador</h2>
                <p class="text-on-surface-variant">Edite sua apresentação, identidade do perfil, recebimentos e padrões da sua sala ao vivo.</p>
            </div>
            <div class="rounded-full bg-surface-container-lowest px-6 py-3 shadow-sm">
                <span class="text-sm font-bold text-primary"><?= e((string) $activeSubscribers) ?> assinantes ativos</span>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Perfil público</p>
                    <h3 class="mt-3 text-3xl font-extrabold">Dados principais</h3>
                </div>

                <form action="/creator/settings/update" class="space-y-8" enctype="multipart/form-data" id="creator-settings-form" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Nome artístico</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" type="text" value="<?= e((string) ($creator['name'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Slug público</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="slug" type="text" value="<?= e((string) ($creator['slug'] ?? '')) ?>">
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Cidade</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text" value="<?= e((string) ($creator['city'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Headline</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($creator['headline'] ?? '')) ?>">
                        </label>
                    </div>

                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Bio</span>
                        <textarea class="min-h-[160px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"><?= e((string) ($creator['bio'] ?? '')) ?></textarea>
                    </label>

                    <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Mídia do perfil</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Avatar e capa</h4>
                            <p class="mt-2 text-sm text-on-surface-variant">Ao selecionar um arquivo, o preview abaixo já é atualizado para indicar que o envio foi preparado.</p>
                        </div>
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="settings-mobile-card space-y-4 rounded-3xl bg-white p-5 shadow-sm">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-lg font-bold text-primary" data-upload-preview-box="avatar" data-upload-preview-fallback="<?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?>">
                                        <?php if ($avatarUrl !== ''): ?>
                                            <img alt="Avatar do criador" class="h-full w-full object-cover" data-upload-preview-image="avatar" src="<?= e($avatarUrl) ?>">
                                        <?php else: ?>
                                            <span data-upload-preview-placeholder="avatar"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                                        <p class="text-xs text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                                    </div>
                                </div>
                                <label class="block space-y-2">
                                    <span class="text-sm font-semibold text-on-surface-variant">Novo avatar</span>
                                    <input accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" data-upload-preview-input="avatar" name="avatar_file" type="file">
                                </label>
                                <p class="text-xs font-semibold text-primary/80" data-upload-preview-status="avatar">Nenhum novo avatar selecionado.</p>
                            </div>

                            <div class="settings-mobile-card space-y-4 rounded-3xl bg-white p-5 shadow-sm">
                                <div class="flex h-40 w-full items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br from-pink-700 via-rose-600 to-orange-400 text-lg font-bold text-white" data-upload-preview-box="cover" data-upload-preview-fallback="SexyLua">
                                    <?php if ($coverUrl !== ''): ?>
                                        <img alt="Capa do criador" class="h-full w-full object-cover" data-upload-preview-image="cover" src="<?= e($coverUrl) ?>">
                                    <?php else: ?>
                                        <span data-upload-preview-placeholder="cover">SexyLua</span>
                                    <?php endif; ?>
                                </div>
                                <label class="block space-y-2">
                                    <span class="text-sm font-semibold text-on-surface-variant">Nova capa</span>
                                    <input accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" data-upload-preview-input="cover" name="cover_file" type="file">
                                </label>
                                <p class="text-xs font-semibold text-primary/80" data-upload-preview-status="cover">Nenhuma nova capa selecionada.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Instagram</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="instagram" type="text" value="<?= e((string) ($creator['instagram'] ?? '')) ?>">
                        </label>
                        <div class="rounded-2xl bg-surface-container-low px-5 py-4 text-sm text-on-surface-variant">
                            <p class="font-semibold text-on-surface">Página pública</p>
                            <p class="mt-2 break-all"><?= e($publicProfileUrl) ?></p>
                        </div>
                    </div>

                    <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Financeiro</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Recebimentos</h4>
                        </div>
                        <label class="settings-mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm">
                            <span class="text-sm font-semibold text-on-surface-variant">Chave PIX</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_key" type="text" value="<?= e((string) ($creator['payout_key'] ?? '')) ?>">
                        </label>
                    </div>

                    <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Verificacao</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Documento de identidade</h4>
                            <p class="mt-2 text-sm text-on-surface-variant">Enquanto a documentacao nao for aprovada, saques ficam bloqueados.</p>
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
                                        <a class="inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-3 text-sm font-bold text-on-surface-variant" href="<?= e('/messages/asset?scope=identity&id=' . (int) ($creator['id'] ?? 0)) ?>" target="_blank">
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

                    <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Segurança</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Acesso da conta</h4>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="hidden block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Nova senha</span>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password" type="password">
                            </label>
                            <label class="settings-mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm md:col-span-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Confirmar nova senha</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password_confirmation" type="password">
                            </label>
                        </div>
                    </div>

                    <div class="settings-mobile-wrap rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Lives e chat</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Padrões do estúdio</h4>
                            <p class="mt-2 text-sm text-on-surface-variant">Defina quem pode falar no chat e personalize os alertas que aparecem sobre o player durante a transmissão.</p>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Quem pode falar no chat</span>
                                <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="live_chat_audience_default">
                                    <option value="all" <?= (string) ($liveDefaults['chat_audience'] ?? 'all') === 'all' ? 'selected' : '' ?>>Assinantes e não assinantes</option>
                                    <option value="subscriber" <?= (string) ($liveDefaults['chat_audience'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Só assinantes</option>
                                    <option value="off" <?= (string) ($liveDefaults['chat_audience'] ?? '') === 'off' ? 'selected' : '' ?>>Chat fechado</option>
                                </select>
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Visibilidade padrão do replay</span>
                                <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="replay_visibility_default">
                                    <option value="subscriber" <?= (string) ($liveDefaults['replay_visibility'] ?? 'subscriber') === 'subscriber' ? 'selected' : '' ?>>Só assinantes</option>
                                    <option value="public" <?= (string) ($liveDefaults['replay_visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Público</option>
                                </select>
                            </label>
                        </div>

                        <div class="mt-6 space-y-4">
                            <?php
                            $alertRows = [
                                ['label' => 'Valor 1', 'price_field' => 'priority_tip_tier_1', 'message_field' => 'priority_tip_message_1', 'price' => (int) ($priorityTipTiers[0] ?? 1), 'message' => (string) ($priorityTipMessages[(string) ($priorityTipTiers[0] ?? 1)] ?? '')],
                                ['label' => 'Valor 2', 'price_field' => 'priority_tip_tier_2', 'message_field' => 'priority_tip_message_2', 'price' => (int) ($priorityTipTiers[1] ?? 10), 'message' => (string) ($priorityTipMessages[(string) ($priorityTipTiers[1] ?? 10)] ?? '')],
                                ['label' => 'Valor 3', 'price_field' => 'priority_tip_tier_3', 'message_field' => 'priority_tip_message_3', 'price' => (int) ($priorityTipTiers[2] ?? 25), 'message' => (string) ($priorityTipMessages[(string) ($priorityTipTiers[2] ?? 25)] ?? '')],
                                ['label' => 'Valor 4', 'price_field' => 'priority_tip_tier_4', 'message_field' => 'priority_tip_message_4', 'price' => (int) ($priorityTipTiers[3] ?? 50), 'message' => (string) ($priorityTipMessages[(string) ($priorityTipTiers[3] ?? 50)] ?? '')],
                                ['label' => 'Valor 5', 'price_field' => 'priority_tip_tier_5', 'message_field' => 'priority_tip_message_5', 'price' => (int) ($priorityTipTiers[4] ?? 100), 'message' => (string) ($priorityTipMessages[(string) ($priorityTipTiers[4] ?? 100)] ?? '')],
                                ['label' => 'Valor personalizado', 'price_field' => 'priority_tip_custom', 'message_field' => 'priority_tip_message_custom', 'price' => (int) ($liveDefaults['priority_tip_custom'] ?? ($priorityTipTiers[5] ?? 150)), 'message' => (string) ($priorityTipMessages[(string) ($liveDefaults['priority_tip_custom'] ?? ($priorityTipTiers[5] ?? 150))] ?? '')],
                            ];
                            ?>
                            <?php foreach ($alertRows as $row): ?>
                                <div class="grid gap-4 rounded-3xl bg-white p-4 shadow-sm md:grid-cols-[180px_minmax(0,1fr)]">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-semibold text-on-surface-variant"><?= e($row['label']) ?></span>
                                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="<?= e($row['price_field']) ?>" step="1" type="number" value="<?= e((string) $row['price']) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-sm font-semibold text-on-surface-variant">Mensagem do alerta</span>
                                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="<?= e($row['message_field']) ?>" placeholder="Ex: {nome}, sua LuaCoin chegou brilhando!" type="text" value="<?= e($row['message']) ?>">
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-5 rounded-3xl bg-white px-5 py-4 text-sm text-on-surface-variant shadow-sm">
                            Use <strong>{nome}</strong>, <strong>{valor}</strong> ou <strong>{tier}</strong> dentro do texto para personalizar o alerta. O replay automático expira em <strong><?= e((string) ($liveDefaults['replay_expiration_days'] ?? 7)) ?> dias</strong> e cada live pode ficar no ar por até <strong><?= e((string) ($liveDefaults['max_duration_minutes'] ?? 30)) ?> minutos</strong>, conforme o painel do admin.
                        </div>
                    </div>
                </form>
            </section>

            <section class="space-y-8 xl:sticky xl:top-28 xl:self-start">
                <div class="rounded-2xl bg-primary p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Resumo rápido</p>
                    <h3 class="mt-3 text-3xl font-extrabold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/80"><?= e(excerpt((string) ($creator['headline'] ?? ''), 100)) ?></p>
                    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-white/60">Saldo</p>
                            <div class="mt-1 text-xl font-bold"><?= luacoin_amount_html((int) ($wallet['balance'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-white/60">LuaCoin em BRL</p>
                            <p class="mt-1 text-xl font-bold"><?= e(brl_amount((float) ($platform['luacoin_price_brl'] ?? 0.07))) ?></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Ações</p>
                    <h3 class="mt-3 text-2xl font-extrabold">Salvar alterações</h3>
                    <p class="mt-3 text-sm text-on-surface-variant">Esse painel acompanha o scroll para você salvar avatar, capa e configurações sem precisar voltar ao topo ou ao fim da página.</p>
                    <div class="mt-6 space-y-4">
                        <button class="flex w-full items-center justify-center gap-2 rounded-full bg-primary px-8 py-4 text-lg font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)] transition-transform duration-200 hover:scale-[1.02]" data-prototype-skip="1" form="creator-settings-form" type="submit">
                            <span class="material-symbols-outlined">save</span>
                            Salvar Configurações
                        </button>
                        <a class="flex w-full items-center justify-center gap-2 rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="<?= e($publicProfileUrl) ?>" target="_blank">
                            <span class="material-symbols-outlined text-base">open_in_new</span>
                            Ver perfil público
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
    (() => {
        const bindPreview = (key) => {
            const input = document.querySelector(`[data-upload-preview-input="${key}"]`);
            const box = document.querySelector(`[data-upload-preview-box="${key}"]`);
            const status = document.querySelector(`[data-upload-preview-status="${key}"]`);
            if (!input || !box || !status) {
                return;
            }

            let objectUrl = null;
            const fallbackText = box.dataset.uploadPreviewFallback || '';
            const initialMarkup = box.innerHTML;

            input.addEventListener('change', () => {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (!file) {
                    box.innerHTML = initialMarkup || `<span>${fallbackText}</span>`;
                    status.textContent = key === 'avatar' ? 'Nenhum novo avatar selecionado.' : 'Nenhuma nova capa selecionada.';
                    return;
                }

                objectUrl = URL.createObjectURL(file);
                box.innerHTML = '';
                const image = document.createElement('img');
                image.src = objectUrl;
                image.alt = key === 'avatar' ? 'Preview do avatar' : 'Preview da capa';
                image.className = 'h-full w-full object-cover';
                image.setAttribute('data-upload-preview-image', key);
                box.appendChild(image);
                status.textContent = `Arquivo selecionado: ${file.name}`;
            });
        };

        bindPreview('avatar');
        bindPreview('cover');

        const replayVisibilityField = document.querySelector('[name="replay_visibility_default"]');
        const replayVisibilityLabel = replayVisibilityField ? replayVisibilityField.closest('label') : null;
        if (replayVisibilityLabel) replayVisibilityLabel.remove();

        document.querySelectorAll('div.rounded-3xl.bg-white, div.rounded-3xl.bg-surface-container-lowest, div.rounded-3xl.bg-surface-container-low').forEach((node) => {
            if (typeof node.textContent === 'string' && node.textContent.toLowerCase().includes('replay')) {
                const paragraph = node.querySelector('p.mt-2.text-sm.text-on-surface-variant, p.mt-2.text-sm.text-slate-500');
                if (paragraph && paragraph.textContent.toLowerCase().includes('replay')) {
                    paragraph.textContent = 'Use {nome}, {valor} ou {tier} dentro do texto para personalizar o alerta. Cada live pode ficar no ar pelo limite definido no painel do admin.';
                }
            }
        });
    })();
</script>
</body>
</html>
