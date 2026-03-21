<section class="hero-panel">
    <p class="eyebrow">Rota indisponivel</p>
    <h2><?= e($title ?? 'Pagina nao encontrada') ?></h2>
    <p><?= e($description ?? 'A pagina solicitada nao foi localizada.') ?></p>
    <div class="button-row">
        <a href="/" class="button button-primary">Voltar para home</a>
        <a href="/explore" class="button button-ghost">Explorar criadores</a>
    </div>
</section>
