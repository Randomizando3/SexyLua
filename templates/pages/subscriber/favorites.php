<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SexyLua - Favoritos e Salvos</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,800&amp;family=Manrope:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
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
      .lunar-blur {
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
      }
      .hide-scrollbar::-webkit-scrollbar {
        display: none;
      }
      .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
    </style>
</head>
<body class="bg-surface font-body text-on-surface selection:bg-primary-container selection:text-white overflow-x-hidden">
<!-- TopNavBar Implementation -->
<header class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-[#D81B60] shadow-lg shadow-pink-900/20">
<div class="flex items-center gap-4">
<span class="text-2xl font-black text-white italic tracking-tighter">SexyLua</span>
</div>
<nav class="hidden lg:flex items-center gap-8">
<a class="font-headline tracking-wide text-sm font-bold uppercase text-white hover:text-pink-100 transition-colors hover:scale-105 transition-transform duration-200" href="#">Descobrir</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-white hover:text-pink-100 transition-colors hover:scale-105 transition-transform duration-200" href="#">Criadores</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-white hover:text-pink-100 transition-colors hover:scale-105 transition-transform duration-200" href="#">Ao Vivo</a>
<a class="font-headline tracking-wide text-sm font-bold uppercase text-white hover:text-pink-100 transition-colors hover:scale-105 transition-transform duration-200" href="#">Mensagens</a>
</nav>
<div class="flex items-center gap-4">
<button class="text-white hover:scale-105 transition-transform duration-200">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="text-white hover:scale-105 transition-transform duration-200">
<span class="material-symbols-outlined">brightness_3</span>
</button>
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20 cursor-pointer">
<img alt="User Profile Lunar Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBk5r8PK3uZSbm3tJQwppXKYqwoJFYeIhrdCy0llgJHjEo8k4AemeIUYkCZ-ybctRTJ06nHq2M0jqFvg-3C6zm0jm1th-EOr0xHiNmeV9wxrXbMVajMj2dXgs_jfWpsooXjl7qcjJHNw4w_s0PoTsicv0br9TaW3pI3Nd_n_18IFTZUMCqXSksIB_lrHtANzFX4-6poOKAnMsywqKCnBOpjC7DbAYYqPrNl-JkdYz_DYK-yRDv-1hkxhCLUJMZ6oOI82T2ie19L97g"/>
</div>
</div>
</header>
<div class="flex">
<!-- Persistent Sidebar (Menu Lunar) -->
<aside class="hidden md:flex fixed left-0 top-0 h-full w-64 bg-white dark:bg-slate-900 border-r border-surface-variant pt-24 pb-12 flex-col z-40 transition-all duration-300">
<div class="px-6 mb-8">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant opacity-60">Menu Lunar</p>
</div>
<nav class="flex-1 px-4 space-y-1">
<a class="flex items-center gap-4 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-primary transition-all group" href="#">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">home</span>
<span class="font-headline font-semibold text-sm">Início</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-primary transition-all group" href="#">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">auto_awesome</span>
<span class="font-headline font-semibold text-sm">Minhas Assinaturas</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-xl bg-primary-container text-white shadow-md shadow-pink-200/50 transition-all group" href="#">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform" style="font-variation-settings: 'FILL' 1;">favorite</span>
<span class="font-headline font-semibold text-sm">Favoritos</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-primary transition-all group" href="#">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">account_balance_wallet</span>
<span class="font-headline font-semibold text-sm">Carteira</span>
</a>
<a class="flex items-center gap-4 px-4 py-3 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-primary transition-all group" href="#">
<span class="material-symbols-outlined group-hover:scale-110 transition-transform">settings</span>
<span class="font-headline font-semibold text-sm">Configurações</span>
</a>
</nav>
<div class="px-8 mt-auto">
<div class="p-4 bg-secondary-fixed rounded-2xl">
<p class="text-xs font-bold text-on-secondary-fixed mb-1">Upgrade Pro</p>
<p class="text-[10px] text-on-secondary-fixed-variant leading-relaxed">Acesse conteúdos exclusivos e transmissões em 4K.</p>
</div>
</div>
</aside>
<!-- Main Content Area integrated with sidebar -->
<main class="pt-28 pb-32 px-6 w-full md:pl-72 max-w-[1600px] mx-auto">
<!-- Header Section -->
<section class="mb-12">
<h1 class="font-headline text-4xl md:text-5xl font-extrabold tracking-tight text-on-surface mb-2">Favoritos e Salvos</h1>
<p class="text-on-surface-variant font-medium">Sua curadoria celestial particular.</p>
</section>
<!-- Fases e Coleções (Horizontal Scroll) -->
<section class="mb-16">
<div class="flex justify-between items-end mb-6">
<h2 class="font-headline text-2xl font-bold text-on-surface">Fases e Coleções</h2>
<button class="text-primary font-bold text-sm flex items-center gap-1 hover:opacity-80 transition-opacity">
                        Ver Tudo <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<div class="flex gap-6 overflow-x-auto hide-scrollbar pb-4 -mx-2 px-2">
<!-- Folder Card 1 -->
<div class="flex-none w-64 group cursor-pointer">
<div class="relative h-80 rounded-lg overflow-hidden bg-surface-container-high transition-transform duration-300 group-hover:scale-[1.02]">
<img alt="Pasta Editorial" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA7hyvfvYLiVgjdsVSojgTpNoTCd4B5LL-02CYCOVLTUoom6VBD6Fh-GsyUGTXAlV7atOI3c0g52xmFXfX7XN1rFYNhzUNMS_aIaxEc55fXD2TbcZoJdhYjLid1RIjFnr2RdO034KrO7QmujJOdfmfI_S0HYRZs7cSbGuSUSRfRBY66B-B7PCcB850qdYIBmDJPdE-EsAfUEeJLQ7sDdGxRR2wU7DzVSUpHf6k67PFYGNKnjKKREdnsK5esR1AzaMtIJsFjjSJT5hg"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-6 left-6 text-white">
<h3 class="font-headline text-xl font-bold">Eclipse Rosa</h3>
<p class="text-xs opacity-80 uppercase tracking-widest mt-1">128 itens</p>
</div>
</div>
</div>
<!-- Folder Card 2 -->
<div class="flex-none w-64 group cursor-pointer">
<div class="relative h-80 rounded-lg overflow-hidden bg-surface-container-high transition-transform duration-300 group-hover:scale-[1.02]">
<img alt="Pasta Neon" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCZCrxJkq9K_FYJ9wTkv57B2hWuMaf39ffCts1Tj596RWyzuaJ2_k6E57iIWop13ICHG6YZKpINskJngtLPcxkgFJ1wCvDw1MA-NffcYd07Vgrb5_jy7Db_5tjntyu_5ZkCTagATvVs2OsHjX2cNolKlXL3QYTel1ygLGadmx9vtJ94bkjFo9WQljcq-s6JGKFB-qhz40A2rpiimpWT2Xb44E7YJ8IP-R5XdiT6pIfd5tuxT8MEP_jQCl8JWtxB_Bk17UzDgENOxuQ"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-6 left-6 text-white">
<h3 class="font-headline text-xl font-bold">Luz Cinzenta</h3>
<p class="text-xs opacity-80 uppercase tracking-widest mt-1">45 itens</p>
</div>
</div>
</div>
<!-- Folder Card 3 -->
<div class="flex-none w-64 group cursor-pointer">
<div class="relative h-80 rounded-lg overflow-hidden bg-surface-container-high transition-transform duration-300 group-hover:scale-[1.02]">
<img alt="Pasta Retratos" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAubVyWDWAZLa32XuZAfNPhnNHWMpaVPhVQVQLTESGnGg-RXneDKXJtPsoDcauDfRY_LZ94vMi0WlPPOkXj2043jzAYrVmDTNMit3idOCnrTioQbvdZF2Cjlr3wTBKQ8sjNv6TPxt-T97ocvG1A_Wpy0-mS2-7HTizLjD-8uFuK_n7yAynAUGNRrLgbXlioFNZFhZiwAnDVe7nA3EcAB050GVXq5EfHvFCsoptfb1zC64T88hIPsihCrtpTMtTLAQ5wSJCfC49yfPI"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-6 left-6 text-white">
<h3 class="font-headline text-xl font-bold">Sombra Lunar</h3>
<p class="text-xs opacity-80 uppercase tracking-widest mt-1">12 itens</p>
</div>
</div>
</div>
<!-- New Folder Action -->
<div class="flex-none w-64 group cursor-pointer">
<div class="h-80 rounded-lg border-2 border-dashed border-outline-variant flex flex-col items-center justify-center gap-4 text-outline hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-4xl">add_circle</span>
<span class="font-headline font-bold uppercase text-xs tracking-widest">Nova Coleção</span>
</div>
</div>
</div>
</section>
<!-- Conteúdo Recente (Grid) -->
<section>
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
<h2 class="font-headline text-2xl font-bold text-on-surface">Conteúdo Recente</h2>
<div class="flex gap-2">
<button class="px-4 py-2 bg-primary text-on-primary rounded-full text-xs font-bold uppercase tracking-wider">Tudo</button>
<button class="px-4 py-2 bg-surface-container-highest text-on-surface-variant rounded-full text-xs font-bold uppercase tracking-wider hover:bg-surface-variant transition-colors">Vídeos</button>
<button class="px-4 py-2 bg-surface-container-highest text-on-surface-variant rounded-full text-xs font-bold uppercase tracking-wider hover:bg-surface-variant transition-colors">Fotos</button>
</div>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Video Item Large -->
<div class="col-span-2 row-span-2 group relative rounded-lg overflow-hidden cursor-pointer shadow-xl">
<img alt="Video Principal" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB9P0xKvOPWU7F1BwYq7khKb5UqUyKnCr35Wxpy4mcGcAeINVlLgpdoTKUxceCLzBF3m5Y353aCbeuQtqn9M1tj_slsWwTDj4Je7pOiXXXlp_yu1EvM0fy6E9C9HH4Wdq5uIE1Fd3CnLpo9pnFbpP-ebrIpA6mW0ZzTciv-2cWxxfkMmAAJ0b3HBWqDKZyOUWRYsoLD5kmmIfQbmTWVXh_mpUTy1SHaksV_BhQ2UDggjpfQ-tQFQASnMUwrMDvwNV7q-Wj7nDGmVYM"/>
<div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="w-20 h-20 rounded-full bg-white/30 lunar-blur flex items-center justify-center border border-white/40 group-hover:scale-110 transition-transform duration-300">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
<div class="absolute top-4 right-4">
<span class="material-symbols-outlined text-white drop-shadow-md scale-125" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
<div class="absolute bottom-6 left-6 text-white">
<p class="font-headline font-bold text-lg">Essência Noturna</p>
<p class="text-xs opacity-80">Há 2 dias • 4:20</p>
</div>
</div>
<!-- Photo Item -->
<div class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer shadow-md">
<img alt="Foto Recente 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform" src="https://lh3.googleusercontent.com/aida-public/AB6AXuActfBOz4tcgf3OocjfqJKeUofYV8BqBC0_BLSxX1RF_3luNhFdO94DPySlSmyFhQR7HjPiQoKRLPuK8vojdLYqCRF97hRF7FSv7Hkvqcw4eKSzOfdfplsvj40bMdyYhwmg2zy2Vh-c-UnRvid5Y9SEdGrK0trGXIRtzQ0xOf3bqfpHvah_eaD_cbjBH-yJFW42omwJy1K9Y2LQPT3t-RQNg8j3SpZ7BJci4RGUP1dHJP7zRXHc2Z-h-UikbgmZG5FHnwbTtfetl6A"/>
<div class="absolute top-4 right-4">
<span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
</div>
<!-- Photo Item -->
<div class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer shadow-md">
<img alt="Foto Recente 2" class="w-full h-full object-cover group-hover:scale-105 transition-transform" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRX6lEdu6y1oBYhU_0gAVkI3RIkNKtw6gvAFHchdEirUzNmajoS4Msi81iaaGW0LY-CjBQ2qNVzOQF1doXoSQP93VovlEIkOZ5gJXYKMx5Ayo-l03zEy8_2jij17kAAwRP5W6HU1qk1aWcdyUN0r8gA5RItzCT5mlZFQDMoBBNcoV92Hhxp0n8fVv0wO9rECfO7avkOwC6HZ-vlmwW7c2JE8nvr6ScdQgBdj1_5yDz0MNK7mCvHqilC3zsAcMz-pH8x8s3Jw5sKR0"/>
<div class="absolute top-4 right-4 text-white">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
</div>
<!-- Video Item Small -->
<div class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer shadow-md">
<img alt="Video Recente 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBlOH5qgqL2tj2lTaN9xhVNLaZnLVG9ZSeU0GcPR5-nF1wFYHHIij_8HqeVhXk59Tp62Wssd25CXdKQFMT7ix06G8ck_ccUCs6S5_LRUO_dNLylHFXjE19fLMQVmz3F7fQk9_z8RbHKaXODoXXkIAN-VZp16e7F8E_HYD51dGZ7yGMKJgBEN_YpXhbdpdS7QtWLkIO9i8cbL9bSPl_kze0llNKUjEjMVyop529owlZNJsDAJh4N3dIyuE9Q0K6XKtP5H_OqxuiZtPI"/>
<div class="absolute inset-0 flex items-center justify-center bg-black/10">
<span class="material-symbols-outlined text-white text-3xl">play_circle</span>
</div>
<div class="absolute top-4 right-4 text-white">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
</div>
<!-- Photo Item -->
<div class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer shadow-md">
<img alt="Foto Recente 3" class="w-full h-full object-cover group-hover:scale-105 transition-transform" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBh6nTrwO5P618aXcvHu6Zashw5OA0XDbqdy7lzn6XI8Qh-Gw6t11j25gIe_x1W_C1aoH9JlDecLIRSkRX3UiIlycvxtBW_KHUO7XaOkRdGNk3zA2cnG5qcJARtvbxo7nP8kKP4x49uoMyL0itfoeWY0O7PiSj24Crl4bEEEiga0LfHxruUvSfXiQWE5hZCMfSr94aXaa_KlYalX7jTDFZrUfzXbW24-aypjLNJ7oS8Q3whjqyQZZBjYW_jwow9LsEiPIUpBGtk_AY"/>
<div class="absolute top-4 right-4 text-white">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
</div>
</div>
</section>
</main>
</div>
<!-- BottomNavBar Implementation (Mobile Only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl rounded-t-[3rem]" style="box-shadow: 0px -10px 30px rgba(216, 27, 96, 0.1);">
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] transition-transform duration-300 ease-out active:scale-110" href="#">
<span class="material-symbols-outlined">home</span>
<span class="font-manrope text-[10px] font-medium">Início</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] transition-transform duration-300 ease-out active:scale-110" href="#">
<span class="material-symbols-outlined">explore</span>
<span class="font-manrope text-[10px] font-medium">Explorar</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] transition-transform duration-300 ease-out active:scale-110" href="#">
<span class="material-symbols-outlined text-4xl text-[#D81B60]">add_circle</span>
<span class="font-manrope text-[10px] font-medium">Criar</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 hover:text-[#D81B60] transition-transform duration-300 ease-out active:scale-110" href="#">
<span class="material-symbols-outlined">account_balance_wallet</span>
<span class="font-manrope text-[10px] font-medium">Carteira</span>
</a>
<a class="flex flex-col items-center justify-center text-[#D81B60] relative after:content-[''] after:w-1 after:h-1 after:bg-[#D81B60] after:rounded-full after:mt-1 transition-transform duration-300 ease-out active:scale-110" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
<span class="font-manrope text-[10px] font-medium">Favoritos</span>
</a>
</nav>
<!-- Footer Implementation -->
<footer class="w-full py-12 px-8 flex flex-col md:flex-row justify-between items-center gap-6 bg-[#D81B60] mt-20 md:pl-72 z-30">
<div class="text-lg font-bold text-white">SexyLua</div>
<div class="flex flex-wrap justify-center gap-8">
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white hover:opacity-80 transition-opacity" href="#">Termos</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white hover:opacity-80 transition-opacity" href="#">Privacidade</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white hover:opacity-80 transition-opacity" href="#">Mapa da Lua</a>
<a class="font-manrope text-xs tracking-widest uppercase text-pink-100 hover:text-white hover:opacity-80 transition-opacity" href="#">Contato</a>
</div>
<p class="font-manrope text-xs tracking-widest uppercase text-white">© 2024 SexyLua Celestial Editorial. Todos os direitos reservados.</p>
</footer>
</body></html>