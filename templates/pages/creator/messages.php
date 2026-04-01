<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$announcements = $data['announcements'] ?? [];
$selectedAnnouncement = $data['selected_announcement'] ?? null;
$conversations = $data['filtered_conversations'] ?? $data['conversations'] ?? [];
$selectedConversation = $data['selected_conversation'] ?? null;
$messages = $data['messages'] ?? [];
$availablePlans = $data['available_plans'] ?? [];
$filters = $data['filters'] ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Mensagens do Criador</title>
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
    </style>
</head>
<body class="min-h-screen">
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'messages';
$creatorTopbarAction = ['href' => '/creator/memberships', 'label' => 'Assinaturas'];
require BASE_PATH . '/templates/partials/creator_sidebar.php';
require BASE_PATH . '/templates/partials/creator_topbar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Bate-papo com assinantes</p>
        <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Mensagens <span class="italic text-primary">Privadas</span></h2>
        <p class="mt-4 max-w-2xl text-on-surface-variant">Converse com sua base pagante e mantenha cada conversa organizada em um unico lugar.</p>
    </section>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="space-y-6">
            <form action="/creator/messages" class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm" method="get">
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar conversa..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
            </form>
            <?php if ($announcements !== []): ?>
                <section class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">campaign</span>
                        <h3 class="text-lg font-extrabold">Comunicados da plataforma</h3>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($announcements as $announcement): ?>
                            <?php $announcementActive = $selectedAnnouncement && (int) ($selectedAnnouncement['id'] ?? 0) === (int) ($announcement['id'] ?? 0); ?>
                            <a class="block rounded-3xl p-4 transition-colors <?= $announcementActive ? 'bg-primary text-white' : 'bg-surface-container-low hover:bg-surface-container-lowest' ?>" href="<?= e('/creator/messages?announcement=' . (int) ($announcement['id'] ?? 0)) ?>">
                                <p class="font-bold"><?= e((string) ($announcement['title'] ?? 'Comunicado')) ?></p>
                                <p class="mt-2 text-sm <?= $announcementActive ? 'text-white/80' : 'text-on-surface-variant' ?>"><?= e(excerpt((string) ($announcement['body'] ?? ''), 80)) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            <div class="space-y-4">
                <?php foreach ($conversations as $conversation): ?>
                    <?php $active = $selectedConversation && (int) ($selectedConversation['id'] ?? 0) === (int) ($conversation['id'] ?? 0); ?>
                    <a class="block rounded-3xl p-5 shadow-sm transition-colors <?= $active ? 'bg-primary text-white' : 'bg-surface-container-lowest hover:bg-surface-container-low' ?>" href="<?= e('/creator/messages?conversation=' . (int) ($conversation['id'] ?? 0)) ?>">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full <?= $active ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' ?> font-bold"><?= e(avatar_initials((string) ($conversation['subscriber']['name'] ?? 'Assinante'))) ?></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold"><?= e((string) ($conversation['subscriber']['name'] ?? 'Assinante')) ?></p>
                                <p class="mt-1 truncate text-sm <?= $active ? 'text-white/80' : 'text-on-surface-variant' ?>"><?= e(excerpt((string) ($conversation['latest_message']['body'] ?? 'Sem mensagens ainda.'), 70)) ?></p>
                                <?php if ((string) (($conversation['subscription']['plan']['name'] ?? '')) !== ''): ?>
                                    <span class="mt-3 inline-flex rounded-full <?= $active ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' ?> px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em]"><?= e((string) ($conversation['subscription']['plan']['name'] ?? 'Plano')) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if ($conversations === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma conversa encontrada.</p><?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <?php if ($selectedAnnouncement): ?>
                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <span class="material-symbols-outlined">campaign</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold"><?= e((string) ($selectedAnnouncement['title'] ?? 'Comunicado')) ?></h3>
                            <p class="text-sm text-on-surface-variant">Mensagem geral da plataforma</p>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-surface-container-low p-6">
                        <p class="text-sm leading-relaxed text-on-surface"><?= nl2br(e((string) ($selectedAnnouncement['body'] ?? ''))) ?></p>
                        <?php if (is_array($selectedAnnouncement['attachment'] ?? null)): ?>
                            <a class="mt-5 inline-flex items-center gap-3 rounded-full bg-white px-5 py-3 text-sm font-bold text-primary shadow-sm" href="<?= e((string) (($selectedAnnouncement['attachment']['href'] ?? '#'))) ?>" target="_blank">
                                <span class="material-symbols-outlined"><?= e((string) (($selectedAnnouncement['attachment']['kind'] ?? 'document') === 'image' ? 'image' : (($selectedAnnouncement['attachment']['kind'] ?? 'document') === 'video' ? 'play_circle' : 'description'))) ?></span>
                                <?= e((string) ($selectedAnnouncement['attachment']['original_name'] ?? 'Abrir anexo')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($selectedConversation): ?>
                <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($selectedConversation['subscriber']['name'] ?? 'Assinante'))) ?></div>
                    <div>
                        <h3 class="text-2xl font-extrabold"><?= e((string) ($selectedConversation['subscriber']['name'] ?? 'Assinante')) ?></h3>
                        <p class="text-sm text-on-surface-variant"><?= e((string) ($selectedConversation['subscriber']['headline'] ?? 'Chat ativo')) ?></p>
                        <?php if ((string) (($selectedConversation['subscription']['plan']['name'] ?? '')) !== ''): ?>
                            <span class="mt-3 inline-flex rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-primary"><?= e((string) ($selectedConversation['subscription']['plan']['name'] ?? 'Plano ativo')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-6 max-h-[420px] space-y-4 overflow-y-auto pr-2">
                    <?php foreach ($messages as $message): ?>
                        <?php $isMine = (int) ($message['sender_id'] ?? 0) === (int) ($creator['id'] ?? 0); ?>
                        <article class="flex <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                            <div class="max-w-[80%] rounded-3xl px-5 py-4 shadow-sm <?= $isMine ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface' ?>">
                                <?php if (trim((string) ($message['body'] ?? '')) !== ''): ?>
                                    <p class="text-sm leading-6"><?= nl2br(e((string) ($message['body'] ?? ''))) ?></p>
                                <?php endif; ?>
                                <?php $attachment = is_array($message['attachment'] ?? null) ? $message['attachment'] : null; ?>
                                <?php if ($attachment): ?>
                                    <div class="mt-4">
                                        <?php if ((bool) ($message['can_access_attachment'] ?? false)): ?>
                                            <?php if ((string) ($attachment['kind'] ?? 'document') === 'image'): ?>
                                                <a class="block overflow-hidden rounded-2xl border border-white/10" href="<?= e((string) ($attachment['href'] ?? '#')) ?>" target="_blank">
                                                    <img alt="<?= e((string) ($attachment['original_name'] ?? 'Imagem')) ?>" class="max-h-72 w-full object-cover" src="<?= e((string) ($attachment['href'] ?? '')) ?>">
                                                </a>
                                            <?php else: ?>
                                                <a class="flex items-center gap-3 rounded-2xl bg-white/90 px-4 py-3 text-sm font-bold text-slate-800" href="<?= e((string) ($attachment['href'] ?? '#')) ?>" target="_blank">
                                                    <span class="material-symbols-outlined"><?= e((string) (($attachment['kind'] ?? 'document') === 'video' ? 'play_circle' : 'description')) ?></span>
                                                    <?= e((string) ($attachment['original_name'] ?? 'Abrir anexo')) ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/80 p-4 text-slate-700">
                                                <div class="flex items-start gap-3">
                                                    <span class="material-symbols-outlined text-primary">lock</span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="font-bold"><?= e(((int) ($message['unlock_price'] ?? 0)) > 0 ? 'Conteudo instantaneo' : 'Conteudo privado') ?></p>
                                                        <p class="mt-1 text-sm text-slate-500"><?= e((string) ($message['lock_reason'] ?? 'Conteudo bloqueado')) ?></p>
                                                        <?php if ((string) ($message['required_plan_name'] ?? '') !== ''): ?>
                                                            <span class="mt-3 inline-flex rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary"><?= e((string) ($message['required_plan_name'] ?? 'Plano')) ?></span>
                                                        <?php elseif ((int) ($message['unlock_price'] ?? 0) > 0): ?>
                                                            <span class="mt-3 inline-flex rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary"><?= luacoin_amount_html((int) ($message['unlock_price'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-3.5 w-3.5 shrink-0') ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <p class="mt-2 text-[11px] font-bold uppercase tracking-[0.2em] <?= $isMine ? 'text-white/70' : 'text-slate-400' ?>"><?= e(format_datetime((string) ($message['created_at'] ?? ''), 'd/m H:i')) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($messages === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma mensagem nesta conversa ainda.</p><?php endif; ?>
                </div>
                <form action="/creator/messages/send" class="mt-6 space-y-4" enctype="multipart/form-data" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input name="conversation_id" type="hidden" value="<?= e((string) ($selectedConversation['id'] ?? 0)) ?>">
                    <textarea class="min-h-[110px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body" placeholder="Mensagem, descrição do anexo ou descrição do conteúdo instantâneo..."></textarea>
                    <div class="grid gap-4 lg:grid-cols-3">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Anexo</span>
                            <input accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm,.pdf,.doc,.docx,.txt,.zip,.rar,.7z" class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="attachment_file" type="file">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Liberar para plano</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="required_plan_id">
                                <option value="0">Sem bloqueio por plano</option>
                                <?php foreach ($availablePlans as $plan): ?>
                                    <option value="<?= e((string) ($plan['id'] ?? 0)) ?>"><?= e((string) ($plan['name'] ?? 'Plano')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Conteúdo instantâneo (LuaCoins)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="unlock_price" placeholder="0" type="number" value="0">
                        </label>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">Se houver preço, o anexo entra como conteúdo instantâneo. Se houver plano, o anexo fica restrito a ele.</p>
                        <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white shadow-lg shadow-primary/20" type="submit">Enviar</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="flex h-full min-h-[420px] items-center justify-center rounded-3xl bg-surface-container-low p-8 text-center text-on-surface-variant">
                    Selecione uma conversa para responder aos seus assinantes.
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
