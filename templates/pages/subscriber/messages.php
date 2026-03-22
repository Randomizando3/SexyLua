<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
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
<header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <h1 class="text-2xl font-black">SexyLua</h1>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Subscriber Club</span>
    </div>
    <div class="flex items-center gap-3">
        <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="/subscriber/favorites">Favoritos</a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($subscriber['name'] ?? 'Assinante'))) ?></div>
    </div>
</header>

<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber"><span class="material-symbols-outlined">home</span><span>Inicio</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/subscriptions"><span class="material-symbols-outlined">stars</span><span>Minhas Assinaturas</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/favorites"><span class="material-symbols-outlined">favorite</span><span>Favoritos</span></a>
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/subscriber/messages"><span class="material-symbols-outlined">chat</span><span>Mensagens</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/wallet"><span class="material-symbols-outlined">account_balance_wallet</span><span>Carteira</span></a>
    </nav>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Chat com criadores</p>
        <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Mensagens <span class="italic text-primary">Privadas</span></h2>
        <p class="mt-4 max-w-2xl text-on-surface-variant">Continue conversas, combine experiencias e acompanhe respostas dos criadores em um lugar so.</p>
    </section>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="space-y-6">
            <form action="/subscriber/messages" class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm" method="get">
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar conversa..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
            </form>
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

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <?php if ($selectedConversation): ?>
                <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($selectedConversation['creator']['name'] ?? 'Criador'))) ?></div>
                    <div>
                        <h3 class="text-2xl font-extrabold"><?= e((string) ($selectedConversation['creator']['name'] ?? 'Criador')) ?></h3>
                        <p class="text-sm text-on-surface-variant"><?= e((string) ($selectedConversation['creator']['headline'] ?? 'Chat ativo')) ?></p>
                    </div>
                </div>
                <div class="mt-6 max-h-[420px] space-y-4 overflow-y-auto pr-2">
                    <?php foreach ($messages as $message): ?>
                        <?php $isMine = (int) ($message['sender_id'] ?? 0) === (int) ($subscriber['id'] ?? 0); ?>
                        <div class="flex <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                            <div class="max-w-[78%] rounded-3xl px-5 py-4 <?= $isMine ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface' ?>">
                                <p class="text-sm leading-relaxed"><?= e((string) ($message['body'] ?? '')) ?></p>
                                <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.25em] <?= $isMine ? 'text-white/70' : 'text-slate-400' ?>"><?= e(format_datetime((string) ($message['created_at'] ?? ''), 'd/m H:i')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form action="/subscriber/messages/send" class="mt-6 space-y-4" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input name="conversation_id" type="hidden" value="<?= e((string) ($selectedConversation['id'] ?? 0)) ?>">
                    <textarea class="min-h-[140px] w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body" placeholder="Escreva sua mensagem..." required></textarea>
                    <button class="w-full rounded-full bg-primary px-5 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Enviar mensagem</button>
                </form>
            <?php else: ?>
                <div class="flex min-h-[420px] items-center justify-center rounded-3xl bg-surface-container-low p-8 text-center text-on-surface-variant">Selecione uma conversa para visualizar o historico e responder.</div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
