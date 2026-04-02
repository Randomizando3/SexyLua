<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Termos</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { background: #fbf9fb; color: #1b1c1d; font-family: Manrope, sans-serif; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
    </style>
</head>
<body>
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>
<main class="mx-auto max-w-5xl px-6 pb-20 pt-28 md:px-8">
    <section class="rounded-[2rem] bg-[linear-gradient(135deg,#fff5f8_0%,#fbf9fb_100%)] p-8 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Termos</p>
        <h1 class="headline mt-3 text-4xl font-extrabold">Termos de uso</h1>
        <p class="mt-4 max-w-3xl text-slate-600">Estas regras orientam o uso de contas, publicacao de conteudos, pagamentos, lives e operacao administrativa dentro da SexyLua.</p>
    </section>

    <section class="mt-8 rounded-[2rem] bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
        <div class="prose prose-slate max-w-none">
            <h2 class="headline !mb-4 !mt-0 !text-2xl !font-extrabold">Regras principais</h2>
            <p>Cada usuario responde pela veracidade dos dados enviados, pelo uso seguro da propria conta e pelo cumprimento das regras internas da plataforma. Criadores sao responsaveis pelos materiais publicados, pelas configuracoes de assinaturas e pelas transmissões iniciadas em seus paineis.</p>
            <p>Conteudos podem passar por moderacao administrativa e podem ser bloqueados, rejeitados, arquivados ou removidos quando violarem politicas internas, regras legais ou exigencias operacionais. Contas tambem podem ter verificacao pendente, aprovada ou reprovada conforme a analise documental.</p>
            <p>LuaCoins sao a moeda interna usada para recargas, gorjetas, assinaturas e desbloqueio de microconteudos. A liberacao de saldo depende da confirmacao do pagamento no provedor externo. Saques sao processados manualmente pelo admin e exigem verificacao documental aprovada.</p>
            <p>A administracao pode suspender contas, rever saldos, bloquear transacoes e restringir acessos quando houver suspeita de fraude, abuso, chargeback, uso indevido do sistema ou descumprimento destas regras.</p>
        </div>
    </section>
</main>
<?php require BASE_PATH . '/templates/partials/public_footer.php'; ?>
</body>
</html>
