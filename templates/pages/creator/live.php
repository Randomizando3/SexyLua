<?php
declare(strict_types=1);
$creator = $data['creator'] ?? [];
$lives = $data['filtered_lives'] ?? $data['lives'] ?? [];
$selected = $data['selected_live'] ?? null;
$messages = $data['messages'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$categories = ['Chatting & Chill', 'Dancing', 'ASMR Lunar', 'Cosplay', 'Editorial', 'Backstage'];
$schedule = $selected && (string) ($selected['scheduled_for'] ?? '') !== '' ? date('Y-m-d\TH:i', strtotime((string) $selected['scheduled_for'])) : '';
$redirect = path_with_query('/creator/live', ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? '', 'live' => (int) ($selected['id'] ?? 0)]);
$cover = media_url((string) ($selected['cover_url'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Live Studio</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body{font-family:Manrope,sans-serif;background:#fbf9fb;color:#1b1c1d}.headline{font-family:'Plus Jakarta Sans',sans-serif}.signature-glow{background:linear-gradient(135deg,#D81B60 0%,#ab1155 100%)}
    </style>
</head>
<body>
<header class="fixed top-0 z-40 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 text-white shadow-lg">
    <div class="flex items-center gap-4"><h1 class="headline text-2xl font-extrabold">SexyLua</h1><span class="hidden text-xs uppercase tracking-[0.3em] md:block">Live Studio</span></div>
    <div class="flex items-center gap-3"><a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest" href="/creator/content">Conteudo</a><div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div></div>
</header>
<aside class="fixed left-0 top-0 h-full w-64 bg-[#f5f3f5] px-6 pt-24 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
    <nav class="space-y-2 text-sm font-semibold text-slate-500">
        <a class="block rounded-full px-4 py-3 hover:bg-white/50" href="/creator">Painel</a>
        <a class="block rounded-full px-4 py-3 hover:bg-white/50" href="/creator/content">Conteudo</a>
        <a class="block rounded-full bg-white px-4 py-3 text-[#ab1155]" href="/creator/live">Lives</a>
        <a class="block rounded-full px-4 py-3 hover:bg-white/50" href="/creator/memberships">Assinaturas</a>
        <a class="block rounded-full px-4 py-3 hover:bg-white/50" href="/creator/wallet">Carteira</a>
        <a class="block rounded-full px-4 py-3 hover:bg-white/50" href="/creator/settings">Configuracoes</a>
    </nav>
</aside>
<main class="ml-64 px-10 pb-12 pt-24">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Creator Studio</p><h2 class="headline mt-2 text-4xl font-extrabold">Operacao de Live</h2><p class="mt-3 max-w-3xl text-slate-500">Agende, edite, entre no ar, encerre, altere chat e remova lives sem sair do painel.</p></div>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agendadas</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e((string) ($summary['scheduled'] ?? 0)) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Ao vivo</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e((string) ($summary['live'] ?? 0)) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chat ativo</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e((string) ($summary['chat_enabled'] ?? 0)) ?></p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.95fr_1.05fr]">
        <section class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <h3 class="headline text-2xl font-extrabold"><?= $selected ? 'Editar transmissao' : 'Nova transmissao' ?></h3>
            <form action="/creator/live/save" class="mt-6 space-y-4" enctype="multipart/form-data" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <?php if ($selected): ?><input name="id" type="hidden" value="<?= e((string) ($selected['id'] ?? 0)) ?>"><?php endif; ?>
                <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="title" placeholder="Titulo da live" required type="text" value="<?= e((string) ($selected['title'] ?? '')) ?>">
                <textarea class="min-h-[120px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="description" placeholder="Descricao"><?= e((string) ($selected['description'] ?? '')) ?></textarea>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="scheduled_for" type="datetime-local" value="<?= e($schedule) ?>">
                    <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="category"><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= (string) ($selected['category'] ?? 'Chatting & Chill') === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select>
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="price_tokens" placeholder="Preco em tokens" type="number" value="<?= e((string) ($selected['price_tokens'] ?? 0)) ?>">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="goal_tokens" placeholder="Meta em tokens" type="number" value="<?= e((string) ($selected['goal_tokens'] ?? 0)) ?>">
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="cover_url" placeholder="Cover URL" type="url" value="<?= e((string) ($selected['cover_url'] ?? '')) ?>">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 file:mr-4 file:rounded-full file:border-0 file:bg-[#D81B60] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="cover_file" type="file">
                </div>
                <textarea class="min-h-[92px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="pinned_notice" placeholder="Aviso fixado"><?= e((string) ($selected['pinned_notice'] ?? '')) ?></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (string) ($selected['access_mode'] ?? 'public') === 'public' ? 'checked' : '' ?> class="mr-3" name="access_mode" type="radio" value="public"> Publico</label>
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (string) ($selected['access_mode'] ?? '') === 'subscriber' ? 'checked' : '' ?> class="mr-3" name="access_mode" type="radio" value="subscriber"> Assinantes</label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= ! isset($selected['chat_enabled']) || (bool) ($selected['chat_enabled'] ?? false) ? 'checked' : '' ?> class="mr-3" name="chat_enabled" type="checkbox" value="1"> Chat habilitado</label>
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (bool) ($selected['recording_enabled'] ?? false) ? 'checked' : '' ?> class="mr-3" name="recording_enabled" type="checkbox" value="1"> Gravar replay</label>
                </div>
                <button class="signature-glow w-full rounded-full px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit"><?= $selected ? 'Salvar alteracoes' : 'Salvar live' ?></button>
            </form>
        </section>

        <section class="space-y-6">
            <div class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <div class="relative aspect-[9/16] bg-slate-900">
                    <?php if ($cover !== ''): ?><img alt="Preview" class="h-full w-full object-cover" src="<?= e($cover) ?>"><?php else: ?><div class="signature-glow flex h-full w-full items-center justify-center text-center text-white"><div><div class="headline text-4xl font-extrabold">LIVE</div><p class="mt-2 text-xs font-bold uppercase tracking-[0.3em]">Preview do estudio</p></div></div><?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30 p-6 text-white">
                        <div class="flex h-full flex-col justify-between">
                            <div class="flex items-start justify-between"><span class="rounded-full bg-[#D81B60] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><?= e((string) ($selected['status'] ?? 'preview')) ?></span><span class="rounded-full bg-white/20 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><?= e((string) ($selected['viewer_count'] ?? 0)) ?> viewers</span></div>
                            <div><p class="headline text-2xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Selecione uma live')) ?></p><p class="mt-2 text-sm text-white/70"><?= e((string) ($selected['description'] ?? 'Escolha uma live para visualizar os dados atuais.')) ?></p></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <h4 class="headline text-xl font-extrabold">Ultimas mensagens</h4>
                <div class="mt-4 space-y-3"><?php foreach (array_slice($messages, 0, 5) as $message): ?><div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm"><span class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-[#D81B60]"><?= e((string) (($message['sender']['name'] ?? 'Convidado'))) ?></span><?= e((string) ($message['body'] ?? '')) ?></div><?php endforeach; ?><?php if ($messages === []): ?><p class="text-sm text-slate-500">Sem mensagens para esta live ainda.</p><?php endif; ?></div>
            </div>
        </section>
    </div>

    <form action="/creator/live" class="mt-10 grid grid-cols-1 gap-4 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] md:grid-cols-[1fr_0.6fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar live..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="status"><option value="">Todos os status</option><option value="scheduled" <?= (string) ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendada</option><option value="live" <?= (string) ($filters['status'] ?? '') === 'live' ? 'selected' : '' ?>>Ao vivo</option><option value="ended" <?= (string) ($filters['status'] ?? '') === 'ended' ? 'selected' : '' ?>>Encerrada</option></select>
        <div class="flex items-end gap-3"><button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button><a class="rounded-full bg-[#f5f3f5] px-5 py-4 text-sm font-bold text-slate-600" href="/creator/live">Reset</a></div>
    </form>

    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($lives as $live): ?>
            <?php $status = (string) ($live['status'] ?? 'scheduled'); $next = $status === 'live' ? ['ended', 'Encerrar'] : ($status === 'ended' ? ['scheduled', 'Reagendar'] : ['live', 'Entrar no ar']); ?>
            <article class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <div class="signature-glow p-5 text-white"><p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p><p class="mt-2 text-sm text-white/80"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i')) ?></p></div>
                <div class="space-y-4 p-5">
                    <p class="text-sm text-slate-500"><?= e(excerpt((string) ($live['description'] ?? ''), 120)) ?></p>
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500"><span><?= e((string) ($live['category'] ?? 'Studio')) ?></span><span><?= e((string) ($live['viewer_count'] ?? 0)) ?> viewers</span></div>
                    <div class="grid grid-cols-2 gap-3">
                        <a class="rounded-full bg-[#f5f3f5] px-4 py-3 text-center text-xs font-bold text-slate-700" href="<?= e(path_with_query('/creator/live', ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? '', 'live' => (int) ($live['id'] ?? 0)])) ?>">Editar</a>
                        <form action="/creator/live/status" method="post"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>"><input name="status" type="hidden" value="<?= e((string) $next[0]) ?>"><input name="redirect" type="hidden" value="<?= e($redirect) ?>"><button class="w-full rounded-full bg-slate-900 px-4 py-3 text-xs font-bold text-white" data-prototype-skip="1" type="submit"><?= e((string) $next[1]) ?></button></form>
                        <form action="/creator/live/save" method="post"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><input name="id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>"><input name="title" type="hidden" value="<?= e((string) ($live['title'] ?? '')) ?>"><input name="description" type="hidden" value="<?= e((string) ($live['description'] ?? '')) ?>"><input name="scheduled_for" type="hidden" value="<?= e((string) ($live['scheduled_for'] ?? '')) ?>"><input name="price_tokens" type="hidden" value="<?= e((string) ($live['price_tokens'] ?? 0)) ?>"><input name="category" type="hidden" value="<?= e((string) ($live['category'] ?? 'Chatting & Chill')) ?>"><input name="access_mode" type="hidden" value="<?= e((string) ($live['access_mode'] ?? 'public')) ?>"><input name="goal_tokens" type="hidden" value="<?= e((string) ($live['goal_tokens'] ?? 0)) ?>"><input name="cover_url" type="hidden" value="<?= e((string) ($live['cover_url'] ?? '')) ?>"><input name="pinned_notice" type="hidden" value="<?= e((string) ($live['pinned_notice'] ?? '')) ?>"><input name="chat_enabled" type="hidden" value="<?= (bool) ($live['chat_enabled'] ?? false) ? '0' : '1' ?>"><input name="recording_enabled" type="hidden" value="<?= (bool) ($live['recording_enabled'] ?? false) ? '1' : '0' ?>"><input name="status" type="hidden" value="<?= e((string) ($live['status'] ?? 'scheduled')) ?>"><button class="w-full rounded-full bg-[#D81B60]/10 px-4 py-3 text-xs font-bold text-[#D81B60]" data-prototype-skip="1" type="submit"><?= (bool) ($live['chat_enabled'] ?? false) ? 'Fechar chat' : 'Abrir chat' ?></button></form>
                        <form action="/creator/live/delete" method="post" onsubmit="return confirm('Remover esta live?');"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>"><input name="redirect" type="hidden" value="<?= e($redirect) ?>"><button class="w-full rounded-full bg-rose-50 px-4 py-3 text-xs font-bold text-rose-700" data-prototype-skip="1" type="submit">Excluir</button></form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
