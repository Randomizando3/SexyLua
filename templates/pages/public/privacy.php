<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Privacidade</title>
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
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Privacidade</p>
        <h1 class="headline mt-3 text-4xl font-extrabold">Politica de privacidade</h1>
        <p class="mt-4 max-w-3xl text-slate-600">Este documento resume como a SexyLua trata dados de conta, pagamentos, mensagens e operacao da plataforma.</p>
    </section>

    <section class="mt-8 rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
        <div class="prose prose-slate max-w-none">
            <h2 class="headline !mb-4 !mt-0 !text-2xl !font-extrabold">Tratamento de dados</h2>
            <p>A SexyLua armazena dados cadastrais, autenticacao, historico financeiro, favoritos, assinaturas, mensagens privadas, anexos protegidos, dados operacionais de lives e informacoes de moderacao para manter a plataforma funcional e segura.</p>
            <p>Recargas via SyncPay usam identificadores de transacao, referencias externas, status do pagamento, codigo PIX e retorno de webhook para conciliacao e liberacao de LuaCoins. Dados documentais enviados pelos usuarios ficam acessiveis apenas para o proprio usuario e para a equipe administrativa responsavel pela verificacao.</p>
            <p>Mensagens privadas e anexos podem ser armazenados para garantir continuidade das conversas, liberacao de microconteudos e auditoria interna. Dados tambem podem ser preservados pelo tempo necessario para prevencao a fraude, cumprimento de obrigacoes legais e suporte operacional.</p>
            <p>Quando aplicavel, o usuario pode solicitar revisao cadastral, reenviar documentacao e atualizar informacoes diretamente pelas configuracoes da conta.</p>
        </div>
    </section>
</main>
<?php require BASE_PATH . '/templates/partials/public_footer.php'; ?>
</body>
</html>
