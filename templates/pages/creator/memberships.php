<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SexyLua - Gestão de Assinaturas (Criador)</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-container-highest": "#e3e2e4",
              "surface-container": "#efedef",
              "surface": "#fbf9fb",
              "on-surface": "#1b1c1d",
              "on-tertiary": "#ffffff",
              "on-primary": "#ffffff",
              "on-error-container": "#93000a",
              "primary-fixed-dim": "#ffb1c5",
              "secondary-container": "#fd6c9c",
              "on-tertiary-fixed": "#280056",
              "on-secondary-fixed": "#3f001b",
              "inverse-primary": "#ffb1c5",
              "tertiary-container": "#8658ca",
              "on-tertiary-container": "#fcf3ff",
              "on-secondary-fixed-variant": "#8b0e45",
              "on-background": "#1b1c1d",
              "inverse-surface": "#303032",
              "tertiary": "#6c3eaf",
              "surface-container-high": "#e9e7e9",
              "inverse-on-surface": "#f2f0f2",
              "surface-variant": "#e3e2e4",
              "surface-bright": "#fbf9fb",
              "background": "#fbf9fb",
              "outline": "#8e6f74",
              "surface-container-lowest": "#ffffff",
              "error": "#ba1a1a",
              "surface-tint": "#b41b5c",
              "surface-dim": "#dbd9db",
              "on-surface-variant": "#5a4044",
              "on-primary-container": "#fff2f4",
              "tertiary-fixed": "#eddcff",
              "error-container": "#ffdad6",
              "secondary-fixed-dim": "#ffb1c5",
              "secondary-fixed": "#ffd9e1",
              "primary-fixed": "#ffd9e1",
              "on-error": "#ffffff",
              "surface-container-low": "#f5f3f5",
              "on-tertiary-fixed-variant": "#5a2a9c",
              "tertiary-fixed-dim": "#d7baff",
              "primary": "#ab1155",
              "secondary": "#ab2c5d",
              "primary-container": "#cc326e",
              "on-primary-fixed-variant": "#8f0045",
              "outline-variant": "#e3bdc3",
              "on-secondary-container": "#6e0034",
              "on-secondary": "#ffffff",
              "on-primary-fixed": "#3f001a"
            },
            fontFamily: {
              "headline": ["Plus Jakarta Sans"],
              "body": ["Manrope"],
              "label": ["Manrope"]
            },
            borderRadius: {"DEFAULT": "1rem", "lg": "2rem", "xl": "3rem", "full": "9999px"},
          },
        },
      }
    </script>
<style>
      .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      }
      .tonal-shift {
        background-color: #f5f3f5;
      }
      .lunar-glass {
        background: rgba(251, 249, 251, 0.7);
        backdrop-filter: blur(24px);
      }
      .signature-glow {
        background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%);
      }
      body {
        background-color: #fbf9fb;
        color: #1b1c1d;
        font-family: 'Manrope', sans-serif;
      }
    </style>
</head>
<body class="min-h-screen">
<!-- TopNavBar (Shared Component style) -->
<header class="fixed top-0 w-full h-16 flex items-center justify-between px-6 bg-[#D81B60] text-white z-[60] shadow-lg shadow-[#D81B60]/20 font-['Plus_Jakarta_Sans'] font-bold tracking-wide">
<div class="flex items-center gap-4">
<h1 class="text-2xl font-black text-white">SexyLua</h1>
<span class="text-xs opacity-80 border-l border-white/20 pl-4 py-1 uppercase tracking-widest hidden md:block">Creator Studio</span>
</div>
<div class="flex items-center gap-6">
<div class="relative hidden md:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/60 text-lg" data-icon="search">search</span>
<input class="bg-white/10 border-none rounded-full py-1.5 pl-10 pr-4 focus:ring-1 focus:ring-white/30 text-xs w-64 placeholder-white/60 text-white" placeholder="Buscar assinantes..." type="text"/>
</div>
<div class="flex items-center gap-3">
<button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors scale-95 active:scale-90">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors scale-95 active:scale-90">
<span class="material-symbols-outlined" data-icon="mail">mail</span>
</button>
<div class="w-px h-6 bg-white/20 mx-1"></div>
<img alt="Avatar do Criador" class="w-8 h-8 rounded-full object-cover border border-white/20" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCFm7hyGuAgP3nCsmrA7K8R5hRiT4ZMAn2b4HmW_WevF3bkkSFP2VpdCsz766O_FrpEy6dJgs2v9ZCjFXvW5dP4F7LWjW_KyP0FSDh2t9Ud7C-cKpvhA6mr8YZUr5S0G0tXdZXNalbrxLUKJImd-BAR6WvRlBNeNJ9lnNISfWtuIoy-vekPTAPBIMf3hBHY7dJg-mA0P4f115Iu-JqtY6Uj8DfB_-8X5l7cxPaBuKYP_cuitehzzltzpDf3-UDyeysUAt2-TGgqpjE"/>
</div>
</div>
</header>
<!-- SideNavBar -->
<aside class="fixed left-0 top-0 h-full flex flex-col p-6 h-screen w-64 border-r-0 rounded-r-[3rem] bg-[#f5f3f5] dark:bg-slate-900 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] z-50 pt-20">
<nav class="flex-1 space-y-2">
<a class="flex items-center gap-4 px-4 py-3 rounded-full hover:bg-[#ffffff]/50 transition-all duration-300 text-slate-500 dark:text-slate-400 font-medium" href="#">
<span class="material-symbols-outlined" data-icon="movie">movie</span>
<span class="font-['Plus_Jakarta_Sans'] text-sm">Conteúdo</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-full hover:bg-[#ffffff]/50 transition-all duration-300 text-slate-500 dark:text-slate-400 font-medium" href="#">
<span class="material-symbols-outlined" data-icon="live_tv">live_tv</span>
<span class="font-['Plus_Jakarta_Sans'] text-sm">Ao Vivo</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-full bg-[#ffffff]/50 text-[#ab1155] dark:text-[#d81b60] font-bold relative after:content-[''] after:absolute after:-bottom-2 after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-[#ab1155] after:rounded-full" href="#">
<span class="material-symbols-outlined" data-icon="group">group</span>
<span class="font-['Plus_Jakarta_Sans'] text-sm">Assinantes</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-full hover:bg-[#ffffff]/50 transition-all duration-300 text-slate-500 dark:text-slate-400 font-medium" href="#">
<span class="material-symbols-outlined" data-icon="account_balance_wallet">account_balance_wallet</span>
<span class="font-['Plus_Jakarta_Sans'] text-sm">Carteira</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-full hover:bg-[#ffffff]/50 transition-all duration-300 text-slate-500 dark:text-slate-400 font-medium" href="#">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
<span class="font-['Plus_Jakarta_Sans'] text-sm">Configurações</span>
</a>
</nav>
<div class="mt-auto">
<button class="w-full signature-glow text-white font-bold py-4 rounded-full flex items-center justify-center gap-2 hover:scale-105 transition-transform ease-out shadow-lg"><span class="material-symbols-outlined" data-icon="sensors">sensors</span>
                Entrar ao Vivo</button>
<div class="flex items-center gap-3 mt-8 p-2">
<img class="w-10 h-10 rounded-full object-cover" data-alt="Creator profile avatar with pink lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCFm7hyGuAgP3nCsmrA7K8R5hRiT4ZMAn2b4HmW_WevF3bkkSFP2VpdCsz766O_FrpEy6dJgs2v9ZCjFXvW5dP4F7LWjW_KyP0FSDh2t9Ud7C-cKpvhA6mr8YZUr5S0G0tXdZXNalbrxLUKJImd-BAR6WvRlBNeNJ9lnNISfWtuIoy-vekPTAPBIMf3hBHY7dJg-mA0P4f115Iu-JqtY6Uj8DfB_-8X5l7cxPaBuKYP_cuitehzzltzpDf3-UDyeysUAt2-TGgqpjE"/>
<div>
<p class="text-sm font-bold text-on-surface">SexyLua</p>
<p class="text-[10px] text-slate-500">Pro Creator</p>
</div>
</div>
</div>
</aside>
<!-- Main Canvas -->
<main class="ml-64 min-h-screen pt-16">
<!-- Content -->
<div class="px-12 py-8 space-y-12">
<!-- Header Editorial Section -->
<section class="relative">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
<div>
<h2 class="font-headline text-5xl font-extrabold tracking-tight text-on-surface">Gestão de <span class="text-primary italic">Assinaturas (Criador)</span></h2>
<p class="font-body text-slate-500 mt-4 max-w-md">Cultive sua constelação. Acompanhe o crescimento da sua comunidade e a evolução de seus seguidores mais fiéis.</p>
</div>
<div class="flex gap-4">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex flex-col items-center">
<span class="text-primary font-headline text-2xl font-bold">1.240</span>
<span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">Assinantes Ativos</span>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex flex-col items-center">
<span class="text-primary font-headline text-2xl font-bold">R$ 12.450</span>
<span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">Mensal Estimado</span>
</div>
</div>
</div>
</section>
<!-- Filters -->
<section class="flex flex-wrap items-center gap-4">
<button class="px-6 py-2 rounded-full bg-primary text-white text-sm font-bold shadow-md">Todos</button>
<button class="px-6 py-2 rounded-full bg-surface-container-low text-slate-600 text-sm font-semibold hover:bg-surface-container-high transition-colors">Novos (24h)</button>
<button class="px-6 py-2 rounded-full bg-surface-container-low text-slate-600 text-sm font-semibold hover:bg-surface-container-high transition-colors">Renovações Próximas</button>
<button class="px-6 py-2 rounded-full bg-surface-container-low text-slate-600 text-sm font-semibold hover:bg-surface-container-high transition-colors">Super Fans</button>
<div class="ml-auto flex items-center gap-2 text-slate-400">
<span class="text-xs font-bold uppercase tracking-tighter">Ordenar por:</span>
<select class="bg-transparent border-none text-sm font-bold text-on-surface focus:ring-0 cursor-pointer">
<option>Recentes</option>
<option>Valor Mensal</option>
<option>Duração</option>
</select>
</div>
</section>
<!-- Subscriber Bento Grid -->
<section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
<!-- Subscriber Card 1 -->
<div class="group bg-surface-container-lowest p-8 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex items-center gap-6 hover:scale-[1.02] transition-transform duration-300">
<div class="relative">
<img class="w-24 h-24 rounded-full object-cover border-4 border-surface shadow-lg" data-alt="User profile photo in soft lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuRsB8QrJ013WJDvou0cWP-KH-_pA6yTLFJldUkObKv9DNLsdyhlKw1TmI9-iQLr88-NGrhiZZxxkRYyzdOlfa4TeGjDgW6qu-wWGLHzeKq-XghLB0l7skfeiZ2vf6WJoXjcHCsT5S6YOfvIY16f9hVzacX2Hi5dDFe1wmQreFc6YqQflq-4ZPVF4fc-6-T_jozfjNf1yTId3l6KJptchk-hwFvRRsmVDv10sObPYukP0LSe_K65gAeYbiaj88FtqFxJCSu1RHcSs"/>
<div class="absolute -bottom-1 -right-1 bg-primary p-2 rounded-full text-white flex items-center justify-center">
<span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
</div>
<div class="flex-1">
<div class="flex items-center justify-between mb-2">
<h3 class="font-headline text-xl font-bold text-on-surface">Gabriel Martins</h3>
<span class="px-3 py-1 rounded-full bg-primary-container/10 text-primary text-[10px] font-bold uppercase tracking-widest">Full Moon</span>
</div>
<div class="grid grid-cols-2 gap-4 mt-4">
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Duração</p>
<p class="text-sm font-semibold text-on-surface">14 meses</p>
</div>
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Contribuição</p>
<p class="text-sm font-bold text-primary">R$ 49,90 / mês</p>
</div>
</div>
</div>
<button class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</div>
<!-- Subscriber Card 2 -->
<div class="group bg-surface-container-lowest p-8 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex items-center gap-6 hover:scale-[1.02] transition-transform duration-300 border-l-4 border-primary">
<div class="relative">
<img class="w-24 h-24 rounded-full object-cover border-4 border-surface shadow-lg" data-alt="Woman with smile and pink lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAP_oADdJMH35qThLnRGZjpARZgZinESpXl5NyewQC0DJypFA7nJrIXr1tgns1YniMriKSZAZ-3Ssa7lq9R_KBoIptTjyuwVC7pcXCr7QGE52CUjwbpvgs_z_KFlxhIk-B_j9NG2AuJ5vmPoRRZVDoK7dMcC_kKNZonnGupJju4jNRDpdtYsw6P_J6rksvpMAkH2_BlaQHC1B4gfKUH5IHFwX_pa7kC7OgcSqbnopNvA0OjOWkRqkyTrWOHfVNqh_g4Z8_xQi-7wh8"/>
<div class="absolute -bottom-1 -right-1 bg-secondary p-2 rounded-full text-white flex items-center justify-center">
<span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
</div>
<div class="flex-1">
<div class="flex items-center justify-between mb-2">
<h3 class="font-headline text-xl font-bold text-on-surface">Mariana Luz</h3>
<span class="px-3 py-1 rounded-full bg-secondary-container/10 text-secondary text-[10px] font-bold uppercase tracking-widest">Eclipse Gold</span>
</div>
<div class="grid grid-cols-2 gap-4 mt-4">
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Duração</p>
<p class="text-sm font-semibold text-on-surface">3 dias</p>
</div>
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Contribuição</p>
<p class="text-sm font-bold text-primary">R$ 99,90 / mês</p>
</div>
</div>
</div>
<button class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</div>
<!-- Subscriber Card 3 -->
<div class="group bg-surface-container-lowest p-8 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex items-center gap-6 hover:scale-[1.02] transition-transform duration-300">
<div class="relative">
<img class="w-24 h-24 rounded-full object-cover border-4 border-surface shadow-lg" data-alt="Man in studio profile picture" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVw9sHGbUVk5mPe7FwW2OWBflbGxna4Lwi_bENQHwJhWAVrHu6zaEuKe6YJBJhC_lWeMGg2aYeoEEdIKwk-HxRWgrmK_kcKspqoLnAyLMQnnJ1zzjimAx6vSVSsjpiOlIu_yIGNpkzIk8jbqZYtjMblkyyDVHOjFqQlSAF9lHYBe1Xfohi_bUE7RI8SXZ7FttLJTYVjSuzsp_iC4FsTTaNTVtNeljprpNuh0j1PIVyiOy7GPwyZr5vb3EGPZk6o0_4Ys-9KUzmqP8"/>
<div class="absolute -bottom-1 -right-1 bg-slate-300 p-2 rounded-full text-white flex items-center justify-center">
<span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">nights_stay</span>
</div>
</div>
<div class="flex-1">
<div class="flex items-center justify-between mb-2">
<h3 class="font-headline text-xl font-bold text-on-surface">Ricardo Silva</h3>
<span class="px-3 py-1 rounded-full bg-surface-container-highest text-slate-500 text-[10px] font-bold uppercase tracking-widest">New Moon</span>
</div>
<div class="grid grid-cols-2 gap-4 mt-4">
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Duração</p>
<p class="text-sm font-semibold text-on-surface">6 meses</p>
</div>
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Contribuição</p>
<p class="text-sm font-bold text-primary">R$ 19,90 / mês</p>
</div>
</div>
</div>
<button class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</div>
<!-- Subscriber Card 4 -->
<div class="group bg-surface-container-lowest p-8 rounded-xl shadow-[0px_20px_40px_rgba(27,28,29,0.06)] flex items-center gap-6 hover:scale-[1.02] transition-transform duration-300">
<div class="relative">
<div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center border-4 border-surface shadow-lg">
<span class="text-3xl font-bold text-primary/40">AP</span>
</div>
</div>
<div class="flex-1">
<div class="flex items-center justify-between mb-2">
<h3 class="font-headline text-xl font-bold text-on-surface">Ana Paula</h3>
<span class="px-3 py-1 rounded-full bg-primary-container/10 text-primary text-[10px] font-bold uppercase tracking-widest">Full Moon</span>
</div>
<div class="grid grid-cols-2 gap-4 mt-4">
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Duração</p>
<p class="text-sm font-semibold text-on-surface">24 meses</p>
</div>
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Contribuição</p>
<p class="text-sm font-bold text-primary">R$ 49,90 / mês</p>
</div>
</div>
</div>
<button class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</div>
</section>
<!-- Bottom Stats & Footer -->
<section class="mt-20 pt-10 border-t border-outline-variant/10">
<div class="flex flex-col md:flex-row justify-between items-center gap-8">
<div class="flex gap-12">
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Crescimento Mensal</p>
<div class="flex items-center gap-2 mt-2">
<span class="text-2xl font-bold text-on-surface">+12%</span>
<span class="material-symbols-outlined text-green-500 text-sm" data-icon="trending_up">trending_up</span>
</div>
</div>
<div>
<p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Taxa de Retenção</p>
<div class="flex items-center gap-2 mt-2">
<span class="text-2xl font-bold text-on-surface">94%</span>
<span class="material-symbols-outlined text-primary text-sm" data-icon="verified">verified</span>
</div>
</div>
</div>
<div class="flex items-center gap-4">
<span class="text-xs text-slate-400 font-medium italic">"Sua luz brilha mais forte com cada novo seguidor."</span>
<div class="w-px h-8 bg-outline-variant/20"></div>
<button class="px-8 py-3 rounded-full bg-surface-container-high text-on-surface text-sm font-bold hover:bg-primary hover:text-white transition-all">Ver Relatório Completo</button>
</div>
</div>
</section>
</div>
</main>
</body></html>