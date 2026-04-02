<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
$announcements = $data['announcements'] ?? [];
$selectedAnnouncement = $data['selected_announcement'] ?? null;
$conversations = $data['filtered_conversations'] ?? $data['conversations'] ?? [];
$selectedConversation = $data['selected_conversation'] ?? null;
$messages = $data['messages'] ?? [];
$filters = $data['filters'] ?? [];
$mobileConversationListUrl = path_with_query('/subscriber/messages', ['q' => $filters['q'] ?? '']);
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Mensagens</title>
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
$subscriberTopbarUser = $subscriber;
$subscriberTopbarAction = ['href' => '/subscriber/favorites', 'label' => 'Favoritos'];
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
$subscriberSidebarCurrent = 'messages';
require BASE_PATH . '/templates/partials/subscriber_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Chat com criadores</p>
        <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Mensagens <span class="italic text-primary">Privadas</span></h2>
        <p class="mt-4 max-w-2xl text-on-surface-variant">Continue conversas com os criadores, envie anexos e acompanhe respostas no ritmo de um chat mais fluido.</p>
    </section>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.92fr_1.08fr]">
        <section class="space-y-6">
            <form action="/subscriber/messages" class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm" method="get">
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
                            <a class="block rounded-3xl p-4 transition-colors <?= $announcementActive ? 'bg-primary text-white' : 'bg-surface-container-low hover:bg-surface-container-lowest' ?>" href="<?= e('/subscriber/messages?announcement=' . (int) ($announcement['id'] ?? 0)) ?>">
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
                    <a class="block rounded-3xl p-5 shadow-sm transition-colors <?= $active ? 'bg-primary text-white' : 'bg-surface-container-lowest hover:bg-surface-container-low' ?>" href="<?= e('/subscriber/messages?conversation=' . (int) ($conversation['id'] ?? 0)) ?>">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full <?= $active ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' ?> font-bold"><?= e(avatar_initials((string) ($conversation['creator']['name'] ?? 'Criador'))) ?></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold"><?= e((string) ($conversation['creator']['name'] ?? 'Criador')) ?></p>
                                <p class="mt-1 truncate text-sm <?= $active ? 'text-white/80' : 'text-on-surface-variant' ?>"><?= e(excerpt((string) ($conversation['latest_message']['body'] ?? 'Sem mensagens ainda.'), 70)) ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if ($conversations === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma conversa encontrada.</p><?php endif; ?>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl bg-surface-container-lowest p-0 shadow-sm lg:p-8" data-mobile-chat-panel data-mobile-chat-state="<?= ($selectedAnnouncement || $selectedConversation) ? 'selected' : 'empty' ?>">
            <?php if ($selectedAnnouncement): ?>
                <div class="space-y-6">
                    <a class="inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-3 text-sm font-bold text-primary lg:hidden" href="<?= e($mobileConversationListUrl) ?>">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Voltar
                    </a>
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
                <div class="flex h-full min-h-0 flex-col lg:h-[78vh]">
                    <a class="mx-4 mt-3 inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-3 text-sm font-bold text-primary lg:hidden" href="<?= e($mobileConversationListUrl) ?>">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Voltar
                    </a>
                    <div class="mx-4 flex items-center gap-4 border-b border-slate-200 pb-4 lg:mx-0">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($selectedConversation['creator']['name'] ?? 'Criador'))) ?></div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-2xl font-extrabold"><?= e((string) ($selectedConversation['creator']['name'] ?? 'Criador')) ?></h3>
                            <p class="truncate text-sm text-on-surface-variant"><?= e((string) ($selectedConversation['creator']['headline'] ?? 'Chat ativo')) ?></p>
                        </div>
                    </div>

                    <div class="mx-4 mt-3 min-h-0 flex-1 overflow-y-auto pr-1 lg:mx-0 lg:mt-4" data-chat-thread>
                        <div class="h-full">
                            <div class="flex min-h-full flex-col justify-end gap-3">
                                <?php foreach ($messages as $message): ?>
                                    <?php $isMine = (int) ($message['sender_id'] ?? 0) === (int) ($subscriber['id'] ?? 0); ?>
                                    <article class="flex <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                                        <div class="max-w-[86%] rounded-[28px] px-4 py-3 shadow-sm <?= $isMine ? 'bg-primary text-white' : 'bg-white text-on-surface' ?>">
                                            <?php if (trim((string) ($message['body'] ?? '')) !== ''): ?>
                                                <p class="text-sm leading-6"><?= nl2br(e((string) ($message['body'] ?? ''))) ?></p>
                                            <?php endif; ?>
                                            <?php $attachment = is_array($message['attachment'] ?? null) ? $message['attachment'] : null; ?>
                                            <?php if ($attachment): ?>
                                                <div class="mt-3">
                                                    <?php if ((bool) ($message['can_access_attachment'] ?? false)): ?>
                                                        <?php if ((string) ($attachment['kind'] ?? 'document') === 'image'): ?>
                                                            <a class="block overflow-hidden rounded-2xl border border-white/10" href="<?= e((string) ($attachment['href'] ?? '#')) ?>" target="_blank">
                                                                <img alt="<?= e((string) ($attachment['original_name'] ?? 'Imagem')) ?>" class="max-h-72 w-full object-cover" src="<?= e((string) ($attachment['href'] ?? '')) ?>">
                                                            </a>
                                                        <?php else: ?>
                                                            <a class="flex items-center gap-3 rounded-2xl bg-white/90 px-4 py-3 text-sm font-bold text-slate-800" href="<?= e((string) ($attachment['href'] ?? '#')) ?>" target="_blank">
                                                                <span class="material-symbols-outlined"><?= e((string) (($attachment['kind'] ?? 'document') === 'video' ? 'play_circle' : 'description')) ?></span>
                                                                <span class="truncate"><?= e((string) ($attachment['original_name'] ?? 'Abrir anexo')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/80 p-4 text-slate-700">
                                                            <div class="flex items-start gap-3">
                                                                <span class="material-symbols-outlined text-primary">lock</span>
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="font-bold"><?= e(((int) ($message['unlock_price'] ?? 0)) > 0 ? 'Conteudo instantaneo' : 'Conteudo privado') ?></p>
                                                                    <p class="mt-1 text-sm text-slate-500"><?= e((string) ($message['lock_reason'] ?? 'Conteudo bloqueado')) ?></p>
                                                                    <?php if ((int) ($message['unlock_price'] ?? 0) > 0): ?>
                                                                        <form action="/messages/unlock" class="mt-3 flex flex-wrap items-center gap-3" method="post">
                                                                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                                                            <input name="message_id" type="hidden" value="<?= e((string) ($message['id'] ?? 0)) ?>">
                                                                            <input name="redirect" type="hidden" value="<?= e('/subscriber/messages?conversation=' . (int) ($selectedConversation['id'] ?? 0)) ?>">
                                                                            <span class="rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary"><?= luacoin_amount_html((int) ($message['unlock_price'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-3.5 w-3.5 shrink-0') ?></span>
                                                                            <button class="rounded-full bg-primary px-4 py-2 text-xs font-bold text-white" type="submit">Desbloquear</button>
                                                                        </form>
                                                                    <?php elseif ((string) ($message['required_plan_name'] ?? '') !== ''): ?>
                                                                        <span class="mt-3 inline-flex rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary"><?= e((string) ($message['required_plan_name'] ?? 'Plano')) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.22em] <?= $isMine ? 'text-white/70' : 'text-slate-400' ?>"><?= e(format_datetime((string) ($message['created_at'] ?? ''), 'd/m H:i')) ?></p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                                <?php if ($messages === []): ?><p class="rounded-3xl bg-white p-5 text-sm text-on-surface-variant">Nenhuma mensagem nesta conversa ainda.</p><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form action="/subscriber/messages/send" class="mx-4 mt-3 shrink-0 border-t border-slate-200 pt-3 lg:mx-0" enctype="multipart/form-data" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="conversation_id" type="hidden" value="<?= e((string) ($selectedConversation['id'] ?? 0)) ?>">
                        <details class="group relative">
                            <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full bg-surface-container-low text-slate-700 marker:content-none">
                                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                            </summary>
                            <div class="absolute bottom-[calc(100%+0.75rem)] left-0 right-0 z-10 hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-[0px_24px_48px_rgba(27,28,29,0.12)] group-open:block">
                                <label class="block">
                                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-on-surface-variant">Arquivo</span>
                                    <input accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm,.pdf,.doc,.docx,.txt,.zip,.rar,.7z" class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" data-file-label-target="subscriber-chat-file-name" name="attachment_file" type="file">
                                    <span class="mt-2 block text-xs text-on-surface-variant" data-file-label="subscriber-chat-file-name">Imagem, video, documento ou pacote privado.</span>
                                </label>
                            </div>
                        </details>
                        <div class="mt-3 flex items-end gap-3 pb-[calc(env(safe-area-inset-bottom,0px)+0.5rem)] lg:pb-0">
                            <textarea class="h-12 flex-1 resize-none rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="body" placeholder="Responder ao criador..."></textarea>
                            <button class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-white" data-prototype-skip="1" type="submit">
                                <span class="material-symbols-outlined text-[20px]">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="flex min-h-[420px] items-center justify-center rounded-none bg-surface-container-low p-8 text-center text-on-surface-variant lg:rounded-3xl">Selecione uma conversa para visualizar o historico e responder.</div>
            <?php endif; ?>
        </section>
    </div>
</main>
<script>
    document.querySelectorAll('[data-chat-thread]').forEach((element) => {
        element.scrollTop = element.scrollHeight;
    });

    (() => {
        const panel = document.querySelector('[data-mobile-chat-panel]');
        if (!panel || !window.matchMedia('(max-width: 1023px)').matches) {
            return;
        }

        if (panel.getAttribute('data-mobile-chat-state') !== 'selected') {
            panel.classList.add('hidden');
            return;
        }

        panel.classList.add('fixed', 'left-0', 'right-0', 'top-16', 'bottom-0', 'z-[90]', 'm-0', 'flex', 'overflow-hidden', 'rounded-none', 'bg-[#fbf9fb]', 'px-0', 'pb-0', 'pt-0');
        document.body.classList.add('overflow-hidden');
        window.addEventListener('beforeunload', () => {
            document.body.classList.remove('overflow-hidden');
        }, { once: true });
    })();

    document.querySelectorAll('input[type="file"][data-file-label-target]').forEach((input) => {
        input.addEventListener('change', () => {
            const key = input.getAttribute('data-file-label-target');
            const label = document.querySelector(`[data-file-label="${key}"]`);
            if (!label) {
                return;
            }

            label.textContent = input.files && input.files.length > 0
                ? input.files[0].name
                : 'Imagem, video, documento ou pacote privado.';
        });
    });
</script>
</body>
</html>
