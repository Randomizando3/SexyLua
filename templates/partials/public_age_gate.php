<?php

declare(strict_types=1);

if (public_age_gate_completed()) {
    return;
}

$audienceOptions = audience_category_options();
?>
<div class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/90 px-3 py-4 sm:px-4 sm:py-6" data-age-gate-modal>
    <div class="w-full max-w-[min(92vw,38rem)] overflow-hidden rounded-[1.6rem] bg-white shadow-[0px_30px_80px_rgba(0,0,0,0.45)] sm:max-w-2xl sm:rounded-[2rem]">
        <div class="border-b border-slate-200 bg-[#fff6f8] px-4 py-4 text-center sm:px-6 sm:py-6">
            <div class="mx-auto inline-flex items-center gap-2 rounded-full bg-[#ab1155]/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.24em] text-[#ab1155] sm:px-4 sm:py-2 sm:text-xs sm:tracking-[0.3em]">
                <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">warning</span>
                Acesso adulto
            </div>
            <h2 class="headline mt-3 text-[1.55rem] font-extrabold leading-tight text-slate-950 sm:mt-4 sm:text-3xl">Material adulto sexualmente explicito</h2>
            <p class="mt-2 text-[13px] leading-6 text-slate-600 sm:mt-3 sm:text-sm sm:leading-relaxed">
                Este site e destinado exclusivamente a maiores de 18 anos. Antes de entrar, confirme que deseja prosseguir e escolha a categoria que quer explorar agora.
            </p>
        </div>

        <div class="px-4 py-4 sm:px-6 sm:py-6">
            <div class="max-h-[23vh] overflow-y-auto rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 text-[13px] leading-6 text-slate-700 sm:max-h-[34vh] sm:rounded-3xl sm:p-5 sm:text-sm sm:leading-7">
                <p>
                    Ao continuar, voce declara ser maior de idade e concorda em acessar conteudos, imagens, videos, opinioes e interacoes de natureza adulta dentro da plataforma.
                </p>
                <ul class="mt-3 list-disc space-y-1.5 pl-5 sm:mt-4 sm:space-y-2">
                    <li>Voce confirma que tem 18 anos ou mais.</li>
                    <li>Voce concorda com os Termos de Uso e com a Politica de Privacidade da SexyLua.</li>
                    <li>Voce entende que o acesso ao site depende da sua confirmacao e da categoria escolhida.</li>
                </ul>
            </div>

            <form action="/audience-gate" class="mt-4 sm:mt-6" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input name="accepted" type="hidden" value="1">
                <div class="text-center">
                    <p class="headline text-[1.35rem] font-extrabold text-slate-950 sm:text-2xl">O que voce gostaria de assistir?</p>
                    <p class="mt-1.5 text-[13px] text-slate-500 sm:mt-2 sm:text-sm">Escolha sua categoria inicial e siga direto para a exploracao.</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:mt-6 sm:gap-3 sm:grid-cols-4">
                    <?php foreach ($audienceOptions as $optionValue => $optionLabel): ?>
                        <button class="rounded-[1.15rem] bg-[#ff5a26] px-3 py-3 text-center text-[13px] font-bold text-white transition-transform hover:scale-[1.02] sm:rounded-2xl sm:px-4 sm:py-4 sm:text-sm" name="category" type="submit" value="<?= e($optionValue) ?>">
                            <?= e($optionLabel) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>

            <div class="mt-4 flex items-center justify-center gap-2.5 sm:mt-6 sm:gap-3">
                <span class="text-[13px] font-semibold text-slate-500 sm:text-sm">Nao concorda?</span>
                <a class="inline-flex min-h-[44px] items-center justify-center rounded-[1.15rem] border border-slate-300 px-5 text-[13px] font-bold text-slate-700 transition-colors hover:bg-slate-50 sm:min-h-[52px] sm:rounded-2xl sm:px-6 sm:text-sm" href="https://www.google.com">
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
