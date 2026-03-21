<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "secondary-fixed": "#ffd9e1",
              "tertiary-container": "#8658ca",
              "surface-container-high": "#e9e7e9",
              "primary-container": "#cc326e",
              "on-tertiary-fixed": "#280056",
              "on-error": "#ffffff",
              "inverse-surface": "#303032",
              "background": "#fbf9fb",
              "surface-variant": "#e3e2e4",
              "error": "#ba1a1a",
              "on-tertiary": "#ffffff",
              "secondary": "#ab2c5d",
              "surface": "#fbf9fb",
              "primary-fixed-dim": "#ffb1c5",
              "outline": "#8e6f74",
              "on-tertiary-fixed-variant": "#5a2a9c",
              "outline-variant": "#e3bdc3",
              "on-secondary-container": "#6e0034",
              "on-surface": "#1b1c1d",
              "tertiary": "#6c3eaf",
              "secondary-fixed-dim": "#ffb1c5",
              "inverse-primary": "#ffb1c5",
              "on-primary-fixed": "#3f001a",
              "on-surface-variant": "#5a4044",
              "surface-container": "#efedef",
              "on-primary": "#ffffff",
              "surface-dim": "#dbd9db",
              "surface-container-low": "#f5f3f5",
              "on-secondary-fixed": "#3f001b",
              "surface-container-highest": "#e3e2e4",
              "surface-bright": "#fbf9fb",
              "tertiary-fixed-dim": "#d7baff",
              "on-tertiary-container": "#fcf3ff",
              "tertiary-fixed": "#eddcff",
              "on-primary-container": "#fff2f4",
              "surface-container-lowest": "#ffffff",
              "secondary-container": "#fd6c9c",
              "surface-tint": "#b41b5c",
              "primary-fixed": "#ffd9e1",
              "on-secondary-fixed-variant": "#8b0e45",
              "on-error-container": "#93000a",
              "inverse-on-surface": "#f2f0f2",
              "primary": "#ab1155",
              "on-background": "#1b1c1d",
              "error-container": "#ffdad6",
              "on-secondary": "#ffffff",
              "on-primary-fixed-variant": "#8f0045"
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
      .lunar-glass {
        background: rgba(251, 249, 251, 0.7);
        backdrop-filter: blur(24px);
      }
      body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }
    </style>
</head>
<body class="bg-surface font-body text-on-surface selection:bg-primary-container selection:text-white">
<!-- Top Navigation Bar -->
<nav class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-[#D81B60] shadow-lg shadow-pink-900/10">
<div class="flex items-center gap-8">
<span class="text-2xl font-black text-white italic tracking-tighter font-headline">SexyLua</span>
<div class="hidden md:flex gap-6">
<a class="font-headline tracking-wide text-sm font-bold uppercase text-white border-b-2 border-white pb-1" href="#">Descobrir</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-pink-100/80 hover:text-white transition-colors" href="#">Criadores</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-pink-100/80 hover:text-white transition-colors" href="#">Ao Vivo</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-pink-100/80 hover:text-white transition-colors" href="#">Mensagens</a>
</div>
</div>
<div class="flex items-center gap-4 text-white">
<span class="material-symbols-outlined cursor-pointer hover:opacity-80 transition-opacity">notifications</span>
<span class="material-symbols-outlined cursor-pointer hover:opacity-80 transition-opacity">brightness_3</span>
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20">
<img alt="User Profile" class="w-full h-full object-cover" data-alt="User profile avatar looking celestial" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAyDFW5YILz-jdqovVFgjb60khEm7uaVU_AUDkK6EgiNvOTecvh_zVyJWn4VYz5rd_fld48nc8fTFwEExtWXu25Fo7p6aurJuHuJJnb4z8NB0x_IbDHErGjvQjAIQp_4jYTQiYIB_3dkTYFYw-UtFH0zvjS9561MkSxVNskXBYuzfcODROMA5d2pkCRUCtWE_xT1y82otlWANGm2aHdp0Clq4yhKXpKlrmD-czs1LVNsknOA2LOI0CyG7fcouW42_tKxD094PQfxAY"/>
</div>
</div>
</nav>
<div class="flex pt-16 flex-1">
<!-- Persistent Left Sidebar: Menu Lunar -->
<aside class="fixed left-0 top-16 bottom-0 w-64 bg-surface-container-lowest border-r border-outline-variant/30 hidden lg:flex flex-col p-6 z-40">
<div class="flex flex-col gap-2">
<p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/60 mb-2 px-4">Menu Lunar</p>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-bold transition-all group" href="#">
<span class="material-symbols-outlined transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' 1;">home</span>
<span class="font-headline text-sm">Início</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-low font-semibold transition-all group" href="#">
<span class="material-symbols-outlined transition-transform group-hover:scale-110">stars</span>
<span class="font-headline text-sm">Minhas Assinaturas</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-low font-semibold transition-all group" href="#">
<span class="material-symbols-outlined transition-transform group-hover:scale-110">favorite</span>
<span class="font-headline text-sm">Favoritos</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-low font-semibold transition-all group" href="#">
<span class="material-symbols-outlined transition-transform group-hover:scale-110">account_balance_wallet</span>
<span class="font-headline text-sm">Carteira</span>
</a>
<div class="h-px bg-outline-variant/30 my-4 mx-4"></div>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-low font-semibold transition-all group" href="#">
<span class="material-symbols-outlined transition-transform group-hover:scale-110">settings</span>
<span class="font-headline text-sm">Configurações</span>
</a>
</div>
<div class="mt-auto p-4 bg-primary rounded-2xl text-white space-y-3">
<p class="text-xs font-bold leading-tight">Torne-se Criador</p>
<p class="text-[10px] opacity-80">Brilhe como uma estrela e monetize seu conteúdo.</p>
<button class="w-full py-2 bg-white text-primary text-[10px] font-bold rounded-lg uppercase tracking-wider">Começar Agora</button>
</div>
</aside>
<main class="lg:ml-64 flex-1 pb-32 px-6 pt-12 max-w-7xl mx-auto w-full">
<!-- Editorial Header Section -->
<header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
<div class="space-y-2">
<p class="font-headline text-primary font-bold tracking-widest uppercase text-xs">Assinaturas Ativas</p>
<h1 class="font-headline text-5xl font-extrabold tracking-tighter text-on-surface">Minhas <span class="text-primary italic">Assinaturas</span></h1>
</div>
<div class="flex gap-3">
<button class="px-6 py-3 rounded-full bg-surface-container-highest text-on-surface font-bold text-sm hover:scale-105 transition-transform duration-200">Recentes</button>
<button class="px-6 py-3 rounded-full bg-primary text-on-primary font-bold text-sm hover:scale-105 transition-transform duration-200 shadow-lg shadow-primary/20">Explorar Novos</button>
</div>
</header>
<!-- Main Content Area: Bento Style Creators List -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<!-- Creator Card 1: New Phase -->
<div class="group relative flex flex-col bg-surface-container-lowest rounded-xl p-6 transition-all duration-300 hover:shadow-xl hover:shadow-primary/5 hover:translate-y-[-4px]">
<div class="relative w-full aspect-[4/5] mb-6 overflow-hidden rounded-lg">
<img alt="Creator 1" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="Elegant fashion creator in lunar lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDPSh3OpxXgTMz0R7G1m2VDlRe5pA6HDfYysZgK2sBoIMKEaEctBbNJhnuGyM63DaOQcuLi6SjZMsmVaYyFjounW3oCDWKfvmf2b3r2YnXi57aAus7gtInWMhXAowXD2wpiBP3fon6hrRXmjipJz_fy6Ocdvhg46VJ2EGo3kgYCwYrr0qb0a8fSHtqholZO_Y8ivEzfcY6Q82WBOT9NpfZmdF4m-WSZtCxjPAmFoVjELm88IkotzhS_svDnNSCOGnj95JcPT8VSMnY"/>
<div class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur-md rounded-full text-white text-xs font-bold uppercase tracking-wider">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">brightness_1</span>
<span>Lua Nova</span>
</div>
</div>
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline text-2xl font-bold tracking-tight">Luna Valente</h3>
<p class="text-on-surface-variant text-sm font-medium">@lunavalente</p>
</div>
<div class="flex flex-col items-end">
<span class="text-primary font-bold font-headline">R$ 49,90</span>
<span class="text-[10px] uppercase tracking-widest text-on-surface-variant/60">Renova em 12 Out</span>
</div>
</div>
<div class="mt-6 flex gap-2">
<button class="flex-1 py-3 rounded-full bg-surface-container-low text-on-surface font-bold text-xs hover:bg-surface-container-high transition-colors">PERFIL</button>
<button class="flex-1 py-3 rounded-full bg-primary-container text-white font-bold text-xs hover:scale-105 transition-transform">CONTEÚDO</button>
</div>
</div>
<!-- Creator Card 2: Waxing Phase -->
<div class="group relative flex flex-col bg-surface-container-low rounded-xl p-6 transition-all duration-300 hover:shadow-xl hover:shadow-primary/5 hover:translate-y-[-4px]">
<div class="relative w-full aspect-[4/5] mb-6 overflow-hidden rounded-lg">
<img alt="Creator 2" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="Artistic profile of a female model with celestial theme" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDCEoUZrfytu8OWeofYPKfYZcOc88zHI6PVZVBBx9sG1XwRkvCL3uYTn4nd0dTWXxyWHDV5LmppJNAlKKXxFUbQ4Yt5XLh05xZ-A413cBdpqNZSuIa9E2vdfWGgQtmwSyxirvUzSZZD6UD7XsJrkpuMLnJqbFaAzDov53vpxCStgLAC-xnfipsCSi9MZ8cfjZp7aTk04aljx7RLRrShdUJe39utIoHlF2mcBCSpixL1_WUuGss6uvjhRjLZPecuelaW8ATEjNiCetc"/>
<div class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1.5 bg-primary/80 backdrop-blur-md rounded-full text-white text-xs font-bold uppercase tracking-wider">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">brightness_2</span>
<span>Crescente</span>
</div>
</div>
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline text-2xl font-bold tracking-tight">Selene Moon</h3>
<p class="text-on-surface-variant text-sm font-medium">@selene_moon</p>
</div>
<div class="flex flex-col items-end">
<span class="text-primary font-bold font-headline">R$ 89,00</span>
<span class="text-[10px] uppercase tracking-widest text-on-surface-variant/60">Renova em 25 Out</span>
</div>
</div>
<div class="mt-6 flex gap-2">
<button class="flex-1 py-3 rounded-full bg-surface-container-lowest text-on-surface font-bold text-xs hover:bg-white transition-colors">PERFIL</button>
<button class="flex-1 py-3 rounded-full bg-primary-container text-white font-bold text-xs hover:scale-105 transition-transform">CONTEÚDO</button>
</div>
</div>
<!-- Creator Card 3: Midnight Phase -->
<div class="group relative flex flex-col bg-surface-container-lowest rounded-xl p-6 transition-all duration-300 hover:shadow-xl hover:shadow-primary/5 hover:translate-y-[-4px]">
<div class="relative w-full aspect-[4/5] mb-6 overflow-hidden rounded-lg">
<img alt="Creator 3" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="Portrait of a smiling creator under dim moon-like light" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1j4xZpQVO-VxUzHwv16b_Yh4fDj3ttN2QqVu-olx4LfvS44SVpC-zDnqCg_2egAHO5e2jhMi70XT5SwePhRiQdMDirIZKemcMo5q7ncRv0tZV1jnarUr8q3e_lTdcoqdv5VV08DrZc5uDetw68DQ_HLxdo4nd620eI6IL422ldWvVhcOIiMSfrObtw5aBmGBLubjGV3R3RL-wnocZ3maroCBVqFbGLEXVm_ZU40u8W__s80tuKb8q_y2zhmh4I3Z2Yb4IDxMgikA"/>
<div class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1.5 bg-black/40 backdrop-blur-md rounded-full text-white text-xs font-bold uppercase tracking-wider">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">nights_stay</span>
<span>Meia Noite</span>
</div>
</div>
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline text-2xl font-bold tracking-tight">Aurora Bloom</h3>
<p class="text-on-surface-variant text-sm font-medium">@aurora_bloom</p>
</div>
<div class="flex flex-col items-end">
<span class="text-primary font-bold font-headline">R$ 35,00</span>
<span class="text-[10px] uppercase tracking-widest text-on-surface-variant/60">Renova em 05 Nov</span>
</div>
</div>
<div class="mt-6 flex gap-2">
<button class="flex-1 py-3 rounded-full bg-surface-container-low text-on-surface font-bold text-xs hover:bg-surface-container-high transition-colors">PERFIL</button>
<button class="flex-1 py-3 rounded-full bg-primary-container text-white font-bold text-xs hover:scale-105 transition-transform">CONTEÚDO</button>
</div>
</div>
</div>
<!-- Lunar Stats Section -->
<section class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="md:col-span-1 bg-primary text-on-primary p-10 rounded-xl flex flex-col justify-between">
<div class="space-y-4">
<h2 class="font-headline text-3xl font-bold tracking-tighter leading-none">Status de<br/>Assinante VIP</h2>
<p class="text-pink-100/70 text-sm">Você está no topo 1% dos apoiadores deste mês. Suas fases lunares estão alinhadas.</p>
</div>
<div class="mt-8 flex items-center gap-4">
<div class="flex -space-x-4">
<div class="w-10 h-10 rounded-full border-2 border-primary bg-white overflow-hidden">
<img alt="Friend 1" class="w-full h-full object-cover" data-alt="Small avatar of a friend" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAzBi81seHPTaWNDaY3r-f4190_-XHGcLrqB7Evc5UhoceFZD37RL2ggrdId0qDiSQc1DP6t7NGnPLzJafxsNrfoUJ3bQYUmkCv67mRSqGgWfdvS2bZK7VviFFmMDbXOygCZNsR1Hze8XTY-OwRp-7N69wrnJmN8eWZsVywdyQYaCCT3xrSXgjP_fXNB6l_7BoIkoRgmuVMixLgUGwMJuGfNalB9pNPLJjxCXWhxNCbOZBl0MlejL_l3NdyajQ7p1pxOHyVdXp5LlY"/>
</div>
<div class="w-10 h-10 rounded-full border-2 border-primary bg-white overflow-hidden">
<img alt="Friend 2" class="w-full h-full object-cover" data-alt="Small avatar of another friend" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC-7BwT9yFszTjONo2ya7TpTK5G2l7PLWIJqCnjD_nyF55rf4sHBy90qOlM_M-hm31BIhrWy4nmvf3awkQkVOHk_canIeF9XB4vOLYLliNMEUwnKUdQhYr3gAeu-caydnIStEqd8MZLJtG9mmFQtFcIrEOssRAJqxcweIwughMvtJOqJAIHD8RwRFlZthW4YgzDcRbpoXq374j1Js3XNpFnC_ejZYCYVamGBK8Lz9mGyy8-cEFOXhqWJFNY_1AYgkHWy6KvU09drzs"/>
</div>
</div>
<span class="text-xs font-bold uppercase">+12 Amigos Ativos</span>
</div>
</div>
<div class="md:col-span-2 bg-surface-container-low p-10 rounded-xl relative overflow-hidden">
<div class="relative z-10 flex flex-col md:flex-row gap-8 items-center">
<div class="flex-1 space-y-6">
<h3 class="font-headline text-3xl font-extrabold tracking-tighter">Ciclo de Renovação</h3>
<div class="space-y-4">
<div class="flex justify-between items-center text-sm">
<span class="font-medium">Total Mensal</span>
<span class="font-bold">R$ 173,90</span>
</div>
<div class="w-full h-2 bg-outline-variant/20 rounded-full">
<div class="w-3/4 h-full bg-primary rounded-full"></div>
</div>
<div class="flex justify-between items-center text-[10px] uppercase tracking-widest text-on-surface-variant">
<span>Início do Mês</span>
<span>21 dias restantes</span>
</div>
</div>
</div>
<div class="w-48 h-48 bg-primary/5 rounded-full flex items-center justify-center border-4 border-dashed border-primary/20">
<div class="text-center">
<span class="block text-4xl font-black text-primary">03</span>
<span class="text-[10px] uppercase font-bold text-on-surface-variant">Criadores</span>
</div>
</div>
</div>
</div>
</section>
</main>
</div>
<!-- Mobile Navigation (Floating Bottom) -->
<nav class="lg:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-t-[3rem]" style="box-shadow: 0px -10px 30px rgba(216, 27, 96, 0.1);">
<a class="flex flex-col items-center justify-center text-slate-400 font-manrope text-[10px] font-medium" href="#">
<span class="material-symbols-outlined mb-1">wb_twilight</span>
<span>Home</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 font-manrope text-[10px] font-medium" href="#">
<span class="material-symbols-outlined mb-1">explore</span>
<span>Explorar</span>
</a>
<a class="flex flex-col items-center justify-center text-primary" href="#">
<span class="material-symbols-outlined text-4xl mb-1">add_circle</span>
</a>
<a class="flex flex-col items-center justify-center text-primary relative after:content-[''] after:w-1 after:h-1 after:bg-primary after:rounded-full after:mt-1 font-manrope text-[10px] font-medium" href="#">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: 'FILL' 1;">stars</span>
<span>Atividade</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 font-manrope text-[10px] font-medium" href="#">
<span class="material-symbols-outlined mb-1">account_circle</span>
<span>Perfil</span>
</a>
</nav>
<!-- Footer Section -->
<footer class="w-full py-12 px-8 flex flex-col md:flex-row justify-between items-center gap-6 bg-[#D81B60] mt-auto">
<span class="text-lg font-bold text-white font-headline">SexyLua</span>
<div class="flex gap-8">
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white transition-opacity" href="#">Termos</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white transition-opacity" href="#">Privacidade</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white transition-opacity" href="#">Mapa Lunar</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white transition-opacity" href="#">Contato</a>
</div>
<p class="font-manrope text-xs tracking-widest uppercase text-white opacity-60">© 2024 SexyLua Celestial Editorial. Todos os direitos reservados.</p>
</footer>
</body></html>