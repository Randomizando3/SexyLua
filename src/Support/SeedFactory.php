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
            'content_items' => [],
            'plans' => [],
            'subscriptions' => [],
            'live_sessions' => [],
            'favorites' => [],
            'saved_items' => [],
            'conversations' => [],
            'messages' => [],
            'notifications' => [],
            'announcements' => [],
            'live_messages' => [],
            'live_signals' => [],
            'live_presence' => [],
            'live_streams' => [],
            'wallet_transactions' => [],
            'settings' => self::settings(),
        ];
    }

    private static function users(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Admin Master',
                'email' => 'admin@sexylua.local',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'headline' => 'Controle total da plataforma.',
                'bio' => 'Responsavel pela operacao, moderacao e configuracoes centrais do SexyLua.',
                'city' => 'Sao Paulo',
                'created_at' => '2026-03-01 09:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Maria Silva',
                'email' => 'maria@sexylua.local',
                'password' => password_hash('creator123', PASSWORD_DEFAULT),
                'role' => 'creator',
                'status' => 'active',
                'headline' => 'Perfil pronto para voce cadastrar conteudos, planos e lives do zero.',
                'bio' => 'Conta base do criador para testes reais de publicacao, configuracoes e monetizacao.',
                'city' => 'Rio de Janeiro',
                'created_at' => '2026-03-02 11:00:00',
            ],
            [
                'id' => 3,
                'name' => 'Bruno Alves',
                'email' => 'assinante@sexylua.local',
                'password' => password_hash('subscriber123', PASSWORD_DEFAULT),
                'role' => 'subscriber',
                'status' => 'active',
                'headline' => 'Conta base para testar assinaturas, carteira e mensagens.',
                'bio' => 'Assinante limpo para validar fluxos reais sem dados demo antigos.',
                'city' => 'Campinas',
                'created_at' => '2026-03-03 16:00:00',
            ],
        ];
    }

    private static function creatorProfiles(): array
    {
        return [
            [
                'id' => 1,
                'user_id' => 2,
                'slug' => 'maria-silva',
                'mood' => 'Lua Nova',
                'cover_style' => 'rose-dawn',
                'featured' => false,
                'followers' => 0,
                'rating' => 5.0,
                'avatar_url' => '',
                'cover_url' => '',
                'payout_method' => 'pix',
                'payout_key' => '',
                'instagram' => '',
                'telegram' => '',
                'stream_key' => '',
            ],
        ];
    }

    private static function settings(): array
    {
        return [
            'platform_fee_percent' => 20,
            'luacoin_price_brl' => 0.07,
            'withdraw_min_luacoins' => 50,
            'withdraw_max_luacoins' => 25000,
            'maintenance_mode' => false,
            'slow_mode_seconds' => 3,
            'auto_moderation' => true,
            'blur_sensitive_thumbs' => true,
            'live_chat_enabled' => true,
            'theme' => 'lunar-metamorphosis',
            'announcement' => '',
            'site_base_url' => '',
            'syncpay_api_base_url' => 'https://api.syncpayments.com.br',
            'syncpay_client_id' => '',
            'syncpay_client_secret' => '',
            'syncpay_api_key' => '',
            'syncpay_webhook_token' => '',
            'syncpay_pix_expires_in_days' => 2,
        ];
    }
}
