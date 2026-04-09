<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$announcements = $data['announcements'] ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Comunicados</title>
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
$adminTopbarUser = $admin;
$adminTopbarAction = ['href' => '/admin/settings', 'label' => 'Configuracoes'];
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>

<?php
$adminSidebarCurrent = 'messages';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Comunicacao da plataforma</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Mensagens <span class="italic text-primary">Gerais</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Envie comunicados para todos, apenas criadores, apenas assinantes ou somente admins.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Total</p><p class="mt-2 text-3xl font-extrabold text-primary"><?= e((string) ($summary['total'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Todos</p><p class="mt-2 text-3xl font-extrabold text-primary"><?= e((string) ($summary['all'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Criadores</p><p class="mt-2 text-3xl font-extrabold text-primary"><?= e((string) ($summary['creators'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Assinantes</p><p class="mt-2 text-3xl font-extrabold text-primary"><?= e((string) ($summary['subscribers'] ?? 0)) ?></p></article>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <h3 class="text-2xl font-extrabold">Novo comunicado</h3>
                <p class="mt-2 text-sm text-on-surface-variant">Essas mensagens entram como notificacao para o publico escolhido.</p>
            </div>
            <form action="/admin/messages/send" class="space-y-4" enctype="multipart/form-data" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="title" placeholder="Titulo do comunicado" required type="text">
                <textarea class="min-h-[180px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body" placeholder="Mensagem para a plataforma..." required></textarea>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="audience">
                        <option value="all">Todos</option>
                        <option value="creator">Criadores</option>
                        <option value="subscriber">Assinantes</option>
                        <option value="admin">Admins</option>
                    </select>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="href" placeholder="Link opcional de destino" type="text">
                </div>
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Anexo opcional</span>
                    <input accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm,.pdf,.doc,.docx,.txt,.zip,.rar,.7z" class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" name="attachment_file" type="file">
                </label>
                <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white shadow-lg shadow-primary/20" type="submit">Enviar comunicado</button>
            </form>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold">Historico</h3>
                    <p class="mt-2 text-sm text-on-surface-variant">Ultimos envios feitos pelo admin.</p>
                </div>
                <form action="/admin/messages" class="flex flex-col gap-3 sm:flex-row" method="get">
                    <input class="rounded-full border-none bg-surface-container-low px-5 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                    <select class="rounded-full border-none bg-surface-container-low px-5 py-3 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="audience">
                        <option value="">Todos os públicos</option>
                        <option value="all"<?= (string) ($filters['audience'] ?? '') === 'all' ? ' selected' : '' ?>>Todos</option>
                        <option value="creator"<?= (string) ($filters['audience'] ?? '') === 'creator' ? ' selected' : '' ?>>Criadores</option>
                        <option value="subscriber"<?= (string) ($filters['audience'] ?? '') === 'subscriber' ? ' selected' : '' ?>>Assinantes</option>
                        <option value="admin"<?= (string) ($filters['audience'] ?? '') === 'admin' ? ' selected' : '' ?>>Admins</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto rounded-3xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-[#f7f4f7] text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Titulo</th>
                        <th class="px-5 py-4">Publico</th>
                        <th class="px-5 py-4">Anexo</th>
                        <th class="px-5 py-4">Destinatarios</th>
                        <th class="px-5 py-4">Criado em</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($announcements as $announcement): ?>
                        <?php
                        $audienceLabel = match ((string) ($announcement['audience'] ?? 'all')) {
                            'all' => 'Todos',
                            'creator' => 'Criadores',
                            'subscriber' => 'Assinantes',
                            'admin' => 'Admins',
                            default => 'Publico',
                        };
                        ?>
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <p class="font-bold text-slate-900"><?= e((string) ($announcement['title'] ?? 'Comunicado')) ?></p>
                                <p class="mt-1 max-w-xl text-slate-500"><?= e(excerpt((string) ($announcement['body'] ?? ''), 120)) ?></p>
                            </td>
                            <td class="px-5 py-4 align-top"><?= e($audienceLabel) ?></td>
                            <td class="px-5 py-4 align-top">
                                <?php if (is_array($announcement['attachment'] ?? null)): ?>
                                    <a class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary" href="<?= e((string) (($announcement['attachment']['href'] ?? '#'))) ?>" target="_blank">
                                        <span class="material-symbols-outlined text-base"><?= e((string) (($announcement['attachment']['kind'] ?? 'document') === 'image' ? 'image' : (($announcement['attachment']['kind'] ?? 'document') === 'video' ? 'play_circle' : 'description'))) ?></span>
                                        Anexo
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 align-top"><?= e((string) ($announcement['recipient_count'] ?? 0)) ?></td>
                            <td class="px-5 py-4 align-top"><?= e(format_datetime((string) ($announcement['created_at'] ?? ''))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($announcements === []): ?>
                        <tr><td class="px-5 py-8 text-slate-500" colspan="5">Nenhum comunicado enviado ainda.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>
</html>
