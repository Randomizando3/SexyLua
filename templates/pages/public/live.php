<!DOCTYPE html>

<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SexyLua - Live Room</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,800&amp;family=Manrope:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-container": "#efedef",
              "secondary": "#ab2c5d",
              "inverse-surface": "#303032",
              "surface-bright": "#fbf9fb",
              "surface-container-highest": "#e3e2e4",
              "secondary-container": "#fd6c9c",
              "on-error-container": "#93000a",
              "on-tertiary-fixed": "#280056",
              "on-primary": "#ffffff",
              "tertiary-fixed-dim": "#d7baff",
              "on-secondary-container": "#6e0034",
              "on-surface-variant": "#5a4044",
              "on-tertiary-container": "#fcf3ff",
              "on-secondary": "#ffffff",
              "on-background": "#1b1c1d",
              "surface-container-high": "#e9e7e9",
              "surface": "#fbf9fb",
              "on-secondary-fixed-variant": "#8b0e45",
              "inverse-primary": "#ffb1c5",
              "on-primary-fixed": "#3f001a",
              "surface-variant": "#e3e2e4",
              "on-tertiary-fixed-variant": "#5a2a9c",
              "on-secondary-fixed": "#3f001b",
              "error-container": "#ffdad6",
              "tertiary-fixed": "#eddcff",
              "on-surface": "#1b1c1d",
              "tertiary": "#6c3eaf",
              "error": "#ba1a1a",
              "outline": "#8e6f74",
              "on-error": "#ffffff",
              "tertiary-container": "#8658ca",
              "surface-tint": "#b41b5c",
              "on-primary-container": "#fff2f4",
              "inverse-on-surface": "#f2f0f2",
              "primary-fixed": "#ffd9e1",
              "surface-dim": "#dbd9db",
              "surface-container-low": "#f5f3f5",
              "primary-container": "#cc326e",
              "primary-fixed-dim": "#ffb1c5",
              "background": "#fbf9fb",
              "surface-container-lowest": "#ffffff",
              "on-tertiary": "#ffffff",
              "secondary-fixed": "#ffd9e1",
              "primary": "#ab1155",
              "secondary-fixed-dim": "#ffb1c5",
              "outline-variant": "#e3bdc3",
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
            vertical-align: middle;
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
            background-image: radial-gradient(circle at 10% 20%, rgba(216, 27, 96, 0.03) 0%, transparent 50%),
                              radial-gradient(circle at 90% 80%, rgba(171, 17, 85, 0.03) 0%, transparent 50%);
        }
        .play-glass {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(171, 17, 85, 0.2);
            border-radius: 10px;
        }
    </style>
</head>
<body class="font-body text-on-background antialiased">
<!-- TopNavBar -->
<nav class="fixed top-0 w-full z-50 flex justify-between items-center px-8 h-20 bg-[#D81B60] shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
<div class="flex items-center gap-12">
<span class="text-2xl font-black text-white italic tracking-tighter">SexyLua</span>
<div class="hidden md:flex gap-8 items-center">
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white border-b-2 border-white pb-1" href="#">Live Cam</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors hover:scale-105 transition-transform duration-200" href="#">Explorar</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors hover:scale-105 transition-transform duration-200" href="#">Assinaturas</a>
<a class="font-['Plus_Jakarta_Sans'] tracking-wide uppercase text-sm font-bold text-white/80 hover:text-white transition-colors hover:scale-105 transition-transform duration-200" href="#">Mensagens</a>
</div>
</div>
<div class="flex items-center gap-4">
<button class="px-6 py-2 rounded-full font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-widest text-white hover:scale-105 transition-all">Login</button>
<button class="px-6 py-2 rounded-full bg-white text-primary font-['Plus_Jakarta_Sans'] text-sm font-bold uppercase tracking-widest shadow-lg hover:scale-105 transition-all active:scale-95">Registro</button>
</div>
</nav>
<main class="pt-24 pb-20 px-4 md:px-8 max-w-7xl mx-auto">
<!-- Live Stream Core -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
<!-- Video Player Section -->
<div class="lg:col-span-8 space-y-6">
<div class="relative aspect-video bg-surface-container-high rounded-lg overflow-hidden shadow-2xl group">
<img class="w-full h-full object-cover" data-alt="Cinematic close up of a beautiful woman streaming" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBL1a15F2Rl4BXI5JWrjUtkSooE6uyqk68tX2_O0bOfgtMxKk9EZEJSlEQXS9Zy-4ebGa_mBD1ijX4h9mzfi29bDgDDGPX8_4xroQMJ74Z3Ge0n32F4IgYayrCnGwuL6KK55ncsPQgJfF3wkDknDmFx_2W0mOle7iWSKl8Btq0NgDJHOvXAoNjdt7fBLKlgnTfV5sEqSzciHAPRQcZz-eRmfAyvJJQkCqf-DkFHm15o5EinGgLCifN8UulqGx4jGdi1rJs-YphB-Kc"/>
<!-- Overlays -->
<div class="absolute top-6 left-6 flex items-center gap-3">
<span class="signature-glow text-white px-4 py-1 rounded-full text-xs font-bold tracking-widest flex items-center gap-2">
<span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                            AO VIVO
                        </span>
<span class="bg-black/40 backdrop-blur-md text-white px-4 py-1 rounded-full text-xs font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-sm">visibility</span>
                            14.2k
                        </span>
</div>
<!-- Stream Controls Overlay (Internal) -->
<div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-black/80 to-transparent p-6 flex items-end justify-between opacity-0 group-hover:opacity-100 transition-opacity duration-300">
<div class="flex gap-4">
<button class="text-white hover:text-primary transition-colors">
<span class="material-symbols-outlined text-3xl">play_arrow</span>
</button>
<button class="text-white hover:text-primary transition-colors">
<span class="material-symbols-outlined text-3xl">volume_up</span>
</button>
</div>
<div class="flex gap-4">
<button class="text-white hover:text-primary transition-colors">
<span class="material-symbols-outlined text-3xl">settings</span>
</button>
<button class="text-white hover:text-primary transition-colors">
<span class="material-symbols-outlined text-3xl">fullscreen</span>
</button>
</div>
</div>
</div>
<!-- Profile Header -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 p-2">
<div class="flex items-center gap-5">
<div class="relative">
<div class="w-20 h-20 rounded-full p-1 border-2 border-primary">
<img class="w-full h-full object-cover rounded-full" data-alt="Ana Silva profile avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCuO8xjpl4r7kIerQP_Wfk3K8RHoR6baAEZQV-5bJyKx5keuaLBUmCPqY7JIQ5LnmKyXhrn3EJby6Gyh-Gm4AH4VrC2lgG9H-pVJnQhbwbJfG4KWwu4hgKOHxbpJN4exHS_siTkMZlb-5xQuQ2Jedbb1e-smM7zPGkXt2vFfF_7tUebn7d0IMBP0wNb7ZCCeXNBDfFrhvSY8w0BywiN59jgbWH9HnuEJmiClGOFDFJzr2ROl5x5vU5rtlM_cHAGHeAwEOb2CVIibXY"/>
</div>
<div class="absolute bottom-0 right-0 w-6 h-6 signature-glow rounded-full border-4 border-surface flex items-center justify-center"></div>
</div>
<div>
<h1 class="text-3xl font-headline font-extrabold tracking-tight text-on-surface">Ana Silva</h1>
<p class="text-on-surface-variant font-medium">Explorando novas sensações ao vivo</p>
</div>
</div>
<div class="flex gap-3 w-full md:w-auto">
<button class="flex-1 md:flex-none px-8 py-3 rounded-full bg-surface-container-highest text-on-surface font-headline font-bold hover:bg-surface-container-high transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined">favorite</span>
                            Seguir
                        </button>
<button class="flex-1 md:flex-none px-8 py-3 rounded-full signature-glow text-white font-headline font-bold shadow-lg hover:scale-105 transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">diamond</span>
                            Dar Gorjeta
                        </button>
</div>
</div>
</div>
<!-- Interactive Chat & Stats Section -->
<div class="lg:col-span-4 h-[700px] flex flex-col bg-surface-container-low rounded-xl overflow-hidden shadow-sm">
<!-- Chat Header -->
<div class="p-4 bg-surface-container-high flex justify-between items-center shrink-0">
<span class="font-headline font-bold text-sm tracking-widest uppercase">Chat da Lua</span>
<span class="material-symbols-outlined text-on-surface-variant cursor-pointer">more_vert</span>
</div>
<!-- TOP DONATES & RECENT DONATES -->
<div class="px-4 py-3 bg-surface-container border-b border-outline-variant/20 space-y-4 shrink-0">
<div>
<h4 class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
<span class="material-symbols-outlined text-xs text-primary">brightness_7</span> Top Donates
</h4>
<div class="flex gap-5 px-1">
<div class="flex flex-col items-center">
<div class="relative">
<div class="w-10 h-10 rounded-full ring-2 ring-primary ring-offset-2 overflow-hidden mb-1.5 bg-surface">
<img alt="Luan_X" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8WCleacQUvS5KkrbaB8sulK_GkXdG1sGaaMU8eWmoNhmjauEDnrOOP2jwjKTE9vmOuQt8Nsm89YYvamnwSMeQE9_llh1OT6y4kNcpm4P5cW-iTICgSHWa0a84w2GayppgiU_cT3bLjuaCxXI1XEkWBDIwciBfbMGlvAYPW_Zac47PvLck2BagPPS9fR4nEmT24aAheEltH2NQ3uMP9zS1KbeIk6keeYTYk_NWvi20Wxt2iFkSTqCoTni-xw_sVf-zdOxFl-oBhIg"/>
</div>
<span class="absolute -top-1 -right-1 w-4 h-4 bg-primary text-[8px] font-bold text-white rounded-full flex items-center justify-center border border-white">1</span>
</div>
<span class="text-[10px] font-bold text-primary">Luan_X</span>
</div>
<div class="flex flex-col items-center">
<div class="relative">
<div class="w-10 h-10 rounded-full ring-2 ring-outline-variant/50 ring-offset-2 overflow-hidden mb-1.5 bg-surface">
<img alt="Marcos" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMi7tvK2IY-AFQvxo8kW6aqRpoych-3_bPRxh9fERXqRaeifHl-8p__ZRa4EEnFfhXVIiWSE2U6QkFH-srGalUGQCKxXwq21v-VYpd3WLkjRew664acti8VZMykU-09tMliqbXSCLvNVpYpLNISd0JSWABx48XqscbI9NHMHbL9Wesmmmv12BUAmg691ILFinWA3RYkkK6cgW4FYWakTUnE8KSz_pLAAn7WMEuGDjqCikoPXiTa7DPkAk6rL8TvupH9Wxd6wD814Y"/>
</div>
<span class="absolute -top-1 -right-1 w-4 h-4 bg-on-surface-variant text-[8px] font-bold text-white rounded-full flex items-center justify-center border border-white">2</span>
</div>
<span class="text-[10px] font-medium text-on-surface-variant">Marcos</span>
</div>
<div class="flex flex-col items-center">
<div class="relative">
<div class="w-10 h-10 rounded-full ring-2 ring-outline-variant/30 ring-offset-2 overflow-hidden mb-1.5 bg-surface">
<img alt="Rodrigo" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXIfHaSMxlhyROFz-X5kdbw0o4qZEmOw_PP9LrE5U6rjOIdiHsMz-XTGbnH64YYQRusM5qQ7xS-HTiGzLj1zMEPicZkCO_79yOg3bLHFJNNVyaf1ZSlXBVY5h68crn0jV1v1zYvNZ4RZwKHCiHMW5Rr7GHKpnFcym8f9YgpbH6vEspGsG3HMHNHL_nsNYr_Bnvl3Z40sVPz_T10GhdwW09IlFgeHl-z6MrggoUnGdN_pmFxlKmspBdcrhnEIuSZ168s5LUgn7MXIE"/>
</div>
<span class="absolute -top-1 -right-1 w-4 h-4 bg-outline-variant text-[8px] font-bold text-white rounded-full flex items-center justify-center border border-white">3</span>
</div>
<span class="text-[10px] font-medium text-on-surface-variant">Rodrigo</span>
</div>
</div>
</div>
<div>
<h4 class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.2em] mb-2 flex items-center gap-2">
<span class="material-symbols-outlined text-xs text-secondary">stars</span> Doações Recentes
</h4>
<div class="flex flex-col gap-1.5">
<div class="flex justify-between items-center text-xs bg-surface-container-low px-3 py-1.5 rounded-full border border-secondary/10 hover:bg-secondary/5 transition-colors cursor-default">
<span class="font-bold text-on-surface truncate">Carlos_Sky</span>
<div class="flex items-center gap-1.5">
<span class="text-secondary font-black">50</span>
<span class="material-symbols-outlined text-sm text-secondary" style="font-variation-settings: 'FILL' 1;">diamond</span>
</div>
</div>
<div class="flex justify-between items-center text-xs bg-surface-container-low px-3 py-1.5 rounded-full border border-secondary/10">
<span class="font-bold text-on-surface truncate">Mila_Glow</span>
<div class="flex items-center gap-1.5">
<span class="text-secondary font-black">25</span>
<span class="material-symbols-outlined text-sm text-secondary" style="font-variation-settings: 'FILL' 1;">diamond</span>
</div>
</div>
</div>
</div>
</div>
<!-- Chat Messages -->
<div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
<div class="flex flex-col gap-1">
<span class="text-xs font-bold text-primary tracking-wide">Marcos_Vini</span>
<p class="bg-surface-container-lowest p-3 rounded-2xl rounded-tl-none text-sm text-on-surface-variant shadow-sm">Você está maravilhosa hoje, Ana! 😍</p>
</div>
<div class="flex flex-col gap-1">
<span class="text-xs font-bold text-tertiary tracking-wide">Luan_Sky</span>
<p class="bg-surface-container-lowest p-3 rounded-2xl rounded-tl-none text-sm text-on-surface-variant shadow-sm">Alguém mais notou como a luz está perfeita?</p>
</div>
<div class="flex flex-col gap-1 items-end">
<span class="text-xs font-bold text-secondary tracking-wide">Privado</span>
<p class="signature-glow text-white p-3 rounded-2xl rounded-tr-none text-sm shadow-md italic">Enviou 50 diamantes: "Faz aquele olhar que eu gosto?"</p>
</div>
<div class="flex flex-col gap-1">
<span class="text-xs font-bold text-primary tracking-wide">Julia_Moon</span>
<p class="bg-surface-container-lowest p-3 rounded-2xl rounded-tl-none text-sm text-on-surface-variant shadow-sm">Amo sua energia! ✨</p>
</div>
<div class="flex flex-col gap-1">
<span class="text-xs font-bold text-on-surface-variant tracking-wide">System</span>
<p class="text-xs text-outline-variant italic">Rodrigo_Silva entrou na sala.</p>
</div>
<div class="flex flex-col gap-1">
<span class="text-xs font-bold text-secondary tracking-wide">Dark_Soul</span>
<p class="bg-surface-container-lowest p-3 rounded-2xl rounded-tl-none text-sm text-on-surface-variant shadow-sm">A noite está apenas começando... 🔥</p>
</div>
</div>
<!-- Chat Input -->
<div class="p-4 bg-surface-container-lowest shrink-0">
<div class="relative flex items-center gap-2">
<input class="w-full bg-surface-container-low border-none rounded-full py-3 px-6 text-sm focus:ring-1 focus:ring-primary" placeholder="Diga algo sensual..." type="text"/>
<button class="absolute right-12 text-primary">
<span class="material-symbols-outlined">sentiment_satisfied</span>
</button>
<button class="w-10 h-10 rounded-full signature-glow text-white flex items-center justify-center flex-shrink-0 shadow-md">
<span class="material-symbols-outlined">send</span>
</button>
</div>
</div>
</div>
</div>
<!-- Recommended Creators Section -->
<section class="mt-20">
<div class="flex justify-between items-end mb-10">
<div>
<h2 class="text-3xl font-headline font-extrabold tracking-tight text-on-surface">Outras Lives em Destaque</h2>
<p class="text-on-surface-variant mt-2">Criadores que estão brilhando agora</p>
</div>
<a class="text-primary font-bold hover:underline flex items-center gap-1" href="#">Ver todos <span class="material-symbols-outlined">chevron_right</span></a>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
<!-- Creator Card 1 -->
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-4 shadow-sm group-hover:shadow-xl transition-all">
<img class="w-full h-full object-cover blur-sm group-hover:blur-md transition-all duration-500" data-alt="Portrait of creator Clara Luz" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLGW_WOLWXPALXRYVsZbY8aHnzGxcPddnNMQ7WTdPYpy70B4J71YefahzAKTyzaluuROVSRSVZ_kFcNL_kHsqV-z1tWgb0Cp8FxF9LoZvb76YV3C1P1Uf-U-10CVCb2k4Tyw3QCHy55gO-Yl1R2icKCWJzrTcVttkcScEwhxueBAmBVrNls8QpUtmQfAONK5_bXX-2NCP24HXx7INL4uzGzBFcPsorgvrRvoNjw2wvNIaQd3FpeEnL_dVc1TcjAu5Ti61FO0dNEpI"/>
<div class="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="play-glass w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition-transform group-hover:scale-110">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
<div class="absolute top-4 left-4">
<span class="bg-primary text-white text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">LIVE</span>
</div>
</div>
<h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">Clara Luz</h3>
<p class="text-xs text-on-surface-variant">Sensualidade Pura</p>
</div>
<!-- Creator Card 2 -->
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-4 shadow-sm group-hover:shadow-xl transition-all">
<img class="w-full h-full object-cover blur-sm group-hover:blur-md transition-all duration-500" data-alt="Portrait of creator Beatriz Mendes" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAsdxE9bSZiwoTaJETQ1dJ0SNxPAvTd36nkrbSE8JsFr9XjWI3CEkzZJEJMBcQ33ZWtvzukgC4h7oA3AN1jB0onWyLTN2mNWbxmQkaeydyDSluBn8bkOSzIgNkVPDgZ3nf4vF5J6kZGMxi-bFpUSYuxCfGbniVlFMNWlvMCMuax0R5X3n5b_saZyzCIqdmdU-43D_T-zjh9g2QtyeGgC0fd1QsGXhlTgNig9x8H5yE30LrVylyz4O3UD30tXOhmrTAaBilzFGv96CU"/>
<div class="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="play-glass w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition-transform group-hover:scale-110">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
</div>
<h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">Beatriz Mendes</h3>
<p class="text-xs text-on-surface-variant">Mistérios da Noite</p>
</div>
<!-- Creator Card 3 -->
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-4 shadow-sm group-hover:shadow-xl transition-all">
<img class="w-full h-full object-cover blur-sm group-hover:blur-md transition-all duration-500" data-alt="Portrait of creator Rafael Costa" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMi7tvK2IY-AFQvxo8kW6aqRpoych-3_bPRxh9fERXqRaeifHl-8p__ZRa4EEnFfhXVIiWSE2U6QkFH-srGalUGQCKxXwq21v-VYpd3WLkjRew664acti8VZMykU-09tMliqbXSCLvNVpYpLNISd0JSWABx48XqscbI9NHMHbL9Wesmmmv12BUAmg691ILFinWA3RYkkK6cgW4FYWakTUnE8KSz_pLAAn7WMEuGDjqCikoPXiTa7DPkAk6rL8TvupH9Wxd6wD814Y"/>
<div class="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="play-glass w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition-transform group-hover:scale-110">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
<div class="absolute top-4 left-4">
<span class="bg-primary text-white text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">LIVE</span>
</div>
</div>
<h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">Rafael Costa</h3>
<p class="text-xs text-on-surface-variant">Elegância e Charme</p>
</div>
<!-- Creator Card 4 -->
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-4 shadow-sm group-hover:shadow-xl transition-all">
<img class="w-full h-full object-cover blur-sm group-hover:blur-md transition-all duration-500" data-alt="Portrait of creator Vanessa Lima" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8WCleacQUvS5KkrbaB8sulK_GkXdG1sGaaMU8eWmoNhmjauEDnrOOP2jwjKTE9vmOuQt8Nsm89YYvamnwSMeQE9_llh1OT6y4kNcpm4P5cW-iTICgSHWa0a84w2GayppgiU_cT3bLjuaCxXI1XEkWBDIwciBfbMGlvAYPW_Zac47PvLck2BagPPS9fR4nEmT24aAheEltH2NQ3uMP9zS1KbeIk6keeYTYk_NWvi20Wxt2iFkSTqCoTni-xw_sVf-zdOxFl-oBhIg"/>
<div class="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="play-glass w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition-transform group-hover:scale-110">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
</div>
<h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">Vanessa Lima</h3>
<p class="text-xs text-on-surface-variant">Curvas e Desejos</p>
</div>
<!-- Creator Card 5 -->
<div class="group cursor-pointer">
<div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-4 shadow-sm group-hover:shadow-xl transition-all">
<img class="w-full h-full object-cover blur-sm group-hover:blur-md transition-all duration-500" data-alt="Portrait of creator Lucas Amaral" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXIfHaSMxlhyROFz-X5kdbw0o4qZEmOw_PP9LrE5U6rjOIdiHsMz-XTGbnH64YYQRusM5qQ7xS-HTiGzLj1zMEPicZkCO_79yOg3bLHFJNNVyaf1ZSlXBVY5h68crn0jV1v1zYvNZ4RZwKHCiHMW5Rr7GHKpnFcym8f9YgpbH6vEspGsG3HMHNHL_nsNYr_Bnvl3Z40sVPz_T10GhdwW09IlFgeHl-z6MrggoUnGdN_pmFxlKmspBdcrhnEIuSZ168s5LUgn7MXIE"/>
<div class="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors"></div>
<div class="absolute inset-0 flex items-center justify-center">
<div class="play-glass w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition-transform group-hover:scale-110">
<span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
</div>
<h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">Lucas Amaral</h3>
<p class="text-xs text-on-surface-variant">Conexão Intensa</p>
</div>
</div>
</section>
<!-- Conheça Outros Criadores Section -->
<section class="mt-20">
<div class="flex justify-between items-end mb-10">
<div>
<h2 class="text-3xl font-headline font-extrabold tracking-tight text-on-surface">Conheça Outros Criadores</h2>
<p class="text-on-surface-variant mt-2">Nossas estrelas que aguardam seu brilho</p>
</div>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
<!-- Creator Card 1 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Luna Estelar" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAEP4t4D4EaZ0Y56R5Uu6L1Z4O3D_79yOg3bLHFJNNVyaf1ZSlXBVY5h68crn0jV1v1zYvNZ4RZwKHCiHMW5Rr7GHKpnFcym8f9YgpbH6vEspGsG3HMHNHL_nsNYr_Bnvl3Z40sVPz_T10GhdwW09IlFgeHl-z6MrggoUnGdN_pmFxlKmspBdcrhnEIuSZ168s5LUgn7MXIE"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Luna Estelar</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Nível Galáctico</p>
</div>
<!-- Creator Card 2 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Bia Velvet" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLGW_WOLWXPALXRYVsZbY8aHnzGxcPddnNMQ7WTdPYpy70B4J71YefahzAKTyzaluuROVSRSVZ_kFcNL_kHsqV-z1tWgb0Cp8FxF9LoZvb76YV3C1P1Uf-U-10CVCb2k4Tyw3QCHy55gO-Yl1R2icKCWJzrTcVttkcScEwhxueBAmBVrNls8QpUtmQfAONK5_bXX-2NCP24HXx7INL4uzGzBFcPsorgvrRvoNjw2wvNIaQd3FpeEnL_dVc1TcjAu5Ti61FO0dNEpI"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Bia Velvet</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Toque de Veludo</p>
</div>
<!-- Creator Card 3 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Mel Solar" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAsdxE9bSZiwoTaJETQ1dJ0SNxPAvTd36nkrbSE8JsFr9XjWI3CEkzZJEJMBcQ33ZWtvzukgC4h7oA3AN1jB0onWyLTN2mNWbxmQkaeydyDSluBn8bkOSzIgNkVPDgZ3nf4vF5J6kZGMxi-bFpUSYuxCfGbniVlFMNWlvMCMuax0R5X3n5b_saZyzCIqdmdU-43D_T-zjh9g2QtyeGgC0fd1QsGXhlTgNig9x8H5yE30LrVylyz4O3UD30tXOhmrTAaBilzFGv96CU"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Mel Solar</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Raio de Sol</p>
</div>
<!-- Creator Card 4 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Ícaro Dream" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMi7tvK2IY-AFQvxo8kW6aqRpoych-3_bPRxh9fERXqRaeifHl-8p__ZRa4EEnFfhXVIiWSE2U6QkFH-srGalUGQCKxXwq21v-VYpd3WLkjRew664acti8VZMykU-09tMliqbXSCLvNVpYpLNISd0JSWABx48XqscbI9NHMHbL9Wesmmmv12BUAmg691ILFinWA3RYkkK6cgW4FYWakTUnE8KSz_pLAAn7WMEuGDjqCikoPXiTa7DPkAk6rL8TvupH9Wxd6wD814Y"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Ícaro Dream</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Sonho Noturno</p>
</div>
<!-- Creator Card 5 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Jade Moon" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8WCleacQUvS5KkrbaB8sulK_GkXdG1sGaaMU8eWmoNhmjauEDnrOOP2jwjKTE9vmOuQt8Nsm89YYvamnwSMeQE9_llh1OT6y4kNcpm4P5cW-iTICgSHWa0a84w2GayppgiU_cT3bLjuaCxXI1XEkWBDIwciBfbMGlvAYPW_Zac47PvLck2BagPPS9fR4nEmT24aAheEltH2NQ3uMP9zS1KbeIk6keeYTYk_NWvi20Wxt2iFkSTqCoTni-xw_sVf-zdOxFl-oBhIg"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Jade Moon</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Brilho de Jade</p>
</div>
<!-- Creator Card 6 -->
<div class="group cursor-pointer">
<div class="relative aspect-square rounded-full overflow-hidden mb-4 border-4 border-transparent group-hover:border-primary transition-all p-1 shadow-md">
<img alt="Ciro Dark" class="w-full h-full object-cover rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXIfHaSMxlhyROFz-X5kdbw0o4qZEmOw_PP9LrE5U6rjOIdiHsMz-XTGbnH64YYQRusM5qQ7xS-HTiGzLj1zMEPicZkCO_79yOg3bLHFJNNVyaf1ZSlXBVY5h68crn0jV1v1zYvNZ4RZwKHCiHMW5Rr7GHKpnFcym8f9YgpbH6vEspGsG3HMHNHL_nsNYr_Bnvl3Z40sVPz_T10GhdwW09IlFgeHl-z6MrggoUnGdN_pmFxlKmspBdcrhnEIuSZ168s5LUgn7MXIE"/>
</div>
<h3 class="text-center font-bold text-sm text-on-surface">Ciro Dark</h3>
<p class="text-center text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Eclipse Total</p>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="w-full flex flex-col items-center gap-6 px-10 bg-[#D81B60] py-12">
<div class="flex flex-col items-center gap-4">
<span class="text-lg font-bold text-white">SexyLua</span>
<div class="flex gap-8">
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white underline-offset-4 hover:underline transition-all duration-300" href="#">Termos</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white underline-offset-4 hover:underline transition-all duration-300" href="#">Privacidade</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white underline-offset-4 hover:underline transition-all duration-300" href="#">Ajuda</a>
<a class="font-['Manrope'] text-xs tracking-widest uppercase text-white/70 hover:text-white underline-offset-4 hover:underline transition-all duration-300" href="#">Carreiras</a>
</div>
</div>
<p class="font-['Manrope'] text-xs tracking-widest uppercase text-white/50 text-center">© 2024 SexyLua. The Lunar Metamorphosis.</p>
</footer>
<!-- FAB for Tipping (Mobile only) -->
<button class="md:hidden fixed bottom-6 right-6 w-16 h-16 rounded-full signature-glow text-white shadow-2xl flex items-center justify-center z-40 animate-bounce">
<span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">diamond</span>
</button>
</body></html>