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
    </style>
</head>
<body>
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>
<main class="mx-auto max-w-5xl px-6 pb-20 pt-28 md:px-8">
    <section class="rounded-[2rem] bg-[linear-gradient(135deg,#fff5f8_0%,#fbf9fb_100%)] p-8 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Suporte</p>
        <h1 class="headline mt-3 text-4xl font-extrabold">Ajuda e suporte</h1>
        <p class="mt-4 max-w-3xl text-slate-600">Centralizamos aqui as orientacoes principais para assinantes, criadores e equipe administrativa, mantendo um canal simples para contato operacional.</p>
    </section>

    <section class="mt-8 rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
        <div class="prose prose-slate max-w-none">
            <h2 class="headline !mb-4 !mt-0 !text-2xl !font-extrabold">Como podemos ajudar</h2>
            <p>Assinantes podem usar a carteira de LuaCoins para recargas, assinaturas, gorjetas e desbloqueio de microconteudos. Criadores gerenciam conteudos, planos, lives, carteira e pedidos de saque pelo proprio painel. O admin acompanha verificacao documental, pagamentos, operacoes e SEO em uma area separada.</p>
            <p>Se uma recarga via SyncPay nao confirmar, primeiro confira o status do PIX no banco e depois valide se o webhook da plataforma foi cadastrado corretamente. Para saques, lembre que o perfil precisa estar com a documentacao aprovada.</p>
            <p>Para lives, o estúdio do criador usa ingest RTMP e a sala publica reproduz a transmissao via HLS. Em caso de duvida operacional, revise os ajustes da live no painel do criador e os limites configurados pelo admin.</p>
            <h3 class="headline !mt-8 !text-xl !font-extrabold">Contato</h3>
            <p>Suporte operacional: <a href="mailto:admin@sexylua.com.br">admin@sexylua.com.br</a></p>
        </div>
    </section>
</main>
<?php require BASE_PATH . '/templates/partials/public_footer.php'; ?>
</body>
</html>
