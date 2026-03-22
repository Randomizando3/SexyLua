<?php
declare(strict_types=1);
$creator = $data['creator'] ?? [];
$transactions = $data['transactions_filtered'] ?? $data['transactions'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$balance = (int) ($data['balance'] ?? 0);
$minWithdrawal = (int) ($data['min_withdrawal'] ?? 50);
$payoutProfile = $data['payout_profile'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Carteira Lunar</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body{font-family:Manrope,sans-serif;background:#fbf9fb;color:#1b1c1d}.headline{font-family:'Plus Jakarta Sans',sans-serif}.signature-glow{background:linear-gradient(135deg,#D81B60 0%,#ab1155 100%)}
    </style>
</head>
<body>
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'wallet';
$creatorTopbarLabel = 'Carteira Lunar';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Go Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>
<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Financeiro do criador</p><h2 class="headline mt-2 text-4xl font-extrabold">Carteira e saques</h2><p class="mt-3 max-w-3xl text-slate-500">Saldo em LuaCoins, receita por assinatura, gorjetas e solicitacoes de saque com chave de pagamento real do criador.</p></div>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Saldo</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e(token_amount($balance)) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e(token_amount((int) ($summary['subscription_income'] ?? 0))) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e(token_amount((int) ($summary['tips_income'] ?? 0))) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Pendentes</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e(token_amount((int) ($summary['pending_payouts'] ?? 0))) ?></p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.8fr_1.2fr]">
        <section class="space-y-6">
            <div class="signature-glow rounded-3xl p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Disponivel para saque</p>
                <h3 class="headline mt-4 text-5xl font-extrabold"><?= e(token_amount($balance)) ?></h3>
                <p class="mt-3 text-sm text-white/80">Aproximadamente <?= e(brl_amount((float) ($summary['available_brl'] ?? 0))) ?>, respeitando saque minimo de <?= e(luacoins_amount($minWithdrawal)) ?>.</p>
            </div>
            <form action="/creator/wallet/payout" class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <h3 class="headline text-2xl font-extrabold">Solicitar saque</h3>
                <div class="mt-6 space-y-4">
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="<?= e((string) $minWithdrawal) ?>" name="luacoins" placeholder="Quantidade em LuaCoins" required type="number" value="<?= e((string) $minWithdrawal) ?>">
                    <select class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="payout_method">
                        <option value="pix" <?= (string) ($payoutProfile['method'] ?? 'pix') === 'pix' ? 'selected' : '' ?>>PIX</option>
                        <option value="bank" <?= (string) ($payoutProfile['method'] ?? '') === 'bank' ? 'selected' : '' ?>>Conta bancaria</option>
                        <option value="wallet" <?= (string) ($payoutProfile['method'] ?? '') === 'wallet' ? 'selected' : '' ?>>Carteira digital</option>
                    </select>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="payout_key" placeholder="Chave PIX, banco ou conta" required type="text" value="<?= e((string) ($payoutProfile['key'] ?? '')) ?>">
                    <textarea class="min-h-[120px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="note" placeholder="Observacao para o financeiro">Saque solicitado pelo painel do criador.</textarea>
                    <button class="signature-glow w-full rounded-full px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Enviar solicitacao</button>
                </div>
            </form>
        </section>

        <section class="space-y-6">
            <form action="/creator/wallet" class="grid grid-cols-1 gap-4 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] md:grid-cols-[1fr_0.6fr_auto]" method="get">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar transacao..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="type">
                    <option value="">Todos os tipos</option>
                    <option value="subscription" <?= (string) ($filters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinaturas</option>
                    <option value="tip" <?= (string) ($filters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjetas</option>
                    <option value="payout" <?= (string) ($filters['type'] ?? '') === 'payout' ? 'selected' : '' ?>>Saques</option>
                    <option value="top_up" <?= (string) ($filters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recargas</option>
                </select>
                <div class="flex items-end gap-3"><button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button><a class="rounded-full bg-[#f5f3f5] px-5 py-4 text-sm font-bold text-slate-600" href="/creator/wallet">Reset</a></div>
            </form>

            <div class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <h3 class="headline text-2xl font-extrabold">Historico financeiro</h3>
                <div class="mt-6 space-y-4">
                    <?php foreach ($transactions as $transaction): ?>
                        <?php $isIn = (string) ($transaction['direction'] ?? 'in') === 'in'; ?>
                        <div class="flex flex-col gap-3 rounded-2xl bg-[#f5f3f5] p-5 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-bold"><?= e((string) ($transaction['note'] ?? ($transaction['type'] ?? 'Movimentacao'))) ?></p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="headline text-xl font-extrabold <?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?>"><?= $isIn ? '+' : '-' ?><?= e(token_amount((int) ($transaction['amount'] ?? 0))) ?></p>
                                <?php if ((string) ($transaction['payout_method'] ?? '') !== ''): ?><p class="mt-1 text-xs font-bold uppercase tracking-widest text-slate-400"><?= e((string) ($transaction['payout_method'] ?? '')) ?></p><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($transactions === []): ?><p class="rounded-2xl bg-[#f5f3f5] p-6 text-sm text-slate-500">Nenhuma transacao encontrada com esse filtro.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
