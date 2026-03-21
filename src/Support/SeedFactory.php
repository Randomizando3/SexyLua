<?php

declare(strict_types=1);

namespace App\Support;

final class SeedFactory
{
    public static function build(): array
    {
        return [
            'users' => self::users(),
            'creator_profiles' => self::creatorProfiles(),
            'content_items' => self::contentItems(),
            'plans' => self::plans(),
            'subscriptions' => self::subscriptions(),
            'live_sessions' => self::liveSessions(),
            'favorites' => self::favorites(),
            'saved_items' => self::savedItems(),
            'conversations' => self::conversations(),
            'messages' => self::messages(),
            'live_messages' => self::liveMessages(),
            'wallet_transactions' => self::walletTransactions(),
            'settings' => self::settings(),
        ];
    }

    private static function users(): array
    {
        return [
            ['id' => 1, 'name' => 'Admin Master', 'email' => 'admin@sexylua.local', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'role' => 'admin', 'status' => 'active', 'headline' => 'Controle total da plataforma.', 'bio' => 'Responsavel pela curadoria, financas e saude operacional do ecossistema SexyLua.', 'city' => 'Sao Paulo', 'created_at' => '2026-03-01 09:00:00'],
            ['id' => 2, 'name' => 'Maria Silva', 'email' => 'maria@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Editorial lunar, bastidores e atmosfera intima.', 'bio' => 'Crio experiencias sensoriais com fotografia, videos autorais e encontros ao vivo.', 'city' => 'Rio de Janeiro', 'created_at' => '2026-03-02 11:00:00'],
            ['id' => 3, 'name' => 'Ana Silva', 'email' => 'ana@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Lives intimas com energia de lua cheia.', 'bio' => 'Misturo conversa, performance e presenca ao vivo em sessoes que mudam a cada fase.', 'city' => 'Belo Horizonte', 'created_at' => '2026-03-03 12:00:00'],
            ['id' => 4, 'name' => 'Luna Estelar', 'email' => 'luna@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Fashion editorial, confissoes e bastidores.', 'bio' => 'Minha galeria mistura estetica premium, humor e intimidade elegante.', 'city' => 'Curitiba', 'created_at' => '2026-03-04 10:30:00'],
            ['id' => 5, 'name' => 'Clara Luz', 'email' => 'clara@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Narrativas quentes com pegada cinematografica.', 'bio' => 'Experiencias em video e audio para assinantes que gostam de exclusividade.', 'city' => 'Recife', 'created_at' => '2026-03-05 08:45:00'],
            ['id' => 6, 'name' => 'Rafael Costa', 'email' => 'rafael@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Conteudo premium com ritmo e presenca.', 'bio' => 'Produzo galerias, lives e bastidores com uma estetica editorial marcante.', 'city' => 'Florianopolis', 'created_at' => '2026-03-06 14:15:00'],
            ['id' => 7, 'name' => 'Bruno Alves', 'email' => 'assinante@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'active', 'headline' => 'Assinante em busca de experiencias premium.', 'bio' => 'Acompanho criadores, salvo colecoes e participo das lives mais disputadas.', 'city' => 'Campinas', 'created_at' => '2026-03-07 16:00:00'],
            ['id' => 8, 'name' => 'Julia Costa', 'email' => 'julia@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'active', 'headline' => 'Colecionadora de bastidores exclusivos.', 'bio' => 'Uso a plataforma para acompanhar lancamentos e conversar com meus criadores favoritos.', 'city' => 'Salvador', 'created_at' => '2026-03-08 09:20:00'],
            ['id' => 9, 'name' => 'Paula Nunes', 'email' => 'paula@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'suspended', 'headline' => 'Conta pausada para revisao.', 'bio' => 'Perfil temporariamente indisponivel.', 'city' => 'Fortaleza', 'created_at' => '2026-03-09 15:10:00'],
            ['id' => 10, 'name' => 'Mariana Vale', 'email' => 'mariana.vale@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Editorial quente com glamour de hotel e bastidores privados.', 'bio' => 'Misturo moda, lifestyle e sessoes intimistas com direcao de arte sofisticada.', 'city' => 'Sao Paulo', 'created_at' => '2026-03-10 11:25:00'],
            ['id' => 11, 'name' => 'Diego Noir', 'email' => 'diego.noir@sexylua.local', 'password' => password_hash('creator123', PASSWORD_DEFAULT), 'role' => 'creator', 'status' => 'active', 'headline' => 'Encontros noturnos, voz marcante e atmosfera cinematografica.', 'bio' => 'Crio experiencias premium com foco em narrativa, proximidade e fidelizacao.', 'city' => 'Porto Alegre', 'created_at' => '2026-03-11 21:40:00'],
            ['id' => 12, 'name' => 'Carla Freitas', 'email' => 'carla.freitas@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'active', 'headline' => 'Colecionadora de lives e drops premium.', 'bio' => 'Participo das transmissoes ao vivo e acompanho criadores em diferentes fases lunares.', 'city' => 'Brasilia', 'created_at' => '2026-03-12 10:10:00'],
            ['id' => 13, 'name' => 'Igor Melo', 'email' => 'igor.melo@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'active', 'headline' => 'Assinante fiel com foco em colecoes exclusivas.', 'bio' => 'Uso a SexyLua para acompanhar galerias premium e conversar com criadores em lancamentos.', 'city' => 'Goiania', 'created_at' => '2026-03-13 13:35:00'],
            ['id' => 14, 'name' => 'Sabrina Lopes', 'email' => 'sabrina.lopes@sexylua.local', 'password' => password_hash('subscriber123', PASSWORD_DEFAULT), 'role' => 'subscriber', 'status' => 'active', 'headline' => 'Exploradora de talentos e experiencias VIP.', 'bio' => 'Entro em lives, envio gorjetas e acompanho renovacoes para nao perder nenhum drop.', 'city' => 'Santos', 'created_at' => '2026-03-14 18:05:00'],
        ];
    }

    private static function creatorProfiles(): array
    {
        return [
            ['user_id' => 2, 'slug' => 'maria-silva', 'mood' => 'Lua Crescente', 'cover_style' => 'rose-dawn', 'featured' => true, 'followers' => 2810, 'rating' => 4.9],
            ['user_id' => 3, 'slug' => 'ana-silva', 'mood' => 'Lua Cheia', 'cover_style' => 'amber-night', 'featured' => true, 'followers' => 1964, 'rating' => 4.8],
            ['user_id' => 4, 'slug' => 'luna-estelar', 'mood' => 'Lua Nova', 'cover_style' => 'violet-haze', 'featured' => true, 'followers' => 3225, 'rating' => 5.0],
            ['user_id' => 5, 'slug' => 'clara-luz', 'mood' => 'Eclipse Rosa', 'cover_style' => 'solar-flare', 'featured' => true, 'followers' => 1576, 'rating' => 4.7],
            ['user_id' => 6, 'slug' => 'rafael-costa', 'mood' => 'Lua Minguante', 'cover_style' => 'midnight-ruby', 'featured' => false, 'followers' => 913, 'rating' => 4.6],
            ['user_id' => 10, 'slug' => 'mariana-vale', 'mood' => 'Aurora Rubi', 'cover_style' => 'rose-lounge', 'featured' => true, 'followers' => 2874, 'rating' => 4.9],
            ['user_id' => 11, 'slug' => 'diego-noir', 'mood' => 'Meia Noite', 'cover_style' => 'noir-silk', 'featured' => false, 'followers' => 1328, 'rating' => 4.8],
        ];
    }

    private static function contentItems(): array
    {
        return [
            ['id' => 1, 'creator_id' => 2, 'title' => 'Colecao Eclipse', 'excerpt' => 'Editorial premium com atmosfera noturna e acesso por assinatura.', 'body' => 'Uma selecao curada de fotos e videos com direcao de arte lunar.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'gallery', 'created_at' => '2026-03-18 22:10:00', 'saved_count' => 84],
            ['id' => 2, 'creator_id' => 2, 'title' => 'Bastidores Lunares', 'excerpt' => 'Video curto mostrando o setup, make e preparacao do ensaio.', 'body' => 'Conteudo espontaneo com clima intimo e visual editorial.', 'visibility' => 'public', 'status' => 'approved', 'kind' => 'video', 'created_at' => '2026-03-17 19:20:00', 'saved_count' => 53],
            ['id' => 3, 'creator_id' => 3, 'title' => 'Sala Vermelha AO VIVO', 'excerpt' => 'Previa da live com enquadramento preparado para interacao intensa.', 'body' => 'Teaser oficial da live de hoje com enquete para definir o ritmo da sessao.', 'visibility' => 'public', 'status' => 'approved', 'kind' => 'live_teaser', 'created_at' => '2026-03-21 19:00:00', 'saved_count' => 102],
            ['id' => 4, 'creator_id' => 4, 'title' => 'Sessao Velvet Moon em Milao', 'excerpt' => 'Galeria premium com estetica de revista e trilha autoral.', 'body' => 'Conteudo principal da colecao internacional da temporada.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'gallery', 'created_at' => '2026-03-16 21:15:00', 'saved_count' => 67],
            ['id' => 5, 'creator_id' => 4, 'title' => 'Behind the Scenes: Edicao 01', 'excerpt' => 'Making of em video com comentarios pessoais e enquadramentos ineditos.', 'body' => 'Bastidores soltos e sensoriais para o nucleo mais proximo da comunidade.', 'visibility' => 'subscriber', 'status' => 'pending', 'kind' => 'video', 'created_at' => '2026-03-21 10:00:00', 'saved_count' => 18],
            ['id' => 6, 'creator_id' => 5, 'title' => 'Confissoes em Audio', 'excerpt' => 'Audio exclusivo com narrativa lenta e intima.', 'body' => 'Uma faixa autoral focada em voz, pausa e sugestao.', 'visibility' => 'premium', 'status' => 'approved', 'kind' => 'audio', 'created_at' => '2026-03-15 23:40:00', 'saved_count' => 49],
            ['id' => 7, 'creator_id' => 6, 'title' => 'Vlog Semanal: Rotina em Paris', 'excerpt' => 'Rotina, figurinos e bastidores de producao em viagem.', 'body' => 'Material documental com linguagem premium e calor humano.', 'visibility' => 'public', 'status' => 'approved', 'kind' => 'video', 'created_at' => '2026-03-14 18:30:00', 'saved_count' => 27],
            ['id' => 8, 'creator_id' => 2, 'title' => 'Editorial Rubi', 'excerpt' => 'Ensaio fotografico com foco em luz baixa e textura.', 'body' => 'Conteudo enviado para aprovacao da equipe administrativa.', 'visibility' => 'subscriber', 'status' => 'pending', 'kind' => 'gallery', 'created_at' => '2026-03-21 09:00:00', 'saved_count' => 9],
            ['id' => 9, 'creator_id' => 3, 'title' => 'Diario da Lua Quente', 'excerpt' => 'Texto intimo com contexto e reflexoes apos a live.', 'body' => 'Um diario de bastidores para aproximar comunidade e criadora.', 'visibility' => 'subscriber', 'status' => 'rejected', 'kind' => 'article', 'created_at' => '2026-03-13 17:00:00', 'saved_count' => 4],
            ['id' => 10, 'creator_id' => 10, 'title' => 'Suite Aurora', 'excerpt' => 'Galeria premium gravada em um ensaio de hotel com clima intimista.', 'body' => 'Colecao exclusiva com 18 fotos e 3 takes verticais para assinantes.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'gallery', 'created_at' => '2026-03-20 23:10:00', 'saved_count' => 41],
            ['id' => 11, 'creator_id' => 10, 'title' => 'After Dinner Confessions', 'excerpt' => 'Video curto com narrativa baixa, humor e um clima de pos-encontro.', 'body' => 'Drop audiovisual para a base mais fiel da Mariana Vale.', 'visibility' => 'premium', 'status' => 'approved', 'kind' => 'video', 'created_at' => '2026-03-19 21:45:00', 'saved_count' => 34],
            ['id' => 12, 'creator_id' => 11, 'title' => 'Noir Session 01', 'excerpt' => 'Teaser noturno enviado para moderacao com enquadramentos fechados.', 'body' => 'Primeira sessao solo do Diego Noir em formato live teaser.', 'visibility' => 'public', 'status' => 'pending', 'kind' => 'live_teaser', 'created_at' => '2026-03-21 20:10:00', 'saved_count' => 12],
            ['id' => 13, 'creator_id' => 4, 'title' => 'Velvet Rooftop', 'excerpt' => 'Editorial urbano com atmosfera fria e figurino premium.', 'body' => 'Nova galeria internacional pronta para assinantes ativos.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'gallery', 'created_at' => '2026-03-20 18:00:00', 'saved_count' => 58],
            ['id' => 14, 'creator_id' => 5, 'title' => 'Audio de Boa Noite', 'excerpt' => 'Mensagem guiada para assinantes com trilha suave e voz proxima.', 'body' => 'Conteudo em audio premium com publicacao noturna.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'audio', 'created_at' => '2026-03-18 23:58:00', 'saved_count' => 26],
            ['id' => 15, 'creator_id' => 2, 'title' => 'Drop Scarlet Draft', 'excerpt' => 'Video em avaliacao com proposta mais ousada para a proxima semana.', 'body' => 'Conteudo aguardando validacao da equipe antes da estreia.', 'visibility' => 'premium', 'status' => 'pending', 'kind' => 'video', 'created_at' => '2026-03-21 20:25:00', 'saved_count' => 7],
            ['id' => 16, 'creator_id' => 4, 'title' => 'Checklist de Viagem', 'excerpt' => 'Texto curto com referencias visuais e figurinos usados na ultima campanha.', 'body' => 'Material de bastidor com contexto da colecao internacional.', 'visibility' => 'public', 'status' => 'approved', 'kind' => 'article', 'created_at' => '2026-03-19 16:20:00', 'saved_count' => 15],
            ['id' => 17, 'creator_id' => 11, 'title' => 'Private Notes', 'excerpt' => 'Carta pessoal sobre rotinas, rituais e o que vem no proximo drop.', 'body' => 'Texto premium enviado apenas para membros da fase Meia Noite.', 'visibility' => 'subscriber', 'status' => 'approved', 'kind' => 'article', 'created_at' => '2026-03-18 20:20:00', 'saved_count' => 19],
            ['id' => 18, 'creator_id' => 10, 'title' => 'Room Service Heat', 'excerpt' => 'Teaser sensual sob luz rubi enviado para revisao rapida.', 'body' => 'Conteudo vertical com foco em atmosfera e proximidade.', 'visibility' => 'subscriber', 'status' => 'pending', 'kind' => 'video', 'created_at' => '2026-03-21 18:45:00', 'saved_count' => 6],
        ];
    }

    private static function plans(): array
    {
        return [
            ['id' => 1, 'creator_id' => 2, 'name' => 'Lunar Mood', 'description' => 'Bastidores, mensagens e drops semanais.', 'price_tokens' => 59, 'active' => true, 'perks' => ['Galerias premium', 'Chat direto', 'Descontos em lives']],
            ['id' => 2, 'creator_id' => 2, 'name' => 'Colecao Eclipse', 'description' => 'Acesso total ao acervo e previas antes de todos.', 'price_tokens' => 99, 'active' => true, 'perks' => ['Tudo do plano anterior', 'Lives fechadas', 'Colecoes completas']],
            ['id' => 3, 'creator_id' => 3, 'name' => 'Lua Cheia', 'description' => 'Entrada nas lives e bastidores pos-show.', 'price_tokens' => 69, 'active' => true, 'perks' => ['Lives semanais', 'Replay exclusivo', 'Prioridade no chat']],
            ['id' => 4, 'creator_id' => 4, 'name' => 'Velvet Society', 'description' => 'Assinatura editorial premium.', 'price_tokens' => 89, 'active' => true, 'perks' => ['Galerias internacionais', 'Conteudo estendido', 'Chat VIP']],
            ['id' => 5, 'creator_id' => 5, 'name' => 'Solar Intimista', 'description' => 'Audios, textos e cenas curtas.', 'price_tokens' => 49, 'active' => true, 'perks' => ['Audios exclusivos', 'Textos intimos', 'Desafios semanais']],
            ['id' => 6, 'creator_id' => 6, 'name' => 'Midnight Access', 'description' => 'Rotina premium e drops especiais.', 'price_tokens' => 39, 'active' => true, 'perks' => ['Conteudo documental', 'Drops surpresa', 'Mensagens prioritarias']],
            ['id' => 7, 'creator_id' => 10, 'name' => 'Aurora Club', 'description' => 'Galerias premium, room drops e chat prioritario.', 'price_tokens' => 79, 'active' => true, 'perks' => ['Galerias exclusivas', 'Acesso antecipado', 'DM prioritaria']],
            ['id' => 8, 'creator_id' => 11, 'name' => 'Noir Circle', 'description' => 'Notas privadas, lives noturnas e bastidores cinematograficos.', 'price_tokens' => 69, 'active' => true, 'perks' => ['Lives exclusivas', 'Notas privadas', 'Conteudo premium']],
            ['id' => 9, 'creator_id' => 10, 'name' => 'Penthouse Access', 'description' => 'Plano mais intenso com drops semanais e backstage completo.', 'price_tokens' => 129, 'active' => true, 'perks' => ['Tudo do Aurora Club', 'Video premium semanal', 'Fila VIP nas lives']],
            ['id' => 10, 'creator_id' => 11, 'name' => 'Black Label', 'description' => 'Experiencia premium com prioridade em mensagens e conteudo fechado.', 'price_tokens' => 109, 'active' => true, 'perks' => ['Tudo do Noir Circle', 'Respostas prioritarias', 'Drops surpresa']],
        ];
    }

    private static function subscriptions(): array
    {
        return [
            ['id' => 1, 'subscriber_id' => 7, 'creator_id' => 2, 'plan_id' => 1, 'status' => 'active', 'renews_at' => '2026-04-12 10:00:00'],
            ['id' => 2, 'subscriber_id' => 7, 'creator_id' => 3, 'plan_id' => 3, 'status' => 'active', 'renews_at' => '2026-04-18 18:00:00'],
            ['id' => 3, 'subscriber_id' => 8, 'creator_id' => 4, 'plan_id' => 4, 'status' => 'active', 'renews_at' => '2026-04-04 09:00:00'],
            ['id' => 4, 'subscriber_id' => 7, 'creator_id' => 5, 'plan_id' => 5, 'status' => 'cancelled', 'renews_at' => '2026-03-01 12:00:00'],
            ['id' => 5, 'subscriber_id' => 7, 'creator_id' => 4, 'plan_id' => 4, 'status' => 'active', 'renews_at' => '2026-04-09 20:00:00'],
            ['id' => 6, 'subscriber_id' => 7, 'creator_id' => 10, 'plan_id' => 7, 'status' => 'active', 'renews_at' => '2026-04-15 22:00:00'],
            ['id' => 7, 'subscriber_id' => 12, 'creator_id' => 2, 'plan_id' => 2, 'status' => 'active', 'renews_at' => '2026-04-16 11:00:00'],
            ['id' => 8, 'subscriber_id' => 13, 'creator_id' => 2, 'plan_id' => 1, 'status' => 'active', 'renews_at' => '2026-04-20 13:00:00'],
            ['id' => 9, 'subscriber_id' => 8, 'creator_id' => 10, 'plan_id' => 7, 'status' => 'active', 'renews_at' => '2026-04-11 09:30:00'],
            ['id' => 10, 'subscriber_id' => 12, 'creator_id' => 11, 'plan_id' => 8, 'status' => 'active', 'renews_at' => '2026-04-22 23:15:00'],
            ['id' => 11, 'subscriber_id' => 14, 'creator_id' => 4, 'plan_id' => 4, 'status' => 'active', 'renews_at' => '2026-04-06 19:00:00'],
            ['id' => 12, 'subscriber_id' => 14, 'creator_id' => 2, 'plan_id' => 1, 'status' => 'active', 'renews_at' => '2026-04-25 08:40:00'],
        ];
    }

    private static function liveSessions(): array
    {
        return [
            ['id' => 1, 'creator_id' => 3, 'title' => 'Sala Rubi ao Vivo', 'description' => 'Interacao, enquete e clima de lua cheia.', 'status' => 'live', 'scheduled_for' => '2026-03-21 21:00:00', 'viewer_count' => 214, 'price_tokens' => 25, 'chat_enabled' => true],
            ['id' => 2, 'creator_id' => 2, 'title' => 'Eclipse After Hours', 'description' => 'Sessao fechada com bastidores e conversa intima.', 'status' => 'scheduled', 'scheduled_for' => '2026-03-22 22:00:00', 'viewer_count' => 0, 'price_tokens' => 35, 'chat_enabled' => true],
            ['id' => 3, 'creator_id' => 5, 'title' => 'Noite Solar', 'description' => 'Live com roteiro livre e participacao do publico.', 'status' => 'live', 'scheduled_for' => '2026-03-21 20:30:00', 'viewer_count' => 162, 'price_tokens' => 20, 'chat_enabled' => true],
            ['id' => 4, 'creator_id' => 4, 'title' => 'Velvet Rehearsal', 'description' => 'Encontro fechado para assinantes premium.', 'status' => 'ended', 'scheduled_for' => '2026-03-20 23:30:00', 'viewer_count' => 87, 'price_tokens' => 30, 'chat_enabled' => false],
            ['id' => 5, 'creator_id' => 10, 'title' => 'Aurora Suite Live', 'description' => 'Sessao intimista com room lighting rubi e interacao premium.', 'status' => 'live', 'scheduled_for' => '2026-03-21 22:15:00', 'viewer_count' => 189, 'price_tokens' => 40, 'chat_enabled' => true],
            ['id' => 6, 'creator_id' => 11, 'title' => 'Noir Circle Premiere', 'description' => 'Estreia noturna para membros com leitura de mensagens ao vivo.', 'status' => 'scheduled', 'scheduled_for' => '2026-03-22 23:00:00', 'viewer_count' => 0, 'price_tokens' => 30, 'chat_enabled' => true],
            ['id' => 7, 'creator_id' => 4, 'title' => 'Velvet Balcony', 'description' => 'Live fashion com backstage e chat liberado para assinantes.', 'status' => 'live', 'scheduled_for' => '2026-03-21 21:40:00', 'viewer_count' => 148, 'price_tokens' => 25, 'chat_enabled' => true],
        ];
    }

    private static function favorites(): array
    {
        return [
            ['id' => 1, 'user_id' => 7, 'creator_id' => 2],
            ['id' => 2, 'user_id' => 7, 'creator_id' => 4],
            ['id' => 3, 'user_id' => 8, 'creator_id' => 3],
            ['id' => 4, 'user_id' => 7, 'creator_id' => 3],
            ['id' => 5, 'user_id' => 7, 'creator_id' => 10],
            ['id' => 6, 'user_id' => 12, 'creator_id' => 2],
            ['id' => 7, 'user_id' => 13, 'creator_id' => 10],
        ];
    }

    private static function savedItems(): array
    {
        return [
            ['id' => 1, 'user_id' => 7, 'content_id' => 1],
            ['id' => 2, 'user_id' => 7, 'content_id' => 4],
            ['id' => 3, 'user_id' => 8, 'content_id' => 6],
            ['id' => 4, 'user_id' => 7, 'content_id' => 3],
            ['id' => 5, 'user_id' => 7, 'content_id' => 6],
            ['id' => 6, 'user_id' => 7, 'content_id' => 10],
            ['id' => 7, 'user_id' => 7, 'content_id' => 13],
            ['id' => 8, 'user_id' => 12, 'content_id' => 17],
        ];
    }

    private static function conversations(): array
    {
        return [
            ['id' => 1, 'subscriber_id' => 7, 'creator_id' => 2, 'updated_at' => '2026-03-21 18:20:00'],
            ['id' => 2, 'subscriber_id' => 7, 'creator_id' => 3, 'updated_at' => '2026-03-21 19:15:00'],
            ['id' => 3, 'subscriber_id' => 7, 'creator_id' => 4, 'updated_at' => '2026-03-21 20:35:00'],
            ['id' => 4, 'subscriber_id' => 7, 'creator_id' => 10, 'updated_at' => '2026-03-21 21:18:00'],
            ['id' => 5, 'subscriber_id' => 7, 'creator_id' => 11, 'updated_at' => '2026-03-21 21:30:00'],
        ];
    }

    private static function messages(): array
    {
        return [
            ['id' => 1, 'conversation_id' => 1, 'sender_id' => 7, 'body' => 'Maria, a Colecao Eclipse ficou linda. Vai ter continuacao?', 'created_at' => '2026-03-21 17:42:00'],
            ['id' => 2, 'conversation_id' => 1, 'sender_id' => 2, 'body' => 'Vai sim. Estou finalizando a parte 2 para liberar aos assinantes esta semana.', 'created_at' => '2026-03-21 18:20:00'],
            ['id' => 3, 'conversation_id' => 2, 'sender_id' => 3, 'body' => 'A live de hoje vai abrir com enquete. Chega cedo.', 'created_at' => '2026-03-21 19:05:00'],
            ['id' => 4, 'conversation_id' => 2, 'sender_id' => 7, 'body' => 'Perfeito. Ja deixei meus tokens separados.', 'created_at' => '2026-03-21 19:15:00'],
            ['id' => 5, 'conversation_id' => 3, 'sender_id' => 4, 'body' => 'Subi agora a selecao Velvet Rooftop. Quero saber sua favorita.', 'created_at' => '2026-03-21 20:20:00'],
            ['id' => 6, 'conversation_id' => 3, 'sender_id' => 7, 'body' => 'Abri assim que recebi. A direcao de arte ficou impecavel.', 'created_at' => '2026-03-21 20:35:00'],
            ['id' => 7, 'conversation_id' => 4, 'sender_id' => 10, 'body' => 'Hoje vou abrir a Suite Aurora com um bonus para quem chegar cedo.', 'created_at' => '2026-03-21 21:02:00'],
            ['id' => 8, 'conversation_id' => 4, 'sender_id' => 7, 'body' => 'Ja garanti meus tokens. Me avisa quando liberar o backstage.', 'created_at' => '2026-03-21 21:18:00'],
            ['id' => 9, 'conversation_id' => 5, 'sender_id' => 11, 'body' => 'Minha estreia vai ser amanha. Quer participar do chat fechado?', 'created_at' => '2026-03-21 21:23:00'],
            ['id' => 10, 'conversation_id' => 5, 'sender_id' => 7, 'body' => 'Quero sim. O teaser Noir Session me ganhou.', 'created_at' => '2026-03-21 21:30:00'],
        ];
    }

    private static function liveMessages(): array
    {
        return [
            ['id' => 1, 'live_id' => 1, 'sender_id' => 7, 'body' => 'A energia hoje esta absurda.', 'created_at' => '2026-03-21 21:01:00'],
            ['id' => 2, 'live_id' => 1, 'sender_id' => 8, 'body' => 'Gostei demais da trilha de abertura.', 'created_at' => '2026-03-21 21:02:00'],
            ['id' => 3, 'live_id' => 1, 'sender_id' => 3, 'body' => 'Segura que ainda vem uma surpresa no segundo bloco.', 'created_at' => '2026-03-21 21:03:00'],
            ['id' => 4, 'live_id' => 1, 'sender_id' => 12, 'body' => 'Chat pegando fogo hoje. Quero replay depois.', 'created_at' => '2026-03-21 21:04:00'],
            ['id' => 5, 'live_id' => 5, 'sender_id' => 7, 'body' => 'Mariana, esse set esta lindo demais.', 'created_at' => '2026-03-21 22:16:00'],
            ['id' => 6, 'live_id' => 5, 'sender_id' => 10, 'body' => 'Hoje vou liberar um preview extra para os mais rapidos.', 'created_at' => '2026-03-21 22:17:00'],
            ['id' => 7, 'live_id' => 5, 'sender_id' => 8, 'body' => 'A iluminacao do quarto esta impecavel.', 'created_at' => '2026-03-21 22:18:00'],
            ['id' => 8, 'live_id' => 7, 'sender_id' => 14, 'body' => 'Velvet Balcony esta com clima de editorial de revista.', 'created_at' => '2026-03-21 21:42:00'],
            ['id' => 9, 'live_id' => 7, 'sender_id' => 4, 'body' => 'Daqui a pouco eu abro o bloco final so para assinantes.', 'created_at' => '2026-03-21 21:43:00'],
        ];
    }

    private static function walletTransactions(): array
    {
        return [
            ['id' => 1, 'user_id' => 7, 'type' => 'top_up', 'direction' => 'in', 'amount' => 300, 'note' => 'Recarga via pix simulada', 'created_at' => '2026-03-20 14:00:00'],
            ['id' => 2, 'user_id' => 7, 'type' => 'subscription', 'direction' => 'out', 'amount' => 59, 'note' => 'Assinatura Lunar Mood', 'created_at' => '2026-03-20 14:10:00', 'creator_id' => 2],
            ['id' => 3, 'user_id' => 2, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 47, 'note' => 'Receita liquida da assinatura Lunar Mood', 'created_at' => '2026-03-20 14:10:00', 'subscriber_id' => 7],
            ['id' => 4, 'user_id' => 7, 'type' => 'subscription', 'direction' => 'out', 'amount' => 69, 'note' => 'Assinatura Lua Cheia', 'created_at' => '2026-03-21 09:00:00', 'creator_id' => 3],
            ['id' => 5, 'user_id' => 3, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 55, 'note' => 'Receita liquida da assinatura Lua Cheia', 'created_at' => '2026-03-21 09:00:00', 'subscriber_id' => 7],
            ['id' => 6, 'user_id' => 7, 'type' => 'tip', 'direction' => 'out', 'amount' => 25, 'note' => 'Gorjeta enviada na live Sala Rubi', 'created_at' => '2026-03-21 21:05:00', 'creator_id' => 3],
            ['id' => 7, 'user_id' => 3, 'type' => 'tip_income', 'direction' => 'in', 'amount' => 20, 'note' => 'Receita liquida de gorjeta na live Sala Rubi', 'created_at' => '2026-03-21 21:05:00', 'subscriber_id' => 7],
            ['id' => 8, 'user_id' => 2, 'type' => 'payout_request', 'direction' => 'out', 'amount' => 60, 'note' => 'Pedido de saque em analise', 'created_at' => '2026-03-21 12:20:00'],
            ['id' => 9, 'user_id' => 7, 'type' => 'top_up', 'direction' => 'in', 'amount' => 400, 'note' => 'Recarga de reforco para novas assinaturas', 'created_at' => '2026-03-21 18:45:00'],
            ['id' => 10, 'user_id' => 7, 'type' => 'subscription', 'direction' => 'out', 'amount' => 89, 'note' => 'Assinatura Velvet Society', 'created_at' => '2026-03-21 19:40:00', 'creator_id' => 4],
            ['id' => 11, 'user_id' => 4, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 71, 'note' => 'Receita liquida da assinatura Velvet Society', 'created_at' => '2026-03-21 19:40:00', 'subscriber_id' => 7],
            ['id' => 12, 'user_id' => 7, 'type' => 'subscription', 'direction' => 'out', 'amount' => 79, 'note' => 'Assinatura Aurora Club', 'created_at' => '2026-03-21 20:10:00', 'creator_id' => 10],
            ['id' => 13, 'user_id' => 10, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 63, 'note' => 'Receita liquida da assinatura Aurora Club', 'created_at' => '2026-03-21 20:10:00', 'subscriber_id' => 7],
            ['id' => 14, 'user_id' => 8, 'type' => 'top_up', 'direction' => 'in', 'amount' => 420, 'note' => 'Recarga de pacote Lua Cheia', 'created_at' => '2026-03-20 11:20:00'],
            ['id' => 15, 'user_id' => 8, 'type' => 'subscription', 'direction' => 'out', 'amount' => 79, 'note' => 'Assinatura Aurora Club', 'created_at' => '2026-03-20 11:35:00', 'creator_id' => 10],
            ['id' => 16, 'user_id' => 10, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 63, 'note' => 'Receita liquida da assinatura Aurora Club', 'created_at' => '2026-03-20 11:35:00', 'subscriber_id' => 8],
            ['id' => 17, 'user_id' => 12, 'type' => 'top_up', 'direction' => 'in', 'amount' => 500, 'note' => 'Recarga via pix demo', 'created_at' => '2026-03-21 08:50:00'],
            ['id' => 18, 'user_id' => 12, 'type' => 'subscription', 'direction' => 'out', 'amount' => 99, 'note' => 'Assinatura Colecao Eclipse', 'created_at' => '2026-03-21 09:10:00', 'creator_id' => 2],
            ['id' => 19, 'user_id' => 2, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 79, 'note' => 'Receita liquida da assinatura Colecao Eclipse', 'created_at' => '2026-03-21 09:10:00', 'subscriber_id' => 12],
            ['id' => 20, 'user_id' => 12, 'type' => 'subscription', 'direction' => 'out', 'amount' => 69, 'note' => 'Assinatura Noir Circle', 'created_at' => '2026-03-21 09:40:00', 'creator_id' => 11],
            ['id' => 21, 'user_id' => 11, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 55, 'note' => 'Receita liquida da assinatura Noir Circle', 'created_at' => '2026-03-21 09:40:00', 'subscriber_id' => 12],
            ['id' => 22, 'user_id' => 13, 'type' => 'top_up', 'direction' => 'in', 'amount' => 260, 'note' => 'Recarga inicial do assinante', 'created_at' => '2026-03-21 12:05:00'],
            ['id' => 23, 'user_id' => 13, 'type' => 'subscription', 'direction' => 'out', 'amount' => 59, 'note' => 'Assinatura Lunar Mood', 'created_at' => '2026-03-21 12:12:00', 'creator_id' => 2],
            ['id' => 24, 'user_id' => 2, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 47, 'note' => 'Receita liquida da assinatura Lunar Mood', 'created_at' => '2026-03-21 12:12:00', 'subscriber_id' => 13],
            ['id' => 25, 'user_id' => 14, 'type' => 'top_up', 'direction' => 'in', 'amount' => 320, 'note' => 'Pacote demo de estreia', 'created_at' => '2026-03-21 13:20:00'],
            ['id' => 26, 'user_id' => 14, 'type' => 'subscription', 'direction' => 'out', 'amount' => 89, 'note' => 'Assinatura Velvet Society', 'created_at' => '2026-03-21 13:28:00', 'creator_id' => 4],
            ['id' => 27, 'user_id' => 4, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 71, 'note' => 'Receita liquida da assinatura Velvet Society', 'created_at' => '2026-03-21 13:28:00', 'subscriber_id' => 14],
            ['id' => 28, 'user_id' => 14, 'type' => 'subscription', 'direction' => 'out', 'amount' => 59, 'note' => 'Assinatura Lunar Mood', 'created_at' => '2026-03-21 13:31:00', 'creator_id' => 2],
            ['id' => 29, 'user_id' => 2, 'type' => 'subscription_income', 'direction' => 'in', 'amount' => 47, 'note' => 'Receita liquida da assinatura Lunar Mood', 'created_at' => '2026-03-21 13:31:00', 'subscriber_id' => 14],
            ['id' => 30, 'user_id' => 7, 'type' => 'tip', 'direction' => 'out', 'amount' => 35, 'note' => 'Gorjeta enviada na live Aurora Suite', 'created_at' => '2026-03-21 22:20:00', 'creator_id' => 10],
            ['id' => 31, 'user_id' => 10, 'type' => 'tip_income', 'direction' => 'in', 'amount' => 28, 'note' => 'Receita liquida de gorjeta na live Aurora Suite', 'created_at' => '2026-03-21 22:20:00', 'subscriber_id' => 7],
            ['id' => 32, 'user_id' => 8, 'type' => 'tip', 'direction' => 'out', 'amount' => 20, 'note' => 'Gorjeta enviada na live Aurora Suite', 'created_at' => '2026-03-21 22:22:00', 'creator_id' => 10],
            ['id' => 33, 'user_id' => 10, 'type' => 'tip_income', 'direction' => 'in', 'amount' => 16, 'note' => 'Receita liquida de gorjeta na live Aurora Suite', 'created_at' => '2026-03-21 22:22:00', 'subscriber_id' => 8],
            ['id' => 34, 'user_id' => 4, 'type' => 'payout_request', 'direction' => 'out', 'amount' => 120, 'note' => 'Pedido de saque da Velvet Society', 'created_at' => '2026-03-21 16:15:00'],
        ];
    }

    private static function settings(): array
    {
        return [
            'platform_fee_percent' => 20,
            'token_price_brl' => 0.35,
            'withdraw_min_tokens' => 50,
            'withdraw_max_tokens' => 25000,
            'maintenance_mode' => false,
            'slow_mode_seconds' => 3,
            'auto_moderation' => true,
            'blur_sensitive_thumbs' => true,
            'live_chat_enabled' => true,
            'theme' => 'lunar-metamorphosis',
            'announcement' => 'Noite especial com criadores em destaque e novas colecoes em aprovacao.',
        ];
    }
}
