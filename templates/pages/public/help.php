<?php

declare(strict_types=1);

$settings = (array) (($data['settings'] ?? $data ?? []));
$supportEmail = trim((string) ($settings['support_recipient_email'] ?? ''));
$supportName = trim((string) ($settings['support_recipient_name'] ?? 'SexyLua'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Ajuda</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { background: #fbf9fb; color: #1b1c1d; font-family: Manrope, sans-serif; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        .flash-stack { display: grid; gap: 0.75rem; }
        .flash { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 1.5rem; padding: 1rem 1.25rem; font-weight: 700; }
        .flash-success { background: #ecfdf3; color: #047857; }
        .flash-error { background: #fff1f2; color: #be123c; }
        .flash-close { display: none; }
    </style>
</head>
<body>
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>
<main class="mx-auto max-w-7xl px-6 pb-20 pt-28 md:px-8">
    <section class="rounded-[2rem] bg-[linear-gradient(135deg,#fff5f8_0%,#fbf9fb_100%)] p-8 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Suporte</p>
        <h1 class="headline mt-3 text-4xl font-extrabold">Ajuda e contato</h1>
        <p class="mt-4 max-w-3xl text-slate-600">Precisa de ajuda com cadastro, assinatura, carteira, lives ou perfil? Use o formulario abaixo e a equipe recebe sua mensagem no e-mail configurado pelo admin.</p>
        <div class="mt-6 flex flex-wrap gap-3 text-sm">
            <span class="rounded-full bg-white px-4 py-2 font-semibold text-slate-600">Assinantes</span>
            <span class="rounded-full bg-white px-4 py-2 font-semibold text-slate-600">Criadores</span>
            <span class="rounded-full bg-white px-4 py-2 font-semibold text-slate-600">Pagamento</span>
            <span class="rounded-full bg-white px-4 py-2 font-semibold text-slate-600">Lives</span>
        </div>
    </section>

    <section class="mt-8 grid grid-cols-1 gap-8 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-6">
            <?php require BASE_PATH . '/templates/partials/flash.php'; ?>

            <article class="rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8ef] text-[#ab1155]">
                        <span class="material-symbols-outlined text-3xl">support_agent</span>
                    </span>
                    <div>
                        <h2 class="headline text-2xl font-extrabold">Como funciona o suporte</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Explique o problema com contexto, inclua a area afetada e use o mesmo e-mail da sua conta quando possivel. Isso acelera a triagem de carteira, assinatura, verificacao documental e operacao de lives.</p>
                    </div>
                </div>
            </article>

            <article class="rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <h2 class="headline text-2xl font-extrabold">Temas mais comuns</h2>
                <div class="mt-6 grid gap-4">
                    <div class="rounded-3xl bg-[#fbf9fb] p-5 ring-1 ring-[#f0e8ee]">
                        <p class="font-bold text-slate-800">Carteira e recargas</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Use este canal para relatar recarga pendente, saldo nao refletido, bonus de LuaCoins ou problemas na compra de microconteudos e darkroom.</p>
                    </div>
                    <div class="rounded-3xl bg-[#fbf9fb] p-5 ring-1 ring-[#f0e8ee]">
                        <p class="font-bold text-slate-800">Criador e conteudo</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Ideal para dificuldades com packs, postagem de conteudo, planos, moderacao, verificacao documental e solicitacao de saque.</p>
                    </div>
                    <div class="rounded-3xl bg-[#fbf9fb] p-5 ring-1 ring-[#f0e8ee]">
                        <p class="font-bold text-slate-800">Lives e transmissao</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Use quando houver problema no estudio, RTMP, preview, paywall de Live VIP, darkroom, chat ou exibicao publica da transmissao.</p>
                    </div>
                </div>
            </article>

            <article class="rounded-[2rem] bg-[#ab1155] p-8 text-white shadow-xl">
                <h2 class="headline text-2xl font-extrabold">Canal de recebimento</h2>
                <p class="mt-3 text-sm leading-7 text-white/80">
                    <?= $supportEmail !== '' ? e($supportName !== '' ? $supportName . ' - ' . $supportEmail : $supportEmail) : 'O e-mail de suporte ainda nao foi configurado no admin.' ?>
                </p>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.22em] text-white/70">Resposta por e-mail conforme a configuracao SMTP ativa</p>
            </article>
        </div>

        <section class="rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <div class="mb-6">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Formulario</p>
                <h2 class="headline mt-3 text-3xl font-extrabold">Fale com a equipe</h2>
                <p class="mt-3 text-sm text-slate-500">Preencha os detalhes abaixo. A mensagem sai da propria plataforma e vai para o destino configurado em Integra&ccedil;&otilde;es no admin.</p>
            </div>

            <form action="/help/contact" class="space-y-4" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome</span>
                        <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="name" placeholder="Como podemos te chamar?" required type="text">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail</span>
                        <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="email" placeholder="voce@exemplo.com" required type="email">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Perfil</span>
                        <select class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="role">
                            <option value="visitante">Visitante</option>
                            <option value="subscriber">Assinante</option>
                            <option value="creator">Criador</option>
                            <option value="admin">Admin</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Categoria</span>
                        <select class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="category">
                            <option value="geral">Geral</option>
                            <option value="cadastro">Cadastro e login</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="conteudo">Conteudo e packs</option>
                            <option value="lives">Lives</option>
                            <option value="moderacao">Moderacao</option>
                        </select>
                    </label>
                </div>

                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assunto</span>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="subject" placeholder="Resumo rapido do problema" required type="text">
                </label>

                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Mensagem</span>
                    <textarea class="min-h-[220px] w-full rounded-3xl border-none bg-[#f5f3f5] px-5 py-4" name="message" placeholder="Descreva com o maximo de contexto possivel o que aconteceu, em qual area e o que voce esperava que ocorresse." required></textarea>
                </label>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Se o assunto for financeiro, informe o e-mail da conta e o horario aproximado da tentativa.</p>
                    <button class="inline-flex items-center justify-center gap-2 rounded-full bg-[#ab1155] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-[#ab1155]/20" type="submit">
                        <span class="material-symbols-outlined text-base">send</span>
                        Enviar mensagem
                    </button>
                </div>
            </form>
        </section>
    </section>
</main>
<?php require BASE_PATH . '/templates/partials/public_footer.php'; ?>
</body>
</html>
