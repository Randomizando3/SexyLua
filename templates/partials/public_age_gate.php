<?php

declare(strict_types=1);

if (public_age_gate_completed()) {
    return;
}

$audienceOptions = audience_category_options();
?>
<div class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/90 px-4 py-6" data-age-gate-modal>
    <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-[0px_30px_80px_rgba(0,0,0,0.45)]">
        <div class="border-b border-slate-200 bg-[#fff6f8] px-6 py-6 text-center">
            <div class="mx-auto inline-flex items-center gap-2 rounded-full bg-[#ab1155]/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.3em] text-[#ab1155]">
                <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">warning</span>
                Acesso adulto
            </div>
            <h2 class="headline mt-4 text-3xl font-extrabold text-slate-950">Material adulto sexualmente explicito</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                Este site e destinado exclusivamente a maiores de 18 anos. Antes de entrar, confirme que deseja prosseguir e escolha a categoria que quer explorar agora.
            </p>
        </div>

        <div class="px-6 py-6">
            <div class="max-h-[34vh] overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-5 text-sm leading-7 text-slate-700">
                <p>
                    Ao continuar, voce declara ser maior de idade e concorda em acessar conteudos, imagens, videos, opinioes e interacoes de natureza adulta dentro da plataforma.
                </p>
                <ul class="mt-4 list-disc space-y-2 pl-5">
                    <li>Voce confirma que tem 18 anos ou mais.</li>
                    <li>Voce concorda com os Termos de Uso e com a Politica de Privacidade da SexyLua.</li>
                    <li>Voce entende que o acesso ao site depende da sua confirmacao e da categoria escolhida.</li>
                </ul>
            </div>

            <form action="/audience-gate" class="mt-6" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input name="accepted" type="hidden" value="1">
                <div class="text-center">
                    <p class="headline text-2xl font-extrabold text-slate-950">O que voce gostaria de assistir?</p>
                    <p class="mt-2 text-sm text-slate-500">Escolha sua categoria inicial e siga direto para a exploracao.</p>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <?php foreach ($audienceOptions as $optionValue => $optionLabel): ?>
                        <button class="rounded-2xl bg-[#ff5a26] px-4 py-4 text-center text-sm font-bold text-white transition-transform hover:scale-[1.02]" name="category" type="submit" value="<?= e($optionValue) ?>">
                            <?= e($optionLabel) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>

            <div class="mt-6 flex items-center justify-center gap-3">
                <span class="text-sm font-semibold text-slate-500">Nao concorda?</span>
                <a class="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-slate-300 px-6 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50" href="https://www.google.com">
                    Sair
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');
    })();
</script>
