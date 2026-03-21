<!DOCTYPE html>

<html class="light" lang="pt-br"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(24px);
      }
      body {
        font-family: 'Manrope', sans-serif;
        background-color: #fbf9fb;
      }
      h1, h2, h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
      }
    </style>
</head>
<body class="text-on-surface">
<!-- Top Navigation Bar -->
<header class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-[#D81B60] shadow-lg shadow-pink-900/20 text-white">
<div class="text-2xl font-black italic tracking-tighter">SexyLua</div>
<nav class="hidden md:flex items-center gap-8 font-headline tracking-wide text-sm font-bold uppercase">
<a class="text-pink-100/80 hover:text-white transition-colors" href="#">Descobrir</a>
<a class="text-pink-100/80 hover:text-white transition-colors" href="#">Criadores</a>
<a class="text-pink-100/80 hover:text-white transition-colors" href="#">Ao Vivo</a>
<a class="text-pink-100/80 hover:text-white transition-colors" href="#">Mensagens</a>
</nav>
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-white hover:scale-105 transition-transform cursor-pointer" data-icon="notifications">notifications</span>
<span class="material-symbols-outlined text-white hover:scale-105 transition-transform cursor-pointer" data-icon="brightness_3">brightness_3</span>
<div class="w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border-2 border-white/20">
<img alt="User Profile Lunar Avatar" class="w-full h-full object-cover" data-alt="Female profile avatar with artistic lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDcwA_TpbkR0_nAyuAAj4VfMJywD9GBcg2u04BY_-qxegFKHxbt8RoDnwy_vt9m5vxuFeI7E9S-d3rE5N4D7ndVmDI-CNV1aa5DN1ZiO2BN9uzJ7oKmUEZzWXXT7OFieDRMEzWiUVN2qR-eFtO48t5WlNINJgk27CeCmjtp2x4l9nMcmBmSA-bg2RpLt05sIFKGpBga2YL8OcE6yiIuk-apzWOZkQ0r-NMWgl9ZL2a5SVPHu7DRuor-cwYlC0X02GYIYJRzG7ozWyk"/>
</div>
</div>
</header>
<div class="flex min-h-screen pt-20">
<!-- Persistent Left Sidebar (Menu Lunar) -->
<aside class="hidden md:flex flex-col w-64 bg-surface-container-lowest border-r border-outline-variant/10 fixed h-[calc(100vh-80px)] overflow-y-auto">
<div class="p-6">
<p class="text-[10px] font-black tracking-widest text-primary uppercase mb-6">Menu Lunar</p>
<nav class="space-y-1">
<a class="flex items-center gap-4 p-3 rounded-lg hover:bg-primary/5 transition-colors group" href="#">
<span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary" data-icon="home">home</span>
<span class="font-bold text-sm text-on-surface-variant group-hover:text-on-surface">Início</span>
</a>
<a class="flex items-center gap-4 p-3 rounded-lg hover:bg-primary/5 transition-colors group" href="#">
<span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary" data-icon="subscriptions">subscriptions</span>
<span class="font-bold text-sm text-on-surface-variant group-hover:text-on-surface">Minhas Assinaturas</span>
</a>
<a class="flex items-center gap-4 p-3 rounded-lg hover:bg-primary/5 transition-colors group" href="#">
<span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary" data-icon="favorite">favorite</span>
<span class="font-bold text-sm text-on-surface-variant group-hover:text-on-surface">Favoritos</span>
</a>
<a class="flex items-center gap-4 p-3 rounded-lg bg-primary/10 transition-colors group" href="#">
<span class="material-symbols-outlined text-primary" data-icon="account_balance_wallet" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
<span class="font-bold text-sm text-primary">Carteira</span>
</a>
<a class="flex items-center gap-4 p-3 rounded-lg hover:bg-primary/5 transition-colors group" href="#">
<span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary" data-icon="settings">settings</span>
<span class="font-bold text-sm text-on-surface-variant group-hover:text-on-surface">Configurações</span>
</a>
</nav>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 md:ml-64 pb-32 px-4 md:px-8 max-w-7xl mx-auto w-full">
<!-- Hero Balance Section -->
<section class="mt-8 mb-12">
<div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary to-primary-container p-8 md:p-12 text-white flex flex-col md:flex-row justify-between items-center gap-8 shadow-2xl">
<div class="relative z-10 text-center md:text-left">
<p class="font-label text-pink-100 text-sm tracking-widest uppercase mb-2">Saldo Celestial</p>
<div class="flex items-center justify-center md:justify-start gap-4">
<span class="text-5xl md:text-7xl font-black font-headline tracking-tighter">1.250</span>
<span class="material-symbols-outlined text-4xl text-pink-200" data-icon="stars" style="font-variation-settings: 'FILL' 1;">stars</span>
</div>
<p class="mt-4 text-pink-100 font-medium">Tokens disponíveis para apoiar criadores</p>
</div>
<div class="relative z-10 flex flex-col items-center bg-white/10 backdrop-blur-md p-6 rounded-lg border border-white/20">
<span class="material-symbols-outlined text-4xl mb-2" data-icon="account_balance_wallet">account_balance_wallet</span>
<p class="font-bold text-lg mb-4">Carteira Digital</p>
<button class="bg-white text-primary px-8 py-3 rounded-full font-bold hover:scale-105 transition-transform active:scale-95">
                            RECARREGAR AGORA
                        </button>
</div>
<!-- Abstract Lunar background element -->
<div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
<div class="absolute top-0 left-1/2 w-40 h-40 bg-pink-400/20 rounded-full blur-2xl"></div>
</div>
</section>
<!-- Token Packages Grid -->
<section class="mb-16">
<h2 class="text-3xl font-black font-headline mb-8 flex items-center gap-3">
<span class="material-symbols-outlined text-primary" data-icon="auto_awesome">auto_awesome</span>
                    Pacotes de Energia Lunar
                </h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Package 1: Crescente -->
<div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/10 flex flex-col items-center text-center transition-all hover:shadow-xl hover:-translate-y-2">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-4xl text-primary" data-icon="brightness_2">brightness_2</span>
</div>
<h3 class="text-xl font-bold font-headline mb-2">Crescente</h3>
<p class="text-on-surface-variant text-sm mb-6">Ideal para pequenos mimos e reações rápidas.</p>
<div class="text-3xl font-black text-primary mb-8">R$ 49,90</div>
<div class="bg-surface-container-high w-full py-3 rounded-lg mb-8 font-bold">500 Tokens</div>
<button class="w-full bg-surface-container-highest text-on-surface py-4 rounded-full font-bold hover:bg-primary hover:text-white transition-colors">SELECIONAR</button>
</div>
<!-- Package 2: Lua Cheia (Featured) -->
<div class="bg-white p-8 rounded-xl shadow-2xl border-2 border-primary/20 flex flex-col items-center text-center relative overflow-hidden transition-all hover:scale-105">
<div class="absolute top-4 right-4 bg-primary text-white text-[10px] font-black px-3 py-1 rounded-full">POPULAR</div>
<div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center mb-6 shadow-lg shadow-primary/20">
<span class="material-symbols-outlined text-5xl text-primary" data-icon="brightness_5" style="font-variation-settings: 'FILL' 1;">brightness_5</span>
</div>
<h3 class="text-2xl font-black font-headline mb-2">Lua Cheia</h3>
<p class="text-on-surface-variant text-sm mb-6">O equilíbrio perfeito para assinantes ativos.</p>
<div class="text-4xl font-black text-primary mb-8">R$ 119,90</div>
<div class="bg-primary-container/10 text-primary w-full py-3 rounded-lg mb-8 font-black">1.500 Tokens</div>
<button class="w-full bg-primary text-white py-4 rounded-full font-bold shadow-lg shadow-primary/30 active:scale-95 transition-all">COMPRAR AGORA</button>
</div>
<!-- Package 3: Eclipse Total -->
<div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/10 flex flex-col items-center text-center transition-all hover:shadow-xl hover:-translate-y-2">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-4xl text-primary" data-icon="wb_twilight">wb_twilight</span>
</div>
<h3 class="text-xl font-bold font-headline mb-2">Eclipse Total</h3>
<p class="text-on-surface-variant text-sm mb-6">Poder máximo para experiências exclusivas.</p>
<div class="text-3xl font-black text-primary mb-8">R$ 299,90</div>
<div class="bg-surface-container-high w-full py-3 rounded-lg mb-8 font-bold">5.000 Tokens</div>
<button class="w-full bg-surface-container-highest text-on-surface py-4 rounded-full font-bold hover:bg-primary hover:text-white transition-colors">SELECIONAR</button>
</div>
</div>
</section>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
<!-- Payment Methods -->
<section>
<h2 class="text-2xl font-black font-headline mb-6">Métodos de Pagamento</h2>
<div class="bg-surface-container-low rounded-xl p-8 space-y-4">
<div class="flex items-center justify-between p-4 bg-surface-container-lowest rounded-lg">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary" data-icon="credit_card">credit_card</span>
<div>
<p class="font-bold">Cartão de Crédito</p>
<p class="text-xs text-on-surface-variant">Final 4429 • Expira 10/26</p>
</div>
</div>
<span class="material-symbols-outlined text-primary" data-icon="check_circle" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
<div class="flex items-center justify-between p-4 bg-surface-container-lowest/50 rounded-lg">
<div class="flex items-center gap-4 opacity-70">
<span class="material-symbols-outlined" data-icon="account_balance">account_balance</span>
<div>
<p class="font-bold">PIX</p>
<p class="text-xs">Pagamento Instantâneo</p>
</div>
</div>
<button class="text-primary text-xs font-bold underline">CONFIGURAR</button>
</div>
<button class="w-full border-2 border-dashed border-outline-variant py-4 rounded-lg text-on-surface-variant font-bold flex items-center justify-center gap-2 hover:bg-white transition-colors">
<span class="material-symbols-outlined" data-icon="add">add</span>
                            Adicionar Novo Método
                        </button>
</div>
</section>
<!-- History Section -->
<section>
<div class="flex justify-between items-center mb-6">
<h2 class="text-2xl font-black font-headline">Histórico de Fases</h2>
<button class="text-primary font-bold text-sm">VER TUDO</button>
</div>
<div class="space-y-4">
<!-- History Item 1 -->
<div class="flex items-center justify-between p-4 border-b border-outline-variant/10">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center">
<span class="material-symbols-outlined text-primary" data-icon="add_shopping_cart">add_shopping_cart</span>
</div>
<div>
<p class="font-bold">Recarga: Lua Cheia</p>
<p class="text-xs text-on-surface-variant">15 de Mai, 2024 • 14:30</p>
</div>
</div>
<div class="text-right">
<p class="font-black text-green-600">+1.500</p>
<p class="text-[10px] uppercase font-bold text-on-surface-variant">Tokens</p>
</div>
</div>
<!-- History Item 2 -->
<div class="flex items-center justify-between p-4 border-b border-outline-variant/10">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-surface-container-high overflow-hidden">
<img alt="Creator Profile" class="w-full h-full object-cover" data-alt="Close-up of a smiling woman with purple hair" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfjFQHptvSp3MCg5suGoJbECb8XXIbsm-qEnHV1KpDNmSOEjYJd2Z672gH8s_VtG93nJ6wcpHOU5VW9SMzJu1fwnTh6IGPfYN6PYyKM3_wjcdzh-6rd9guC4uGfObismTRi2qAti9mbjQ7TUQn8wP8M-3tOYRi_5lcYsHqNwDN5-EafNttSYT1HdnJ6vlyM7gyw3btvof95agXJ7XD-iGU1MU9oamIWuXoZPjCO0Cx-Ek-0DLRLmc4w58cDpGRSKwUKZ0zDxGH_vA"/>
</div>
<div>
<p class="font-bold">Presente para @LunaStar</p>
<p class="text-xs text-on-surface-variant">12 de Mai, 2024 • 22:15</p>
</div>
</div>
<div class="text-right">
<p class="font-black text-primary">-250</p>
<p class="text-[10px] uppercase font-bold text-on-surface-variant">Tokens</p>
</div>
</div>
<!-- History Item 3 -->
<div class="flex items-center justify-between p-4 border-b border-outline-variant/10">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-surface-container-high overflow-hidden">
<img alt="Creator Profile" class="w-full h-full object-cover" data-alt="Portrait of a young creative professional" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDv_4nk7LU1mcyXNzpMw8nPKyDm0SpbFirwnxs6-ug9OANpsCJ1nx_3UI0degSAdmtCNaZ-givJ5zBPvSypLJb1twQP6-xSakrF061ythzzBGkDh0PTtOXdmbJayJovr7lowsPG-9iycEfkSpuMFrhBtlH7agoN5rG3Q87Y2wxuH5l22-Qym0HZ1BqAHaUbHgXLPb1ilkXePZgBXgWap-wBbV-AyJcXcnXWAT85DKEm-_ec2frjJphYacCEVBIDsgZhysTNSEG9IpA"/>
</div>
<div>
<p class="font-bold">Acesso Conteúdo VIP @Solaris</p>
<p class="text-xs text-on-surface-variant">10 de Mai, 2024 • 09:12</p>
</div>
</div>
<div class="text-right">
<p class="font-black text-primary">-500</p>
<p class="text-[10px] uppercase font-bold text-on-surface-variant">Tokens</p>
</div>
</div>
</div>
</section>
</div>
</main>
</div>
<!-- Footer Component -->
<footer class="w-full py-12 px-8 flex flex-col md:flex-row justify-between items-center gap-6 bg-[#D81B60] text-white font-body">
<div class="text-lg font-bold">SexyLua</div>
<div class="flex gap-6 font-manrope text-xs tracking-widest uppercase">
<a class="text-pink-100 hover:text-white transition-opacity" href="#">Termos</a>
<a class="text-pink-100 hover:text-white transition-opacity" href="#">Privacidade</a>
<a class="text-pink-100 hover:text-white transition-opacity underline font-bold" href="#">Mapa Lunar</a>
<a class="text-pink-100 hover:text-white transition-opacity" href="#">Contato</a>
</div>
<div class="font-manrope text-xs tracking-widest uppercase opacity-80">
            © 2024 SexyLua Celestial Editorial. Todos as fases reservadas.
        </div>
</footer>
<!-- Bottom Navigation Bar (Mobile) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/70 backdrop-blur-xl rounded-t-[3rem] shadow-[0px_-10px_30px_rgba(216,27,96,0.1)]">
<div class="flex flex-col items-center justify-center text-slate-400">
<span class="material-symbols-outlined" data-icon="wb_twilight">wb_twilight</span>
<span class="font-manrope text-[10px] font-medium">Início</span>
</div>
<div class="flex flex-col items-center justify-center text-slate-400">
<span class="material-symbols-outlined" data-icon="explore">explore</span>
<span class="font-manrope text-[10px] font-medium">Explorar</span>
</div>
<div class="flex flex-col items-center justify-center text-slate-400">
<span class="material-symbols-outlined text-3xl text-primary" data-icon="add_circle">add_circle</span>
<span class="font-manrope text-[10px] font-medium">Criar</span>
</div>
<div class="flex flex-col items-center justify-center text-slate-400">
<span class="material-symbols-outlined" data-icon="nights_stay">nights_stay</span>
<span class="font-manrope text-[10px] font-medium">Atividade</span>
</div>
<!-- Active Tab: Profile/Wallet -->
<div class="flex flex-col items-center justify-center text-[#D81B60] relative after:content-[''] after:w-1 after:h-1 after:bg-[#D81B60] after:rounded-full after:mt-1">
<span class="material-symbols-outlined" data-icon="account_circle" style="font-variation-settings: 'FILL' 1;">account_circle</span>
<span class="font-manrope text-[10px] font-medium">Perfil</span>
</div>
</nav>
</body></html>