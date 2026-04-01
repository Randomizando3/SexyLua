<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$conversations = $data['filtered_conversations'] ?? $data['conversations'] ?? [];
$selectedConversation = $data['selected_conversation'] ?? null;
$messages = $data['messages'] ?? [];
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
            <div class="space-y-4">
                <?php foreach ($conversations as $conversation): ?>
                    <?php $active = $selectedConversation && (int) ($selectedConversation['id'] ?? 0) === (int) ($conversation['id'] ?? 0); ?>
                    <a class="block rounded-3xl p-5 shadow-sm transition-colors <?= $active ? 'bg-primary text-white' : 'bg-surface-container-lowest hover:bg-surface-container-low' ?>" href="<?= e('/creator/messages?conversation=' . (int) ($conversation['id'] ?? 0)) ?>">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full <?= $active ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' ?> font-bold"><?= e(avatar_initials((string) ($conversation['subscriber']['name'] ?? 'Assinante'))) ?></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold"><?= e((string) ($conversation['subscriber']['name'] ?? 'Assinante')) ?></p>
                                <p class="mt-1 truncate text-sm <?= $active ? 'text-white/80' : 'text-on-surface-variant' ?>"><?= e(excerpt((string) ($conversation['latest_message']['body'] ?? 'Sem mensagens ainda.'), 70)) ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if ($conversations === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma conversa encontrada.</p><?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <?php if ($selectedConversation): ?>
                <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($selectedConversation['subscriber']['name'] ?? 'Assinante'))) ?></div>
                    <div>
                        <h3 class="text-2xl font-extrabold"><?= e((string) ($selectedConversation['subscriber']['name'] ?? 'Assinante')) ?></h3>
                        <p class="text-sm text-on-surface-variant"><?= e((string) ($selectedConversation['subscriber']['headline'] ?? 'Chat ativo')) ?></p>
                    </div>
                </div>
                <div class="mt-6 max-h-[420px] space-y-4 overflow-y-auto pr-2">
                    <?php foreach ($messages as $message): ?>
                        <?php $isMine = (int) ($message['sender_id'] ?? 0) === (int) ($creator['id'] ?? 0); ?>
                        <article class="flex <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                            <div class="max-w-[80%] rounded-3xl px-5 py-4 shadow-sm <?= $isMine ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface' ?>">
                                <p class="text-sm leading-6"><?= nl2br(e((string) ($message['body'] ?? ''))) ?></p>
                                <p class="mt-2 text-[11px] font-bold uppercase tracking-[0.2em] <?= $isMine ? 'text-white/70' : 'text-slate-400' ?>"><?= e(format_datetime((string) ($message['created_at'] ?? ''), 'd/m H:i')) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($messages === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma mensagem nesta conversa ainda.</p><?php endif; ?>
                </div>
                <form action="/creator/messages/send" class="mt-6 flex flex-col gap-4 sm:flex-row" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input name="conversation_id" type="hidden" value="<?= e((string) ($selectedConversation['id'] ?? 0)) ?>">
                    <textarea class="min-h-[70px] flex-1 rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body" placeholder="Responder ao assinante..." required></textarea>
                    <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white shadow-lg shadow-primary/20" type="submit">Enviar</button>
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
