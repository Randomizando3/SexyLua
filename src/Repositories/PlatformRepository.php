<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\StoreInterface;
use App\Support\SeedFactory;

final class PlatformRepository
{
    private const LIVE_PRESENCE_TIMEOUT_SECONDS = 90;
    private const LIVE_SIGNAL_TIMEOUT_SECONDS = 180;
    private const LIVE_DEFAULT_SEGMENT_DURATION_SECONDS = 10;
    private const LIVE_SEGMENT_RETENTION_COUNT = 30;
    private const LIVE_DEFAULT_BITRATE_KBPS = 800;
    private const LIVE_DEFAULT_WIDTH = 854;
    private const LIVE_DEFAULT_HEIGHT = 480;
    private const LIVE_DEFAULT_FPS = 30;
    private const LIVE_DEFAULT_GOP_SECONDS = 2;
    private const LIVE_DEFAULT_AUDIO_BITRATE_KBPS = 96;
    private const LIVE_DEFAULT_AUDIO_SAMPLE_RATE = 48000;
    private const CREATOR_CONTENT_STORAGE_LIMIT_BYTES = 52428800;
    private const SEEDED_COLLECTIONS = [
        'users',
        'creator_profiles',
        'content_items',
        'plans',
        'subscriptions',
        'live_sessions',
        'favorites',
        'saved_items',
        'conversations',
        'messages',
        'message_unlocks',
        'live_unlocks',
        'live_darkrooms',
        'notifications',
        'announcements',
        'live_messages',
        'wallet_transactions',
        'settings',
    ];

    private array $collectionCache = [];

    public function __construct(
        private readonly StoreInterface $store,
        private readonly array $config,
    ) {
    }

    public function seedIfMissing(): void
    {
        $missing = [];

        foreach (self::SEEDED_COLLECTIONS as $collection) {
            if (! $this->store->exists($collection)) {
                $missing[] = $collection;
            }
        }

        if ($missing === []) {
            return;
        }

        $seedCollections = SeedFactory::build();

        foreach ($missing as $collection) {
            $payload = is_array($seedCollections[$collection] ?? null) ? $seedCollections[$collection] : [];
            $this->store->write($collection, $payload);
            $this->collectionCache[$collection] = $payload;
        }
    }

    public function findUserById(int $id): ?array
    {
        foreach ($this->users() as $user) {
            if ((int) $user['id'] === $id) {
                return $this->sanitizeUser($user);
            }
        }

        return null;
    }

    public function findUserByEmail(string $email): ?array
    {
        $email = mb_strtolower(trim($email));

        foreach ($this->users() as $user) {
            if (mb_strtolower((string) $user['email']) === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findUserByUsername(string $username): ?array
    {
        $username = $this->normalizeUsername($username);

        if ($username === '') {
            return null;
        }

        foreach ($this->users() as $user) {
            if ($this->normalizeUsername((string) ($user['username'] ?? '')) === $username) {
                return $user;
            }
        }

        return null;
    }

    public function findUserByLogin(string $login): ?array
    {
        $login = trim($login);

        if ($login === '') {
            return null;
        }

        $user = $this->findUserByEmail($login);
        if ($user !== null) {
            return $user;
        }

        return $this->findUserByUsername($login);
    }

    public function normalizeUsername(string $username): string
    {
        $username = mb_strtolower(trim($username));
        if ($username === '') {
            return '';
        }

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
        if ($ascii !== false) {
            $username = strtolower($ascii);
        }

        $username = preg_replace('/[^a-z0-9._-]+/', '', $username) ?? '';

        return trim($username, '._-');
    }

    public function registerUser(array $data): array
    {
        $users = $this->users();
        $userId = $this->store->nextId($users);
        $role = in_array(($data['role'] ?? 'subscriber'), ['subscriber', 'creator'], true) ? $data['role'] : 'subscriber';
        $termsAcceptedAt = trim((string) ($data['terms_accepted_at'] ?? ''));
        $identityDocument = is_array($data['identity_document'] ?? null) ? $data['identity_document'] : null;
        $username = $this->uniqueUsername((string) ($data['username'] ?? ''), null, (string) ($data['email'] ?? ($data['name'] ?? 'usuario')));

        $user = [
            'id' => $userId,
            'name' => trim((string) ($data['name'] ?? 'Novo Usuario')),
            'username' => $username,
            'email' => mb_strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'active',
            'headline' => $role === 'creator' ? 'Novo criador em fase de estreia.' : 'Novo assinante da comunidade SexyLua.',
            'bio' => $role === 'creator' ? 'Perfil criado para publicar conteudo, planos e lives.' : 'Perfil criado para acompanhar criadores, salvar colecoes e conversar.',
            'city' => trim((string) ($data['city'] ?? 'Brasil')),
            'age' => max(18, (int) ($data['age'] ?? 18)),
            'created_at' => date('Y-m-d H:i:s'),
            'terms_accepted_at' => $termsAcceptedAt !== '' ? $termsAcceptedAt : date('Y-m-d H:i:s'),
            'terms_version' => trim((string) ($data['terms_version'] ?? '2026-04')),
            'identity_document' => $identityDocument,
            'verification_status' => $role === 'admin' ? 'approved' : 'pending',
            'verification_note' => '',
            'verification_requested_at' => date('Y-m-d H:i:s'),
            'verification_reviewed_at' => $role === 'admin' ? date('Y-m-d H:i:s') : null,
        ];

        $users[] = $user;
        $this->save('users', $users);

        if ($role === 'creator') {
            $profiles = $this->creatorProfiles();
            $profiles[] = [
                'user_id' => $userId,
                'slug' => $this->uniqueSlug($user['name']),
                'mood' => 'Lua Nova',
                'cover_style' => 'rose-dawn',
                'featured' => false,
                'followers' => 0,
                'rating' => 5.0,
            ];
            $this->save('creator_profiles', $profiles);

            $plans = $this->plans();
            $plans[] = [
                'id' => $this->store->nextId($plans),
                'creator_id' => $userId,
                'name' => 'Plano Inicial',
                'description' => 'Assinatura base criada automaticamente.',
                'price_tokens' => 39,
                'active' => true,
                'perks' => ['Conteudo exclusivo', 'Mensagens diretas', 'Acesso antecipado'],
            ];
            $this->save('plans', $plans);
        } else {
            $settings = $this->settings();
            $signupBonusEnabled = (bool) ($settings['subscriber_signup_bonus_enabled'] ?? true);
            $signupBonusAmount = max(0, (int) ($settings['subscriber_signup_bonus_luacoins'] ?? 10));

            if ($signupBonusEnabled && $signupBonusAmount > 0) {
                $transactions = $this->walletTransactions();
                $transactions[] = [
                    'id' => $this->store->nextId($transactions),
                    'user_id' => $userId,
                    'type' => 'welcome_bonus',
                    'direction' => 'in',
                    'amount' => $signupBonusAmount,
                    'note' => 'Bonus de boas-vindas',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $this->save('wallet_transactions', $transactions);
            }
        }

        return $this->sanitizeUser($user);
    }

    public function homepageData(array $filters = []): array
    {
        $category = \normalize_audience_category((string) ($filters['category'] ?? 'todos'));
        $allCreators = $this->creators();
        usort($allCreators, static fn (array $left, array $right): int => [
            $right['featured'] ? 1 : 0,
            (int) ($right['subscriber_count'] ?? 0),
            (int) ($right['content_count'] ?? 0),
        ] <=> [
            $left['featured'] ? 1 : 0,
            (int) ($left['subscriber_count'] ?? 0),
            (int) ($left['content_count'] ?? 0),
        ]);

        $creators = array_values(array_filter($allCreators, static fn (array $creator): bool => (bool) ($creator['featured'] ?? false)));
        if ($creators === []) {
            $creators = $allCreators;
        }

        $liveNow = array_values(array_filter($this->livesWithCreators(), fn (array $live): bool => $live['status'] === 'live' && $this->matchesAudienceCategory($category, (string) ($live['category'] ?? 'todos'))));
        $upcomingLives = array_values(array_filter($this->livesWithCreators(), fn (array $live): bool => $live['status'] === 'scheduled' && $this->matchesAudienceCategory($category, (string) ($live['category'] ?? 'todos'))));
        $upcomingLives = $this->sortByDate($upcomingLives, 'scheduled_for');
        $featuredContent = array_values(array_filter($this->contentsWithCreators(), fn (array $item): bool => $item['status'] === 'approved' && ! $this->contentIsExpired($item) && $this->matchesAudienceCategory($category, (string) ($item['category'] ?? 'todos'))));

        return [
            'featured_creators' => array_slice($creators, 0, 5),
            'live_now' => array_slice($liveNow, 0, 4),
            'upcoming_lives' => array_slice($upcomingLives, 0, 4),
            'featured_content' => array_slice($featuredContent, 0, 6),
            'audience_category' => $category,
            'stats' => [
                'creators' => count($allCreators),
                'live_now' => count($liveNow),
                'approved_content' => count($featuredContent),
                'subscribers' => count(array_filter($this->users(), static fn (array $user): bool => $user['role'] === 'subscriber' && $user['status'] === 'active')),
            ],
            'settings' => $this->settings(),
        ];
    }

    public function exploreData(array $filters = []): array
    {
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $kind = trim((string) ($filters['kind'] ?? ''));
        $liveOnly = ($filters['live_only'] ?? false) === true;
        $includeScheduled = ($filters['include_scheduled'] ?? false) === true;
        $category = \normalize_audience_category((string) ($filters['category'] ?? 'todos'));

        $creators = array_values(array_filter($this->creators(), function (array $creator) use ($query): bool {
            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower($creator['name'] . ' ' . $creator['headline'] . ' ' . $creator['bio']);

            return str_contains($haystack, $query);
        }));

        $content = array_values(array_filter($this->contentsWithCreators(), function (array $item) use ($query, $kind, $category): bool {
            if ($item['status'] !== 'approved' || $this->contentIsExpired($item)) {
                return false;
            }

            if (! $this->matchesAudienceCategory($category, (string) ($item['category'] ?? 'todos'))) {
                return false;
            }

            if ($kind !== '' && $item['kind'] !== $kind) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower($item['title'] . ' ' . $item['excerpt'] . ' ' . $item['creator']['name']);

            return str_contains($haystack, $query);
        }));

        $allowedLiveStatuses = $includeScheduled ? ['live', 'scheduled'] : ['live'];
        $lives = array_values(array_filter($this->livesWithCreators(), function (array $live) use ($allowedLiveStatuses, $query, $category): bool {
            if (! in_array($live['status'], $allowedLiveStatuses, true)) {
                return false;
            }

            if (! $this->matchesAudienceCategory($category, (string) ($live['category'] ?? 'todos'))) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($live['title'] ?? '') . ' ' . ($live['description'] ?? '') . ' ' . ($live['creator']['name'] ?? '')));

            return str_contains($haystack, $query);
        }));

        if ($liveOnly) {
            $creators = [];
            $content = [];
        }

        usort($lives, static function (array $left, array $right): int {
            $priority = static function (array $live): int {
                return match ((string) ($live['status'] ?? '')) {
                    'live' => 0,
                    'scheduled' => 1,
                    default => 2,
                };
            };

            $leftPriority = $priority($left);
            $rightPriority = $priority($right);
            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            if ($leftPriority === 0) {
                return ((int) ($right['viewer_count'] ?? 0)) <=> ((int) ($left['viewer_count'] ?? 0));
            }

            return strcmp((string) ($left['scheduled_for'] ?? ''), (string) ($right['scheduled_for'] ?? ''));
        });

        return [
            'creators' => $creators,
            'content' => $content,
            'lives' => $lives,
            'filters' => [
                'q' => $query,
                'kind' => $kind,
                'live_only' => $liveOnly,
                'include_scheduled' => $includeScheduled,
                'category' => $category,
            ],
        ];
    }

    public function findCreatorBySlugOrId(?string $slug, ?int $id): ?array
    {
        foreach ($this->creators() as $creator) {
            if ($id !== null && (int) $creator['id'] === $id) {
                return $creator;
            }

            if ($slug !== null && $creator['slug'] === $slug) {
                return $creator;
            }
        }

        return null;
    }

    public function creatorProfileData(int $creatorId, ?int $viewerId = null, array $filters = []): ?array
    {
        $creator = $this->findCreatorBySlugOrId(null, $creatorId);
        $category = \normalize_audience_category((string) ($filters['category'] ?? 'todos'));

        if (! $creator) {
            return null;
        }

        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId && (bool) $plan['active']));
        $content = array_values(array_filter($this->contentsWithCreators(), fn (array $item): bool => (int) $item['creator_id'] === $creatorId && $item['status'] === 'approved' && ! $this->contentIsExpired($item) && $this->matchesAudienceCategory($category, (string) ($item['category'] ?? 'todos'))));
        $relatedCreators = array_values(array_filter($this->creators(), static fn (array $item): bool => (int) $item['id'] !== $creatorId));
        $upcomingLives = array_values(array_filter(
            $this->livesWithCreators(),
            fn (array $live): bool => (int) ($live['creator_id'] ?? 0) === $creatorId && (string) ($live['status'] ?? '') === 'scheduled' && $this->matchesAudienceCategory($category, (string) ($live['category'] ?? 'todos'))
        ));
        $upcomingLives = $this->sortByDate($upcomingLives, 'scheduled_for');
        $upcomingLives = array_reverse($upcomingLives);

        return [
            'creator' => $creator,
            'plans' => $plans,
            'content' => $content,
            'upcoming_lives' => array_slice($upcomingLives, 0, 8),
            'audience_category' => $category,
            'is_favorite' => $viewerId ? $this->isFavoriteCreator($viewerId, $creatorId) : false,
            'is_subscribed' => $viewerId ? $this->activeSubscriptionFor($viewerId, $creatorId) !== null : false,
            'related_creators' => array_slice($relatedCreators, 0, 5),
        ];
    }

    public function liveRoomData(int $liveId, ?int $viewerId = null): ?array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live) {
            return null;
        }

        $access = $this->accessStateForLive($live, $viewerId);
        $decoratedLive = $this->hydrateLiveRuntime($this->decorateLive($live));
        $messages = $this->liveChatMessagesFor($liveId, 20);
        $related = array_values(array_filter($this->livesWithCreators(), static fn (array $item): bool => (int) ($item['id'] ?? 0) !== $liveId));
        $engagement = $this->liveEngagementData($decoratedLive);
        $stream = $this->publicLiveStreamState($liveId);
        $shouldHideRoomData = ! (bool) ($access['granted'] ?? false)
            && ((bool) ($access['requires_vip_unlock'] ?? false) || (bool) ($access['requires_darkroom_wait'] ?? false));

        if (! (bool) ($access['granted'] ?? false)) {
            $stream['hls_url'] = '';
            $stream['ready'] = false;
        }

        return [
            'live' => $decoratedLive,
            'messages' => $shouldHideRoomData ? [] : $messages,
            'related_lives' => array_slice($related, 0, 5),
            'recent_tips' => $shouldHideRoomData ? [] : $engagement['recent_tips'],
            'top_supporters' => $shouldHideRoomData ? [] : $engagement['top_supporters'],
            'tip_total_amount' => (int) ($engagement['tip_total_amount'] ?? 0),
            'can_watch' => (bool) ($access['granted'] ?? false),
            'requires_login' => (bool) ($access['requires_login'] ?? false),
            'requires_subscription' => (bool) ($access['requires_subscription'] ?? false),
            'requires_vip_unlock' => (bool) ($access['requires_vip_unlock'] ?? false),
            'vip_unlocked' => (bool) ($access['vip_unlocked'] ?? false),
            'vip_unlock_price' => (int) ($access['vip_price_tokens'] ?? (int) ($decoratedLive['price_tokens'] ?? 0)),
            'darkroom_available' => (bool) ($access['darkroom_available'] ?? false),
            'darkroom_active' => (bool) ($access['darkroom_active'] ?? false),
            'requires_darkroom_wait' => (bool) ($access['requires_darkroom_wait'] ?? false),
            'darkroom_is_owner' => (bool) ($access['darkroom_is_owner'] ?? false),
            'darkroom_price_tokens' => (int) ($access['darkroom_price_tokens'] ?? 0),
            'darkroom_duration_minutes' => (int) ($access['darkroom_duration_minutes'] ?? 0),
            'darkroom_remaining_seconds' => (int) ($access['darkroom_remaining_seconds'] ?? 0),
            'darkroom_owner_name' => (string) ($access['darkroom_owner_name'] ?? ''),
            'darkroom_started_at' => (string) ($access['darkroom_started_at'] ?? ''),
            'darkroom_ends_at' => (string) ($access['darkroom_ends_at'] ?? ''),
            'access_message' => (string) ($access['access_message'] ?? ''),
            'can_chat' => $this->canUserChatInLive($decoratedLive, $viewerId),
            'can_tip' => $viewerId !== null && (bool) ($access['granted'] ?? false),
            'stream' => $stream,
            'priority_tip_tiers' => $this->priorityTipTiersForLive($decoratedLive),
            'priority_tip_messages' => $this->priorityTipMessagesForLive($decoratedLive),
            'priority_alert' => $this->latestPriorityAlertForLive($liveId),
        ];
    }

    public function postLiveMessage(int $liveId, int $userId, string $body): bool
    {
        $body = trim($body);
        $live = $this->findLiveById($liveId);

        if (
            $body === ''
            || ! $live
            || ! $this->findUserById($userId)
            || ! $this->canUserChatInLive($live, $userId)
        ) {
            return false;
        }

        $messages = $this->liveMessages();
        $messages[] = [
            'id' => $this->store->nextId($messages),
            'live_id' => $liveId,
            'sender_id' => $userId,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
            'kind' => 'chat',
        ];
        $this->save('live_messages', $messages);

        return true;
    }

    public function subscriberDashboardData(int $subscriberId): array
    {
        $subscriber = $this->findUserById($subscriberId) ?? [];
        $wallet = $this->walletData($subscriberId);
        $activeSubscriptions = $this->activeSubscriptionsForSubscriber($subscriberId);
        $activeCreatorIds = array_map(static fn (array $item): int => (int) ($item['creator_id'] ?? 0), $activeSubscriptions);
        $upcomingLives = array_values(array_filter($this->livesWithCreators(), function (array $live) use ($activeSubscriptions): bool {
            foreach ($activeSubscriptions as $subscription) {
                if ((int) $subscription['creator_id'] === (int) $live['creator_id'] && in_array((string) ($live['status'] ?? ''), ['live', 'scheduled'], true)) {
                    return true;
                }
            }

            return false;
        }));
        $favorites = $this->favoritesData($subscriberId);
        $conversations = $this->conversationList($subscriberId);
        $availablePlans = array_values(array_filter(
            $this->plansWithCreators(),
            static fn (array $plan): bool => ! in_array((int) ($plan['creator_id'] ?? 0), $activeCreatorIds, true) && (bool) ($plan['active'] ?? false)
        ));

        return [
            'subscriber' => $subscriber,
            'wallet_balance' => $wallet['balance'],
            'wallet' => $wallet,
            'subscriptions' => array_slice($activeSubscriptions, 0, 4),
            'active_subscriptions' => $activeSubscriptions,
            'favorites_count' => count($favorites['favorite_creators']),
            'saved_count' => count($favorites['saved_content']),
            'conversations' => array_slice($conversations, 0, 3),
            'recent_messages_count' => count($conversations),
            'upcoming_lives' => array_slice($this->sortByDate($upcomingLives, 'scheduled_for'), 0, 4),
            'available_plans' => array_slice($availablePlans, 0, 4),
            'transactions' => array_slice($wallet['transactions'], 0, 5),
        ];
    }

    public function subscriberSettingsData(int $subscriberId): array
    {
        $subscriber = $this->findUserById($subscriberId) ?? [];
        $wallet = $this->walletData($subscriberId);
        $favorites = $this->favoritesData($subscriberId);
        $subscriptions = $this->activeSubscriptionsForSubscriber($subscriberId);

        return [
            'subscriber' => $subscriber,
            'wallet' => $wallet,
            'verification' => [
                'status' => $this->verificationStatusValue((string) ($subscriber['verification_status'] ?? 'pending')),
                'identity_document' => is_array($subscriber['identity_document'] ?? null) ? $subscriber['identity_document'] : null,
            ],
            'stats' => [
                'subscriptions' => count(array_filter($subscriptions, static fn (array $subscription): bool => (string) ($subscription['status'] ?? '') === 'active')),
                'favorites' => count($favorites['favorite_creators'] ?? []),
                'saved' => count($favorites['saved_content'] ?? []),
                'balance' => (int) ($wallet['balance'] ?? 0),
            ],
            'recent_transactions' => array_slice($wallet['transactions'] ?? [], 0, 4),
        ];
    }

    public function subscriberSubscriptionsData(int $subscriberId, array $filters = []): array
    {
        $subscriber = $this->findUserById($subscriberId) ?? [];
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $active = $this->activeSubscriptionsForSubscriber($subscriberId);
        $activeCreatorIds = array_map(static fn (array $item): int => (int) ($item['creator_id'] ?? 0), $active);
        $availablePlans = array_values(array_filter(
            $this->plansWithCreators(),
            static fn (array $plan): bool => ! in_array((int) ($plan['creator_id'] ?? 0), $activeCreatorIds, true) && (bool) ($plan['active'] ?? false)
        ));
        $filterSubscription = static function (array $subscription) use ($query, $status): bool {
            if ($status !== '' && (string) ($subscription['status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) (($subscription['creator']['name'] ?? '') . ' ' . ($subscription['creator']['email'] ?? '') . ' ' . ($subscription['plan']['name'] ?? ''))
            );

            return str_contains($haystack, $query);
        };
        $filterPlan = static function (array $plan) use ($query): bool {
            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) (($plan['creator']['name'] ?? '') . ' ' . ($plan['name'] ?? '') . ' ' . ($plan['description'] ?? ''))
            );

            return str_contains($haystack, $query);
        };
        $filteredSubscriptions = array_values(array_filter($active, $filterSubscription));
        $filteredPlans = array_values(array_filter($availablePlans, $filterPlan));
        $monthlySpend = array_reduce(
            array_filter($active, static fn (array $subscription): bool => (string) ($subscription['status'] ?? '') === 'active'),
            static fn (int $carry, array $subscription): int => $carry + (int) (($subscription['plan']['price_tokens'] ?? 0)),
            0
        );

        return [
            'subscriber' => $subscriber,
            'active_subscriptions' => $active,
            'filtered_subscriptions' => $filteredSubscriptions,
            'available_plans' => $availablePlans,
            'filtered_plans' => $filteredPlans,
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
            'summary' => [
                'active_count' => count(array_filter($active, static fn (array $subscription): bool => (string) ($subscription['status'] ?? '') === 'active')),
                'available_count' => count($availablePlans),
                'monthly_spend' => $monthlySpend,
            ],
        ];
    }

    public function favoritesData(int $subscriberId): array
    {
        $favoriteRows = array_values(array_filter($this->favorites(), static fn (array $favorite): bool => (int) ($favorite['user_id'] ?? 0) === $subscriberId));
        $savedRows = array_values(array_filter($this->savedItems(), static fn (array $saved): bool => (int) ($saved['user_id'] ?? 0) === $subscriberId));
        $favoriteCreators = array_values(array_filter(array_map(
            fn (array $row): ?array => $this->findCreatorBySlugOrId(null, (int) ($row['creator_id'] ?? 0)),
            $favoriteRows
        )));
        $savedContent = array_values(array_filter(array_map(
            fn (array $row): ?array => $this->findContentWithCreator((int) ($row['content_id'] ?? 0)),
            $savedRows
        )));
        $favoriteCreatorIds = array_map(static fn (array $creator): int => (int) ($creator['id'] ?? 0), $favoriteCreators);
        $savedContentIds = array_map(static fn (array $item): int => (int) ($item['id'] ?? 0), $savedContent);
        $trackedLives = array_values(array_filter(
            $this->livesWithCreators(),
            static fn (array $live): bool => in_array((int) ($live['creator_id'] ?? 0), $favoriteCreatorIds, true) && in_array((string) ($live['status'] ?? ''), ['live', 'scheduled'], true)
        ));
        $suggestedCreators = array_values(array_filter(
            $this->creators(),
            static fn (array $creator): bool => ! in_array((int) ($creator['id'] ?? 0), $favoriteCreatorIds, true) && (int) ($creator['id'] ?? 0) !== $subscriberId
        ));
        usort($suggestedCreators, static fn (array $left, array $right): int => [$right['featured'] ? 1 : 0, $right['followers'] ?? 0] <=> [$left['featured'] ? 1 : 0, $left['followers'] ?? 0]);
        $suggestedContent = array_values(array_filter(
            $this->contentsWithCreators(),
            static fn (array $item): bool => ! in_array((int) ($item['id'] ?? 0), $savedContentIds, true) && (string) ($item['status'] ?? '') === 'approved'
        ));

        return [
            'subscriber' => $this->findUserById($subscriberId),
            'favorite_creators' => $favoriteCreators,
            'saved_content' => $savedContent,
            'tracked_lives' => array_slice($this->sortByDate($trackedLives, 'scheduled_for'), 0, 4),
            'suggested_creators' => array_slice($suggestedCreators, 0, 4),
            'suggested_content' => array_slice($this->sortByDate($suggestedContent, 'created_at'), 0, 4),
        ];
    }

    public function conversationsData(int $subscriberId, ?int $conversationId = null, array $filters = []): array
    {
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $announcementId = max(0, (int) ($filters['announcement'] ?? 0));
        $subscriber = $this->findUserById($subscriberId) ?? [];
        $announcements = $this->announcementsForUser($subscriber, 12, '/subscriber/messages');
        $conversations = $this->conversationList($subscriberId);
        $filteredConversations = array_values(array_filter($conversations, static function (array $conversation) use ($query): bool {
            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) (($conversation['creator']['name'] ?? '') . ' ' . ($conversation['creator']['email'] ?? '') . ' ' . ($conversation['latest_message']['body'] ?? ''))
            );

            return str_contains($haystack, $query);
        }));
        $selected = null;

        if ($conversationId !== null) {
            foreach ($filteredConversations as $conversation) {
                if ((int) $conversation['id'] === $conversationId) {
                    $selected = $conversation;
                    break;
                }
            }
        }

        if ($selected === null && $conversationId !== null) {
            $selected = $filteredConversations[0] ?? null;
        }

        $messages = [];

        if ($selected) {
            $messages = $this->messagesForConversation((int) ($selected['id'] ?? 0), $subscriberId);
        }

        return [
            'subscriber' => $subscriber,
            'announcements' => $announcements,
            'selected_announcement' => $this->findAnnouncementForUser($announcementId, $subscriber),
            'conversations' => $conversations,
            'filtered_conversations' => $filteredConversations,
            'selected_conversation' => $selected,
            'messages' => $messages,
            'filters' => [
                'q' => $query,
                'announcement' => $announcementId,
            ],
            'summary' => [
                'announcement_count' => count($announcements),
                'conversation_count' => count($conversations),
                'visible_count' => count($filteredConversations),
            ],
        ];
    }

    public function creatorConversationsData(int $creatorId, ?int $conversationId = null, array $filters = []): array
    {
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $announcementId = max(0, (int) ($filters['announcement'] ?? 0));
        $creator = $this->findCreatorBySlugOrId(null, $creatorId) ?? [];
        $creatorUser = $this->findUserById($creatorId) ?? $creator;
        $announcements = $this->announcementsForUser($creatorUser, 12, '/creator/messages');
        $conversations = $this->creatorConversationList($creatorId);
        $filteredConversations = array_values(array_filter($conversations, static function (array $conversation) use ($query): bool {
            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) (($conversation['subscriber']['name'] ?? '') . ' ' . ($conversation['subscriber']['email'] ?? '') . ' ' . ($conversation['latest_message']['body'] ?? ''))
            );

            return str_contains($haystack, $query);
        }));
        $selected = null;

        if ($conversationId !== null) {
            foreach ($filteredConversations as $conversation) {
                if ((int) ($conversation['id'] ?? 0) === $conversationId) {
                    $selected = $conversation;
                    break;
                }
            }
        }

        if ($selected === null && $conversationId !== null) {
            $selected = $filteredConversations[0] ?? null;
        }

        $messages = [];

        if ($selected !== null) {
            $messages = $this->messagesForConversation((int) ($selected['id'] ?? 0), $creatorId);
        }

        return [
            'creator' => $creator,
            'announcements' => $announcements,
            'selected_announcement' => $this->findAnnouncementForUser($announcementId, $creatorUser),
            'conversations' => $conversations,
            'filtered_conversations' => $filteredConversations,
            'selected_conversation' => $selected,
            'messages' => $messages,
            'available_plans' => array_values(array_filter($this->plans(), static fn (array $plan): bool => (int) ($plan['creator_id'] ?? 0) === $creatorId && (bool) ($plan['active'] ?? false))),
            'filters' => [
                'q' => $query,
                'announcement' => $announcementId,
            ],
            'summary' => [
                'announcement_count' => count($announcements),
                'conversation_count' => count($conversations),
                'visible_count' => count($filteredConversations),
            ],
        ];
    }

    public function adminMessagesData(array $filters = []): array
    {
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $audience = trim((string) ($filters['audience'] ?? ''));
        $announcements = $this->sortByDate($this->announcements(), 'created_at');
        $filtered = array_values(array_filter($announcements, static function (array $announcement) use ($query, $audience): bool {
            if ($audience !== '' && (string) ($announcement['audience'] ?? '') !== $audience) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) (($announcement['title'] ?? '') . ' ' . ($announcement['body'] ?? '') . ' ' . ($announcement['audience'] ?? ''))
            );

            return str_contains($haystack, $query);
        }));

        return [
            'announcements' => array_map(fn (array $announcement): array => $this->decorateAnnouncementForAdmin($announcement), $filtered),
            'recent_announcements' => array_slice($announcements, 0, 8),
            'filters' => [
                'q' => $query,
                'audience' => $audience,
            ],
            'summary' => [
                'total' => count($announcements),
                'all' => count(array_filter($announcements, static fn (array $item): bool => (string) ($item['audience'] ?? '') === 'all')),
                'creators' => count(array_filter($announcements, static fn (array $item): bool => (string) ($item['audience'] ?? '') === 'creator')),
                'subscribers' => count(array_filter($announcements, static fn (array $item): bool => (string) ($item['audience'] ?? '') === 'subscriber')),
            ],
        ];
    }

    public function topbarFeedDataForUser(int $userId): array
    {
        $user = $this->findUserById($userId);

        if ($user === null) {
            return [
                'notifications' => ['items' => [], 'latest_marker' => 0],
                'messages' => ['items' => [], 'latest_marker' => 0],
            ];
        }

        return [
            'notifications' => $this->notificationFeedForUser($user),
            'messages' => $this->messageFeedForUser($user),
        ];
    }

    public function walletData(int $userId, array $filters = []): array
    {
        $transactions = array_map(function (array $transaction): array {
            $transaction['counts_for_balance'] = $this->transactionCountsForBalance($transaction);

            return $transaction;
        }, $this->walletTransactionsFor($userId));
        $inflow = 0;
        $outflow = 0;
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $type = trim((string) ($filters['type'] ?? ''));

        foreach ($transactions as $transaction) {
            if (! (bool) ($transaction['counts_for_balance'] ?? false)) {
                continue;
            }

            if (($transaction['direction'] ?? 'in') === 'in') {
                $inflow += (int) ($transaction['amount'] ?? 0);
            } else {
                $outflow += (int) ($transaction['amount'] ?? 0);
            }
        }

        $filteredTransactions = array_values(array_filter($transactions, static function (array $transaction) use ($query, $type): bool {
            if ($type !== '' && ! str_contains((string) ($transaction['type'] ?? ''), $type)) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($transaction['note'] ?? '') . ' ' . ($transaction['type'] ?? '')));

            return str_contains($haystack, $query);
        }));
        $topUpTotal = array_reduce(
            array_filter($transactions, static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'top_up' && (bool) ($transaction['counts_for_balance'] ?? false)),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );
        $subscriptionSpend = array_reduce(
            array_filter($transactions, static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'subscription'),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );
        $tipSpend = array_reduce(
            array_filter($transactions, static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'tip'),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );
        $instantContentSpend = array_reduce(
            array_filter($transactions, static fn (array $transaction): bool => in_array((string) ($transaction['type'] ?? ''), ['instant_content', 'vip_live', 'darkroom'], true)),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );

        return [
            'balance' => $this->walletBalance($userId),
            'inflow' => $inflow,
            'outflow' => $outflow,
            'transactions' => $transactions,
            'filtered_transactions' => $filteredTransactions,
            'filters' => [
                'q' => $query,
                'type' => $type,
            ],
            'summary' => [
                'top_up_total' => $topUpTotal,
                'subscription_spend' => $subscriptionSpend,
                'tip_spend' => $tipSpend,
                'instant_content_spend' => $instantContentSpend,
            ],
        ];
    }

    public function addFunds(int $userId, int $tokens): bool
    {
        if ($tokens <= 0) {
            return false;
        }

        $transactions = $this->walletTransactions();
        $transactions[] = [
            'id' => $this->store->nextId($transactions),
            'user_id' => $userId,
            'type' => 'top_up',
            'direction' => 'in',
            'amount' => $tokens,
            'note' => 'Recarga manual de LuaCoins',
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('wallet_transactions', $transactions);

        return true;
    }

    public function createWalletTopUpRequest(int $userId, int $luacoins, string $provider = 'syncpay'): ?array
    {
        $minimumDeposit = (int) ($this->settings()['deposit_min_luacoins'] ?? 100);

        if ($luacoins < $minimumDeposit || ! $this->findUserById($userId)) {
            return null;
        }

        $settings = $this->settings();
        $price = (float) ($settings['luacoin_price_brl'] ?? 0.07);
        $transactions = $this->walletTransactions();
        $transactionId = $this->store->nextId($transactions);
        $externalReference = sprintf('topup-%d-%s', $transactionId, bin2hex(random_bytes(4)));

        $transaction = [
            'id' => $transactionId,
            'user_id' => $userId,
            'type' => 'top_up_pending',
            'direction' => 'in',
            'amount' => $luacoins,
            'amount_brl_expected' => round($luacoins * $price, 2),
            'note' => 'Recarga de LuaCoins aguardando pagamento',
            'provider' => $provider,
            'external_reference' => $externalReference,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $transactions[] = $transaction;
        $this->save('wallet_transactions', $transactions);

        return $transaction;
    }

    public function discardWalletTopUpRequest(int $userId, int $transactionId): bool
    {
        $transactions = $this->walletTransactions();
        $before = count($transactions);
        $transactions = array_values(array_filter($transactions, static function (array $transaction) use ($userId, $transactionId): bool {
            if ((int) ($transaction['id'] ?? 0) !== $transactionId) {
                return true;
            }

            if ((int) ($transaction['user_id'] ?? 0) !== $userId) {
                return true;
            }

            if ((string) ($transaction['type'] ?? '') !== 'top_up_pending') {
                return true;
            }

            return false;
        }));

        if (count($transactions) === $before) {
            return false;
        }

        $this->save('wallet_transactions', $transactions);

        return true;
    }

    public function attachWalletTopUpCheckout(int $transactionId, array $checkout): bool
    {
        $transactions = $this->walletTransactions();
        $changed = false;

        foreach ($transactions as &$transaction) {
            if ((int) ($transaction['id'] ?? 0) !== $transactionId || (string) ($transaction['type'] ?? '') !== 'top_up_pending') {
                continue;
            }

            $transaction['checkout_url'] = (string) ($checkout['checkout_url'] ?? $checkout['init_point'] ?? $transaction['checkout_url'] ?? '');
            $transaction['sandbox_checkout_url'] = (string) ($checkout['sandbox_checkout_url'] ?? $checkout['sandbox_init_point'] ?? $transaction['sandbox_checkout_url'] ?? '');
            $transaction['provider_checkout_id'] = (string) ($checkout['data']['identifier'] ?? $checkout['identifier'] ?? $checkout['client_id'] ?? $checkout['id'] ?? $transaction['provider_checkout_id'] ?? '');
            $transaction['provider_payment_id'] = (string) ($checkout['data']['identifier'] ?? $checkout['identifier'] ?? $checkout['idTransaction'] ?? $checkout['idtransaction'] ?? $transaction['provider_payment_id'] ?? '');
            $transaction['provider_status'] = $this->normalizeSyncPayStatus((string) ($checkout['status_transaction'] ?? $checkout['status'] ?? $transaction['provider_status'] ?? 'pending'));
            $transaction['provider_status_raw'] = trim((string) ($checkout['status_transaction'] ?? $checkout['status'] ?? $transaction['provider_status_raw'] ?? ''));
            $transaction['pix_code'] = (string) ($checkout['data']['pix_code'] ?? $checkout['pix_code'] ?? $checkout['paymentCode'] ?? $checkout['paymentcode'] ?? $transaction['pix_code'] ?? '');
            $transaction['pix_code_base64'] = (string) ($checkout['paymentCodeBase64'] ?? $checkout['paymentcodebase64'] ?? $transaction['pix_code_base64'] ?? '');
            $transaction['provider_payload'] = $checkout + (array) ($transaction['provider_payload'] ?? []);
            $changed = true;
            break;
        }
        unset($transaction);

        if ($changed) {
            $this->save('wallet_transactions', $transactions);
        }

        return $changed;
    }

    public function syncSyncPayWalletTopUp(int $transactionId, array $payment): bool
    {
        $transactions = $this->walletTransactions();
        $changed = false;
        $notifyUserId = 0;
        $notifyStatus = '';
        $notifyAmount = 0;
        $bonusAmount = 0;
        $settings = $this->settings();
        $topUpBonusPercent = max(0, min(100, (int) ($settings['topup_bonus_percent'] ?? 0)));
        $rawStatus = (string) (
            $payment['data']['status']
            ?? $payment['status_transaction']
            ?? $payment['status']
            ?? $payment['situacao']
            ?? 'pending'
        );
        $status = $this->normalizeSyncPayStatus($rawStatus);
        $paymentId = (string) (
            $payment['data']['reference_id']
            ?? $payment['reference_id']
            ?? $payment['data']['identifier']
            ?? $payment['identifier']
            ?? $payment['data']['idtransaction']
            ?? $payment['data']['idTransaction']
            ?? $payment['idTransaction']
            ?? $payment['data']['id']
            ?? $payment['id']
            ?? ''
        );
        $externalReference = (string) (
            $payment['data']['externalreference']
            ?? $payment['externalreference']
            ?? $payment['data']['external_reference']
            ?? $payment['external_reference']
            ?? ''
        );
        $approvedAt = (string) (
            $payment['data']['updated_at']
            ?? $payment['updated_at']
            ?? $payment['data']['data_registro']
            ?? $payment['data_registro']
            ?? date('Y-m-d H:i:s')
        );
        $pixCode = (string) (
            $payment['data']['pix_code']
            ?? $payment['pix_code']
            ?? $payment['data']['paymentcode']
            ?? $payment['paymentCode']
            ?? $payment['paymentcode']
            ?? ''
        );
        $pixCodeBase64 = (string) (
            $payment['data']['paymentCodeBase64']
            ?? $payment['paymentCodeBase64']
            ?? $payment['paymentcodebase64']
            ?? ''
        );
        $amountPaid = (float) (
            $payment['data']['deposito_liquido']
            ?? $payment['deposito_liquido']
            ?? $payment['data']['final_amount']
            ?? $payment['final_amount']
            ?? $payment['data']['amount']
            ?? $payment['amount']
            ?? 0
        );

        foreach ($transactions as &$transaction) {
            if ((int) ($transaction['id'] ?? 0) !== $transactionId) {
                continue;
            }

            if ((string) ($transaction['type'] ?? '') === 'top_up' && in_array((string) ($transaction['status'] ?? ''), ['approved', 'completed', 'paid'], true)) {
                return true;
            }

            $previousType = (string) ($transaction['type'] ?? '');
            $previousStatus = (string) ($transaction['status'] ?? 'pending');

            $transaction['provider'] = 'syncpay';
            $transaction['provider_payment_id'] = $paymentId !== '' ? $paymentId : (string) ($transaction['provider_payment_id'] ?? '');
            $transaction['provider_status'] = $status;
            $transaction['provider_status_raw'] = trim($rawStatus);
            $transaction['provider_payload'] = $payment;
            $transaction['pix_code'] = $pixCode !== '' ? $pixCode : (string) ($transaction['pix_code'] ?? '');
            $transaction['pix_code_base64'] = $pixCodeBase64 !== '' ? $pixCodeBase64 : (string) ($transaction['pix_code_base64'] ?? '');
            if ($externalReference !== '') {
                $transaction['external_reference'] = $externalReference;
            }
            if ($amountPaid > 0) {
                $transaction['amount_brl_paid'] = round($amountPaid, 2);
            }

            if (in_array($status, ['approved', 'paid', 'completed'], true)) {
                $transaction['type'] = 'top_up';
                $transaction['status'] = 'approved';
                $transaction['note'] = 'Recarga SyncPay aprovada';
                $transaction['approved_at'] = $approvedAt;
                if (! in_array($previousStatus, ['approved', 'completed', 'paid'], true) || $previousType !== 'top_up') {
                    $notifyUserId = (int) ($transaction['user_id'] ?? 0);
                    $notifyStatus = 'approved';
                    $notifyAmount = (int) ($transaction['amount'] ?? 0);
                    if ($topUpBonusPercent > 0) {
                        $bonusAmount = max(0, (int) round($notifyAmount * ($topUpBonusPercent / 100)));
                    }
                }
            } elseif (in_array($status, ['rejected', 'cancelled', 'canceled', 'expired', 'failed', 'refunded', 'charged_back'], true)) {
                $transaction['status'] = $status;
                $transaction['note'] = 'Recarga SyncPay nao aprovada';
                if (! in_array($previousStatus, ['rejected', 'cancelled', 'canceled', 'expired', 'failed', 'refunded', 'charged_back'], true)) {
                    $notifyUserId = (int) ($transaction['user_id'] ?? 0);
                    $notifyStatus = 'rejected';
                    $notifyAmount = (int) ($transaction['amount'] ?? 0);
                }
            } else {
                $transaction['status'] = $status !== '' ? $status : 'pending';
                $transaction['note'] = 'Recarga de LuaCoins aguardando pagamento';
            }

            $changed = true;
            break;
        }
        unset($transaction);

        if ($changed) {
            if ($notifyStatus === 'approved' && $notifyUserId > 0 && $bonusAmount > 0 && ! $this->hasWalletTransactionByRelatedId($transactions, 'top_up_bonus', $transactionId)) {
                $transactions[] = [
                    'id' => $this->store->nextId($transactions),
                    'user_id' => $notifyUserId,
                    'type' => 'top_up_bonus',
                    'direction' => 'in',
                    'amount' => $bonusAmount,
                    'note' => 'Bonus da recarga de LuaCoins',
                    'status' => 'approved',
                    'related_transaction_id' => $transactionId,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }

            $this->save('wallet_transactions', $transactions);
        }

        if ($notifyUserId > 0) {
            $title = $notifyStatus === 'approved' ? 'Recarga confirmada' : 'Recarga nao aprovada';
            $body = $notifyStatus === 'approved'
                ? 'Sua recarga de ' . $notifyAmount . ' LuaCoins foi aprovada.' . ($bonusAmount > 0 ? ' Bonus aplicado: +' . $bonusAmount . ' LuaCoins.' : '')
                : 'Sua tentativa de recarga de ' . $notifyAmount . ' LuaCoins nao foi aprovada.';
            $this->notifyUser($notifyUserId, 'wallet', $title, $body, '/subscriber/wallet');
        }

        return $changed;
    }

    public function findWalletTransactionForUser(int $userId, int $transactionId): ?array
    {
        foreach ($this->walletTransactionsFor($userId) as $transaction) {
            if ((int) ($transaction['id'] ?? 0) === $transactionId) {
                return $transaction;
            }
        }

        return null;
    }

    public function latestPendingWalletTopUpForUser(int $userId): ?array
    {
        foreach ($this->walletTransactionsFor($userId) as $transaction) {
            if ((string) ($transaction['type'] ?? '') === 'top_up_pending' && (string) ($transaction['status'] ?? 'pending') === 'pending') {
                return $transaction;
            }
        }

        return null;
    }

    public function latestSyncableWalletTopUpForUser(int $userId): ?array
    {
        $terminalStatuses = ['rejected', 'cancelled', 'canceled', 'expired', 'failed', 'refunded', 'charged_back'];

        foreach ($this->walletTransactionsFor($userId) as $transaction) {
            if ((string) ($transaction['type'] ?? '') !== 'top_up_pending') {
                continue;
            }

            $status = strtolower((string) ($transaction['status'] ?? 'pending'));
            if (in_array($status, $terminalStatuses, true)) {
                continue;
            }

            $providerPaymentId = trim((string) ($transaction['provider_payment_id'] ?? $transaction['provider_checkout_id'] ?? ''));
            if ($providerPaymentId === '') {
                continue;
            }

            return $transaction;
        }

        return null;
    }

    public function findWalletTopUpByProviderReference(string $providerPaymentId = '', string $externalReference = ''): ?array
    {
        $providerPaymentId = trim($providerPaymentId);
        $externalReference = trim($externalReference);

        foreach ($this->walletTransactions() as $transaction) {
            if (! in_array((string) ($transaction['type'] ?? ''), ['top_up_pending', 'top_up'], true)) {
                continue;
            }

            if ($providerPaymentId !== '') {
                $knownIds = array_filter([
                    (string) ($transaction['provider_payment_id'] ?? ''),
                    (string) ($transaction['provider_checkout_id'] ?? ''),
                ], static fn (string $value): bool => $value !== '');

                if (in_array($providerPaymentId, $knownIds, true)) {
                    return $transaction;
                }
            }

            if ($externalReference !== '' && hash_equals((string) ($transaction['external_reference'] ?? ''), $externalReference)) {
                return $transaction;
            }
        }

        if ($externalReference !== '' && preg_match('/^topup-(\d+)-/i', $externalReference, $matches) === 1) {
            $transactionId = (int) $matches[1];

            foreach ($this->walletTransactions() as $transaction) {
                if ((int) ($transaction['id'] ?? 0) === $transactionId) {
                    return $transaction;
                }
            }
        }

        return null;
    }

    public function subscribeToPlan(int $subscriberId, int $planId): array
    {
        $plan = $this->findPlanById($planId);

        if (! $plan || ! $plan['active']) {
            return ['ok' => false, 'message' => 'Plano nao encontrado.'];
        }

        if ($this->activeSubscriptionFor($subscriberId, (int) $plan['creator_id']) !== null) {
            return ['ok' => false, 'message' => 'Voce ja possui uma assinatura ativa com este criador.'];
        }

        $price = (int) $plan['price_tokens'];

        if ($this->walletBalance($subscriberId) < $price) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para concluir a assinatura.'];
        }

        $subscriptions = $this->subscriptions();
        $subscriptions[] = [
            'id' => $this->store->nextId($subscriptions),
            'subscriber_id' => $subscriberId,
            'creator_id' => (int) $plan['creator_id'],
            'plan_id' => $planId,
            'status' => 'active',
            'renews_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ];
        $this->save('subscriptions', $subscriptions);

        $this->chargeSubscriberAndCreditCreator($subscriberId, (int) $plan['creator_id'], $price, 'subscription', 'Assinatura ' . $plan['name']);
        $subscriber = $this->findUserById($subscriberId);
        $creator = $this->findCreatorBySlugOrId(null, (int) ($plan['creator_id'] ?? 0));
        $creatorName = (string) ($creator['name'] ?? 'criador');
        $subscriberName = (string) ($subscriber['name'] ?? 'Novo assinante');
        $planName = (string) ($plan['name'] ?? 'Plano');

        $this->notifyUser(
            $subscriberId,
            'subscription',
            'Assinatura ativada',
            'Sua assinatura no plano ' . $planName . ' de ' . $creatorName . ' esta ativa.',
            '/subscriber/subscriptions'
        );
        $this->notifyUser(
            (int) ($plan['creator_id'] ?? 0),
            'subscription',
            'Novo assinante confirmado',
            $subscriberName . ' assinou o plano ' . $planName . '.',
            '/creator/memberships'
        );

        return ['ok' => true, 'message' => 'Assinatura ativada com sucesso.'];
    }

    public function cancelSubscription(int $subscriberId, int $subscriptionId): bool
    {
        $subscriptions = $this->subscriptions();
        $changed = false;
        $targetSubscription = null;

        foreach ($subscriptions as &$subscription) {
            if ((int) $subscription['id'] === $subscriptionId && (int) $subscription['subscriber_id'] === $subscriberId) {
                $subscription['status'] = 'cancelled';
                $targetSubscription = $subscription;
                $changed = true;
                break;
            }
        }
        unset($subscription);

        if ($changed) {
            $this->save('subscriptions', $subscriptions);
            if (is_array($targetSubscription)) {
                $creator = $this->findCreatorBySlugOrId(null, (int) ($targetSubscription['creator_id'] ?? 0));
                $subscriber = $this->findUserById($subscriberId);
                $this->notifyUser(
                    (int) ($targetSubscription['creator_id'] ?? 0),
                    'subscription',
                    'Assinatura cancelada',
                    (string) ($subscriber['name'] ?? 'Um assinante') . ' cancelou a assinatura' . (($creator['name'] ?? '') !== '' ? ' em ' . (string) ($creator['name'] ?? '') : '') . '.',
                    '/creator/memberships'
                );
            }
        }

        return $changed;
    }

    public function toggleFavoriteCreator(int $subscriberId, int $creatorId): bool
    {
        $favorites = $this->favorites();

        foreach ($favorites as $index => $favorite) {
            if ((int) $favorite['user_id'] === $subscriberId && (int) $favorite['creator_id'] === $creatorId) {
                unset($favorites[$index]);
                $this->save('favorites', array_values($favorites));

                return false;
            }
        }

        $favorites[] = [
            'id' => $this->store->nextId($favorites),
            'user_id' => $subscriberId,
            'creator_id' => $creatorId,
        ];
        $this->save('favorites', $favorites);

        return true;
    }

    public function toggleSavedContent(int $subscriberId, int $contentId): bool
    {
        $savedItems = $this->savedItems();

        foreach ($savedItems as $index => $saved) {
            if ((int) $saved['user_id'] === $subscriberId && (int) $saved['content_id'] === $contentId) {
                unset($savedItems[$index]);
                $this->save('saved_items', array_values($savedItems));

                return false;
            }
        }

        $savedItems[] = [
            'id' => $this->store->nextId($savedItems),
            'user_id' => $subscriberId,
            'content_id' => $contentId,
        ];
        $this->save('saved_items', $savedItems);

        return true;
    }

    public function sendConversationMessage(int $conversationId, int $senderId, string $body, array $options = []): bool
    {
        $body = trim($body);
        $attachment = $this->normalizeConversationAttachment(is_array($options['attachment'] ?? null) ? $options['attachment'] : null);

        $conversation = $this->findConversationById($conversationId);

        if (! $conversation) {
            return false;
        }

        $isConversationParticipant = in_array($senderId, [
            (int) ($conversation['subscriber_id'] ?? 0),
            (int) ($conversation['creator_id'] ?? 0),
        ], true);

        if (! $isConversationParticipant || ($body === '' && $attachment === null)) {
            return false;
        }

        $requiredPlanId = 0;
        $unlockPrice = 0;
        if ($senderId === (int) ($conversation['creator_id'] ?? 0)) {
            $requiredPlanId = max(0, (int) ($options['required_plan_id'] ?? 0));
            $unlockPrice = max(0, (int) ($options['unlock_price'] ?? 0));

            if ($requiredPlanId > 0) {
                $plan = $this->findPlanById($requiredPlanId);
                if (! $plan || (int) ($plan['creator_id'] ?? 0) !== (int) ($conversation['creator_id'] ?? 0)) {
                    $requiredPlanId = 0;
                }
            }

            if ($unlockPrice > 0) {
                $requiredPlanId = 0;
            }
        }

        if ($attachment === null) {
            $requiredPlanId = 0;
            $unlockPrice = 0;
        }

        $messageType = 'text';
        if ($attachment !== null) {
            if ($unlockPrice > 0) {
                $messageType = 'instant_content';
            } elseif ($requiredPlanId > 0) {
                $messageType = 'private_attachment';
            } else {
                $messageType = 'attachment';
            }
        }

        $messages = $this->messages();
        $messages[] = [
            'id' => $this->store->nextId($messages),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'message_type' => $messageType,
            'required_plan_id' => $requiredPlanId,
            'unlock_price' => $unlockPrice,
            'attachment' => $attachment,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $messageId = (int) (($messages[array_key_last($messages)] ?? [])['id'] ?? 0);
        $this->save('messages', $messages);

        $conversations = $this->conversations();
        foreach ($conversations as &$item) {
            if ((int) $item['id'] === $conversationId) {
                $item['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($item);
        $this->save('conversations', $conversations);

        $sender = $this->findUserById($senderId);
        $recipientId = (int) ($conversation['subscriber_id'] ?? 0) === $senderId
            ? (int) ($conversation['creator_id'] ?? 0)
            : (int) ($conversation['subscriber_id'] ?? 0);
        $recipientHref = (int) ($conversation['subscriber_id'] ?? 0) === $senderId
            ? path_with_query('/creator/messages', ['conversation' => $conversationId])
            : path_with_query('/subscriber/messages', ['conversation' => $conversationId]);

        $this->notifyUser(
            $recipientId,
            'message',
            'Nova mensagem de ' . (string) ($sender['name'] ?? 'Conta'),
            excerpt($body !== '' ? $body : $this->messageNotificationPreview($messageType, $attachment, $unlockPrice, $requiredPlanId), 90),
            $recipientHref,
            [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'sender_id' => $senderId,
            ]
        );

        return true;
    }

    public function startConversation(int $subscriberId, int $creatorId, string $body): int
    {
        foreach ($this->conversations() as $conversation) {
            if ((int) $conversation['subscriber_id'] === $subscriberId && (int) $conversation['creator_id'] === $creatorId) {
                $this->sendConversationMessage((int) $conversation['id'], $subscriberId, $body);

                return (int) $conversation['id'];
            }
        }

        $conversations = $this->conversations();
        $conversationId = $this->store->nextId($conversations);
        $conversations[] = [
            'id' => $conversationId,
            'subscriber_id' => $subscriberId,
            'creator_id' => $creatorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('conversations', $conversations);
        $this->sendConversationMessage($conversationId, $subscriberId, $body);

        return $conversationId;
    }

    public function tipCreator(int $subscriberId, int $creatorId, int $amount, string $note = 'Gorjeta enviada', int $liveId = 0, ?string $priorityMessage = null): array
    {
        if ($amount <= 0) {
            return ['ok' => false, 'message' => 'Informe uma quantidade valida de LuaCoins.'];
        }

        if ($this->walletBalance($subscriberId) < $amount) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para enviar a gorjeta.'];
        }

        $this->chargeSubscriberAndCreditCreator($subscriberId, $creatorId, $amount, 'tip', $note, $liveId);
        if ($liveId > 0) {
            $live = $this->findLiveById($liveId);
            if ($live && (int) ($live['creator_id'] ?? 0) === $creatorId) {
                $this->appendPriorityTipMessage($live, $subscriberId, $amount, $priorityMessage);
            }
        }

        $subscriber = $this->findUserById($subscriberId);
        $this->notifyUser(
            $creatorId,
            'tip',
            'Nova gorjeta recebida',
            (string) ($subscriber['name'] ?? 'Um assinante') . ' enviou ' . $amount . ' LuaCoins.',
            $liveId > 0 ? path_with_query('/creator/live/studio', ['live' => $liveId]) : '/creator/wallet',
            [
                'amount' => $amount,
                'live_id' => $liveId,
            ]
        );

        return ['ok' => true, 'message' => 'Gorjeta enviada com sucesso.'];
    }

    public function sendMessageToSubscriber(int $creatorId, int $subscriberId, string $body, array $options = []): array
    {
        $body = trim($body);
        $attachment = $this->normalizeConversationAttachment(is_array($options['attachment'] ?? null) ? $options['attachment'] : null);
        $subscriber = $this->findUserById($subscriberId);

        if ($body === '' && $attachment === null) {
            return ['ok' => false, 'message' => 'Escreva a mensagem antes de enviar.'];
        }

        if (! $subscriber || (string) ($subscriber['role'] ?? '') !== 'subscriber') {
            return ['ok' => false, 'message' => 'Assinante nao encontrado.'];
        }

        $hasSubscription = false;
        foreach ($this->subscriptions() as $subscription) {
            if ((int) ($subscription['creator_id'] ?? 0) === $creatorId && (int) ($subscription['subscriber_id'] ?? 0) === $subscriberId) {
                $hasSubscription = true;
                break;
            }
        }

        if (! $hasSubscription) {
            return ['ok' => false, 'message' => 'Este assinante nao faz parte da sua base.'];
        }

        $conversation = $this->findConversationByPair($subscriberId, $creatorId);
        if ($conversation !== null) {
            $ok = $this->sendConversationMessage((int) ($conversation['id'] ?? 0), $creatorId, $body, $options);

            return [
                'ok' => $ok,
                'message' => $ok ? 'Mensagem enviada ao assinante.' : 'Nao foi possivel enviar a mensagem.',
                'conversation_id' => (int) ($conversation['id'] ?? 0),
            ];
        }

        $conversations = $this->conversations();
        $conversationId = $this->store->nextId($conversations);
        $conversations[] = [
            'id' => $conversationId,
            'subscriber_id' => $subscriberId,
            'creator_id' => $creatorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('conversations', $conversations);

        $ok = $this->sendConversationMessage($conversationId, $creatorId, $body, $options);

        return [
            'ok' => $ok,
            'message' => $ok ? 'Mensagem enviada ao assinante.' : 'Nao foi possivel enviar a mensagem.',
            'conversation_id' => $conversationId,
        ];
    }

    public function unlockConversationMessage(int $messageId, int $subscriberId): array
    {
        $message = $this->findMessageById($messageId);

        if ($message === null) {
            return ['ok' => false, 'message' => 'Conteudo nao encontrado.'];
        }

        $conversation = $this->findConversationById((int) ($message['conversation_id'] ?? 0));
        if ($conversation === null || (int) ($conversation['subscriber_id'] ?? 0) !== $subscriberId) {
            return ['ok' => false, 'message' => 'Voce nao tem acesso a este conteudo.'];
        }

        $unlockPrice = max(0, (int) ($message['unlock_price'] ?? 0));
        $attachment = $this->normalizeConversationAttachment(is_array($message['attachment'] ?? null) ? $message['attachment'] : null);
        if ($unlockPrice <= 0 || $attachment === null) {
            return ['ok' => false, 'message' => 'Este item nao exige desbloqueio em LuaCoins.'];
        }

        if ($this->hasConversationMessageUnlock($messageId, $subscriberId)) {
            return ['ok' => true, 'message' => 'Conteudo ja desbloqueado.'];
        }

        $creatorId = (int) ($conversation['creator_id'] ?? 0);
        if ($creatorId <= 0) {
            return ['ok' => false, 'message' => 'Criador nao encontrado para este conteudo.'];
        }

        if ($this->walletBalance($subscriberId) < $unlockPrice) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para desbloquear este conteudo.'];
        }

        $description = trim((string) ($message['body'] ?? 'Conteudo instantaneo no chat'));
        $this->chargeSubscriberAndCreditCreator($subscriberId, $creatorId, $unlockPrice, 'instant_content', $description);

        $unlocks = $this->messageUnlocks();
        $unlocks[] = [
            'id' => $this->store->nextId($unlocks),
            'message_id' => $messageId,
            'user_id' => $subscriberId,
            'amount' => $unlockPrice,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('message_unlocks', $unlocks);

        $subscriber = $this->findUserById($subscriberId);
        $this->notifyUser(
            $creatorId,
            'sale',
            'Conteudo instantaneo desbloqueado',
            (string) ($subscriber['name'] ?? 'Assinante') . ' desbloqueou seu conteudo por ' . $unlockPrice . ' LuaCoins.',
            path_with_query('/creator/messages', ['conversation' => (int) ($conversation['id'] ?? 0)]),
            [
                'message_id' => $messageId,
                'conversation_id' => (int) ($conversation['id'] ?? 0),
                'amount' => $unlockPrice,
            ]
        );

        return ['ok' => true, 'message' => 'Conteudo desbloqueado com sucesso.'];
    }

    public function unlockLiveAccess(int $liveId, int $userId): array
    {
        $live = $this->findLiveById($liveId);

        if ($live === null) {
            return ['ok' => false, 'message' => 'Live nao encontrada.'];
        }

        if ((string) ($live['access_mode'] ?? 'public') !== 'vip') {
            return ['ok' => false, 'message' => 'Esta live nao exige desbloqueio em LuaCoins.'];
        }

        $user = $this->findUserById($userId);
        if ($user === null || (string) ($user['status'] ?? 'active') !== 'active') {
            return ['ok' => false, 'message' => 'Sua conta nao pode desbloquear esta live agora.'];
        }

        if ((int) ($live['creator_id'] ?? 0) === $userId || (string) ($user['role'] ?? '') === 'admin') {
            return ['ok' => true, 'message' => 'Acesso liberado para esta live.'];
        }

        $unlockPrice = max(1, (int) ($live['price_tokens'] ?? 0));
        if ($this->hasLiveUnlock($liveId, $userId)) {
            return ['ok' => true, 'message' => 'Live VIP ja desbloqueada.'];
        }

        if ($this->walletBalance($userId) < $unlockPrice) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para desbloquear esta live VIP.'];
        }

        $creatorId = (int) ($live['creator_id'] ?? 0);
        $title = trim((string) ($live['title'] ?? 'Live VIP'));
        $this->chargeSubscriberAndCreditCreator($userId, $creatorId, $unlockPrice, 'vip_live', 'Desbloqueio da live VIP ' . $title, $liveId);

        $unlocks = $this->liveUnlocks();
        $unlocks[] = [
            'id' => $this->store->nextId($unlocks),
            'live_id' => $liveId,
            'user_id' => $userId,
            'amount' => $unlockPrice,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('live_unlocks', $unlocks);

        $this->notifyUser(
            $creatorId,
            'sale',
            'Live VIP desbloqueada',
            (string) ($user['name'] ?? 'Usuario') . ' desbloqueou sua live VIP por ' . $unlockPrice . ' LuaCoins.',
            path_with_query('/creator/live', ['live' => $liveId, 'status' => 'scheduled']),
            [
                'live_id' => $liveId,
                'amount' => $unlockPrice,
                'buyer_id' => $userId,
            ]
        );

        return ['ok' => true, 'message' => 'Live VIP desbloqueada com sucesso.'];
    }

    public function activateLiveDarkroom(int $liveId, int $userId): array
    {
        $live = $this->findLiveById($liveId);

        if ($live === null) {
            return ['ok' => false, 'message' => 'Live nao encontrada.'];
        }

        $user = $this->findUserById($userId);
        if ($user === null || (string) ($user['status'] ?? 'active') !== 'active') {
            return ['ok' => false, 'message' => 'Sua conta nao pode ativar o darkroom agora.'];
        }

        if ((int) ($live['creator_id'] ?? 0) === $userId || (string) ($user['role'] ?? '') === 'admin') {
            return ['ok' => false, 'message' => 'O criador ja possui acesso total a esta live.'];
        }

        $darkroomPrice = max(0, (int) ($live['darkroom_price_tokens'] ?? 0));
        $darkroomDuration = $this->sanitizeDarkroomDurationMinutes((int) ($live['darkroom_duration_minutes'] ?? 0));

        if ($darkroomPrice <= 0 || $darkroomDuration <= 0) {
            return ['ok' => false, 'message' => 'O darkroom nao esta disponivel para esta live.'];
        }

        if ($this->resolveLiveStatus($live) !== 'live') {
            return ['ok' => false, 'message' => 'O darkroom so pode ser ativado durante a live ao vivo.'];
        }

        $baseAccess = $this->baseAccessStateForLive($live, $userId);
        if (! (bool) ($baseAccess['granted'] ?? false)) {
            if ((bool) ($baseAccess['requires_subscription'] ?? false)) {
                return ['ok' => false, 'message' => 'Assine esta live antes de ativar o darkroom.'];
            }

            if ((bool) ($baseAccess['requires_vip_unlock'] ?? false)) {
                return ['ok' => false, 'message' => 'Desbloqueie a Live VIP antes de ativar o darkroom.'];
            }

            return ['ok' => false, 'message' => 'Seu acesso a esta live nao esta ativo.'];
        }

        $activeDarkroom = $this->activeDarkroomForLive($liveId);
        if ($activeDarkroom !== null) {
            if ((int) ($activeDarkroom['user_id'] ?? 0) === $userId) {
                return ['ok' => true, 'message' => 'Seu darkroom ja esta ativo nesta live.'];
            }

            return [
                'ok' => false,
                'message' => 'Esta live ja esta em darkroom para ' . (string) ($activeDarkroom['user']['name'] ?? 'outro espectador') . '.',
            ];
        }

        if ($this->walletBalance($userId) < $darkroomPrice) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para ativar o darkroom.'];
        }

        $creatorId = (int) ($live['creator_id'] ?? 0);
        $title = trim((string) ($live['title'] ?? 'Live'));
        $this->chargeSubscriberAndCreditCreator($userId, $creatorId, $darkroomPrice, 'darkroom', 'Darkroom da live ' . $title, $liveId);

        $darkrooms = $this->liveDarkrooms();
        $now = date('Y-m-d H:i:s');
        foreach ($darkrooms as &$row) {
            if ((int) ($row['live_id'] ?? 0) !== $liveId || (string) ($row['status'] ?? 'ended') !== 'active') {
                continue;
            }

            $row['status'] = 'ended';
            $row['ended_at'] = $now;
        }
        unset($row);

        $darkrooms[] = [
            'id' => $this->store->nextId($darkrooms),
            'live_id' => $liveId,
            'creator_id' => $creatorId,
            'user_id' => $userId,
            'amount' => $darkroomPrice,
            'duration_minutes' => $darkroomDuration,
            'status' => 'active',
            'started_at' => $now,
            'ends_at' => date('Y-m-d H:i:s', strtotime('+' . $darkroomDuration . ' minutes')),
            'ended_at' => '',
            'created_at' => $now,
        ];
        $this->save('live_darkrooms', $darkrooms);

        $this->notifyUser(
            $creatorId,
            'sale',
            'Darkroom ativado',
            (string) ($user['name'] ?? 'Usuario') . ' ativou o darkroom por ' . $darkroomPrice . ' LuaCoins durante ' . $darkroomDuration . ' minuto(s).',
            path_with_query('/creator/live/studio', ['live' => $liveId]),
            [
                'live_id' => $liveId,
                'amount' => $darkroomPrice,
                'buyer_id' => $userId,
                'duration_minutes' => $darkroomDuration,
            ]
        );

        return ['ok' => true, 'message' => 'Darkroom ativado com sucesso.'];
    }

    public function findSecureConversationMessageAttachment(int $messageId, int $viewerId): ?array
    {
        $user = $this->findUserById($viewerId);
        $message = $this->findMessageById($messageId);

        if ($user === null || $message === null) {
            return null;
        }

        $conversation = $this->findConversationById((int) ($message['conversation_id'] ?? 0));
        $attachment = $this->normalizeConversationAttachment(is_array($message['attachment'] ?? null) ? $message['attachment'] : null);

        if ($conversation === null || $attachment === null || ! $this->viewerCanAccessConversationMessage($message, $conversation, $viewerId, (string) ($user['role'] ?? ''))) {
            return null;
        }

        return $attachment + [
            'display_name' => (string) ($attachment['original_name'] ?? 'anexo'),
        ];
    }

    public function findSecureAnnouncementAttachment(int $announcementId, int $viewerId): ?array
    {
        $user = $this->findUserById($viewerId);
        $announcement = $this->findAnnouncementById($announcementId);

        if ($user === null || $announcement === null || ! $this->announcementMatchesUser($announcement, $user)) {
            return null;
        }

        $attachment = $this->normalizeAnnouncementAttachment(is_array($announcement['attachment'] ?? null) ? $announcement['attachment'] : null);

        if ($attachment === null) {
            return null;
        }

        return $attachment + [
            'display_name' => (string) ($attachment['original_name'] ?? 'anexo'),
        ];
    }

    public function findSecureIdentityAttachment(int $userId, int $viewerId): ?array
    {
        $viewer = $this->findUserById($viewerId);
        $user = $this->findUserById($userId);

        if ($viewer === null || $user === null) {
            return null;
        }

        if ((string) ($viewer['role'] ?? '') !== 'admin' && (int) ($viewer['id'] ?? 0) !== $userId) {
            return null;
        }

        $attachment = $this->normalizeConversationAttachment(is_array($user['identity_document'] ?? null) ? $user['identity_document'] : null);
        if ($attachment === null) {
            return null;
        }

        return $attachment + [
            'display_name' => (string) ($attachment['original_name'] ?? 'documento-identidade'),
        ];
    }

    public function creatorDashboardData(int $creatorId): array
    {
        $contents = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId));
        $lives = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => (int) $live['creator_id'] === $creatorId));
        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId));
        $wallet = $this->creatorWalletData($creatorId);

        $approved = count(array_filter($contents, fn (array $item): bool => $item['status'] === 'approved' && ! $this->contentIsExpired($item)));
        $pending = count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'pending'));
        $subscribers = count(array_filter($this->subscriptions(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId && $item['status'] === 'active'));

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'metrics' => [
                'approved_content' => $approved,
                'pending_content' => $pending,
                'active_subscribers' => $subscribers,
                'wallet_balance' => $wallet['balance'],
            ],
            'recent_content' => array_slice($this->sortByDate($contents, 'created_at'), 0, 5),
            'plans' => $plans,
            'lives' => array_slice($this->sortByDate($lives, 'scheduled_for'), 0, 3),
            'transactions' => array_slice($wallet['transactions'], 0, 5),
        ];
    }

    public function creatorContentData(int $creatorId, array $filters = []): array
    {
        $contents = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId));
        $contents = $this->sortByDate($contents, 'created_at');
        $plans = array_values(array_filter(
            $this->plansWithCreators(),
            static fn (array $plan): bool => (int) ($plan['creator_id'] ?? 0) === $creatorId && (bool) ($plan['active'] ?? false)
        ));
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $kind = trim((string) ($filters['kind'] ?? ''));
        $editId = (int) ($filters['edit'] ?? 0);

        $filtered = array_values(array_filter($contents, static function (array $item) use ($query, $status, $kind): bool {
            if ($status !== '' && (string) ($item['status'] ?? '') !== $status) {
                return false;
            }

            if ($kind !== '' && (string) ($item['kind'] ?? '') !== $kind) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) ($item['title'] ?? '') . ' ' . (string) ($item['excerpt'] ?? '') . ' ' . (string) ($item['body'] ?? ''));

            return str_contains($haystack, $query);
        }));

        $selectedItem = null;
        if ($editId > 0) {
            foreach ($contents as $item) {
                if ((int) $item['id'] === $editId) {
                    $selectedItem = $item;
                    break;
                }
            }
        }

        $estimatedViews = array_reduce($contents, static fn (int $carry, array $item): int => $carry + ((int) ($item['saved_count'] ?? 0) * 42), 0);
        $storageUsedBytes = $this->creatorContentUsageBytes($creatorId);
        $storageLimitBytes = $this->creatorContentStorageLimitBytes();
        $storageRemainingBytes = max(0, $storageLimitBytes - $storageUsedBytes);

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'plans' => $plans,
            'items' => $contents,
            'filtered_items' => $filtered,
            'selected_item' => $selectedItem,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'kind' => $kind,
                'edit' => $editId,
            ],
            'counts' => [
                'approved' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'approved')),
                'pending' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'pending')),
                'draft' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'draft')),
                'rejected' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'rejected')),
                'archived' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'archived')),
            ],
            'summary' => [
                'total_posts' => count($contents),
                'estimated_views' => $estimatedViews,
                'storage_used_bytes' => $storageUsedBytes,
                'storage_limit_bytes' => $storageLimitBytes,
                'storage_remaining_bytes' => $storageRemainingBytes,
                'storage_used_mb' => round($storageUsedBytes / (1024 * 1024), 1),
                'storage_limit_mb' => round($storageLimitBytes / (1024 * 1024), 0),
                'storage_percent' => $storageLimitBytes > 0 ? min(100, round(($storageUsedBytes / $storageLimitBytes) * 100, 1)) : 0,
            ],
        ];
    }

    public function createContent(int $creatorId, array $data): array
    {
        return $this->saveContent($creatorId, $data);
    }

    public function saveContent(int $creatorId, array $data): array
    {
        $items = $this->contentItems();
        $contentId = isset($data['id']) ? (int) $data['id'] : 0;
        $status = in_array(($data['status'] ?? 'draft'), ['draft', 'pending', 'approved', 'rejected', 'archived'], true) ? $data['status'] : 'draft';
        $visibility = in_array(($data['visibility'] ?? 'public'), ['public', 'subscriber', 'premium'], true) ? $data['visibility'] : 'public';
        $kind = in_array(($data['kind'] ?? 'gallery'), ['gallery', 'video', 'audio', 'article', 'live_teaser'], true) ? $data['kind'] : 'gallery';
        $category = $this->normalizeAudienceCategory((string) ($data['category'] ?? 'todos'));
        $mediaUrl = trim((string) ($data['media_url'] ?? ''));
        $thumbnailUrl = trim((string) ($data['thumbnail_url'] ?? ''));
        $planId = max(0, (int) ($data['plan_id'] ?? 0));
        $plan = $planId > 0 ? $this->findPlanById($planId) : null;

        if (! $plan || (int) ($plan['creator_id'] ?? 0) !== $creatorId) {
            $planId = 0;
            $plan = null;
        }

        $payload = [
            'title' => trim((string) ($data['title'] ?? 'Novo conteudo')),
            'excerpt' => trim((string) ($data['excerpt'] ?? 'Descricao rapida do conteudo.')),
            'body' => trim((string) ($data['body'] ?? '')),
            'visibility' => $visibility,
            'status' => $status,
            'kind' => $kind,
            'category' => $category,
            'duration' => trim((string) ($data['duration'] ?? '')),
            'plan_id' => $planId,
            'price_tokens' => $plan ? (int) ($plan['price_tokens'] ?? 0) : max(0, (int) ($data['price_tokens'] ?? $data['price_luacoins'] ?? 0)),
            'media_url' => $mediaUrl,
            'thumbnail_url' => $thumbnailUrl,
            'media_bytes' => max(0, (int) ($data['media_bytes'] ?? ($mediaUrl !== '' ? \public_media_file_bytes($mediaUrl) : 0))),
            'thumbnail_bytes' => max(0, (int) ($data['thumbnail_bytes'] ?? ($thumbnailUrl !== '' ? \public_media_file_bytes($thumbnailUrl) : 0))),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $currentItem = null;
        if ($contentId > 0) {
            foreach ($items as $item) {
                if ((int) ($item['id'] ?? 0) === $contentId && (int) ($item['creator_id'] ?? 0) === $creatorId) {
                    $currentItem = $item;
                    break;
                }
            }
        }

        $projectedItem = array_merge($currentItem ?? [
            'creator_id' => $creatorId,
            'created_at' => date('Y-m-d H:i:s'),
            'saved_count' => 0,
        ], $payload);
        $projectedUsageBytes = $this->creatorContentUsageBytes($creatorId, $contentId) + $this->contentStorageBytes($projectedItem);
        $storageLimitBytes = $this->creatorContentStorageLimitBytes();

        if ($projectedUsageBytes > $storageLimitBytes) {
            return [
                'ok' => false,
                'message' => 'Sem espaço para salvar este conteúdo. Remova um replay ou outro arquivo em Meus Conteúdos para continuar.',
                'code' => 'storage_quota_exceeded',
                'storage_used_bytes' => $this->creatorContentUsageBytes($creatorId),
                'storage_limit_bytes' => $storageLimitBytes,
            ];
        }

        if ($contentId > 0) {
            foreach ($items as &$item) {
                if ((int) $item['id'] === $contentId && (int) $item['creator_id'] === $creatorId) {
                    $item = array_merge($item, array_filter($payload, static fn (mixed $value): bool => $value !== ''));
                    $this->save('content_items', $items);

                    return ['ok' => true, 'item' => $item];
                }
            }
            unset($item);
        }

        $item = [
            'id' => $this->store->nextId($items),
            'creator_id' => $creatorId,
            'created_at' => date('Y-m-d H:i:s'),
            'saved_count' => 0,
        ] + $payload;

        $items[] = $item;
        $this->save('content_items', $items);

        return ['ok' => true, 'item' => $item];
    }

    public function updateContentStatus(int $creatorId, int $contentId, string $status): bool
    {
        $allowed = ['draft', 'pending', 'approved', 'rejected', 'archived'];

        if (! in_array($status, $allowed, true)) {
            return false;
        }

        $items = $this->contentItems();
        $changed = false;

        foreach ($items as &$item) {
            if ((int) $item['id'] === $contentId && (int) $item['creator_id'] === $creatorId) {
                $item['status'] = $status;
                $changed = true;
                break;
            }
        }
        unset($item);

        if ($changed) {
            $this->save('content_items', $items);
        }

        return $changed;
    }

    public function deleteContent(int $creatorId, int $contentId): bool
    {
        $items = $this->contentItems();
        $before = count($items);
        $removed = null;
        $items = array_values(array_filter($items, static function (array $item) use ($contentId, $creatorId, &$removed): bool {
            $match = (int) ($item['id'] ?? 0) === $contentId && (int) ($item['creator_id'] ?? 0) === $creatorId;
            if ($match) {
                $removed = $item;
            }

            return ! $match;
        }));

        if (count($items) === $before) {
            return false;
        }

        $this->save('content_items', $items);
        $savedItems = array_values(array_filter($this->savedItems(), static fn (array $saved): bool => (int) ($saved['content_id'] ?? 0) !== $contentId));
        $this->save('saved_items', $savedItems);
        if (is_array($removed)) {
            $this->deletePublicMediaFile((string) ($removed['media_url'] ?? ''));
            $this->deletePublicMediaFile((string) ($removed['thumbnail_url'] ?? ''));
            $this->clearReplayReferenceForContent($creatorId, $removed);
        }

        return true;
    }

    public function creatorPlansData(int $creatorId, array $filters = []): array
    {
        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId));
        usort($plans, static fn (array $left, array $right): int => (int) ($right['price_tokens'] ?? 0) <=> (int) ($left['price_tokens'] ?? 0));
        $subscribers = array_values(array_filter($this->subscriptions(), static fn (array $subscription): bool => (int) $subscription['creator_id'] === $creatorId));
        $subscribers = array_map(function (array $subscription): array {
            $subscription['subscriber'] = $this->findUserById((int) $subscription['subscriber_id']);
            $subscription['plan'] = $this->findPlanById((int) $subscription['plan_id']);
            $subscription['vip'] = (bool) ($subscription['vip'] ?? false);
            $subscription['creator_note'] = (string) ($subscription['creator_note'] ?? '');
            $subscription['days_to_renew'] = max(0, (int) ceil((strtotime((string) ($subscription['renews_at'] ?? 'now')) - time()) / 86400));

            return $subscription;
        }, $subscribers);
        $subscribers = $this->sortByDate($subscribers, 'renews_at');
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['subscriber_status'] ?? ''));
        $planId = (int) ($filters['plan'] ?? 0);

        $filteredSubscribers = array_values(array_filter($subscribers, static function (array $subscription) use ($query, $status): bool {
            if ($status !== '' && (string) ($subscription['status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $subscriber = $subscription['subscriber'] ?? [];
            $plan = $subscription['plan'] ?? [];
            $haystack = mb_strtolower((string) ($subscriber['name'] ?? '') . ' ' . (string) ($subscriber['email'] ?? '') . ' ' . (string) ($plan['name'] ?? ''));

            return str_contains($haystack, $query);
        }));

        $selectedPlan = null;
        if ($planId > 0) {
            foreach ($plans as $plan) {
                if ((int) ($plan['id'] ?? 0) === $planId) {
                    $selectedPlan = $plan;
                    break;
                }
            }
        }

        $recurringTokens = array_reduce(
            array_filter($subscribers, static fn (array $subscription): bool => (string) ($subscription['status'] ?? '') === 'active'),
            static fn (int $carry, array $subscription): int => $carry + (int) (($subscription['plan']['price_tokens'] ?? 0)),
            0
        );

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'plans' => $plans,
            'selected_plan' => $selectedPlan,
            'active_subscribers' => count(array_filter($this->subscriptions(), static fn (array $subscription): bool => (int) $subscription['creator_id'] === $creatorId && $subscription['status'] === 'active')),
            'subscribers' => $subscribers,
            'filtered_subscribers' => $filteredSubscribers,
            'filters' => [
                'q' => $query,
                'subscriber_status' => $status,
            ],
            'summary' => [
                'monthly_tokens' => $recurringTokens,
                'vip_count' => count(array_filter($subscribers, static fn (array $subscription): bool => (bool) ($subscription['vip'] ?? false))),
                'paused_count' => count(array_filter($subscribers, static fn (array $subscription): bool => (string) ($subscription['status'] ?? '') === 'paused')),
            ],
        ];
    }

    public function savePlan(int $creatorId, array $data): array
    {
        $plans = $this->plans();
        $planId = isset($data['id']) ? (int) $data['id'] : 0;
        $perks = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) ($data['perks'] ?? '')) ?: [])));

        if ($planId > 0) {
            foreach ($plans as &$plan) {
                if ((int) $plan['id'] === $planId && (int) $plan['creator_id'] === $creatorId) {
                    $plan['name'] = trim((string) ($data['name'] ?? $plan['name']));
                    $plan['description'] = trim((string) ($data['description'] ?? $plan['description']));
                    $plan['price_tokens'] = max(1, (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? $plan['price_tokens']));
                    $plan['active'] = ($data['active'] ?? '1') === '1';
                    $plan['label'] = trim((string) ($data['label'] ?? ($plan['label'] ?? '')));
                    $plan['perks'] = $perks !== [] ? $perks : $plan['perks'];
                    $this->save('plans', $plans);

                    return $plan;
                }
            }
            unset($plan);
        }

        $plan = [
            'id' => $this->store->nextId($plans),
            'creator_id' => $creatorId,
            'name' => trim((string) ($data['name'] ?? 'Novo plano')),
            'description' => trim((string) ($data['description'] ?? 'Beneficios exclusivos para assinantes.')),
            'price_tokens' => max(1, (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? 49)),
            'active' => ($data['active'] ?? '1') === '1',
            'label' => trim((string) ($data['label'] ?? '')),
            'perks' => $perks !== [] ? $perks : ['Conteudo exclusivo', 'Mensagens prioritarias'],
        ];
        $plans[] = $plan;
        $this->save('plans', $plans);

        return $plan;
    }

    public function deletePlan(int $creatorId, int $planId): array
    {
        $plans = $this->plans();
        $hasActiveSubscribers = false;

        foreach ($this->subscriptions() as $subscription) {
            if ((int) ($subscription['creator_id'] ?? 0) === $creatorId && (int) ($subscription['plan_id'] ?? 0) === $planId && (string) ($subscription['status'] ?? '') === 'active') {
                $hasActiveSubscribers = true;
                break;
            }
        }

        foreach ($plans as $index => $plan) {
            if ((int) ($plan['id'] ?? 0) !== $planId || (int) ($plan['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            if ($hasActiveSubscribers) {
                $plans[$index]['active'] = false;
                $this->save('plans', $plans);

                return ['ok' => true, 'message' => 'Plano desativado porque ainda possui assinantes ativos.'];
            }

            unset($plans[$index]);
            $this->save('plans', array_values($plans));

            return ['ok' => true, 'message' => 'Plano removido com sucesso.'];
        }

        return ['ok' => false, 'message' => 'Nao foi possivel remover o plano informado.'];
    }

    public function updateSubscriptionAccess(int $creatorId, int $subscriptionId, array $data): array
    {
        $subscriptions = $this->subscriptions();
        $action = (string) ($data['action'] ?? 'note');
        $changed = false;
        $message = 'Assinatura atualizada.';

        foreach ($subscriptions as &$subscription) {
            if ((int) ($subscription['id'] ?? 0) !== $subscriptionId || (int) ($subscription['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            switch ($action) {
                case 'pause':
                    $subscription['status'] = 'paused';
                    $message = 'Assinatura pausada pelo criador.';
                    $changed = true;
                    break;
                case 'reactivate':
                    $subscription['status'] = 'active';
                    $subscription['renews_at'] = (string) ($subscription['renews_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days')));
                    $message = 'Assinatura reativada.';
                    $changed = true;
                    break;
                case 'cancel':
                    $subscription['status'] = 'cancelled';
                    $message = 'Assinatura cancelada manualmente.';
                    $changed = true;
                    break;
                case 'toggle_vip':
                    $subscription['vip'] = ! (bool) ($subscription['vip'] ?? false);
                    $message = (bool) $subscription['vip'] ? 'Assinante marcado como VIP.' : 'Marcacao VIP removida.';
                    $changed = true;
                    break;
                case 'note':
                default:
                    $subscription['creator_note'] = trim((string) ($data['creator_note'] ?? ''));
                    $message = 'Observacao do assinante atualizada.';
                    $changed = true;
                    break;
            }

            break;
        }
        unset($subscription);

        if (! $changed) {
            return ['ok' => false, 'message' => 'Nao foi possivel atualizar este assinante.'];
        }

        $this->save('subscriptions', $subscriptions);

        return ['ok' => true, 'message' => $message];
    }

    public function creatorLiveData(int $creatorId, array $filters = []): array
    {
        $lives = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => (int) $live['creator_id'] === $creatorId));
        $lives = array_map(fn (array $live): array => $this->hydrateLiveRuntime($live), $lives);
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['status'] ?? 'scheduled'));
        $selectedId = (int) ($filters['live'] ?? 0);

        $filtered = array_values(array_filter($lives, static function (array $live) use ($query, $status): bool {
            if ($status !== '' && (string) ($live['status_bucket'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) ($live['title'] ?? '') . ' ' . (string) ($live['description'] ?? ''));

            return str_contains($haystack, $query);
        }));
        $filtered = $this->sortCreatorLives($filtered, $status);
        $allSorted = $this->sortCreatorLives($lives, $status);

        $selectedLive = null;
        if ($selectedId > 0) {
            foreach ($lives as $live) {
                if ((int) ($live['id'] ?? 0) === $selectedId) {
                    $selectedLive = $live;
                    break;
                }
            }
        }
        $selectedMessages = [];
        $selectedEngagement = [
            'recent_tips' => [],
            'top_supporters' => [],
            'tip_total_amount' => 0,
        ];
        if ($selectedLive !== null) {
            $selectedMessages = $this->liveChatMessagesFor((int) ($selectedLive['id'] ?? 0), 8);
            $selectedEngagement = $this->liveEngagementData($selectedLive, 6, 4);
            $selectedLive['tip_total_amount'] = (int) ($selectedEngagement['tip_total_amount'] ?? 0);
        }

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'lives' => $allSorted,
            'filtered_lives' => $filtered,
            'selected_live' => $selectedLive,
            'active_live' => array_values(array_filter($allSorted, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'live'))[0] ?? null,
            'messages' => $selectedMessages,
            'recent_tips' => $selectedEngagement['recent_tips'],
            'top_supporters' => $selectedEngagement['top_supporters'],
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
            'summary' => [
                'scheduled' => count(array_filter($allSorted, static fn (array $live): bool => (string) ($live['status_bucket'] ?? '') === 'scheduled')),
                'live' => count(array_filter($allSorted, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'live')),
                'ended' => count(array_filter($allSorted, static fn (array $live): bool => (string) ($live['status_bucket'] ?? '') === 'ended')),
                'expired' => count(array_filter($allSorted, static fn (array $live): bool => (string) ($live['status_bucket'] ?? '') === 'expired')),
                'chat_enabled' => count(array_filter($allSorted, static fn (array $live): bool => (bool) ($live['chat_enabled'] ?? false))),
            ],
        ];
    }

    public function saveLive(int $creatorId, array $data): array
    {
        $lives = $this->liveSessions();
        $liveId = isset($data['id']) ? (int) $data['id'] : 0;
        $creatorProfile = $this->findCreatorProfile($creatorId) ?? [];
        $liveType = in_array(($data['live_type'] ?? 'scheduled'), ['instant', 'scheduled'], true) ? (string) $data['live_type'] : 'scheduled';
        $rawStatus = (string) ($data['status'] ?? 'scheduled');
        $status = in_array($rawStatus, ['scheduled', 'live', 'ended'], true) ? $rawStatus : 'scheduled';
        $rawAccessMode = (string) ($data['access_mode'] ?? 'public');
        $normalizedAccessMode = in_array($rawAccessMode, ['public', 'subscriber', 'vip'], true) ? $rawAccessMode : 'public';
        $priceTokens = max(0, (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? 0));
        if ($normalizedAccessMode === 'vip') {
            $priceTokens = max(1, $priceTokens);
        }
        $darkroomPriceTokens = max(0, (int) ($data['darkroom_price_luacoins'] ?? $data['darkroom_price_tokens'] ?? 0));
        $darkroomDurationMinutes = $this->sanitizeDarkroomDurationMinutes((int) ($data['darkroom_duration_minutes'] ?? 0));
        $scheduledFor = $this->normalizeLiveDateTime(
            (string) ($data['scheduled_for'] ?? ''),
            $liveType === 'instant' ? '+2 hours' : '+1 day'
        );
        $chatAudience = $this->sanitizeLiveChatAudience((string) ($data['chat_audience'] ?? ($creatorProfile['live_chat_audience_default'] ?? 'all')));
        $defaultSettings = $this->settings();
        $chatEnabled = $chatAudience !== 'off';
        $payload = [
            'creator_id' => $creatorId,
            'live_type' => $liveType,
            'title' => trim((string) ($data['title'] ?? 'Nova live')),
            'description' => trim((string) ($data['description'] ?? 'Sessao criada pelo painel do criador.')),
            'status' => $status,
            'scheduled_for' => $scheduledFor,
            'viewer_count' => max(0, (int) ($data['viewer_count'] ?? 0)),
            'price_tokens' => $priceTokens,
            'darkroom_price_tokens' => $darkroomPriceTokens,
            'darkroom_duration_minutes' => $darkroomDurationMinutes,
            'chat_enabled' => $chatEnabled,
            'chat_audience' => $chatAudience,
            'category' => $this->normalizeAudienceCategory((string) ($data['category'] ?? 'todos')),
            'access_mode' => $normalizedAccessMode,
            'goal_tokens' => max(0, (int) ($data['goal_luacoins'] ?? $data['goal_tokens'] ?? 0)),
            'cover_url' => trim((string) ($data['cover_url'] ?? '')),
            'pinned_notice' => trim((string) ($data['pinned_notice'] ?? '')),
            // Replay automatico desabilitado para reduzir uso de armazenamento na VPS.
            'recording_enabled' => false,
            'max_live_duration_minutes' => max(5, (int) ($defaultSettings['live_max_duration_minutes'] ?? 30)),
            'stream_mode' => $this->liveDriver() === 'mediamtx' ? 'mediamtx' : 'segment_queue',
            'segment_duration_seconds' => self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS,
            'max_bitrate_kbps' => max(300, min(2500, (int) ($data['max_bitrate_kbps'] ?? self::LIVE_DEFAULT_BITRATE_KBPS))),
            'video_width' => max(320, min(1920, (int) ($data['video_width'] ?? self::LIVE_DEFAULT_WIDTH))),
            'video_height' => max(240, min(1080, (int) ($data['video_height'] ?? self::LIVE_DEFAULT_HEIGHT))),
            'video_fps' => max(12, min(30, (int) ($data['video_fps'] ?? self::LIVE_DEFAULT_FPS))),
            'video_gop_seconds' => max(1, min(4, (int) ($data['video_gop_seconds'] ?? self::LIVE_DEFAULT_GOP_SECONDS))),
            'audio_bitrate_kbps' => max(48, min(320, (int) ($data['audio_bitrate_kbps'] ?? self::LIVE_DEFAULT_AUDIO_BITRATE_KBPS))),
            'audio_sample_rate' => in_array((int) ($data['audio_sample_rate'] ?? self::LIVE_DEFAULT_AUDIO_SAMPLE_RATE), [44100, 48000], true)
                ? (int) ($data['audio_sample_rate'] ?? self::LIVE_DEFAULT_AUDIO_SAMPLE_RATE)
                : self::LIVE_DEFAULT_AUDIO_SAMPLE_RATE,
        ];

        if ($liveId > 0) {
            foreach ($lives as &$live) {
                if ((int) $live['id'] === $liveId && (int) $live['creator_id'] === $creatorId) {
                    if ((int) ($payload['viewer_count'] ?? 0) === 0) {
                        $payload['viewer_count'] = (int) ($live['viewer_count'] ?? 0);
                    }
                    if (trim((string) ($live['stream_key'] ?? '')) === '') {
                        $live['stream_key'] = $this->generateLiveStreamKey();
                    }
                    if (trim((string) ($live['stream_path'] ?? '')) === '') {
                        $live['stream_path'] = $this->buildMediaMtxPath((int) ($live['id'] ?? $liveId), (string) ($live['stream_key'] ?? ''));
                    }
                    $live = array_merge($live, array_filter($payload, static fn (mixed $value): bool => $value !== ''));
                    $this->save('live_sessions', $lives);

                    return $live;
                }
            }
            unset($live);
        }

        $newLiveId = $this->store->nextId($lives);
        $streamKey = $this->generateLiveStreamKey();
        $live = [
            'id' => $newLiveId,
            'stream_key' => $streamKey,
            'stream_path' => $this->buildMediaMtxPath($newLiveId, $streamKey),
        ] + $payload;
        $lives[] = $live;
        $this->save('live_sessions', $lives);

        return $live;
    }

    public function updateLiveStatus(int $creatorId, int $liveId, string $status): bool
    {
        if (! in_array($status, ['scheduled', 'live', 'ended'], true)) {
            return false;
        }

        $lives = $this->liveSessions();
        $changed = false;

        foreach ($lives as &$live) {
            if ((int) $live['id'] === $liveId && (int) $live['creator_id'] === $creatorId) {
                $live['status'] = $status;
                if ($status === 'scheduled') {
                    $live['ended_at'] = '';
                    $live['duration_seconds'] = 0;
                }
                $changed = true;
                break;
            }
        }
        unset($live);

        if ($changed) {
            $this->save('live_sessions', $lives);
        }

        return $changed;
    }

    public function updateLiveStudioSettings(int $creatorId, int $liveId, array $data): bool
    {
        $lives = $this->liveSessions();
        $changed = false;

        foreach ($lives as &$live) {
            if ((int) ($live['id'] ?? 0) !== $liveId || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            $live['access_mode'] = in_array(($data['access_mode'] ?? $live['access_mode']), ['public', 'subscriber', 'vip'], true)
                ? (string) ($data['access_mode'] ?? $live['access_mode'])
                : (string) ($live['access_mode'] ?? 'public');
            $live['price_tokens'] = max(
                (string) ($live['access_mode'] ?? 'public') === 'vip' ? 1 : 0,
                (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? $live['price_tokens'] ?? 0)
            );
            $live['darkroom_price_tokens'] = max(0, (int) ($data['darkroom_price_luacoins'] ?? $data['darkroom_price_tokens'] ?? $live['darkroom_price_tokens'] ?? 0));
            $live['darkroom_duration_minutes'] = $this->sanitizeDarkroomDurationMinutes((int) ($data['darkroom_duration_minutes'] ?? $live['darkroom_duration_minutes'] ?? 0));
            $live['chat_audience'] = $this->sanitizeLiveChatAudience((string) ($data['chat_audience'] ?? ($live['chat_audience'] ?? 'all')));
            $live['chat_enabled'] = (string) $live['chat_audience'] !== 'off';
            $changed = true;
            break;
        }
        unset($live);

        if ($changed) {
            $this->save('live_sessions', $lives);
        }

        return $changed;
    }

    public function deleteLive(int $creatorId, int $liveId): bool
    {
        $lives = $this->liveSessions();
        $before = count($lives);
        $lives = array_values(array_filter($lives, static fn (array $live): bool => ! ((int) ($live['id'] ?? 0) === $liveId && (int) ($live['creator_id'] ?? 0) === $creatorId)));

        if (count($lives) === $before) {
            return false;
        }

        $this->save('live_sessions', $lives);
        $messages = array_values(array_filter($this->liveMessages(), static fn (array $message): bool => (int) ($message['live_id'] ?? 0) !== $liveId));
        $this->save('live_messages', $messages);

        return true;
    }

    public function joinLiveRtc(int $liveId, string $role, ?int $userId, string $sessionId): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live) {
            return ['ok' => false, 'message' => 'Live nao encontrada.'];
        }

        $role = $role === 'creator' ? 'creator' : 'viewer';
        $access = $this->accessStateForLive($live, $userId);

        if ($role === 'creator') {
            if ($userId === null || (int) $live['creator_id'] !== $userId) {
                return ['ok' => false, 'message' => 'Apenas o criador desta live pode abrir a transmissao.'];
            }
        } elseif (! (bool) ($access['granted'] ?? false)) {
            return [
                'ok' => false,
                'message' => (bool) ($access['requires_login'] ?? false)
                    ? 'Entre para acessar esta live.'
                    : ((bool) ($access['requires_vip_unlock'] ?? false)
                        ? 'Desbloqueie esta Live VIP para assistir.'
                        : ((bool) ($access['requires_darkroom_wait'] ?? false)
                            ? (string) ($access['access_message'] ?? 'O darkroom esta ativo nesta live.')
                            : 'Esta live exige assinatura ativa.')),
                'requires_login' => (bool) ($access['requires_login'] ?? false),
                'requires_subscription' => (bool) ($access['requires_subscription'] ?? false),
                'requires_vip_unlock' => (bool) ($access['requires_vip_unlock'] ?? false),
                'requires_darkroom_wait' => (bool) ($access['requires_darkroom_wait'] ?? false),
            ];
        }

        $presence = array_values(array_filter(
            $this->livePresence(),
            static function (array $row) use ($liveId, $role, $userId, $sessionId): bool {
                $sameLive = (int) ($row['live_id'] ?? 0) === $liveId;
                $sameRole = (string) ($row['role'] ?? '') === $role;
                $sameSession = (string) ($row['session_id'] ?? '') === $sessionId;
                $sameUser = (int) ($row['user_id'] ?? 0) === (int) ($userId ?? 0);

                return ! ($sameLive && $sameRole && $sameSession && $sameUser);
            }
        ));
        $peerId = $this->generateLivePeerId($role);
        $user = $userId !== null ? $this->findUserById($userId) : null;
        $displayName = trim((string) ($user['name'] ?? ''));

        if ($displayName === '') {
            $displayName = $role === 'creator' ? 'Criador' : 'Visitante ' . strtoupper(substr($peerId, -4));
        }

        $presence[] = [
            'id' => $this->store->nextId($presence),
            'live_id' => $liveId,
            'peer_id' => $peerId,
            'role' => $role,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'display_name' => $displayName,
            'created_at' => date('Y-m-d H:i:s'),
            'last_seen' => date('Y-m-d H:i:s'),
        ];
        $this->save('live_presence', $presence);

        return [
            'ok' => true,
            'peer_id' => $peerId,
            'role' => $role,
            'display_name' => $displayName,
            'live' => $this->hydrateLiveRuntime($this->decorateLive($live)),
            'stream' => $this->publicLiveStreamState($liveId),
            'viewer_count' => $this->activeViewerCountForLive($liveId),
            'poll_interval_ms' => 1500,
            'heartbeat_interval_ms' => 10000,
        ];
    }

    public function startLiveBroadcast(int $creatorId, int $liveId, string $peerId, string $sessionId, array $settings = []): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
            return ['ok' => false, 'message' => 'Live nao encontrada para este criador.'];
        }

        $presence = $this->findLivePresencePeer($liveId, $peerId, $sessionId, $creatorId);
        if (! $presence || (string) ($presence['role'] ?? '') !== 'creator') {
            return ['ok' => false, 'message' => 'Sessao do criador nao encontrada. Recarregue o studio.'];
        }

        $this->clearLiveSignals($liveId);
        if ($this->liveDriver() !== 'mediamtx') {
            $this->clearLiveSegmentFiles($liveId);
        }
        $streams = $this->liveStreams();
        $updated = false;
        $now = date('Y-m-d H:i:s');
        $segmentDurationSeconds = max(2, min(15, (int) ($settings['segment_duration_seconds'] ?? $live['segment_duration_seconds'] ?? self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS)));
        $maxBitrate = max(300, min(2500, (int) ($settings['max_bitrate_kbps'] ?? $live['max_bitrate_kbps'] ?? self::LIVE_DEFAULT_BITRATE_KBPS)));
        $videoWidth = max(320, min(1920, (int) ($settings['video_width'] ?? $live['video_width'] ?? self::LIVE_DEFAULT_WIDTH)));
        $videoHeight = max(240, min(1080, (int) ($settings['video_height'] ?? $live['video_height'] ?? self::LIVE_DEFAULT_HEIGHT)));
        $videoFps = max(12, min(30, (int) ($settings['video_fps'] ?? $live['video_fps'] ?? self::LIVE_DEFAULT_FPS)));
        $streamMode = $this->liveDriver() === 'mediamtx' ? 'mediamtx' : 'segment_queue';
        $streamPath = trim((string) ($live['stream_path'] ?? ''));
        if ($streamPath === '') {
            $streamPath = $this->buildMediaMtxPath($liveId, trim((string) ($live['stream_key'] ?? $this->generateLiveStreamKey())));
        }

        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            $stream['status'] = 'live';
            $stream['broadcaster_peer_id'] = $peerId;
            $stream['updated_at'] = $now;
            $stream['started_at'] = (string) ($stream['started_at'] ?? $now);
            $stream['stream_mode'] = $streamMode;
            $stream['segment_duration_seconds'] = $segmentDurationSeconds;
            $stream['max_bitrate_kbps'] = $maxBitrate;
            $stream['video_width'] = $videoWidth;
            $stream['video_height'] = $videoHeight;
            $stream['video_fps'] = $videoFps;
            $stream['stream_path'] = $streamPath;
            $stream['latest_sequence'] = 0;
            if ($streamMode !== 'mediamtx') {
                $stream['segments'] = [];
            }
            $updated = true;
            break;
        }
        unset($stream);

        if (! $updated) {
            $streams[] = [
                'id' => $this->store->nextId($streams),
                'live_id' => $liveId,
                'creator_id' => $creatorId,
                'status' => 'live',
                'broadcaster_peer_id' => $peerId,
                'started_at' => $now,
                'updated_at' => $now,
                  'stream_mode' => $streamMode,
                  'segment_duration_seconds' => $segmentDurationSeconds,
                  'max_bitrate_kbps' => $maxBitrate,
                  'video_width' => $videoWidth,
                  'video_height' => $videoHeight,
                  'video_fps' => $videoFps,
                  'stream_path' => $streamPath,
                  'latest_sequence' => 0,
                  'segments' => [],
              ];
          }

        $this->save('live_streams', $streams);
        $this->updateLiveRuntimeFields($liveId, [
            'status' => 'live',
            'stream_mode' => $streamMode,
            'segment_duration_seconds' => $segmentDurationSeconds,
            'max_bitrate_kbps' => $maxBitrate,
            'video_width' => $videoWidth,
            'video_height' => $videoHeight,
            'video_fps' => $videoFps,
            'viewer_count' => $this->activeViewerCountForLive($liveId),
            'started_at' => $now,
            'ended_at' => '',
            'duration_seconds' => 0,
            'was_live' => true,
        ]);

        return [
            'ok' => true,
            'message' => 'Transmissao iniciada.',
            'live' => $this->hydrateLiveRuntime($this->decorateLive($this->findLiveById($liveId) ?? $live)),
            'stream' => $this->publicLiveStreamState($liveId),
        ];
    }

    public function stopLiveBroadcast(int $creatorId, int $liveId, string $peerId, string $sessionId): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
            return ['ok' => false, 'message' => 'Live nao encontrada para este criador.'];
        }

        $presence = $this->findLivePresencePeer($liveId, $peerId, $sessionId, $creatorId);
        if ($presence !== null && (string) ($presence['role'] ?? '') !== 'creator') {
            return ['ok' => false, 'message' => 'Sessao do criador nao encontrada.'];
        }

        $streams = $this->liveStreams();
        $now = date('Y-m-d H:i:s');
        $startedAt = (string) ($live['started_at'] ?? '');
        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            $stream['status'] = 'ended';
            $stream['broadcaster_peer_id'] = '';
            $stream['updated_at'] = $now;
            $stream['stopped_at'] = $now;
            $startedAt = (string) ($stream['started_at'] ?? $startedAt);
        }
        unset($stream);
        $this->save('live_streams', $streams);
        $this->clearLiveSignals($liveId);
        $presenceRows = array_values(array_filter(
            $this->livePresence(),
            static fn (array $row): bool => ! (
                (int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['role'] ?? '') === 'creator'
                && (int) ($row['user_id'] ?? 0) === $creatorId
            )
        ));
        $this->save('live_presence', $presenceRows);
        $durationSeconds = $this->calculateLiveDurationSeconds($startedAt, $now);
        $this->updateLiveRuntimeFields($liveId, [
            'status' => 'ended',
            'viewer_count' => 0,
            'ended_at' => $now,
            'duration_seconds' => $durationSeconds,
            'recording_status' => 'disabled',
        ]);
        // Replay automatico desabilitado para reduzir uso de armazenamento na VPS.

        return [
            'ok' => true,
            'message' => 'Transmissao encerrada.',
            'live' => $this->hydrateLiveRuntime($this->decorateLive($this->findLiveById($liveId) ?? $live)),
            'stream' => $this->publicLiveStreamState($liveId),
            'duration_seconds' => $durationSeconds,
            'duration_label' => $this->formatLiveDuration($durationSeconds),
            'title' => (string) ($live['title'] ?? 'Live'),
        ];
    }

    public function appendLiveSegment(int $creatorId, int $liveId, string $peerId, string $sessionId, array $data): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
            return ['ok' => false, 'message' => 'Live nao encontrada para este criador.'];
        }

        $presence = $this->findLivePresencePeer($liveId, $peerId, $sessionId, $creatorId);
        if (! $presence || (string) ($presence['role'] ?? '') !== 'creator') {
            return ['ok' => false, 'message' => 'Sessao do criador nao encontrada. Reabra o studio.'];
        }

        $streams = $this->liveStreams();
        $now = date('Y-m-d H:i:s');
        $segmentUrl = trim((string) ($data['segment_url'] ?? ''));
        $segmentBytes = max(0, (int) ($data['segment_bytes'] ?? 0));
        $segmentMimeType = trim((string) ($data['segment_mime_type'] ?? 'video/webm'));
        $segmentDurationMs = max(1000, (int) ($data['segment_duration_ms'] ?? (self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS * 1000)));
        $segmentSequence = max(1, (int) ($data['segment_sequence'] ?? 0));
        $filesToDelete = [];
        $updated = false;

        if ($segmentUrl === '') {
            return ['ok' => false, 'message' => 'Segmento invalido para esta live.'];
        }

        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            $currentSequence = (int) ($stream['latest_sequence'] ?? 0);
            if ($segmentSequence <= 0) {
                $segmentSequence = $currentSequence + 1;
            }

            $segments = $this->normalizeLiveSegments((array) ($stream['segments'] ?? []));
            $segments = array_values(array_filter(
                $segments,
                static fn (array $segment): bool => (int) ($segment['sequence'] ?? 0) !== $segmentSequence
            ));
            $segments[] = [
                'sequence' => $segmentSequence,
                'url' => $segmentUrl,
                'duration_ms' => $segmentDurationMs,
                'mime_type' => $segmentMimeType !== '' ? $segmentMimeType : 'video/webm',
                'bytes' => $segmentBytes,
                'created_at' => $now,
            ];
            usort($segments, static fn (array $left, array $right): int => ((int) ($left['sequence'] ?? 0)) <=> ((int) ($right['sequence'] ?? 0)));

            while (count($segments) > self::LIVE_SEGMENT_RETENTION_COUNT) {
                $removed = array_shift($segments);
                if (is_array($removed) && (string) ($removed['url'] ?? '') !== '') {
                    $filesToDelete[] = (string) $removed['url'];
                }
            }

            $stream['status'] = 'live';
            $stream['stream_mode'] = 'segment_queue';
            $stream['broadcaster_peer_id'] = $peerId;
            $stream['updated_at'] = $now;
            $stream['latest_sequence'] = max($segmentSequence, (int) ($stream['latest_sequence'] ?? 0));
            $stream['segment_duration_seconds'] = max(2, min(15, (int) ceil($segmentDurationMs / 1000)));
            $stream['segments'] = $segments;
            $updated = true;
            break;
        }
        unset($stream);

        if (! $updated) {
            return ['ok' => false, 'message' => 'Transmissao nao esta ativa para receber segmentos.'];
        }

        $this->save('live_streams', $streams);

        foreach ($filesToDelete as $fileUrl) {
            $this->deletePublicMediaFile($fileUrl);
        }

        $this->updateLiveRuntimeFields($liveId, [
            'status' => 'live',
            'stream_mode' => 'segment_queue',
            'viewer_count' => $this->activeViewerCountForLive($liveId),
        ]);

        return [
            'ok' => true,
            'message' => 'Segmento recebido.',
            'segment_sequence' => $segmentSequence,
            'stream' => $this->publicLiveStreamState($liveId),
            'viewer_count' => $this->activeViewerCountForLive($liveId),
        ];
    }

    public function saveLiveRecording(int $creatorId, int $liveId, array $data): array
    {
        // O replay automatico foi desabilitado para reduzir uso de armazenamento na VPS.
        return [
            'ok' => false,
            'code' => 'recording_disabled',
            'message' => 'A gravacao automatica da live esta desabilitada neste ambiente.',
        ];

        $lives = $this->liveSessions();
        $recordingUrl = trim((string) ($data['recording_url'] ?? ''));
        $thumbnailUrl = trim((string) ($data['thumbnail_url'] ?? ''));
        $recordingBytes = $recordingUrl !== '' ? \public_media_file_bytes($recordingUrl) : 0;
        if ($recordingBytes <= 0) {
            $recordingBytes = max(0, (int) ($data['recording_bytes'] ?? 0));
        }

        $thumbnailBytes = $thumbnailUrl !== '' ? \public_media_file_bytes($thumbnailUrl) : 0;
        if ($thumbnailBytes <= 0) {
            $thumbnailBytes = max(0, (int) ($data['thumbnail_bytes'] ?? 0));
        }

        if ($recordingUrl === '') {
            return ['ok' => false, 'message' => 'Arquivo de replay invalido.'];
        }

        foreach ($lives as &$live) {
            if ((int) ($live['id'] ?? 0) !== $liveId || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            $storageLimitBytes = $this->creatorContentStorageLimitBytes();
            $projectedUsageBytes = $this->creatorContentUsageBytes($creatorId, (int) ($live['replay_content_id'] ?? 0)) + $recordingBytes + $thumbnailBytes;

            if ($projectedUsageBytes > $storageLimitBytes) {
                return [
                    'ok' => false,
                    'code' => 'storage_quota_exceeded',
                    'message' => 'Sem espaço para salvar o replay automático. Exclua um replay antigo em Meus Conteúdos e tente novamente.',
                    'redirect_url' => '/creator/content',
                    'storage_used_bytes' => $this->creatorContentUsageBytes($creatorId),
                    'storage_limit_bytes' => $storageLimitBytes,
                ];
            }

            $live['recording_enabled'] = true;
            $live['recording_url'] = $recordingUrl;
            $live['recording_status'] = 'ready';
              $live['recording_mime_type'] = trim((string) ($data['recording_mime_type'] ?? 'video/webm'));
              $live['recording_bytes'] = $recordingBytes;
              $live['recording_thumbnail_bytes'] = $thumbnailBytes;
              $live['recording_duration_seconds'] = max(0, (int) ($data['recording_duration_seconds'] ?? 0));
              $live['recording_label'] = trim((string) ($data['recording_label'] ?? 'Replay local'));
              $live['recording_thumbnail_url'] = $thumbnailUrl !== '' ? $thumbnailUrl : (string) ($live['recording_thumbnail_url'] ?? '');
              if ($thumbnailUrl !== '' && trim((string) ($live['cover_url'] ?? '')) === '') {
                  $live['cover_url'] = $thumbnailUrl;
              }
              $live['recorded_at'] = date('Y-m-d H:i:s');
              $content = $this->upsertReplayContentFromLive($live);
              $this->save('live_sessions', $lives);

            return [
                'ok' => true,
                'message' => 'Replay enviado com sucesso.',
                'live' => $this->hydrateLiveRuntime($this->decorateLive($live)),
                'stream' => $this->publicLiveStreamState($liveId),
                'content_id' => (int) ($content['id'] ?? 0),
            ];
        }
        unset($live);

        return ['ok' => false, 'message' => 'Live nao encontrada para anexar o replay.'];
    }

    public function pollLiveRtc(int $liveId, string $peerId, ?int $userId, string $sessionId, int $afterId = 0): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);

        if (! $live) {
            return ['ok' => false, 'message' => 'Live nao encontrada.'];
        }

        $presence = $this->findLivePresencePeer($liveId, $peerId, $sessionId, $userId);
        if (! $presence) {
            return ['ok' => false, 'message' => 'Sessao da live expirada. Entre novamente.'];
        }

        $access = $this->accessStateForLive($live, $userId);
        $signals = array_values(array_filter(
            $this->liveSignals(),
            static fn (array $signal): bool => (int) ($signal['live_id'] ?? 0) === $liveId
                && (string) ($signal['to_peer_id'] ?? '') === $peerId
                && (int) ($signal['id'] ?? 0) > $afterId
        ));
        usort($signals, static fn (array $left, array $right): int => ((int) ($left['id'] ?? 0)) <=> ((int) ($right['id'] ?? 0)));
        $stream = $this->publicLiveStreamState($liveId);
        if ((string) ($presence['role'] ?? 'viewer') === 'viewer' && ! (bool) ($access['granted'] ?? false)) {
            $stream['hls_url'] = '';
            $stream['ready'] = false;
        }
        $segments = array_values(array_filter(
            (array) ($stream['segments'] ?? []),
            static fn (array $segment): bool => (int) ($segment['sequence'] ?? 0) > $afterId
        ));

        $decoratedLive = $this->hydrateLiveRuntime($this->decorateLive($live));
        $engagement = $this->liveEngagementData($decoratedLive);
        $shouldHideRoomData = ! (bool) ($access['granted'] ?? false)
            && ((bool) ($access['requires_vip_unlock'] ?? false) || (bool) ($access['requires_darkroom_wait'] ?? false));

        return [
            'ok' => true,
            'messages' => array_map(static fn (array $signal): array => [
                'id' => (int) ($signal['id'] ?? 0),
                'kind' => (string) ($signal['kind'] ?? ''),
                'from_peer_id' => (string) ($signal['from_peer_id'] ?? ''),
                'payload' => is_array($signal['payload'] ?? null) ? $signal['payload'] : [],
                'created_at' => (string) ($signal['created_at'] ?? ''),
            ], $signals),
            'segments' => $segments,
            'live' => $decoratedLive,
            'stream' => $stream,
            'chat_messages' => $shouldHideRoomData ? [] : $this->liveChatMessagesFor($liveId, 20),
            'recent_tips' => $shouldHideRoomData ? [] : $engagement['recent_tips'],
            'top_supporters' => $shouldHideRoomData ? [] : $engagement['top_supporters'],
            'tip_total_amount' => (int) ($engagement['tip_total_amount'] ?? 0),
            'priority_alert' => $this->latestPriorityAlertForLive($liveId),
            'viewer_count' => $this->activeViewerCountForLive($liveId),
            'can_watch' => (bool) ($access['granted'] ?? false),
            'can_chat' => $this->canUserChatInLive($decoratedLive, $userId),
            'can_tip' => $userId !== null && (bool) ($access['granted'] ?? false),
            'chat_audience' => (string) ($decoratedLive['chat_audience'] ?? 'all'),
            'requires_login' => (bool) ($access['requires_login'] ?? false),
            'requires_subscription' => (bool) ($access['requires_subscription'] ?? false),
            'requires_vip_unlock' => (bool) ($access['requires_vip_unlock'] ?? false),
            'requires_darkroom_wait' => (bool) ($access['requires_darkroom_wait'] ?? false),
            'vip_unlocked' => (bool) ($access['vip_unlocked'] ?? false),
            'vip_unlock_price' => (int) ($access['vip_price_tokens'] ?? (int) ($decoratedLive['price_tokens'] ?? 0)),
            'darkroom_available' => (bool) ($access['darkroom_available'] ?? false),
            'darkroom_active' => (bool) ($access['darkroom_active'] ?? false),
            'darkroom_is_owner' => (bool) ($access['darkroom_is_owner'] ?? false),
            'darkroom_price_tokens' => (int) ($access['darkroom_price_tokens'] ?? 0),
            'darkroom_duration_minutes' => (int) ($access['darkroom_duration_minutes'] ?? 0),
            'darkroom_remaining_seconds' => (int) ($access['darkroom_remaining_seconds'] ?? 0),
            'darkroom_owner_name' => (string) ($access['darkroom_owner_name'] ?? ''),
            'darkroom_started_at' => (string) ($access['darkroom_started_at'] ?? ''),
            'darkroom_ends_at' => (string) ($access['darkroom_ends_at'] ?? ''),
            'access_message' => (string) ($access['access_message'] ?? ''),
            'server_time' => date('c'),
        ];
    }

    public function sendLiveRtcSignal(int $liveId, string $fromPeerId, string $toPeerId, string $kind, array $payload, ?int $userId, string $sessionId): array
    {
        $this->cleanupLiveRtcData();
        $live = $this->findLiveById($liveId);
        $allowedKinds = ['offer', 'answer', 'candidate'];

        if (! $live || $fromPeerId === '' || $toPeerId === '' || ! in_array($kind, $allowedKinds, true)) {
            return ['ok' => false, 'message' => 'Sinal invalido para esta live.'];
        }

        $presence = $this->findLivePresencePeer($liveId, $fromPeerId, $sessionId, $userId);
        if (! $presence) {
            return ['ok' => false, 'message' => 'Peer nao autorizado para esta live.'];
        }

        $access = $this->accessStateForLive($live, $userId);
        if ((string) ($presence['role'] ?? 'viewer') === 'viewer' && ! (bool) ($access['granted'] ?? false)) {
            return ['ok' => false, 'message' => 'Seu acesso a esta live nao esta ativo.'];
        }

        $signals = $this->liveSignals();
        $signals[] = [
            'id' => $this->store->nextId($signals),
            'live_id' => $liveId,
            'from_peer_id' => $fromPeerId,
            'to_peer_id' => $toPeerId,
            'kind' => $kind,
            'payload' => $payload,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('live_signals', $signals);

        return ['ok' => true];
    }

    public function heartbeatLiveRtc(int $liveId, string $peerId, ?int $userId, string $sessionId): array
    {
        $this->cleanupLiveRtcData();
        $presence = $this->livePresence();
        $updated = false;

        foreach ($presence as &$row) {
            if ((int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['peer_id'] ?? '') === $peerId
                && (string) ($row['session_id'] ?? '') === $sessionId
                && ((int) ($row['user_id'] ?? 0) === (int) ($userId ?? 0))
            ) {
                $row['last_seen'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }
        unset($row);

        if (! $updated) {
            return ['ok' => false, 'message' => 'Heartbeat rejeitado para esta live.'];
        }

        $this->save('live_presence', $presence);

        return [
            'ok' => true,
            'stream' => $this->publicLiveStreamState($liveId),
            'viewer_count' => $this->activeViewerCountForLive($liveId),
        ];
    }

    public function leaveLiveRtc(int $liveId, string $peerId, ?int $userId, string $sessionId): array
    {
        $this->cleanupLiveRtcData();
        $presence = $this->findLivePresencePeer($liveId, $peerId, $sessionId, $userId);

        if ($presence === null) {
            return ['ok' => true];
        }

        $this->removeLivePresencePeer($liveId, $peerId, $sessionId, $userId);

        $streams = $this->liveStreams();
        $changed = false;
        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            if ((string) ($stream['broadcaster_peer_id'] ?? '') === $peerId) {
                $stream['broadcaster_peer_id'] = '';
                $stream['status'] = 'idle';
                $stream['updated_at'] = date('Y-m-d H:i:s');
                $changed = true;
            }
        }
        unset($stream);

        if ($changed) {
            $this->save('live_streams', $streams);
        }

        return ['ok' => true];
    }

    public function creatorWalletData(int $creatorId, array $filters = []): array
    {
        $wallet = $this->walletData($creatorId);
        $minWithdrawal = (int) ($this->settings()['withdraw_min_luacoins'] ?? 50);
        $creator = $this->findCreatorBySlugOrId(null, $creatorId);
        $verificationStatus = $this->verificationStatusValue((string) ($creator['verification_status'] ?? 'pending'));
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $type = trim((string) ($filters['type'] ?? ''));
        $transactions = array_values(array_filter($wallet['transactions'], static function (array $transaction) use ($query, $type): bool {
            if ($type !== '' && ! str_contains((string) ($transaction['type'] ?? ''), $type)) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) ($transaction['note'] ?? '') . ' ' . (string) ($transaction['type'] ?? ''));

            return str_contains($haystack, $query);
        }));
        $subscriptionIncome = array_reduce(
            array_filter($wallet['transactions'], static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'subscription_income'),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );
        $tipsIncome = array_reduce(
            array_filter($wallet['transactions'], static fn (array $transaction): bool => in_array((string) ($transaction['type'] ?? ''), ['tip_income', 'vip_live_income', 'darkroom_income'], true)),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );
        $pendingPayouts = array_reduce(
            array_filter($wallet['transactions'], static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout_request'),
            static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0),
            0
        );

        return $wallet + [
            'creator' => $creator,
            'transactions_filtered' => $transactions,
            'can_withdraw' => $wallet['balance'] >= $minWithdrawal && $verificationStatus === 'approved',
            'min_withdrawal' => $minWithdrawal,
            'filters' => [
                'q' => $query,
                'type' => $type,
            ],
            'summary' => [
                'subscription_income' => $subscriptionIncome,
                'tips_income' => $tipsIncome,
                'pending_payouts' => $pendingPayouts,
                'available_brl' => round((float) $wallet['balance'] * (float) ($this->settings()['luacoin_price_brl'] ?? 0.07), 2),
            ],
            'payout_profile' => [
                'method' => (string) ($creator['payout_method'] ?? 'pix'),
                'key' => (string) ($creator['payout_key'] ?? ''),
            ],
            'verification' => [
                'status' => $verificationStatus,
                'identity_document' => is_array($creator['identity_document'] ?? null) ? $creator['identity_document'] : null,
            ],
        ];
    }

    public function creatorFavoritesData(int $creatorId): array
    {
        $favorites = $this->favoritesData($creatorId);
        $favoriteCreators = array_values(array_filter(
            $favorites['favorite_creators'],
            static fn (array $creator): bool => (int) ($creator['id'] ?? 0) !== $creatorId
        ));
        $savedContent = array_values(array_filter(
            $favorites['saved_content'],
            static fn (array $item): bool => (int) ($item['creator_id'] ?? 0) !== $creatorId
        ));

        if ($favoriteCreators === []) {
            $fallbackCreators = array_values(array_filter(
                $this->creators(),
                static fn (array $creator): bool => (int) $creator['id'] !== $creatorId
            ));
            usort($fallbackCreators, static fn (array $left, array $right): int => [$right['featured'] ? 1 : 0, $right['followers'] ?? 0] <=> [$left['featured'] ? 1 : 0, $left['followers'] ?? 0]);
            $favoriteCreators = array_slice($fallbackCreators, 0, 4);
        }

        if ($savedContent === []) {
            $fallbackContent = array_values(array_filter(
                $this->contentsWithCreators(),
                static fn (array $item): bool => (int) $item['creator_id'] !== $creatorId && $item['status'] === 'approved'
            ));
            $savedContent = array_slice($this->sortByDate($fallbackContent, 'created_at'), 0, 6);
        }

        $trackedCreatorIds = array_map(static fn (array $creator): int => (int) $creator['id'], $favoriteCreators);
        $trackedLives = array_values(array_filter(
            $this->livesWithCreators(),
            static fn (array $live): bool => in_array((int) $live['creator_id'], $trackedCreatorIds, true) && in_array($live['status'], ['live', 'scheduled'], true)
        ));
        $suggestedContent = array_values(array_filter(
            $this->contentsWithCreators(),
            static fn (array $item): bool => (int) ($item['creator_id'] ?? 0) !== $creatorId && (string) ($item['status'] ?? '') === 'approved'
        ));

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'favorite_creators' => $favoriteCreators,
            'saved_content' => $savedContent,
            'tracked_lives' => array_slice($this->sortByDate($trackedLives, 'scheduled_for'), 0, 4),
            'suggested_creators' => array_slice(array_values(array_filter($this->creators(), static fn (array $creator): bool => (int) ($creator['id'] ?? 0) !== $creatorId)), 0, 4),
            'suggested_content' => array_slice($this->sortByDate($suggestedContent, 'created_at'), 0, 4),
        ];
    }

    public function creatorSettingsData(int $creatorId): array
    {
        $creator = $this->findCreatorBySlugOrId(null, $creatorId);
        $wallet = $this->creatorWalletData($creatorId);
        $plans = $this->creatorPlansData($creatorId);
        $lives = $this->creatorLiveData($creatorId);
        $settings = $this->settings();

        return [
            'creator' => $creator,
            'wallet' => $wallet,
            'plans' => $plans['plans'],
            'active_subscribers' => $plans['active_subscribers'],
            'active_live' => $lives['active_live'],
            'next_live' => $lives['lives'][0] ?? null,
            'platform' => [
                'luacoin_price_brl' => (float) ($settings['luacoin_price_brl'] ?? 0.07),
                'withdraw_min_luacoins' => (int) ($settings['withdraw_min_luacoins'] ?? 50),
                'withdraw_max_luacoins' => (int) ($settings['withdraw_max_luacoins'] ?? 25000),
            ],
            'live_defaults' => [
                'chat_audience' => $this->sanitizeLiveChatAudience((string) ($creator['live_chat_audience_default'] ?? 'all')),
                'priority_tip_tiers' => $this->priorityTipTiersForCreator($creatorId),
                'priority_tip_messages' => $this->priorityTipMessagesForCreator($creatorId),
                'priority_tip_custom' => max(1, (int) ($creator['priority_tip_custom'] ?? 150)),
                'max_duration_minutes' => max(5, (int) ($settings['live_max_duration_minutes'] ?? 30)),
            ],
            'security' => [
                'has_stream_key' => trim((string) ($creator['stream_key'] ?? '')) !== '',
                'has_payout_key' => trim((string) ($creator['payout_key'] ?? '')) !== '',
            ],
            'verification' => [
                'status' => $this->verificationStatusValue((string) ($creator['verification_status'] ?? 'pending')),
                'can_withdraw' => $this->verificationStatusValue((string) ($creator['verification_status'] ?? 'pending')) === 'approved',
                'identity_document' => is_array($creator['identity_document'] ?? null) ? $creator['identity_document'] : null,
            ],
        ];
    }

    public function updateCreatorSettings(int $creatorId, array $data): bool
    {
        $users = $this->users();
        $profiles = $this->creatorProfiles();
        $foundCreator = false;
        $foundProfile = false;
        $changedUsers = false;
        $changedProfiles = false;
        $name = trim((string) ($data['name'] ?? ''));
        $headline = trim((string) ($data['headline'] ?? ''));
        $bio = trim((string) ($data['bio'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $mood = trim((string) ($data['mood'] ?? ''));
        $coverStyle = trim((string) ($data['cover_style'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $username = $this->normalizeUsername((string) ($data['username'] ?? ''));
        $avatarUrl = trim((string) ($data['avatar_url'] ?? ''));
        $coverUrl = trim((string) ($data['cover_url'] ?? ''));
        $payoutMethod = trim((string) ($data['payout_method'] ?? ''));
        $payoutKey = trim((string) ($data['payout_key'] ?? ''));
        $instagram = trim((string) ($data['instagram'] ?? ''));
        $telegram = trim((string) ($data['telegram'] ?? ''));
        $streamKey = trim((string) ($data['stream_key'] ?? ''));
        $liveChatAudienceDefault = $this->sanitizeLiveChatAudience((string) ($data['live_chat_audience_default'] ?? 'all'));
        $replayVisibilityDefault = $this->sanitizeReplayVisibility((string) ($data['replay_visibility_default'] ?? 'subscriber'));
        $priorityTierKeys = [
            'priority_tip_tier_1',
            'priority_tip_tier_2',
            'priority_tip_tier_3',
            'priority_tip_tier_4',
            'priority_tip_tier_5',
        ];
        $priorityTiers = [];
        foreach ($priorityTierKeys as $key) {
            $priorityTiers[] = max(1, (int) ($data[$key] ?? 0));
        }
        $priorityTipCustom = max(1, (int) ($data['priority_tip_custom'] ?? 150));
        $priorityMessagesInput = [
            (string) ($data['priority_tip_message_1'] ?? ''),
            (string) ($data['priority_tip_message_2'] ?? ''),
            (string) ($data['priority_tip_message_3'] ?? ''),
            (string) ($data['priority_tip_message_4'] ?? ''),
            (string) ($data['priority_tip_message_5'] ?? ''),
            (string) ($data['priority_tip_message_custom'] ?? ''),
        ];
        $normalizedTiers = $this->normalizePriorityTipTiers(array_merge($priorityTiers, [$priorityTipCustom]));
        $priorityMessages = $this->normalizePriorityTipMessages(
            array_combine(
                array_map(static fn (int $tier): string => (string) max(1, $tier), array_merge($priorityTiers, [$priorityTipCustom])),
                array_map(static fn (string $message): string => trim($message), $priorityMessagesInput)
            ) ?: []
        );
        $newPassword = (string) ($data['new_password'] ?? '');
        $identityDocument = is_array($data['identity_document'] ?? null) ? $data['identity_document'] : null;
        $verificationReset = false;

        if (array_key_exists('username', $data) && ($username === '' || $this->usernameInUse($username, $creatorId))) {
            return false;
        }

        foreach ($users as &$user) {
            if ((int) $user['id'] !== $creatorId || ($user['role'] ?? null) !== 'creator') {
                continue;
            }

            $foundCreator = true;

            if ($name !== '' && $name !== (string) $user['name']) {
                $user['name'] = $name;
                $changedUsers = true;
            }

            if (array_key_exists('username', $data) && $username !== (string) ($user['username'] ?? '')) {
                $user['username'] = $username;
                $changedUsers = true;
            }

            if (array_key_exists('headline', $data) && $headline !== (string) ($user['headline'] ?? '')) {
                $user['headline'] = $headline;
                $changedUsers = true;
            }

            if (array_key_exists('bio', $data) && $bio !== (string) ($user['bio'] ?? '')) {
                $user['bio'] = $bio;
                $changedUsers = true;
            }

            if (array_key_exists('city', $data) && $city !== (string) ($user['city'] ?? '')) {
                $user['city'] = $city;
                $changedUsers = true;
            }

            if ($newPassword !== '' && ! password_verify($newPassword, (string) ($user['password'] ?? ''))) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $changedUsers = true;
            }

            if ($identityDocument !== null) {
                $user['identity_document'] = $identityDocument;
                $user['verification_status'] = 'pending';
                $user['verification_note'] = '';
                $user['verification_requested_at'] = date('Y-m-d H:i:s');
                $user['verification_reviewed_at'] = null;
                $changedUsers = true;
                $verificationReset = true;
            }
        }
        unset($user);

        foreach ($profiles as &$profile) {
            if ((int) $profile['user_id'] !== $creatorId) {
                continue;
            }

            $foundProfile = true;

            if ($mood !== '' && $mood !== (string) ($profile['mood'] ?? '')) {
                $profile['mood'] = $mood;
                $changedProfiles = true;
            }

            if ($coverStyle !== '' && $coverStyle !== (string) ($profile['cover_style'] ?? '')) {
                $profile['cover_style'] = $coverStyle;
                $changedProfiles = true;
            }

            if ($slug !== '') {
                $normalizedSlug = $this->uniqueSlug($slug, $creatorId);
                if ($normalizedSlug !== (string) ($profile['slug'] ?? '')) {
                    $profile['slug'] = $normalizedSlug;
                    $changedProfiles = true;
                }
            }

            if (array_key_exists('avatar_url', $data) && $avatarUrl !== (string) ($profile['avatar_url'] ?? '')) {
                $profile['avatar_url'] = $avatarUrl;
                $changedProfiles = true;
            }

            if (array_key_exists('cover_url', $data) && $coverUrl !== (string) ($profile['cover_url'] ?? '')) {
                $profile['cover_url'] = $coverUrl;
                $changedProfiles = true;
            }

            if (array_key_exists('payout_method', $data) && $payoutMethod !== '' && $payoutMethod !== (string) ($profile['payout_method'] ?? '')) {
                $profile['payout_method'] = $payoutMethod;
                $changedProfiles = true;
            }

            if (array_key_exists('payout_key', $data) && $payoutKey !== (string) ($profile['payout_key'] ?? '')) {
                $profile['payout_key'] = $payoutKey;
                $profile['payout_method'] = 'pix';
                $changedProfiles = true;
            }

            if (array_key_exists('instagram', $data) && $instagram !== (string) ($profile['instagram'] ?? '')) {
                $profile['instagram'] = $instagram;
                $changedProfiles = true;
            }

            if (array_key_exists('telegram', $data) && $telegram !== (string) ($profile['telegram'] ?? '')) {
                $profile['telegram'] = $telegram;
                $changedProfiles = true;
            }

            if (array_key_exists('stream_key', $data) && $streamKey !== (string) ($profile['stream_key'] ?? '')) {
                $profile['stream_key'] = $streamKey;
                $changedProfiles = true;
            }

            if ($normalizedTiers !== $this->priorityTipTiersForProfile($profile)) {
                $profile['priority_tip_tiers'] = $normalizedTiers;
                $changedProfiles = true;
            }

            if ($priorityMessages !== $this->priorityTipMessagesForProfile($profile)) {
                $profile['priority_tip_messages'] = $priorityMessages;
                $changedProfiles = true;
            }

            if ($liveChatAudienceDefault !== (string) ($profile['live_chat_audience_default'] ?? 'all')) {
                $profile['live_chat_audience_default'] = $liveChatAudienceDefault;
                $changedProfiles = true;
            }

            if ($replayVisibilityDefault !== (string) ($profile['replay_visibility_default'] ?? 'subscriber')) {
                $profile['replay_visibility_default'] = $replayVisibilityDefault;
                $changedProfiles = true;
            }

            if ($priorityTipCustom !== (int) ($profile['priority_tip_custom'] ?? 150)) {
                $profile['priority_tip_custom'] = $priorityTipCustom;
                $changedProfiles = true;
            }
        }
        unset($profile);

        if (! $foundCreator) {
            return false;
        }

        if (! $foundProfile) {
            $profiles[] = [
                'user_id' => $creatorId,
                'slug' => $this->uniqueSlug($slug !== '' ? $slug : ($name !== '' ? $name : ((string) ($this->findUserById($creatorId)['name'] ?? 'criador'))), $creatorId),
                'mood' => $mood !== '' ? $mood : 'Lua Nova',
                'cover_style' => $coverStyle !== '' ? $coverStyle : 'rose-dawn',
                'featured' => false,
                'followers' => 0,
                'rating' => 5.0,
                'avatar_url' => $avatarUrl,
                'cover_url' => $coverUrl,
                'payout_method' => $payoutMethod !== '' ? $payoutMethod : 'pix',
                'payout_key' => $payoutKey,
                'instagram' => $instagram,
                'telegram' => $telegram,
                'stream_key' => $streamKey,
                'priority_tip_tiers' => $normalizedTiers,
                'priority_tip_messages' => $priorityMessages,
                'priority_tip_custom' => $priorityTipCustom,
                'live_chat_audience_default' => $liveChatAudienceDefault,
                'replay_visibility_default' => $replayVisibilityDefault,
            ];
            $changedProfiles = true;
        }

        if ($changedUsers) {
            $this->save('users', $users);
        }

        if ($changedProfiles) {
            $this->save('creator_profiles', $profiles);
        }

        if ($verificationReset) {
            $creator = $this->findCreatorBySlugOrId(null, $creatorId);
            $this->notifyAdmins(
                'verification',
                'Documento reenviado para analise',
                (string) ($creator['name'] ?? 'Criador') . ' reenviou a documentacao para verificacao.',
                '/admin/users'
            );
        }

        return true;
    }

    public function updateSubscriberSettings(int $subscriberId, array $data): bool
    {
        return $this->updateBasicUserProfile($subscriberId, 'subscriber', $data);
    }

    public function updateAdminProfile(int $adminId, array $data): bool
    {
        return $this->updateBasicUserProfile($adminId, 'admin', $data);
    }

    public function requestPayout(int $creatorId, array $data): array
    {
        $tokens = (int) ($data['luacoins'] ?? $data['tokens'] ?? 0);
        $minWithdrawal = (int) ($this->settings()['withdraw_min_luacoins'] ?? 50);
        $creator = $this->findCreatorBySlugOrId(null, $creatorId) ?? [];
        $payoutMethod = 'pix';
        $payoutKey = trim((string) ($data['payout_key'] ?? ($creator['payout_key'] ?? '')));
        $note = trim((string) ($data['note'] ?? ''));

        if ($this->verificationStatusValue((string) ($creator['verification_status'] ?? 'pending')) !== 'approved') {
            return ['ok' => false, 'message' => 'Sua conta ainda nao foi verificada. Reenvie a documentacao em Configuracoes e aguarde ate 48h.'];
        }

        if ($tokens < $minWithdrawal) {
            return ['ok' => false, 'message' => 'O valor minimo para saque em LuaCoins nao foi atingido.'];
        }

        if ($payoutKey === '') {
            return ['ok' => false, 'message' => 'Preencha uma chave ou conta de pagamento antes de solicitar o saque.'];
        }

        if ($this->walletBalance($creatorId) < $tokens) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para solicitar este saque.'];
        }

        $transactions = $this->walletTransactions();
        $transactions[] = [
            'id' => $this->store->nextId($transactions),
            'user_id' => $creatorId,
            'type' => 'payout_request',
            'direction' => 'out',
            'amount' => $tokens,
            'note' => $note !== '' ? $note : 'Pedido de saque enviado pelo criador',
            'payout_method' => $payoutMethod !== '' ? $payoutMethod : 'pix',
            'payout_key' => $payoutKey,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('wallet_transactions', $transactions);
        $this->notifyAdmins(
            'payout',
            'Novo saque solicitado',
            (string) ($creator['name'] ?? 'Criador') . ' solicitou saque de ' . $tokens . ' LuaCoins.',
            '/admin/finance'
        );

        return ['ok' => true, 'message' => 'Pedido de saque registrado.'];
    }

    public function adminDashboardData(): array
    {
        $users = $this->users();
        $pendingContent = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => $item['status'] === 'pending'));
        $liveNow = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => $live['status'] === 'live'));
        $finance = $this->financeData();
        $creators = $this->creators();
        $pendingPayouts = array_values(array_filter(
            $finance['transactions'] ?? [],
            static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout_request' && (string) ($transaction['status'] ?? 'pending') === 'pending'
        ));

        usort($creators, static fn (array $left, array $right): int => [$right['wallet_balance'], $right['subscriber_count'], $right['followers'] ?? 0] <=> [$left['wallet_balance'], $left['subscriber_count'], $left['followers'] ?? 0]);

        return [
            'metrics' => [
                'users' => count($users),
                'creators' => count(array_filter($users, static fn (array $user): bool => $user['role'] === 'creator')),
                'subscribers' => count(array_filter($users, static fn (array $user): bool => $user['role'] === 'subscriber')),
                'pending_content' => count($pendingContent),
                'live_now' => count($liveNow),
                'platform_result' => $finance['summary']['platform_result'],
            ],
            'pending_content' => array_slice($pendingContent, 0, 5),
            'recent_users' => array_slice($this->sortByDate($users, 'created_at'), 0, 5),
            'live_now' => $liveNow,
            'top_creators' => array_slice($creators, 0, 5),
            'pending_payouts' => array_slice($pendingPayouts, 0, 5),
        ];
    }

    public function sendAdminAnnouncement(int $adminId, array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));
        $audience = in_array(($data['audience'] ?? 'all'), ['all', 'creator', 'subscriber', 'admin'], true)
            ? (string) ($data['audience'] ?? 'all')
            : 'all';
        $href = trim((string) ($data['href'] ?? ''));
        $attachment = $this->normalizeAnnouncementAttachment(is_array($data['attachment'] ?? null) ? $data['attachment'] : null);

        if ($title === '' || $body === '') {
            return ['ok' => false, 'message' => 'Preencha o titulo e a mensagem do comunicado.'];
        }

        $targets = array_values(array_filter($this->users(), static function (array $user) use ($audience): bool {
            if ((string) ($user['status'] ?? 'active') !== 'active') {
                return false;
            }

            if ($audience === 'all') {
                return true;
            }

            return (string) ($user['role'] ?? '') === $audience;
        }));

        $announcements = $this->announcements();
        $announcementId = $this->store->nextId($announcements);
        $createdAt = date('Y-m-d H:i:s');
        $announcements[] = [
            'id' => $announcementId,
            'admin_id' => $adminId,
            'title' => $title,
            'body' => $body,
            'audience' => $audience,
            'href' => $href,
            'attachment' => $attachment,
            'recipient_count' => count($targets),
            'created_at' => $createdAt,
        ];
        $this->save('announcements', $announcements);

        foreach ($targets as $target) {
            $targetRole = (string) ($target['role'] ?? 'subscriber');
            $targetHref = $href !== '' ? $href : match ($targetRole) {
                'creator' => path_with_query('/creator/messages', ['announcement' => $announcementId]),
                'subscriber' => path_with_query('/subscriber/messages', ['announcement' => $announcementId]),
                'admin' => path_with_query('/admin/messages'),
                default => '/',
            };

            $this->notifyUser(
                (int) ($target['id'] ?? 0),
                'announcement',
                $title,
                $body,
                $targetHref,
                [
                    'announcement_id' => $announcementId,
                    'audience' => $audience,
                ],
                $createdAt
            );
        }

        return ['ok' => true, 'message' => 'Comunicado enviado com sucesso.'];
    }

    public function adminUsersData(string|array $filters = ''): array
    {
        $filters = is_array($filters) ? $filters : ['q' => $filters];
        $search = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $role = trim((string) ($filters['role'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $verification = $this->verificationStatusValue((string) ($filters['verification'] ?? ''));
        $allUsers = $this->users();
        $users = array_values(array_filter($allUsers, function (array $user) use ($search, $role, $status, $verification): bool {
            if ($role !== '' && (string) ($user['role'] ?? '') !== $role) {
                return false;
            }

            if ($status !== '' && (string) ($user['status'] ?? '') !== $status) {
                return false;
            }

            if ($verification !== '' && $this->verificationStatusValue((string) ($user['verification_status'] ?? 'pending')) !== $verification) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($user['name'] ?? '') . ' ' . ($user['email'] ?? '') . ' ' . ($user['role'] ?? '')));

            return str_contains($haystack, $search);
        }));
        $users = $this->sortByDate($users, 'created_at');
        $items = array_map(function (array $user): array {
            $item = $this->sanitizeUser($user);

            if ((string) ($user['role'] ?? '') === 'creator') {
                $profile = $this->findCreatorProfile((int) ($user['id'] ?? 0)) ?? [];
                foreach (['slug', 'mood', 'cover_style', 'avatar_url', 'cover_url', 'payout_method', 'payout_key', 'instagram', 'telegram', 'stream_key'] as $field) {
                    if (($profile[$field] ?? '') !== '') {
                        $item[$field] = $profile[$field];
                    }
                }
            }

            $item['wallet_balance'] = $this->walletBalance((int) ($user['id'] ?? 0));

            return $item;
        }, $users);

        return [
            'items' => $items,
            'summary' => [
                'total' => count($allUsers),
                'creators' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['role'] ?? '') === 'creator')),
                'subscribers' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['role'] ?? '') === 'subscriber')),
                'suspended' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['status'] ?? '') === 'suspended')),
                'verification_pending' => count(array_filter($allUsers, fn (array $user): bool => $this->verificationStatusValue((string) ($user['verification_status'] ?? 'pending')) === 'pending')),
            ],
            'filters' => [
                'q' => $search,
                'role' => $role,
                'status' => $status,
                'verification' => $verification,
            ],
        ];
    }

    public function updateUser(int $userId, array $data): bool
    {
        $users = $this->users();
        $profiles = $this->creatorProfiles();
        $changed = false;
        $profileChanged = false;
        $found = false;
        $originalRole = '';
        $targetRole = '';
        $name = trim((string) ($data['name'] ?? ''));
        $username = $this->normalizeUsername((string) ($data['username'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $headline = trim((string) ($data['headline'] ?? ''));
        $bio = trim((string) ($data['bio'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $avatarUrl = trim((string) ($data['avatar_url'] ?? ''));
        $coverUrl = trim((string) ($data['cover_url'] ?? ''));
        $newPassword = trim((string) ($data['new_password'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $mood = trim((string) ($data['mood'] ?? ''));
        $coverStyle = trim((string) ($data['cover_style'] ?? ''));
        $payoutMethod = trim((string) ($data['payout_method'] ?? ''));
        $payoutKey = trim((string) ($data['payout_key'] ?? ''));
        $instagram = trim((string) ($data['instagram'] ?? ''));
        $telegram = trim((string) ($data['telegram'] ?? ''));
        $streamKey = trim((string) ($data['stream_key'] ?? ''));
        $verificationStatus = $this->verificationStatusValue((string) ($data['verification_status'] ?? ''));
        $verificationNote = trim((string) ($data['verification_note'] ?? ''));
        $previousVerificationStatus = '';

        if (array_key_exists('username', $data) && ($username === '' || $this->usernameInUse($username, $userId))) {
            return false;
        }

        foreach ($users as &$user) {
            if ((int) $user['id'] === $userId) {
                $found = true;
                $originalRole = (string) ($user['role'] ?? 'subscriber');
                $previousVerificationStatus = $this->verificationStatusValue((string) ($user['verification_status'] ?? 'pending'));
                $user['status'] = in_array(($data['status'] ?? $user['status']), ['active', 'suspended'], true) ? $data['status'] : $user['status'];
                if (array_key_exists('headline', $data)) {
                    $user['headline'] = $headline;
                }
                if ($name !== '') {
                    $user['name'] = $name;
                }
                if (array_key_exists('username', $data)) {
                    $user['username'] = $username;
                }
                if (array_key_exists('bio', $data)) {
                    $user['bio'] = $bio;
                }
                if (array_key_exists('city', $data)) {
                    $user['city'] = $city;
                }
                if (array_key_exists('avatar_url', $data)) {
                    $user['avatar_url'] = $avatarUrl;
                }
                if (array_key_exists('cover_url', $data)) {
                    $user['cover_url'] = $coverUrl;
                }
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && ! $this->emailInUse($email, $userId)) {
                    $user['email'] = $email;
                }
                if ($newPassword !== '' && ! password_verify($newPassword, (string) ($user['password'] ?? ''))) {
                    $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                if (isset($data['role']) && in_array($data['role'], ['subscriber', 'creator', 'admin'], true)) {
                    $user['role'] = $data['role'];
                }
                if ($verificationStatus !== '') {
                    $user['verification_status'] = $verificationStatus;
                    $user['verification_note'] = $verificationNote;
                    $user['verification_reviewed_at'] = date('Y-m-d H:i:s');
                }
                $targetRole = (string) ($user['role'] ?? $originalRole);
                $changed = true;
                break;
            }
        }
        unset($user);

        if (! $found) {
            return false;
        }

        if (in_array($originalRole, ['creator'], true) || $targetRole === 'creator') {
            $profileFound = false;

            foreach ($profiles as &$profile) {
                if ((int) ($profile['user_id'] ?? 0) !== $userId) {
                    continue;
                }

                $profileFound = true;

                if ($slug !== '') {
                    $normalizedSlug = $this->uniqueSlug($slug, $userId);
                    if ($normalizedSlug !== (string) ($profile['slug'] ?? '')) {
                        $profile['slug'] = $normalizedSlug;
                        $profileChanged = true;
                    }
                }

                foreach ([
                    'mood' => $mood,
                    'cover_style' => $coverStyle,
                    'avatar_url' => $avatarUrl,
                    'cover_url' => $coverUrl,
                    'payout_method' => $payoutMethod,
                    'payout_key' => $payoutKey,
                    'instagram' => $instagram,
                    'telegram' => $telegram,
                    'stream_key' => $streamKey,
                ] as $field => $value) {
                    if (! array_key_exists($field, $data)) {
                        continue;
                    }

                    if ($value !== (string) ($profile[$field] ?? '')) {
                        $profile[$field] = $value;
                        $profileChanged = true;
                    }
                }

                break;
            }
            unset($profile);

            if (! $profileFound && $targetRole === 'creator') {
                $profiles[] = [
                    'id' => $this->store->nextId($profiles),
                    'user_id' => $userId,
                    'slug' => $this->uniqueSlug($slug !== '' ? $slug : ($name !== '' ? $name : ((string) ($this->findUserById($userId)['name'] ?? 'criador'))), $userId),
                    'mood' => $mood !== '' ? $mood : 'Lua Nova',
                    'cover_style' => $coverStyle !== '' ? $coverStyle : 'rose-dawn',
                    'featured' => false,
                    'followers' => 0,
                    'rating' => 5.0,
                    'avatar_url' => $avatarUrl,
                    'cover_url' => $coverUrl,
                    'payout_method' => $payoutMethod !== '' ? $payoutMethod : 'pix',
                    'payout_key' => $payoutKey,
                    'instagram' => $instagram,
                    'telegram' => $telegram,
                    'stream_key' => $streamKey,
                ];
                $profileChanged = true;
            }
        }

        if ($changed) {
            $this->save('users', $users);
        }

        if ($profileChanged) {
            $this->save('creator_profiles', $profiles);
        }

        if ($verificationStatus !== '' && $verificationStatus !== $previousVerificationStatus) {
            $targetUser = $this->findUserById($userId);
            $href = $this->settingsRouteForRole((string) ($targetUser['role'] ?? $targetRole ?: $originalRole));
            if ($verificationStatus === 'approved') {
                $this->notifyUser(
                    $userId,
                    'verification',
                    'Documentacao aprovada',
                    'Sua documentacao foi aprovada e sua conta ja pode solicitar saque.',
                    $href
                );
            } elseif ($verificationStatus === 'rejected') {
                $this->notifyUser(
                    $userId,
                    'verification',
                    'Reenvie sua documentacao',
                    $verificationNote !== '' ? $verificationNote : 'Sua documentacao precisa ser reenviada. Abra seu perfil e envie um novo documento.',
                    $href
                );
            }
        }

        return $changed || $profileChanged;
    }

    public function createAdminManagedUser(array $data): bool
    {
        $name = trim((string) ($data['name'] ?? ''));
        $username = $this->normalizeUsername((string) ($data['username'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = trim((string) ($data['password'] ?? ''));
        $role = in_array(($data['role'] ?? 'subscriber'), ['subscriber', 'creator', 'admin'], true) ? (string) $data['role'] : 'subscriber';
        $status = in_array(($data['status'] ?? 'active'), ['active', 'suspended'], true) ? (string) $data['status'] : 'active';

        if ($name === '' || $username === '' || $email === '' || $password === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $this->emailInUse($email) || $this->usernameInUse($username)) {
            return false;
        }

        $users = $this->users();
        $userId = $this->store->nextId($users);
        $headline = trim((string) ($data['headline'] ?? ''));
        $bio = trim((string) ($data['bio'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $avatarUrl = trim((string) ($data['avatar_url'] ?? ''));
        $coverUrl = trim((string) ($data['cover_url'] ?? ''));

        $users[] = [
            'id' => $userId,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => $status,
            'headline' => $headline,
            'bio' => $bio,
            'city' => $city,
            'avatar_url' => $avatarUrl,
            'cover_url' => $coverUrl,
            'created_at' => date('Y-m-d H:i:s'),
            'verification_status' => $role === 'admin' ? 'approved' : 'pending',
            'verification_note' => '',
            'verification_requested_at' => date('Y-m-d H:i:s'),
            'verification_reviewed_at' => $role === 'admin' ? date('Y-m-d H:i:s') : null,
        ];
        $this->save('users', $users);

        if ($role === 'creator') {
            $profiles = $this->creatorProfiles();
            $slug = trim((string) ($data['slug'] ?? ''));
            $mood = trim((string) ($data['mood'] ?? ''));
            $coverStyle = trim((string) ($data['cover_style'] ?? ''));
            $payoutMethod = trim((string) ($data['payout_method'] ?? ''));
            $payoutKey = trim((string) ($data['payout_key'] ?? ''));
            $instagram = trim((string) ($data['instagram'] ?? ''));
            $telegram = trim((string) ($data['telegram'] ?? ''));
            $streamKey = trim((string) ($data['stream_key'] ?? ''));

            $profiles[] = [
                'id' => $this->store->nextId($profiles),
                'user_id' => $userId,
                'slug' => $this->uniqueSlug($slug !== '' ? $slug : $name, $userId),
                'mood' => $mood !== '' ? $mood : 'Lua Nova',
                'cover_style' => $coverStyle !== '' ? $coverStyle : 'rose-dawn',
                'featured' => false,
                'followers' => 0,
                'rating' => 5.0,
                'avatar_url' => $avatarUrl,
                'cover_url' => $coverUrl,
                'payout_method' => $payoutMethod !== '' ? $payoutMethod : 'pix',
                'payout_key' => $payoutKey,
                'instagram' => $instagram,
                'telegram' => $telegram,
                'stream_key' => $streamKey,
            ];
            $this->save('creator_profiles', $profiles);
        }

        return true;
    }

    private function updateBasicUserProfile(int $userId, string $role, array $data): bool
    {
        $users = $this->users();
        $changed = false;
        $found = false;
        $name = trim((string) ($data['name'] ?? ''));
        $username = $this->normalizeUsername((string) ($data['username'] ?? ''));
        $newPassword = (string) ($data['new_password'] ?? '');
        $identityDocument = is_array($data['identity_document'] ?? null) ? $data['identity_document'] : null;
        $verificationReset = false;

        if (array_key_exists('username', $data) && ($username === '' || $this->usernameInUse($username, $userId))) {
            return false;
        }

        foreach ($users as &$user) {
            if ((int) ($user['id'] ?? 0) !== $userId || (string) ($user['role'] ?? '') !== $role) {
                continue;
            }

            $found = true;

            if ($name !== '' && $name !== (string) ($user['name'] ?? '')) {
                $user['name'] = $name;
                $changed = true;
            }

            if (array_key_exists('username', $data) && $username !== (string) ($user['username'] ?? '')) {
                $user['username'] = $username;
                $changed = true;
            }

            foreach (['headline', 'bio', 'city', 'avatar_url', 'cover_url'] as $field) {
                if (! array_key_exists($field, $data)) {
                    continue;
                }

                $value = trim((string) ($data[$field] ?? ''));
                if ($value !== (string) ($user[$field] ?? '')) {
                    $user[$field] = $value;
                    $changed = true;
                }
            }

            if ($newPassword !== '' && ! password_verify($newPassword, (string) ($user['password'] ?? ''))) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $changed = true;
            }

            if ($identityDocument !== null && $role !== 'admin') {
                $user['identity_document'] = $identityDocument;
                $user['verification_status'] = 'pending';
                $user['verification_note'] = '';
                $user['verification_requested_at'] = date('Y-m-d H:i:s');
                $user['verification_reviewed_at'] = null;
                $changed = true;
                $verificationReset = true;
            }

            break;
        }
        unset($user);

        if (! $found) {
            return false;
        }

        if ($changed) {
            $this->save('users', $users);
        }

        if ($verificationReset) {
            $user = $this->findUserById($userId);
            $this->notifyAdmins(
                'verification',
                'Documento reenviado para analise',
                (string) ($user['name'] ?? 'Usuario') . ' reenviou a documentacao para verificacao.',
                '/admin/users'
            );
        }

        return true;
    }

    public function moderationData(array $filters = []): array
    {
        $content = $this->contentsWithCreators();
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $pending = array_values(array_filter($content, static fn (array $item): bool => (string) ($item['status'] ?? '') === 'pending'));
        $recent = array_values(array_filter($content, static fn (array $item): bool => in_array((string) ($item['status'] ?? ''), ['approved', 'rejected'], true)));
        $filtered = array_values(array_filter($content, static function (array $item) use ($query, $status): bool {
            if ($status !== '' && (string) ($item['status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($item['title'] ?? '') . ' ' . ($item['creator']['name'] ?? '') . ' ' . ($item['kind'] ?? '')));

            return str_contains($haystack, $query);
        }));

        return [
            'pending' => $pending,
            'recent' => $recent,
            'filtered_items' => $filtered,
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
            'summary' => [
                'pending' => count($pending),
                'approved' => count(array_filter($content, static fn (array $item): bool => (string) ($item['status'] ?? '') === 'approved')),
                'rejected' => count(array_filter($content, static fn (array $item): bool => (string) ($item['status'] ?? '') === 'rejected')),
            ],
        ];
    }

    public function reviewContent(int $adminId, int $contentId, string $decision, string $feedback = ''): bool
    {
        if (! $this->findUserById($adminId) || ! in_array($decision, ['approved', 'rejected'], true)) {
            return false;
        }

        $items = $this->contentItems();
        $changed = false;
        $creatorId = 0;
        $contentTitle = 'Conteudo';

        foreach ($items as &$item) {
            if ((int) $item['id'] === $contentId) {
                $item['status'] = $decision;
                if ($feedback !== '') {
                    $item['moderation_feedback'] = trim($feedback);
                }
                $creatorId = (int) ($item['creator_id'] ?? 0);
                $contentTitle = (string) ($item['title'] ?? 'Conteudo');
                $changed = true;
                break;
            }
        }
        unset($item);

        if ($changed) {
            $this->save('content_items', $items);
            $this->notifyUser(
                $creatorId,
                'moderation',
                $decision === 'approved' ? 'Conteudo aprovado' : 'Conteudo reprovado',
                $contentTitle . ($decision === 'approved' ? ' foi aprovado pela moderacao.' : ' recebeu uma revisao da moderacao.'),
                '/creator/content'
            );
        }

        return $changed;
    }

    public function financeData(array $filters = []): array
    {
        $settings = $this->settings();
        $luacoinPriceBrl = (float) ($settings['luacoin_price_brl'] ?? 0.07);
        $transactions = $this->sortByDate($this->walletTransactions(), 'created_at');
        $decorated = array_map(function (array $transaction): array {
            $transaction['user'] = $this->findUserById((int) $transaction['user_id']);
            $transaction['creator'] = isset($transaction['creator_id']) ? $this->findCreatorBySlugOrId(null, (int) $transaction['creator_id']) : null;

            return $transaction;
        }, $transactions);
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $type = trim((string) ($filters['type'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        $subscriberSpend = 0;
        $creatorIncome = 0;

        foreach ($transactions as $transaction) {
            if (in_array($transaction['type'], ['subscription', 'tip', 'instant_content', 'vip_live', 'darkroom'], true) && $transaction['direction'] === 'out') {
                $subscriberSpend += (int) $transaction['amount'];
            }

            if (in_array($transaction['type'], ['subscription_income', 'tip_income', 'instant_content_income', 'vip_live_income', 'darkroom_income'], true) && $transaction['direction'] === 'in') {
                $creatorIncome += (int) $transaction['amount'];
            }
        }

        $filteredTransactions = array_values(array_filter($decorated, static function (array $transaction) use ($query, $type, $status): bool {
            if ($type !== '' && ! str_contains((string) ($transaction['type'] ?? ''), $type)) {
                return false;
            }

            if ($status !== '' && (string) ($transaction['status'] ?? 'completed') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($transaction['note'] ?? '') . ' ' . ($transaction['user']['name'] ?? '') . ' ' . ($transaction['type'] ?? '')));

            return str_contains($haystack, $query);
        }));
        $pendingPayouts = array_values(array_filter(
            $decorated,
            static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout_request' && (string) ($transaction['status'] ?? 'pending') === 'pending'
        ));
        $pendingTopUps = array_values(array_filter(
            $decorated,
            static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'top_up_pending' && (string) ($transaction['status'] ?? 'pending') === 'pending'
        ));
        $payoutTransactions = array_values(array_filter(
            $decorated,
            static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout_request'
        ));
        $users = array_map(function (array $user): array {
            return $this->sanitizeUser($user) + [
                'wallet_balance' => $this->walletBalance((int) ($user['id'] ?? 0)),
            ];
        }, $this->users());
        usort($users, static fn (array $left, array $right): int => strnatcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? '')));

        return [
            'summary' => [
                'gross_volume' => $subscriberSpend,
                'creator_income' => $creatorIncome,
                'platform_result' => max(0, $subscriberSpend - $creatorIncome),
                'top_ups' => count(array_filter($transactions, static fn (array $transaction): bool => $transaction['type'] === 'top_up')),
                'pending_payout_tokens' => array_reduce($pendingPayouts, static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0), 0),
                'pending_payout_count' => count($pendingPayouts),
                'pending_top_up_count' => count($pendingTopUps),
            ],
            'transactions' => $decorated,
            'filtered_transactions' => $filteredTransactions,
            'pending_payouts' => $pendingPayouts,
            'payout_transactions' => $payoutTransactions,
            'pending_topups' => $pendingTopUps,
            'users' => $users,
            'luacoin_price_brl' => $luacoinPriceBrl,
            'filters' => [
                'q' => $query,
                'type' => $type,
                'status' => $status,
            ],
        ];
    }

    public function adminAdjustWalletBalance(int $adminId, int $userId, int $luacoins, string $direction, string $note = ''): bool
    {
        if (! $this->findUserById($adminId) || ! $this->findUserById($userId) || $luacoins <= 0) {
            return false;
        }

        $direction = $direction === 'debit' ? 'debit' : 'credit';
        if ($direction === 'debit' && $this->walletBalance($userId) < $luacoins) {
            return false;
        }

        $transactions = $this->walletTransactions();
        $transactions[] = [
            'id' => $this->store->nextId($transactions),
            'user_id' => $userId,
            'type' => $direction === 'credit' ? 'admin_credit' : 'admin_debit',
            'direction' => $direction === 'credit' ? 'in' : 'out',
            'amount' => $luacoins,
            'note' => trim($note) !== '' ? trim($note) : ($direction === 'credit' ? 'Credito manual do admin' : 'Debito manual do admin'),
            'admin_id' => $adminId,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->save('wallet_transactions', $transactions);
        $this->notifyUser(
            $userId,
            'wallet',
            $direction === 'credit' ? 'LuaCoins adicionadas' : 'LuaCoins ajustadas',
            $direction === 'credit'
                ? 'O admin adicionou ' . $luacoins . ' LuaCoins na sua carteira.'
                : 'O admin ajustou ' . $luacoins . ' LuaCoins da sua carteira.',
            match ((string) ($this->findUserById($userId)['role'] ?? 'subscriber')) {
                'creator' => '/creator/wallet',
                'subscriber' => '/subscriber/wallet',
                default => '/admin/finance',
            }
        );

        return true;
    }

    public function reviewTopUpRequest(int $transactionId, string $status, string $adminNote = ''): bool
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }

        $transactions = $this->walletTransactions();
        $changed = false;
        $userId = 0;
        $amount = 0;

        foreach ($transactions as &$transaction) {
            if ((int) ($transaction['id'] ?? 0) !== $transactionId || (string) ($transaction['type'] ?? '') !== 'top_up_pending') {
                continue;
            }

            $transaction['status'] = $status;
            $transaction['admin_note'] = trim($adminNote);
            $transaction['reviewed_at'] = date('Y-m-d H:i:s');
            if ($status === 'approved') {
                $transaction['type'] = 'top_up';
                $transaction['note'] = 'Recarga aprovada manualmente pelo admin';
                $transaction['approved_at'] = date('Y-m-d H:i:s');
            } else {
                $transaction['note'] = 'Recarga rejeitada manualmente pelo admin';
            }
            $userId = (int) ($transaction['user_id'] ?? 0);
            $amount = (int) ($transaction['amount'] ?? 0);
            $changed = true;
            break;
        }
        unset($transaction);

        if (! $changed) {
            return false;
        }

        $this->save('wallet_transactions', $transactions);
        $this->notifyUser(
            $userId,
            'wallet',
            $status === 'approved' ? 'Recarga aprovada' : 'Recarga rejeitada',
            $status === 'approved'
                ? 'Sua recarga manual de ' . $amount . ' LuaCoins foi aprovada.'
                : 'Sua recarga manual de ' . $amount . ' LuaCoins foi rejeitada.',
            '/subscriber/wallet'
        );

        return true;
    }

    public function adminOperationsData(array $filters = []): array
    {
        $creatorId = (int) ($filters['creator_id'] ?? 0);
        $contentQuery = mb_strtolower(trim((string) ($filters['content_q'] ?? $filters['q'] ?? '')));
        $contentStatus = trim((string) ($filters['content_status'] ?? ''));
        $contentPage = max(1, (int) ($filters['content_page'] ?? 1));
        $planQuery = mb_strtolower(trim((string) ($filters['plan_q'] ?? $filters['q'] ?? '')));
        $planStatus = trim((string) ($filters['plan_status'] ?? ''));
        $planPage = max(1, (int) ($filters['plan_page'] ?? 1));
        $liveQuery = mb_strtolower(trim((string) ($filters['live_q'] ?? $filters['q'] ?? '')));
        $liveStatus = trim((string) ($filters['live_status'] ?? ''));
        $livePage = max(1, (int) ($filters['live_page'] ?? 1));
        $microQuery = mb_strtolower(trim((string) ($filters['micro_q'] ?? $filters['q'] ?? '')));
        $microStatus = trim((string) ($filters['micro_status'] ?? ''));
        $microPage = max(1, (int) ($filters['micro_page'] ?? 1));

        $contents = array_values(array_filter($this->contentsWithCreators(), static function (array $item) use ($creatorId, $contentQuery, $contentStatus): bool {
            if ($creatorId > 0 && (int) ($item['creator_id'] ?? 0) !== $creatorId) {
                return false;
            }

            if ($contentStatus !== '' && (string) ($item['status'] ?? '') !== $contentStatus) {
                return false;
            }

            if ($contentQuery === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($item['title'] ?? '') . ' ' . ($item['excerpt'] ?? '') . ' ' . ($item['creator']['name'] ?? '')));

            return str_contains($haystack, $contentQuery);
        }));
        $contents = $this->sortByDate($contents, 'created_at');
        $contentPagination = $this->paginateItems($contents, $contentPage, 10);

        $plans = array_values(array_filter($this->plansWithCreators(), static function (array $plan) use ($creatorId, $planQuery, $planStatus): bool {
            if ($creatorId > 0 && (int) ($plan['creator_id'] ?? 0) !== $creatorId) {
                return false;
            }

            if ($planStatus === 'active' && ! (bool) ($plan['active'] ?? false)) {
                return false;
            }

            if ($planStatus === 'inactive' && (bool) ($plan['active'] ?? false)) {
                return false;
            }

            if ($planQuery === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($plan['name'] ?? '') . ' ' . ($plan['description'] ?? '') . ' ' . ($plan['creator']['name'] ?? '')));

            return str_contains($haystack, $planQuery);
        }));
        usort($plans, static fn (array $left, array $right): int => ((int) ($right['id'] ?? 0)) <=> ((int) ($left['id'] ?? 0)));
        $planPagination = $this->paginateItems($plans, $planPage, 10);

        $lives = array_values(array_filter($this->livesWithCreators(), static function (array $live) use ($creatorId, $liveQuery, $liveStatus): bool {
            if ($creatorId > 0 && (int) ($live['creator_id'] ?? 0) !== $creatorId) {
                return false;
            }

            if ($liveStatus !== '' && (string) ($live['status'] ?? '') !== $liveStatus) {
                return false;
            }

            if ($liveQuery === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($live['title'] ?? '') . ' ' . ($live['description'] ?? '') . ' ' . ($live['creator']['name'] ?? '')));

            return str_contains($haystack, $liveQuery);
        }));
        $lives = $this->sortByDate($lives, 'scheduled_for');
        $livePagination = $this->paginateItems($lives, $livePage, 10);

        $microcontents = $this->adminMicrocontentItems($microQuery, $creatorId);
        if ($microStatus !== '') {
            $microcontents = array_values(array_filter(
                $microcontents,
                static fn (array $item): bool => (string) ($item['unlock_status'] ?? '') === $microStatus
            ));
        }
        $microPagination = $this->paginateItems($microcontents, $microPage, 10);

        $users = array_filter($this->users(), static fn (array $user): bool => (string) ($user['role'] ?? '') === 'creator');
        $users = array_map(fn (array $user): array => $this->sanitizeUser($user), $users);
        usort($users, static fn (array $left, array $right): int => strnatcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? '')));

        return [
            'contents' => $contentPagination['items'],
            'content_pagination' => $contentPagination,
            'content_filters' => [
                'q' => $contentQuery,
                'status' => $contentStatus,
            ],
            'plans' => $planPagination['items'],
            'plan_pagination' => $planPagination,
            'plan_filters' => [
                'q' => $planQuery,
                'status' => $planStatus,
            ],
            'lives' => $livePagination['items'],
            'live_pagination' => $livePagination,
            'live_filters' => [
                'q' => $liveQuery,
                'status' => $liveStatus,
            ],
            'microcontents' => $microPagination['items'],
            'micro_pagination' => $microPagination,
            'micro_filters' => [
                'q' => $microQuery,
                'status' => $microStatus,
            ],
            'creators' => $users,
            'filters' => [
                'creator_id' => $creatorId,
            ],
            'summary' => [
                'content_count' => count($contents),
                'plan_count' => count($plans),
                'live_count' => count($lives),
                'microcontent_count' => count($microcontents),
                'live_now' => count(array_filter($lives, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'live')),
            ],
        ];
    }

    private function adminMicrocontentItems(string $query, int $creatorId): array
    {
        $items = [];

        foreach ($this->messages() as $message) {
            $attachment = $this->normalizeConversationAttachment(is_array($message['attachment'] ?? null) ? $message['attachment'] : null);
            $unlockPrice = max(0, (int) ($message['unlock_price'] ?? 0));

            if ($attachment === null || $unlockPrice <= 0) {
                continue;
            }

            $conversation = $this->findConversationById((int) ($message['conversation_id'] ?? 0));
            if (! is_array($conversation)) {
                continue;
            }

            $creator = $this->findCreatorBySlugOrId(null, (int) ($conversation['creator_id'] ?? 0));
            $subscriber = $this->findUserById((int) ($conversation['subscriber_id'] ?? 0));
            if (! is_array($creator) || ! is_array($subscriber)) {
                continue;
            }

            if ($creatorId > 0 && (int) ($creator['id'] ?? 0) !== $creatorId) {
                continue;
            }

            $unlock = $this->latestConversationMessageUnlock((int) ($message['id'] ?? 0));
            $unlockUser = is_array($unlock) ? $this->findUserById((int) ($unlock['user_id'] ?? 0)) : null;
            $body = trim((string) ($message['body'] ?? ''));
            $filename = trim((string) ($attachment['original_name'] ?? ''));

            if ($query !== '') {
                $haystack = mb_strtolower((string) (
                    $body . ' ' .
                    $filename . ' ' .
                    ($creator['name'] ?? '') . ' ' .
                    ($subscriber['name'] ?? '')
                ));

                if (! str_contains($haystack, $query)) {
                    continue;
                }
            }

            $items[] = [
                'id' => (int) ($message['id'] ?? 0),
                'conversation_id' => (int) ($conversation['id'] ?? 0),
                'body' => $body,
                'filename' => $filename,
                'attachment' => $attachment,
                'unlock_price' => $unlockPrice,
                'created_at' => (string) ($message['created_at'] ?? ''),
                'creator' => $creator,
                'subscriber' => $subscriber,
                'asset_href' => path_with_query('/messages/asset', ['scope' => 'message', 'id' => (int) ($message['id'] ?? 0)]),
                'unlock_status' => $unlock !== null ? 'unlocked' : 'pending',
                'unlock_label' => $unlock !== null ? 'Desbloqueado' : 'Aguardando desbloqueio',
                'unlock_at' => is_array($unlock) ? (string) ($unlock['created_at'] ?? '') : '',
                'unlock_user_name' => (string) ($unlockUser['name'] ?? ''),
            ];
        }

        return $this->sortByDate($items, 'created_at');
    }

    public function adminSaveContent(int $contentId, array $data): bool
    {
        if ($contentId <= 0) {
            return false;
        }

        $items = $this->contentItems();
        $changed = false;

        foreach ($items as &$item) {
            if ((int) ($item['id'] ?? 0) !== $contentId) {
                continue;
            }

            $item['title'] = trim((string) ($data['title'] ?? $item['title']));
            $item['excerpt'] = trim((string) ($data['excerpt'] ?? $item['excerpt']));
            $item['body'] = trim((string) ($data['body'] ?? $item['body']));
            $item['visibility'] = in_array(($data['visibility'] ?? $item['visibility']), ['public', 'subscriber', 'premium'], true) ? (string) ($data['visibility'] ?? $item['visibility']) : (string) $item['visibility'];
            $item['kind'] = in_array(($data['kind'] ?? $item['kind']), ['gallery', 'video', 'audio', 'article', 'live_teaser'], true) ? (string) ($data['kind'] ?? $item['kind']) : (string) $item['kind'];
            $item['status'] = in_array(($data['status'] ?? $item['status']), ['draft', 'pending', 'approved', 'rejected', 'archived'], true) ? (string) ($data['status'] ?? $item['status']) : (string) $item['status'];
            $item['category'] = $this->normalizeAudienceCategory((string) ($data['category'] ?? $item['category'] ?? 'todos'));
            $item['duration'] = trim((string) ($data['duration'] ?? $item['duration'] ?? ''));
            $item['media_url'] = trim((string) ($data['media_url'] ?? $item['media_url'] ?? ''));
            $item['thumbnail_url'] = trim((string) ($data['thumbnail_url'] ?? $item['thumbnail_url'] ?? ''));
            $item['updated_at'] = date('Y-m-d H:i:s');
            $changed = true;
            break;
        }
        unset($item);

        if (! $changed) {
            return false;
        }

        $this->save('content_items', $items);

        return true;
    }

    public function adminDeleteContent(int $contentId): bool
    {
        $items = $this->contentItems();
        $before = count($items);
        $items = array_values(array_filter($items, static fn (array $item): bool => (int) ($item['id'] ?? 0) !== $contentId));

        if (count($items) === $before) {
            return false;
        }

        $this->save('content_items', $items);
        $savedItems = array_values(array_filter($this->savedItems(), static fn (array $saved): bool => (int) ($saved['content_id'] ?? 0) !== $contentId));
        $this->save('saved_items', $savedItems);

        return true;
    }

    public function adminSavePlan(int $planId, array $data): bool
    {
        if ($planId <= 0) {
            return false;
        }

        $plans = $this->plans();
        $changed = false;
        $perks = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) ($data['perks'] ?? '')) ?: [])));

        foreach ($plans as &$plan) {
            if ((int) ($plan['id'] ?? 0) !== $planId) {
                continue;
            }

            $plan['name'] = trim((string) ($data['name'] ?? $plan['name']));
            $plan['description'] = trim((string) ($data['description'] ?? $plan['description']));
            $plan['price_tokens'] = max(1, (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? $plan['price_tokens']));
            $plan['active'] = ($data['active'] ?? ($plan['active'] ? '1' : '0')) === '1';
            $plan['label'] = trim((string) ($data['label'] ?? ($plan['label'] ?? '')));
            if ($perks !== []) {
                $plan['perks'] = $perks;
            }
            $changed = true;
            break;
        }
        unset($plan);

        if (! $changed) {
            return false;
        }

        $this->save('plans', $plans);

        return true;
    }

    public function adminDeletePlan(int $planId): array
    {
        $plans = $this->plans();
        $hasActiveSubscribers = false;

        foreach ($this->subscriptions() as $subscription) {
            if ((int) ($subscription['plan_id'] ?? 0) === $planId && (string) ($subscription['status'] ?? '') === 'active') {
                $hasActiveSubscribers = true;
                break;
            }
        }

        foreach ($plans as $index => $plan) {
            if ((int) ($plan['id'] ?? 0) !== $planId) {
                continue;
            }

            if ($hasActiveSubscribers) {
                $plans[$index]['active'] = false;
                $this->save('plans', $plans);

                return ['ok' => true, 'message' => 'Plano desativado porque ainda possui assinantes ativos.'];
            }

            unset($plans[$index]);
            $this->save('plans', array_values($plans));

            return ['ok' => true, 'message' => 'Plano removido com sucesso.'];
        }

        return ['ok' => false, 'message' => 'Nao foi possivel remover o plano informado.'];
    }

    public function adminSaveLive(int $liveId, array $data): bool
    {
        if ($liveId <= 0) {
            return false;
        }

        $lives = $this->liveSessions();
        $changed = false;

        foreach ($lives as &$live) {
            if ((int) ($live['id'] ?? 0) !== $liveId) {
                continue;
            }

            $live['title'] = trim((string) ($data['title'] ?? $live['title']));
            $live['description'] = trim((string) ($data['description'] ?? $live['description']));
            $live['status'] = in_array(($data['status'] ?? $live['status']), ['scheduled', 'live', 'ended'], true) ? (string) ($data['status'] ?? $live['status']) : (string) $live['status'];
            $live['scheduled_for'] = trim((string) ($data['scheduled_for'] ?? $live['scheduled_for']));
            $live['category'] = $this->normalizeAudienceCategory((string) ($data['category'] ?? $live['category'] ?? 'todos'));
            $live['access_mode'] = in_array(($data['access_mode'] ?? $live['access_mode']), ['public', 'subscriber', 'vip'], true) ? (string) ($data['access_mode'] ?? $live['access_mode']) : (string) $live['access_mode'];
            $live['price_tokens'] = max((string) ($live['access_mode'] ?? 'public') === 'vip' ? 1 : 0, (int) ($data['price_luacoins'] ?? $data['price_tokens'] ?? $live['price_tokens'] ?? 0));
            $live['darkroom_price_tokens'] = max(0, (int) ($data['darkroom_price_luacoins'] ?? $data['darkroom_price_tokens'] ?? $live['darkroom_price_tokens'] ?? 0));
            $live['darkroom_duration_minutes'] = $this->sanitizeDarkroomDurationMinutes((int) ($data['darkroom_duration_minutes'] ?? $live['darkroom_duration_minutes'] ?? 0));
            $live['goal_tokens'] = max(0, (int) ($data['goal_luacoins'] ?? $data['goal_tokens'] ?? $live['goal_tokens'] ?? 0));
            $live['viewer_count'] = max(0, (int) ($data['viewer_count'] ?? $live['viewer_count'] ?? 0));
            $live['chat_enabled'] = ($data['chat_enabled'] ?? ($live['chat_enabled'] ? '1' : '0')) === '1';
            $live['recording_enabled'] = false;
            $live['cover_url'] = trim((string) ($data['cover_url'] ?? $live['cover_url'] ?? ''));
            $live['pinned_notice'] = trim((string) ($data['pinned_notice'] ?? $live['pinned_notice'] ?? ''));
            $changed = true;
            break;
        }
        unset($live);

        if (! $changed) {
            return false;
        }

        $this->save('live_sessions', $lives);

        return true;
    }

    public function adminDeleteLive(int $liveId): bool
    {
        $lives = $this->liveSessions();
        $before = count($lives);
        $lives = array_values(array_filter($lives, static fn (array $live): bool => (int) ($live['id'] ?? 0) !== $liveId));

        if (count($lives) === $before) {
            return false;
        }

        $this->save('live_sessions', $lives);
        $messages = array_values(array_filter($this->liveMessages(), static fn (array $message): bool => (int) ($message['live_id'] ?? 0) !== $liveId));
        $this->save('live_messages', $messages);

        return true;
    }

    public function reviewPayoutRequest(int $transactionId, string $status, string $adminNote = ''): bool
    {
        if (! in_array($status, ['processing', 'paid', 'rejected'], true)) {
            return false;
        }

        $transactions = $this->walletTransactions();
        $changed = false;
        $target = null;

        foreach ($transactions as &$transaction) {
            if ((int) ($transaction['id'] ?? 0) !== $transactionId || (string) ($transaction['type'] ?? '') !== 'payout_request') {
                continue;
            }

            $target = $transaction;
            $transaction['status'] = $status;
            $transaction['admin_note'] = trim($adminNote);
            $transaction['reviewed_at'] = date('Y-m-d H:i:s');
            $changed = true;
            break;
        }
        unset($transaction);

        if (! $changed || ! is_array($target)) {
            return false;
        }

        if ($status === 'rejected') {
            $alreadyRefunded = array_values(array_filter(
                $transactions,
                static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'payout_reversal'
                    && (int) ($transaction['related_transaction_id'] ?? 0) === $transactionId
            ));

            if ($alreadyRefunded === []) {
                $transactions[] = [
                    'id' => $this->store->nextId($transactions),
                    'user_id' => (int) ($target['user_id'] ?? 0),
                    'type' => 'payout_reversal',
                    'direction' => 'in',
                    'amount' => (int) ($target['amount'] ?? 0),
                    'note' => 'Estorno automatico de saque rejeitado',
                    'related_transaction_id' => $transactionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'status' => 'completed',
                ];
            }
        }

        $this->save('wallet_transactions', $transactions);
        $title = match ($status) {
            'paid' => 'Saque pago',
            'rejected' => 'Saque rejeitado',
            default => 'Saque em analise',
        };
        $body = match ($status) {
            'paid' => 'Seu saque de ' . (int) ($target['amount'] ?? 0) . ' LuaCoins foi marcado como pago.',
            'rejected' => 'Seu saque de ' . (int) ($target['amount'] ?? 0) . ' LuaCoins foi rejeitado pelo admin.',
            default => 'Seu saque de ' . (int) ($target['amount'] ?? 0) . ' LuaCoins esta em processamento.',
        };
        $this->notifyUser((int) ($target['user_id'] ?? 0), 'payout', $title, $body, '/creator/wallet');

        return true;
    }

    public function updateSettings(array $data): array
    {
        $settings = $this->settings();
        $previousBannerCountdownEnabled = (bool) ($settings['home_banner_countdown_enabled'] ?? true);
        $previousBannerCountdownSeconds = max(0, (int) ($settings['home_banner_countdown_seconds'] ?? 172800));
        $previousBannerCountdownTargetAt = trim((string) ($settings['home_banner_countdown_target_at'] ?? ''));
        $previousBannerCountdownTargetTimestamp = $previousBannerCountdownTargetAt !== '' ? strtotime($previousBannerCountdownTargetAt) : false;
        $settings['platform_fee_percent'] = max(0, min(95, (int) ($data['platform_fee_percent'] ?? $settings['platform_fee_percent'])));
        $settings['luacoin_price_brl'] = max(0.01, (float) ($data['luacoin_price_brl'] ?? $data['token_price_brl'] ?? $settings['luacoin_price_brl']));
        $settings['deposit_min_luacoins'] = max(1, (int) ($data['deposit_min_luacoins'] ?? $data['deposit_min_tokens'] ?? $settings['deposit_min_luacoins']));
        $settings['withdraw_min_luacoins'] = max(1, (int) ($data['withdraw_min_luacoins'] ?? $data['withdraw_min_tokens'] ?? $settings['withdraw_min_luacoins']));
        $settings['withdraw_max_luacoins'] = max($settings['withdraw_min_luacoins'], (int) ($data['withdraw_max_luacoins'] ?? $data['withdraw_max_tokens'] ?? $settings['withdraw_max_luacoins']));
        $settings['live_replay_expiration_days'] = max(1, (int) ($data['live_replay_expiration_days'] ?? $settings['live_replay_expiration_days'] ?? 7));
        $settings['live_max_duration_minutes'] = max(5, (int) ($data['live_max_duration_minutes'] ?? $settings['live_max_duration_minutes'] ?? 30));
        $settings['creator_content_storage_limit_mb'] = max(1, (int) ($data['creator_content_storage_limit_mb'] ?? $settings['creator_content_storage_limit_mb'] ?? 50));
        $settings['subscriber_signup_bonus_enabled'] = ($data['subscriber_signup_bonus_enabled'] ?? '0') === '1';
        $settings['subscriber_signup_bonus_luacoins'] = max(0, (int) ($data['subscriber_signup_bonus_luacoins'] ?? $settings['subscriber_signup_bonus_luacoins'] ?? 10));
        $settings['topup_bonus_percent'] = max(0, min(100, (int) ($data['topup_bonus_percent'] ?? $settings['topup_bonus_percent'] ?? 10)));
        $settings['maintenance_mode'] = ($data['maintenance_mode'] ?? '0') === '1';
        $settings['slow_mode_seconds'] = max(0, (int) ($data['slow_mode_seconds'] ?? $settings['slow_mode_seconds']));
        $settings['auto_moderation'] = ($data['auto_moderation'] ?? '0') === '1';
        $settings['blur_sensitive_thumbs'] = ($data['blur_sensitive_thumbs'] ?? '0') === '1';
        $settings['live_chat_enabled'] = ($data['live_chat_enabled'] ?? '0') === '1';
        $settings['live_priority_alert_duration_ms'] = max(2000, (int) ($data['live_priority_alert_duration_ms'] ?? $settings['live_priority_alert_duration_ms'] ?? 8000));
        $settings['announcement'] = trim((string) ($data['announcement'] ?? $settings['announcement']));
        $settings['site_base_url'] = rtrim(trim((string) ($data['site_base_url'] ?? $settings['site_base_url'] ?? '')), '/');
        $settings['seo_site_title'] = trim((string) ($data['seo_site_title'] ?? $settings['seo_site_title'] ?? 'SexyLua'));
        $settings['seo_meta_title'] = trim((string) ($data['seo_meta_title'] ?? $settings['seo_meta_title'] ?? 'SexyLua'));
        $settings['seo_meta_description'] = trim((string) ($data['seo_meta_description'] ?? $settings['seo_meta_description'] ?? 'Plataforma de assinaturas, chats privados, lives e monetizacao com LuaCoins.'));
        $settings['seo_logo_white_url'] = trim((string) ($data['seo_logo_white_url'] ?? $settings['seo_logo_white_url'] ?? ''));
        $settings['seo_logo_color_url'] = trim((string) ($data['seo_logo_color_url'] ?? $settings['seo_logo_color_url'] ?? ''));
        $settings['home_banner_enabled'] = ($data['home_banner_enabled'] ?? '0') === '1';
        $settings['home_banner_title'] = trim((string) ($data['home_banner_title'] ?? $settings['home_banner_title'] ?? ''));
        $settings['home_banner_subtitle'] = trim((string) ($data['home_banner_subtitle'] ?? $settings['home_banner_subtitle'] ?? ''));
        $settings['home_banner_primary_text'] = trim((string) ($data['home_banner_primary_text'] ?? $settings['home_banner_primary_text'] ?? ''));
        $settings['home_banner_primary_link'] = trim((string) ($data['home_banner_primary_link'] ?? $settings['home_banner_primary_link'] ?? ''));
        $settings['home_banner_secondary_text'] = trim((string) ($data['home_banner_secondary_text'] ?? $settings['home_banner_secondary_text'] ?? ''));
        $settings['home_banner_secondary_link'] = trim((string) ($data['home_banner_secondary_link'] ?? $settings['home_banner_secondary_link'] ?? ''));
        $settings['home_banner_countdown_enabled'] = ($data['home_banner_countdown_enabled'] ?? '0') === '1';
        $settings['home_banner_countdown_seconds'] = max(0, (int) ($data['home_banner_countdown_seconds'] ?? $settings['home_banner_countdown_seconds'] ?? 172800));
        $shouldResetHomeBannerCountdown = $settings['home_banner_countdown_enabled']
            && (
                ! $previousBannerCountdownEnabled
                || $settings['home_banner_countdown_seconds'] !== $previousBannerCountdownSeconds
                || $previousBannerCountdownTargetAt === ''
                || $previousBannerCountdownTargetTimestamp === false
            );
        if ($settings['home_banner_countdown_enabled']) {
            $settings['home_banner_countdown_target_at'] = $shouldResetHomeBannerCountdown
                ? date('c', time() + $settings['home_banner_countdown_seconds'])
                : $previousBannerCountdownTargetAt;
        } else {
            $settings['home_banner_countdown_target_at'] = '';
        }
        $settings['home_banner_background_url'] = trim((string) ($data['home_banner_background_url'] ?? $settings['home_banner_background_url'] ?? ''));
        $settings['syncpay_api_base_url'] = rtrim(trim((string) ($data['syncpay_api_base_url'] ?? $settings['syncpay_api_base_url'] ?? 'https://api.syncpayments.com.br')), '/');
        $settings['syncpay_client_id'] = trim((string) ($data['syncpay_client_id'] ?? $settings['syncpay_client_id'] ?? ''));
        $settings['syncpay_client_secret'] = trim((string) ($data['syncpay_client_secret'] ?? $settings['syncpay_client_secret'] ?? ''));
        $settings['syncpay_api_key'] = trim((string) ($data['syncpay_api_key'] ?? $settings['syncpay_api_key'] ?? ''));
        $settings['syncpay_webhook_token'] = trim((string) ($data['syncpay_webhook_token'] ?? $settings['syncpay_webhook_token'] ?? ''));
        $settings['syncpay_pix_expires_in_days'] = max(1, (int) ($data['syncpay_pix_expires_in_days'] ?? $settings['syncpay_pix_expires_in_days'] ?? 2));
        $settings['syncpay_webhook_url'] = webhook_url($this->config, $settings, '/webhook/syncpay');
        $settings['token_price_brl'] = $settings['luacoin_price_brl'];
        $settings['deposit_min_tokens'] = $settings['deposit_min_luacoins'];
        $settings['withdraw_min_tokens'] = $settings['withdraw_min_luacoins'];
        $settings['withdraw_max_tokens'] = $settings['withdraw_max_luacoins'];

        $this->save('settings', $settings);

        return $settings;
    }

    private function emailInUse(string $email, int $exceptUserId = 0): bool
    {
        foreach ($this->users() as $user) {
            if ((int) ($user['id'] ?? 0) === $exceptUserId) {
                continue;
            }

            if (mb_strtolower((string) ($user['email'] ?? '')) === mb_strtolower($email)) {
                return true;
            }
        }

        return false;
    }

    private function users(): array
    {
        return $this->readCollection('users');
    }

    private function creatorProfiles(): array
    {
        return $this->readCollection('creator_profiles');
    }

    private function contentItems(): array
    {
        return $this->readCollection('content_items');
    }

    private function plans(): array
    {
        return $this->readCollection('plans');
    }

    private function subscriptions(): array
    {
        return $this->readCollection('subscriptions');
    }

    private function liveSessions(): array
    {
        return $this->readCollection('live_sessions');
    }

    private function favorites(): array
    {
        return $this->readCollection('favorites');
    }

    private function savedItems(): array
    {
        return $this->readCollection('saved_items');
    }

    private function conversations(): array
    {
        return $this->readCollection('conversations');
    }

    private function messages(): array
    {
        return $this->readCollection('messages');
    }

    private function messageUnlocks(): array
    {
        return $this->readCollection('message_unlocks');
    }

    private function liveUnlocks(): array
    {
        return $this->readCollection('live_unlocks');
    }

    private function liveDarkrooms(): array
    {
        return $this->readCollection('live_darkrooms');
    }

    private function notifications(): array
    {
        return $this->readCollection('notifications');
    }

    private function announcements(): array
    {
        return $this->readCollection('announcements');
    }

    private function liveMessages(): array
    {
        return $this->readCollection('live_messages');
    }

    private function liveSignals(): array
    {
        return $this->readCollection('live_signals');
    }

    private function livePresence(): array
    {
        return $this->readCollection('live_presence');
    }

    private function liveStreams(): array
    {
        return $this->readCollection('live_streams');
    }

    private function walletTransactions(): array
    {
        return $this->readCollection('wallet_transactions');
    }

    public function settings(): array
    {
        return $this->normalizeSettings($this->readCollection('settings'));
    }

    private function save(string $collection, array $payload): void
    {
        $this->store->write($collection, $payload);
        $this->collectionCache[$collection] = $payload;
    }

    private function readCollection(string $collection, array $fallback = []): array
    {
        if (! array_key_exists($collection, $this->collectionCache)) {
            $this->collectionCache[$collection] = $this->store->read($collection, $fallback);
        }

        $payload = $this->collectionCache[$collection];

        return is_array($payload) ? $payload : $fallback;
    }

    private function creators(): array
    {
        $creators = [];

        foreach ($this->users() as $user) {
            if (($user['role'] ?? null) !== 'creator') {
                continue;
            }

            $creators[] = $this->decorateCreator($user);
        }

        return $creators;
    }

    private function contentsWithCreators(): array
    {
        return array_map(fn (array $item): array => $this->decorateContent($item), $this->contentItems());
    }

    private function plansWithCreators(): array
    {
        return array_map(fn (array $plan): array => $this->decoratePlan($plan), $this->plans());
    }

    private function livesWithCreators(): array
    {
        return array_map(fn (array $live): array => $this->decorateLive($live), $this->liveSessions());
    }

    private function decorateCreator(array $user): array
    {
        $profile = $this->findCreatorProfile((int) $user['id']) ?? [];
        $sanitizedUser = $this->sanitizeUser($user);
        $contentCount = 0;
        $subscriberCount = 0;

        foreach ($this->contentItems() as $item) {
            if ((int) $item['creator_id'] === (int) $user['id'] && $item['status'] === 'approved' && ! $this->contentIsExpired($item)) {
                $contentCount++;
            }
        }

        foreach ($this->subscriptions() as $subscription) {
            if ((int) $subscription['creator_id'] === (int) $user['id'] && $subscription['status'] === 'active') {
                $subscriberCount++;
            }
        }

        return array_merge($profile, $sanitizedUser, [
            'id' => (int) ($user['id'] ?? 0),
            'profile_id' => (int) ($profile['id'] ?? 0),
            'user_id' => (int) ($user['id'] ?? 0),
            'subscriber_count' => $subscriberCount,
            'content_count' => $contentCount,
            'wallet_balance' => $this->walletBalance((int) $user['id']),
        ]);
    }

    private function decorateContent(array $item): array
    {
        $plan = null;
        if ((int) ($item['plan_id'] ?? 0) > 0) {
            $rawPlan = $this->findPlanById((int) $item['plan_id']);
            if ($rawPlan !== null) {
                $plan = $this->decoratePlan($rawPlan);
            }
        }

        return $item + [
            'category' => $this->normalizeAudienceCategory((string) ($item['category'] ?? 'todos')),
            'category_label' => $this->audienceCategoryLabel((string) ($item['category'] ?? 'todos')),
            'creator' => $this->findCreatorBySlugOrId(null, (int) $item['creator_id']),
            'plan' => $plan,
            'is_expired' => $this->contentIsExpired($item),
        ];
    }

    private function decoratePlan(array $plan): array
    {
        $subscriberCount = 0;

        foreach ($this->subscriptions() as $subscription) {
            if ((int) $subscription['plan_id'] === (int) $plan['id'] && $subscription['status'] === 'active') {
                $subscriberCount++;
            }
        }

        return $plan + [
            'creator' => $this->findCreatorBySlugOrId(null, (int) $plan['creator_id']),
            'subscriber_count' => $subscriberCount,
        ];
    }

    private function decorateLive(array $live): array
    {
        $creatorId = (int) ($live['creator_id'] ?? 0);
        $creatorProfile = $this->findCreatorProfile($creatorId) ?? [];
        $resolvedStatus = $this->resolveLiveStatus($live);

        return array_merge($live, [
            'category' => $this->normalizeAudienceCategory((string) ($live['category'] ?? 'todos')),
            'category_label' => $this->audienceCategoryLabel((string) ($live['category'] ?? 'todos')),
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'base_status' => (string) ($live['status'] ?? 'scheduled'),
            'status' => $resolvedStatus,
            'status_bucket' => $this->liveStatusBucket($resolvedStatus),
            'chat_audience' => $this->sanitizeLiveChatAudience((string) ($live['chat_audience'] ?? ($creatorProfile['live_chat_audience_default'] ?? 'all'))),
            'replay_visibility' => $this->sanitizeReplayVisibility((string) ($live['replay_visibility'] ?? ($creatorProfile['replay_visibility_default'] ?? 'subscriber'))),
            'priority_tip_tiers' => $this->priorityTipTiersForProfile($creatorProfile),
            'priority_tip_custom' => max(1, (int) ($creatorProfile['priority_tip_custom'] ?? 150)),
            'max_live_duration_minutes' => max(5, (int) ($live['max_live_duration_minutes'] ?? ($this->settings()['live_max_duration_minutes'] ?? 30))),
            'replay_expiration_days' => max(1, (int) ($live['replay_expiration_days'] ?? ($this->settings()['live_replay_expiration_days'] ?? 7))),
        ]);
    }

    private function findCreatorProfile(int $creatorId): ?array
    {
        foreach ($this->creatorProfiles() as $profile) {
            if ((int) $profile['user_id'] === $creatorId) {
                return $profile;
            }
        }

        return null;
    }

    private function findContentWithCreator(int $contentId): ?array
    {
        foreach ($this->contentsWithCreators() as $item) {
            if ((int) $item['id'] === $contentId) {
                return $item;
            }
        }

        return null;
    }

    private function findPlanById(int $planId): ?array
    {
        foreach ($this->plans() as $plan) {
            if ((int) $plan['id'] === $planId) {
                return $plan;
            }
        }

        return null;
    }

    private function findLiveById(int $liveId): ?array
    {
        foreach ($this->liveSessions() as $live) {
            if ((int) $live['id'] === $liveId) {
                return $live;
            }
        }

        return null;
    }

    private function findConversationById(int $conversationId): ?array
    {
        foreach ($this->conversations() as $conversation) {
            if ((int) $conversation['id'] === $conversationId) {
                return $conversation;
            }
        }

        return null;
    }

    private function findConversationByPair(int $subscriberId, int $creatorId): ?array
    {
        foreach ($this->conversations() as $conversation) {
            if ((int) ($conversation['subscriber_id'] ?? 0) === $subscriberId && (int) ($conversation['creator_id'] ?? 0) === $creatorId) {
                return $conversation;
            }
        }

        return null;
    }

    private function contentIsExpired(array $item): bool
    {
        $expiresAt = trim((string) ($item['expires_at'] ?? ''));
        if ($expiresAt === '') {
            return false;
        }

        $timestamp = strtotime($expiresAt);

        return $timestamp !== false && $timestamp <= time();
    }

    private function activeSubscriptionsForSubscriber(int $subscriberId): array
    {
        $rows = array_values(array_filter($this->subscriptions(), static fn (array $subscription): bool => (int) $subscription['subscriber_id'] === $subscriberId && $subscription['status'] === 'active'));

        return array_map(function (array $subscription): array {
            $plan = $this->findPlanById((int) $subscription['plan_id']);
            $creator = $this->findCreatorBySlugOrId(null, (int) $subscription['creator_id']);

            return $subscription + ['plan' => $plan, 'creator' => $creator];
        }, $rows);
    }

    private function activeSubscriptionFor(int $subscriberId, int $creatorId): ?array
    {
        foreach ($this->subscriptions() as $subscription) {
            if ((int) $subscription['subscriber_id'] === $subscriberId && (int) $subscription['creator_id'] === $creatorId && $subscription['status'] === 'active') {
                return $subscription;
            }
        }

        return null;
    }

    private function walletTransactionsFor(int $userId): array
    {
        $transactions = array_values(array_filter($this->walletTransactions(), static fn (array $transaction): bool => (int) $transaction['user_id'] === $userId));
        $transactions = $this->sortByDate($transactions, 'created_at');

        return array_map(function (array $transaction): array {
            $transaction['counterparty'] = isset($transaction['creator_id']) ? $this->findCreatorBySlugOrId(null, (int) $transaction['creator_id']) : null;

            return $transaction;
        }, $transactions);
    }

    private function walletBalance(int $userId): int
    {
        $balance = 0;

        foreach ($this->walletTransactions() as $transaction) {
            if ((int) $transaction['user_id'] !== $userId) {
                continue;
            }

            if (! $this->transactionCountsForBalance($transaction)) {
                continue;
            }

            $balance += $transaction['direction'] === 'in' ? (int) $transaction['amount'] : -1 * (int) $transaction['amount'];
        }

        return $balance;
    }

    private function hasWalletTransactionByRelatedId(array $transactions, string $type, int $relatedTransactionId): bool
    {
        foreach ($transactions as $transaction) {
            if (
                (string) ($transaction['type'] ?? '') === $type
                && (int) ($transaction['related_transaction_id'] ?? 0) === $relatedTransactionId
            ) {
                return true;
            }
        }

        return false;
    }

    private function usernameInUse(string $username, int $exceptUserId = 0): bool
    {
        $username = $this->normalizeUsername($username);

        if ($username === '') {
            return false;
        }

        foreach ($this->users() as $user) {
            if ((int) ($user['id'] ?? 0) === $exceptUserId) {
                continue;
            }

            if ($this->normalizeUsername((string) ($user['username'] ?? '')) === $username) {
                return true;
            }
        }

        return false;
    }

    private function isFavoriteCreator(int $subscriberId, int $creatorId): bool
    {
        foreach ($this->favorites() as $favorite) {
            if ((int) $favorite['user_id'] === $subscriberId && (int) $favorite['creator_id'] === $creatorId) {
                return true;
            }
        }

        return false;
    }

    private function liveChatMessagesFor(int $liveId, int $limit = 20): array
    {
        $live = $this->findLiveById($liveId);
        $messages = array_values(array_filter(
            $this->liveMessages(),
            static fn (array $message): bool => (int) ($message['live_id'] ?? 0) === $liveId
        ));
        $messages = $this->sortByDate($messages, 'created_at');
        $messages = array_map(function (array $message): array {
            $message['sender'] = $this->findUserById((int) ($message['sender_id'] ?? 0));

            return $message;
        }, $messages);
        if ($live !== null) {
            $messages = array_map(fn (array $message): array => $this->decorateLiveMessage($message, $live), $messages);
        }
        $messages = array_slice($messages, 0, max(1, $limit));

        return array_reverse($messages);
    }

    private function liveTipTransactionsFor(array $live): array
    {
        $creatorId = (int) ($live['creator_id'] ?? 0);
        $liveId = (int) ($live['id'] ?? 0);

        if ($creatorId <= 0) {
            return [];
        }

        $transactions = array_values(array_filter(
            $this->walletTransactions(),
            static function (array $transaction) use ($creatorId, $liveId): bool {
                if ((string) ($transaction['type'] ?? '') !== 'tip' || (int) ($transaction['creator_id'] ?? 0) !== $creatorId) {
                    return false;
                }

                $transactionLiveId = (int) ($transaction['live_id'] ?? 0);

                return $liveId <= 0 || $transactionLiveId === 0 || $transactionLiveId === $liveId;
            }
        ));
        $transactions = $this->sortByDate($transactions, 'created_at');

        return array_map(function (array $transaction): array {
            $transaction['sender'] = $this->findUserById((int) ($transaction['user_id'] ?? 0));

            return $transaction;
        }, $transactions);
    }

    private function liveEngagementData(array $live, int $tipLimit = 5, int $supporterLimit = 3): array
    {
        $tipTransactions = $this->liveTipTransactionsFor($live);
        $supporters = [];
        $tipTotalAmount = 0;

        foreach ($tipTransactions as $transaction) {
            $tipTotalAmount += (int) ($transaction['amount'] ?? 0);
            $senderId = (int) ($transaction['sender']['id'] ?? 0);

            if ($senderId === 0) {
                continue;
            }

            if (! isset($supporters[$senderId])) {
                $supporters[$senderId] = [
                    'user' => $transaction['sender'],
                    'amount' => 0,
                ];
            }

            $supporters[$senderId]['amount'] += (int) ($transaction['amount'] ?? 0);
        }

        $supporters = array_values($supporters);
        usort($supporters, static fn (array $left, array $right): int => ((int) ($right['amount'] ?? 0)) <=> ((int) ($left['amount'] ?? 0)));

        return [
            'recent_tips' => array_slice($tipTransactions, 0, max(1, $tipLimit)),
            'top_supporters' => array_slice($supporters, 0, max(1, $supporterLimit)),
            'tip_total_amount' => $tipTotalAmount,
        ];
    }

    private function sortCreatorLives(array $lives, string $bucket = 'scheduled'): array
    {
        usort($lives, function (array $left, array $right) use ($bucket): int {
            $leftDate = (string) ($left['scheduled_for'] ?? '');
            $rightDate = (string) ($right['scheduled_for'] ?? '');

            if ($bucket === 'ended') {
                $leftDate = (string) ($left['ended_at'] ?? $leftDate);
                $rightDate = (string) ($right['ended_at'] ?? $rightDate);

                return strcmp($rightDate, $leftDate);
            }

            if ($bucket === 'expired') {
                return strcmp($rightDate, $leftDate);
            }

            $leftIsLive = (string) ($left['status'] ?? '') === 'live' ? 1 : 0;
            $rightIsLive = (string) ($right['status'] ?? '') === 'live' ? 1 : 0;
            if ($leftIsLive !== $rightIsLive) {
                return $rightIsLive <=> $leftIsLive;
            }

            return strcmp($leftDate, $rightDate);
        });

        return $lives;
    }

    private function normalizeLiveDateTime(string $value, string $fallback = '+1 day'): string
    {
        $value = trim(str_replace('T', ' ', $value));
        if ($value !== '') {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }

        return $fallback === 'now'
            ? date('Y-m-d H:i:s')
            : date('Y-m-d H:i:s', strtotime($fallback));
    }

    private function resolveLiveStatus(array $live): string
    {
        $status = (string) ($live['status'] ?? 'scheduled');
        if ($status === 'live' || $status === 'ended') {
            return $status;
        }

        $scheduledFor = strtotime((string) ($live['scheduled_for'] ?? ''));
        $wasLive = (bool) ($live['was_live'] ?? false)
            || trim((string) ($live['started_at'] ?? '')) !== ''
            || trim((string) ($live['ended_at'] ?? '')) !== '';

        if (! $wasLive && $scheduledFor !== false && $scheduledFor < time()) {
            return 'expired';
        }

        return 'scheduled';
    }

    private function liveStatusBucket(string $status): string
    {
        return match ($status) {
            'ended' => 'ended',
            'expired' => 'expired',
            default => 'scheduled',
        };
    }

    private function sanitizeLiveChatAudience(string $value): string
    {
        return in_array($value, ['all', 'subscriber', 'off'], true) ? $value : 'all';
    }

    private function sanitizeReplayVisibility(string $value): string
    {
        return in_array($value, ['public', 'subscriber'], true) ? $value : 'subscriber';
    }

    private function normalizeAudienceCategory(string $value): string
    {
        return \normalize_audience_category($value);
    }

    private function audienceCategoryLabel(string $value): string
    {
        return \audience_category_label($value);
    }

    private function matchesAudienceCategory(string $selected, string $itemCategory): bool
    {
        return \audience_category_matches_selection($selected, $itemCategory);
    }

    private function normalizePriorityTipTiers(array $tiers): array
    {
        $normalized = array_values(array_filter(array_map(static fn (mixed $tier): int => max(1, (int) $tier), $tiers)));
        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized !== [] ? $normalized : [1, 10, 25, 50, 100, 150];
    }

    private function normalizePriorityTipMessages(array $messages): array
    {
        $normalized = [];

        foreach ($messages as $tier => $message) {
            $tierValue = max(1, (int) $tier);
            $text = trim((string) $message);
            if ($text === '') {
                continue;
            }

            $normalized[(string) $tierValue] = $text;
        }

        ksort($normalized, SORT_NUMERIC);

        return $normalized;
    }

    private function priorityTipTiersForProfile(array $profile): array
    {
        $tiers = $profile['priority_tip_tiers'] ?? null;
        if (is_array($tiers)) {
            return $this->normalizePriorityTipTiers($tiers);
        }

        return [1, 10, 25, 50, 100, max(1, (int) ($profile['priority_tip_custom'] ?? 150))];
    }

    private function priorityTipTiersForCreator(int $creatorId): array
    {
        return $this->priorityTipTiersForProfile($this->findCreatorProfile($creatorId) ?? []);
    }

    private function priorityTipMessagesForProfile(array $profile): array
    {
        $messages = $profile['priority_tip_messages'] ?? [];

        return is_array($messages) ? $this->normalizePriorityTipMessages($messages) : [];
    }

    private function priorityTipMessagesForCreator(int $creatorId): array
    {
        return $this->priorityTipMessagesForProfile($this->findCreatorProfile($creatorId) ?? []);
    }

    private function priorityTipTiersForLive(array $live): array
    {
        if (isset($live['priority_tip_tiers']) && is_array($live['priority_tip_tiers'])) {
            return $this->normalizePriorityTipTiers($live['priority_tip_tiers']);
        }

        return $this->priorityTipTiersForCreator((int) ($live['creator_id'] ?? 0));
    }

    private function priorityTipMessagesForLive(array $live): array
    {
        if (isset($live['priority_tip_messages']) && is_array($live['priority_tip_messages'])) {
            return $this->normalizePriorityTipMessages($live['priority_tip_messages']);
        }

        return $this->priorityTipMessagesForCreator((int) ($live['creator_id'] ?? 0));
    }

    private function priorityTipAlertTextForLive(array $live, int $tier, int $amount, int $subscriberId, ?string $customMessage = null): string
    {
        $customMessage = trim((string) $customMessage);
        if ($customMessage !== '') {
            return $customMessage;
        }

        $sender = $this->findUserById($subscriberId);
        $senderName = trim((string) ($sender['name'] ?? 'Um assinante'));
        $template = trim((string) ($this->priorityTipMessagesForLive($live)[(string) $tier] ?? ''));

        if ($template === '') {
            return $senderName . ' enviou uma mensagem em destaque.';
        }

        return strtr($template, [
            '{nome}' => $senderName,
            '{valor}' => luacoin_value($amount),
            '{tier}' => (string) $tier,
        ]);
    }

    private function canUserChatInLive(array $live, ?int $userId): bool
    {
        if (! (bool) ($this->settings()['live_chat_enabled'] ?? true) || ! (bool) ($live['chat_enabled'] ?? false)) {
            return false;
        }

        $audience = $this->sanitizeLiveChatAudience((string) ($live['chat_audience'] ?? 'all'));
        if ($audience === 'off' || $userId === null) {
            return false;
        }

        $user = $this->findUserById($userId);
        if (! $user || (string) ($user['status'] ?? 'active') !== 'active') {
            return false;
        }

        if (! (bool) ($this->accessStateForLive($live, $userId)['granted'] ?? false)) {
            return false;
        }

        if ((int) ($live['creator_id'] ?? 0) === $userId || (string) ($user['role'] ?? '') === 'admin') {
            return true;
        }

        if ($audience === 'subscriber') {
            return $this->activeSubscriptionFor($userId, (int) ($live['creator_id'] ?? 0)) !== null;
        }

        return true;
    }

    private function appendPriorityTipMessage(array $live, int $subscriberId, int $amount, ?string $customMessage = null): void
    {
        $tiers = $this->priorityTipTiersForLive($live);
        $matchedTier = 0;
        foreach ($tiers as $tier) {
            if ($amount >= $tier) {
                $matchedTier = $tier;
            }
        }

        if ($matchedTier <= 0) {
            return;
        }

        $level = 1;
        foreach ($tiers as $index => $tier) {
            if ($tier === $matchedTier) {
                $level = min(6, $index + 1);
                break;
            }
        }

        $messages = $this->liveMessages();
        $messages[] = [
            'id' => $this->store->nextId($messages),
            'live_id' => (int) ($live['id'] ?? 0),
            'sender_id' => $subscriberId,
            'body' => 'enviou uma gorjeta em destaque.',
            'alert_text' => $this->priorityTipAlertTextForLive($live, $matchedTier, $amount, $subscriberId, $customMessage),
            'created_at' => date('Y-m-d H:i:s'),
            'kind' => 'priority_tip',
            'tip_amount' => $amount,
            'highlight_tier' => $matchedTier,
            'highlight_level' => $level,
        ];
        $this->save('live_messages', $messages);
    }

    private function decorateLiveMessage(array $message, array $live): array
    {
        $kind = (string) ($message['kind'] ?? 'chat');
        if ($kind !== 'priority_tip') {
            return $message + [
                'is_highlighted' => false,
                'highlight_theme' => [],
            ];
        }

        $level = max(1, min(6, (int) ($message['highlight_level'] ?? 1)));
        $theme = $this->liveMessageHighlightTheme($level);
        $amount = max(1, (int) ($message['tip_amount'] ?? 0));
        $tier = max(1, (int) ($message['highlight_tier'] ?? $amount));
        $senderName = (string) ($message['sender']['name'] ?? 'Assinante');

        return $message + [
            'is_highlighted' => true,
            'highlight_theme' => $theme,
            'highlight_label' => 'Destaque ' . $tier,
            'alert_text' => trim((string) ($message['alert_text'] ?? '')) !== '' ? (string) ($message['alert_text'] ?? '') : ($senderName . ' enviou uma mensagem em destaque.'),
            'body' => $senderName . ' enviou ' . luacoin_value($amount) . ' LuaCoins em destaque no chat.',
        ];
    }

    private function latestPriorityAlertForLive(int $liveId): ?array
    {
        $live = $this->findLiveById($liveId);
        if (! $live) {
            return null;
        }

        $alerts = array_values(array_filter(
            $this->liveMessages(),
            static fn (array $message): bool => (int) ($message['live_id'] ?? 0) === $liveId && (string) ($message['kind'] ?? '') === 'priority_tip'
        ));

        if ($alerts === []) {
            return null;
        }

        usort($alerts, static fn (array $left, array $right): int => ((int) ($right['id'] ?? 0)) <=> ((int) ($left['id'] ?? 0)));
        $latest = $this->decorateLiveMessage($alerts[0], $live);

        return [
            'id' => (int) ($latest['id'] ?? 0),
            'body' => (string) ($latest['body'] ?? ''),
            'alert_text' => (string) ($latest['alert_text'] ?? ''),
            'sender_name' => (string) ($latest['sender']['name'] ?? 'Assinante'),
            'tip_amount' => (int) ($latest['tip_amount'] ?? 0),
            'created_at' => (string) ($latest['created_at'] ?? ''),
        ];
    }

    private function liveMessageHighlightTheme(int $level): array
    {
        $themes = [
            1 => ['background' => '#fff6cf', 'border' => '#fde68a', 'label_background' => '#f59e0b', 'label_text' => '#ffffff'],
            2 => ['background' => '#ffe8cc', 'border' => '#fdba74', 'label_background' => '#f97316', 'label_text' => '#ffffff'],
            3 => ['background' => '#ffd7e8', 'border' => '#f9a8d4', 'label_background' => '#db2777', 'label_text' => '#ffffff'],
            4 => ['background' => '#f5d9ff', 'border' => '#d8b4fe', 'label_background' => '#a855f7', 'label_text' => '#ffffff'],
            5 => ['background' => '#dbeafe', 'border' => '#93c5fd', 'label_background' => '#2563eb', 'label_text' => '#ffffff'],
            6 => ['background' => '#dcfce7', 'border' => '#86efac', 'label_background' => '#16a34a', 'label_text' => '#ffffff'],
        ];

        return $themes[$level] ?? $themes[1];
    }

    private function calculateLiveDurationSeconds(string $startedAt, ?string $endedAt = null): int
    {
        $start = strtotime($startedAt);
        $end = strtotime($endedAt ?? date('Y-m-d H:i:s'));

        if ($start === false || $end === false) {
            return 0;
        }

        return max(0, $end - $start);
    }

    private function formatLiveDuration(int $seconds): string
    {
        $minutes = intdiv(max(0, $seconds), 60);
        $rest = max(0, $seconds) % 60;

        return $minutes . ' minutos e ' . str_pad((string) $rest, 2, '0', STR_PAD_LEFT) . ' segundos';
    }

    private function maxLiveDurationSecondsForLive(array $live): int
    {
        return max(300, (int) ($live['max_live_duration_minutes'] ?? ($this->settings()['live_max_duration_minutes'] ?? 30)) * 60);
    }

    private function enforceLiveDurationLimits(): void
    {
        $streams = $this->liveStreams();
        if ($streams === []) {
            return;
        }

        $changed = false;
        $now = date('Y-m-d H:i:s');
        foreach ($streams as &$stream) {
            if ((string) ($stream['status'] ?? '') !== 'live') {
                continue;
            }

            $live = $this->findLiveById((int) ($stream['live_id'] ?? 0));
            if (! $live) {
                continue;
            }

            $durationSeconds = $this->calculateLiveDurationSeconds((string) ($stream['started_at'] ?? ($live['started_at'] ?? $now)), $now);
            if ($durationSeconds < $this->maxLiveDurationSecondsForLive($live)) {
                continue;
            }

            $stream['status'] = 'ended';
            $stream['broadcaster_peer_id'] = '';
            $stream['updated_at'] = $now;
            $stream['stopped_at'] = $now;
            $changed = true;

            $this->updateLiveRuntimeFields((int) ($live['id'] ?? 0), [
                'status' => 'ended',
                'viewer_count' => 0,
                'ended_at' => $now,
                'duration_seconds' => $durationSeconds,
                'recording_status' => 'disabled',
            ]);
        }
        unset($stream);

        if ($changed) {
            $this->save('live_streams', $streams);
        }
    }

    private function upsertReplayContentFromLive(array &$live): array
    {
        $items = $this->contentItems();
        $contentId = (int) ($live['replay_content_id'] ?? 0);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . max(1, (int) ($live['replay_expiration_days'] ?? 7)) . ' days'));
        $duration = (int) ($live['recording_duration_seconds'] ?? $live['duration_seconds'] ?? 0);
        $payload = [
            'title' => 'Replay - ' . trim((string) ($live['title'] ?? 'Live')),
            'excerpt' => trim((string) ($live['description'] ?? 'Replay salvo automaticamente apos a transmissao.')),
            'body' => trim((string) ($live['description'] ?? 'Replay salvo automaticamente apos a transmissao.')),
            'visibility' => $this->sanitizeReplayVisibility((string) ($live['replay_visibility'] ?? 'subscriber')),
            'status' => 'approved',
            'kind' => 'video',
            'duration' => $duration > 0 ? gmdate('H:i:s', $duration) : '',
            'media_url' => trim((string) ($live['recording_url'] ?? '')),
            'thumbnail_url' => trim((string) ($live['recording_thumbnail_url'] ?? $live['cover_url'] ?? '')),
            'media_bytes' => max(0, (int) ($live['recording_bytes'] ?? 0)),
            'thumbnail_bytes' => max(0, (int) ($live['recording_thumbnail_bytes'] ?? \public_media_file_bytes((string) ($live['recording_thumbnail_url'] ?? $live['cover_url'] ?? '')))),
            'updated_at' => date('Y-m-d H:i:s'),
            'source_live_id' => (int) ($live['id'] ?? 0),
            'expires_at' => $expiresAt,
            'auto_generated' => true,
        ];

        if ($contentId > 0) {
            foreach ($items as &$item) {
                if ((int) ($item['id'] ?? 0) !== $contentId) {
                    continue;
                }

                $item = array_merge($item, $payload);
                $this->save('content_items', $items);

                return $item;
            }
            unset($item);
        }

        $item = [
            'id' => $this->store->nextId($items),
            'creator_id' => (int) ($live['creator_id'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
            'saved_count' => 0,
        ] + $payload;
        $items[] = $item;
        $this->save('content_items', $items);

        $live['replay_content_id'] = (int) $item['id'];

        return $item;
    }

    private function syncReplayContentWithLive(array $live): void
    {
        // O replay automatico foi desabilitado para reduzir uso de armazenamento na VPS.
        return;

        if ((int) ($live['replay_content_id'] ?? 0) <= 0) {
            return;
        }

        $shadow = $live;
        $this->upsertReplayContentFromLive($shadow);
    }

    private function chargeSubscriberAndCreditCreator(int $subscriberId, int $creatorId, int $amount, string $type, string $note, int $liveId = 0): void
    {
        $transactions = $this->walletTransactions();
        $feePercent = (int) ($this->settings()['platform_fee_percent'] ?? 20);
        $net = max(1, (int) floor($amount * (100 - $feePercent) / 100));

        $transactions[] = [
            'id' => $this->store->nextId($transactions),
            'user_id' => $subscriberId,
            'type' => $type,
            'direction' => 'out',
            'amount' => $amount,
            'note' => $note,
            'creator_id' => $creatorId,
            'live_id' => max(0, $liveId),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $transactions[] = [
            'id' => $this->store->nextId($transactions),
            'user_id' => $creatorId,
            'type' => $type . '_income',
            'direction' => 'in',
            'amount' => $net,
            'note' => 'Receita liquida de ' . $note,
            'subscriber_id' => $subscriberId,
            'live_id' => max(0, $liveId),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->save('wallet_transactions', $transactions);
    }

    private function normalizeSyncPayStatus(string $status): string
    {
        $status = strtolower(trim($status));
        $status = str_replace([' ', '-'], '_', $status);

        return match ($status) {
            'success', 'approved', 'authorized', 'paid', 'completed', 'confirmado', 'aprovado', 'paid_out', 'paidout', 'settled', 'liquidated', 'received', 'confirmed' => 'approved',
            'waiting_for_approval', 'waiting_payment', 'waiting_for_payment', 'aguardando_pagamento', 'pending', 'created', 'processing', 'in_process', 'em_processamento' => 'pending',
            'cancelled', 'canceled', 'expired', 'failed', 'rejected', 'denied', 'refunded', 'charged_back', 'recusado', 'expirado' => $status,
            default => $status !== '' ? $status : 'pending',
        };
    }

    private function transactionCountsForBalance(array $transaction): bool
    {
        $type = (string) ($transaction['type'] ?? '');
        $status = strtolower((string) ($transaction['status'] ?? 'completed'));

        if ($type === 'top_up_pending') {
            return false;
        }

        if ($type === 'top_up' && ! in_array($status, ['approved', 'completed', 'paid'], true)) {
            return false;
        }

        return true;
    }

    private function normalizeSettings(array $settings): array
    {
        $defaults = $this->settingsDefaults();
        $normalized = array_merge($defaults, $settings);

        if (array_key_exists('token_price_brl', $settings) && ! array_key_exists('luacoin_price_brl', $settings)) {
            $normalized['luacoin_price_brl'] = (float) $settings['token_price_brl'];
        }

        if (array_key_exists('withdraw_min_tokens', $settings) && ! array_key_exists('withdraw_min_luacoins', $settings)) {
            $normalized['withdraw_min_luacoins'] = (int) $settings['withdraw_min_tokens'];
        }

        if (array_key_exists('withdraw_max_tokens', $settings) && ! array_key_exists('withdraw_max_luacoins', $settings)) {
            $normalized['withdraw_max_luacoins'] = (int) $settings['withdraw_max_tokens'];
        }

        if (array_key_exists('deposit_min_tokens', $settings) && ! array_key_exists('deposit_min_luacoins', $settings)) {
            $normalized['deposit_min_luacoins'] = (int) $settings['deposit_min_tokens'];
        }

        $normalized['luacoin_price_brl'] = max(0.01, (float) ($normalized['luacoin_price_brl'] ?? 0.07));
        $normalized['deposit_min_luacoins'] = max(1, (int) ($normalized['deposit_min_luacoins'] ?? 100));
        $normalized['withdraw_min_luacoins'] = max(1, (int) ($normalized['withdraw_min_luacoins'] ?? 50));
        $normalized['withdraw_max_luacoins'] = max($normalized['withdraw_min_luacoins'], (int) ($normalized['withdraw_max_luacoins'] ?? 25000));
        $normalized['live_replay_expiration_days'] = max(1, (int) ($normalized['live_replay_expiration_days'] ?? 7));
        $normalized['live_max_duration_minutes'] = max(5, (int) ($normalized['live_max_duration_minutes'] ?? 30));
        $normalized['creator_content_storage_limit_mb'] = max(1, (int) ($normalized['creator_content_storage_limit_mb'] ?? 50));
        $normalized['subscriber_signup_bonus_enabled'] = (bool) ($normalized['subscriber_signup_bonus_enabled'] ?? true);
        $normalized['subscriber_signup_bonus_luacoins'] = max(0, (int) ($normalized['subscriber_signup_bonus_luacoins'] ?? 10));
        $normalized['topup_bonus_percent'] = max(0, min(100, (int) ($normalized['topup_bonus_percent'] ?? 10)));
        $normalized['live_priority_alert_duration_ms'] = max(2000, (int) ($normalized['live_priority_alert_duration_ms'] ?? 8000));
        $normalized['site_base_url'] = rtrim(trim((string) ($normalized['site_base_url'] ?? '')), '/');
        $normalized['seo_site_title'] = trim((string) ($normalized['seo_site_title'] ?? 'SexyLua'));
        $normalized['seo_meta_title'] = trim((string) ($normalized['seo_meta_title'] ?? $normalized['seo_site_title'] ?? 'SexyLua'));
        $normalized['seo_meta_description'] = trim((string) ($normalized['seo_meta_description'] ?? 'Plataforma de assinaturas, chats privados, lives e monetizacao com LuaCoins.'));
        $normalized['seo_logo_white_url'] = trim((string) ($normalized['seo_logo_white_url'] ?? ''));
        $normalized['seo_logo_color_url'] = trim((string) ($normalized['seo_logo_color_url'] ?? ''));
        $normalized['home_banner_enabled'] = (bool) ($normalized['home_banner_enabled'] ?? true);
        $normalized['home_banner_title'] = trim((string) ($normalized['home_banner_title'] ?? 'Cadastre-se hoje e ganhe 10 LuaCoins gratis'));
        $normalized['home_banner_subtitle'] = trim((string) ($normalized['home_banner_subtitle'] ?? 'Crie sua conta agora, receba 10 LuaCoins no cadastro e aproveite bonus extra em cada deposito para entrar na SexyLua com mais liberdade.'));
        $normalized['home_banner_primary_text'] = trim((string) ($normalized['home_banner_primary_text'] ?? 'Explorar agora'));
        $normalized['home_banner_primary_link'] = trim((string) ($normalized['home_banner_primary_link'] ?? '/explore'));
        $normalized['home_banner_secondary_text'] = trim((string) ($normalized['home_banner_secondary_text'] ?? 'Criar conta'));
        $normalized['home_banner_secondary_link'] = trim((string) ($normalized['home_banner_secondary_link'] ?? '/register'));
        $normalized['home_banner_countdown_enabled'] = (bool) ($normalized['home_banner_countdown_enabled'] ?? true);
        $normalized['home_banner_countdown_seconds'] = max(0, (int) ($normalized['home_banner_countdown_seconds'] ?? 172800));
        $normalized['home_banner_countdown_target_at'] = trim((string) ($normalized['home_banner_countdown_target_at'] ?? ''));
        if ($normalized['home_banner_countdown_target_at'] !== '' && strtotime($normalized['home_banner_countdown_target_at']) === false) {
            $normalized['home_banner_countdown_target_at'] = '';
        }
        $normalized['home_banner_background_url'] = trim((string) ($normalized['home_banner_background_url'] ?? ''));
        $normalized['syncpay_api_base_url'] = rtrim(trim((string) ($normalized['syncpay_api_base_url'] ?? 'https://api.syncpayments.com.br')), '/');
        $normalized['syncpay_client_id'] = trim((string) ($normalized['syncpay_client_id'] ?? ''));
        $normalized['syncpay_client_secret'] = trim((string) ($normalized['syncpay_client_secret'] ?? ''));
        $normalized['syncpay_api_key'] = trim((string) ($normalized['syncpay_api_key'] ?? ''));
        $normalized['syncpay_webhook_token'] = trim((string) ($normalized['syncpay_webhook_token'] ?? ''));
        $normalized['syncpay_pix_expires_in_days'] = max(1, (int) ($normalized['syncpay_pix_expires_in_days'] ?? 2));
        $normalized['syncpay_webhook_url'] = webhook_url($this->config, $normalized, '/webhook/syncpay');
        $normalized['token_price_brl'] = $normalized['luacoin_price_brl'];
        $normalized['deposit_min_tokens'] = $normalized['deposit_min_luacoins'];
        $normalized['withdraw_min_tokens'] = $normalized['withdraw_min_luacoins'];
        $normalized['withdraw_max_tokens'] = $normalized['withdraw_max_luacoins'];

        return $normalized;
    }

    private function settingsDefaults(): array
    {
        return [
            'platform_fee_percent' => 20,
            'luacoin_price_brl' => 0.07,
            'deposit_min_luacoins' => 100,
            'withdraw_min_luacoins' => 50,
            'withdraw_max_luacoins' => 25000,
            'live_replay_expiration_days' => 7,
            'live_max_duration_minutes' => 30,
            'creator_content_storage_limit_mb' => 50,
            'subscriber_signup_bonus_enabled' => true,
            'subscriber_signup_bonus_luacoins' => 10,
            'topup_bonus_percent' => 10,
            'maintenance_mode' => false,
            'slow_mode_seconds' => 3,
            'auto_moderation' => true,
            'blur_sensitive_thumbs' => true,
            'live_chat_enabled' => true,
            'live_priority_alert_duration_ms' => 8000,
            'theme' => 'lunar-metamorphosis',
            'announcement' => 'Noite especial com criadores em destaque e novas colecoes em aprovacao.',
            'site_base_url' => trim((string) ($this->config['app']['base_url'] ?? '')),
            'seo_site_title' => 'SexyLua',
            'seo_meta_title' => 'SexyLua',
            'seo_meta_description' => 'Plataforma de assinaturas, chats privados, lives e monetizacao com LuaCoins.',
            'seo_logo_white_url' => '',
            'seo_logo_color_url' => '',
            'home_banner_enabled' => true,
            'home_banner_title' => 'Cadastre-se hoje e ganhe 10 LuaCoins gratis',
            'home_banner_subtitle' => 'Crie sua conta agora, receba 10 LuaCoins no cadastro e aproveite bonus extra em cada deposito para entrar na SexyLua com mais liberdade.',
            'home_banner_primary_text' => 'Explorar agora',
            'home_banner_primary_link' => '/explore',
            'home_banner_secondary_text' => 'Criar conta',
            'home_banner_secondary_link' => '/register',
            'home_banner_countdown_enabled' => true,
            'home_banner_countdown_seconds' => 172800,
            'home_banner_countdown_target_at' => '',
            'home_banner_background_url' => '',
            'syncpay_api_base_url' => 'https://api.syncpayments.com.br',
            'syncpay_client_id' => '',
            'syncpay_client_secret' => '',
            'syncpay_api_key' => '',
            'syncpay_webhook_token' => '',
            'syncpay_pix_expires_in_days' => 2,
        ];
    }

    private function conversationList(int $subscriberId): array
    {
        $rows = array_values(array_filter($this->conversations(), static fn (array $conversation): bool => (int) $conversation['subscriber_id'] === $subscriberId));
        $rows = $this->sortByDate($rows, 'updated_at');

        return array_map(function (array $conversation): array {
            $creator = $this->findCreatorBySlugOrId(null, (int) $conversation['creator_id']);
            $messages = array_values(array_filter($this->messages(), static fn (array $message): bool => (int) $message['conversation_id'] === (int) $conversation['id']));
            $messages = $this->sortByDate($messages, 'created_at');
            $lastMessage = $messages[0] ?? null;
            $subscription = $this->activeSubscriptionFor((int) ($conversation['subscriber_id'] ?? 0), (int) ($conversation['creator_id'] ?? 0));
            if (is_array($subscription)) {
                $subscription['plan'] = $this->findPlanById((int) ($subscription['plan_id'] ?? 0));
            }

            return $conversation + [
                'creator' => $creator,
                'subscription' => $subscription,
                'latest_message' => $lastMessage,
                'last_message' => $lastMessage,
            ];
        }, $rows);
    }

    private function creatorConversationList(int $creatorId): array
    {
        $rows = array_values(array_filter($this->conversations(), static fn (array $conversation): bool => (int) ($conversation['creator_id'] ?? 0) === $creatorId));
        $rows = $this->sortByDate($rows, 'updated_at');

        return array_map(function (array $conversation): array {
            $subscriber = $this->findUserById((int) ($conversation['subscriber_id'] ?? 0));
            $messages = array_values(array_filter($this->messages(), static fn (array $message): bool => (int) ($message['conversation_id'] ?? 0) === (int) ($conversation['id'] ?? 0)));
            $messages = $this->sortByDate($messages, 'created_at');
            $lastMessage = $messages[0] ?? null;
            $subscription = $this->activeSubscriptionFor((int) ($conversation['subscriber_id'] ?? 0), (int) ($conversation['creator_id'] ?? 0));
            if (is_array($subscription)) {
                $subscription['plan'] = $this->findPlanById((int) ($subscription['plan_id'] ?? 0));
            }

            return $conversation + [
                'subscriber' => $subscriber,
                'subscription' => $subscription,
                'latest_message' => $lastMessage,
                'last_message' => $lastMessage,
            ];
        }, $rows);
    }

    private function messagesForConversation(int $conversationId, int $viewerId = 0): array
    {
        $conversation = $this->findConversationById($conversationId);
        $messages = array_values(array_filter($this->messages(), static fn (array $message): bool => (int) ($message['conversation_id'] ?? 0) === $conversationId));
        usort($messages, static fn (array $left, array $right): int => strcmp((string) ($left['created_at'] ?? ''), (string) ($right['created_at'] ?? '')));

        return array_map(function (array $message) use ($viewerId, $conversation): array {
            $message['sender'] = $this->findUserById((int) ($message['sender_id'] ?? 0));
            if (is_array($conversation)) {
                $message = $this->decorateConversationMessage($message, $conversation, $viewerId);
            }

            return $message;
        }, $messages);
    }

    private function announcementsForUser(array $user, int $limit = 12, string $baseHref = ''): array
    {
        if (! is_array($user) || (int) ($user['id'] ?? 0) <= 0) {
            return [];
        }

        $announcements = array_values(array_filter($this->announcements(), fn (array $announcement): bool => $this->announcementMatchesUser($announcement, $user)));
        $announcements = $this->sortByDate($announcements, 'created_at');

        return array_map(
            fn (array $announcement): array => $this->decorateAnnouncementForUser($announcement, $user, $baseHref),
            array_slice($announcements, 0, max(1, $limit))
        );
    }

    private function findAnnouncementForUser(int $announcementId, array $user, string $baseHref = ''): ?array
    {
        if ($announcementId <= 0) {
            return null;
        }

        $announcement = $this->findAnnouncementById($announcementId);
        if ($announcement === null || ! $this->announcementMatchesUser($announcement, $user)) {
            return null;
        }

        return $this->decorateAnnouncementForUser($announcement, $user, $baseHref);
    }

    private function decorateAnnouncementForUser(array $announcement, array $user, string $baseHref = ''): array
    {
        $attachment = $this->normalizeAnnouncementAttachment(is_array($announcement['attachment'] ?? null) ? $announcement['attachment'] : null);

        return array_merge($announcement, [
            'href' => $baseHref !== '' ? path_with_query($baseHref, ['announcement' => (int) ($announcement['id'] ?? 0)]) : (string) ($announcement['href'] ?? ''),
            'attachment' => $attachment !== null ? array_merge($attachment, [
                'href' => path_with_query('/messages/asset', ['scope' => 'announcement', 'id' => (int) ($announcement['id'] ?? 0)]),
            ]) : null,
        ]);
    }

    private function decorateAnnouncementForAdmin(array $announcement): array
    {
        $attachment = $this->normalizeAnnouncementAttachment(is_array($announcement['attachment'] ?? null) ? $announcement['attachment'] : null);

        return array_merge($announcement, [
            'attachment' => $attachment !== null ? array_merge($attachment, [
                'href' => path_with_query('/messages/asset', ['scope' => 'announcement', 'id' => (int) ($announcement['id'] ?? 0)]),
            ]) : null,
        ]);
    }

    private function decorateConversationMessage(array $message, array $conversation, int $viewerId): array
    {
        $viewer = $viewerId > 0 ? $this->findUserById($viewerId) : null;
        $viewerRole = (string) ($viewer['role'] ?? '');
        $attachment = $this->normalizeConversationAttachment(is_array($message['attachment'] ?? null) ? $message['attachment'] : null);
        $requiredPlanId = max(0, (int) ($message['required_plan_id'] ?? 0));
        $requiredPlan = $requiredPlanId > 0 ? $this->findPlanById($requiredPlanId) : null;
        $unlockPrice = max(0, (int) ($message['unlock_price'] ?? 0));
        $isUnlocked = $viewerId > 0 && $this->hasConversationMessageUnlock((int) ($message['id'] ?? 0), $viewerId);
        $hasAccess = $this->viewerCanAccessConversationMessage($message, $conversation, $viewerId, $viewerRole);
        $creatorId = (int) ($conversation['creator_id'] ?? 0);
        $creatorUnlock = $unlockPrice > 0 && $viewerId === $creatorId
            ? $this->latestConversationMessageUnlock((int) ($message['id'] ?? 0))
            : null;
        $creatorUnlockUser = is_array($creatorUnlock) ? $this->findUserById((int) ($creatorUnlock['user_id'] ?? 0)) : null;

        return array_merge($message, [
            'attachment' => $attachment !== null ? array_merge($attachment, [
                'href' => $hasAccess ? path_with_query('/messages/asset', ['scope' => 'message', 'id' => (int) ($message['id'] ?? 0)]) : null,
            ]) : null,
            'required_plan' => $requiredPlan,
            'required_plan_name' => (string) ($requiredPlan['name'] ?? ''),
            'unlock_price' => $unlockPrice,
            'is_unlocked' => $isUnlocked,
            'can_access_attachment' => $hasAccess,
            'is_locked_attachment' => $attachment !== null && ! $hasAccess,
            'creator_unlock_status' => $unlockPrice > 0 && $viewerId === $creatorId
                ? ($creatorUnlock !== null ? 'unlocked' : 'pending')
                : null,
            'creator_unlock_label' => $unlockPrice > 0 && $viewerId === $creatorId
                ? ($creatorUnlock !== null ? 'Desbloqueado' : 'Aguardando desbloqueio')
                : '',
            'creator_unlock_at' => is_array($creatorUnlock) ? (string) ($creatorUnlock['created_at'] ?? '') : '',
            'creator_unlock_user_name' => (string) ($creatorUnlockUser['name'] ?? ''),
            'lock_reason' => $unlockPrice > 0
                ? 'Desbloqueie este conteudo com LuaCoins para visualizar.'
                : ($requiredPlan !== null ? 'Conteudo liberado apenas para o plano ' . (string) ($requiredPlan['name'] ?? 'selecionado') . '.' : 'Conteudo exclusivo.'),
        ]);
    }

    private function viewerCanAccessConversationMessage(array $message, array $conversation, int $viewerId, string $viewerRole = ''): bool
    {
        if ($viewerId <= 0) {
            return false;
        }

        if ($viewerRole === 'admin') {
            return true;
        }

        $subscriberId = (int) ($conversation['subscriber_id'] ?? 0);
        $creatorId = (int) ($conversation['creator_id'] ?? 0);

        if (! in_array($viewerId, [$subscriberId, $creatorId], true)) {
            return false;
        }

        if ((int) ($message['sender_id'] ?? 0) === $viewerId) {
            return true;
        }

        $attachment = $this->normalizeConversationAttachment(is_array($message['attachment'] ?? null) ? $message['attachment'] : null);
        if ($attachment === null) {
            return true;
        }

        $unlockPrice = max(0, (int) ($message['unlock_price'] ?? 0));
        if ($unlockPrice > 0) {
            return $viewerId === $creatorId || $this->hasConversationMessageUnlock((int) ($message['id'] ?? 0), $viewerId);
        }

        $requiredPlanId = max(0, (int) ($message['required_plan_id'] ?? 0));
        if ($requiredPlanId > 0) {
            $subscription = $this->activeSubscriptionFor($subscriberId, $creatorId);

            return $viewerId === $creatorId || ((int) ($subscription['plan_id'] ?? 0) === $requiredPlanId);
        }

        return true;
    }

    private function hasConversationMessageUnlock(int $messageId, int $userId): bool
    {
        foreach ($this->messageUnlocks() as $unlock) {
            if ((int) ($unlock['message_id'] ?? 0) === $messageId && (int) ($unlock['user_id'] ?? 0) === $userId) {
                return true;
            }
        }

        return false;
    }

    private function hasLiveUnlock(int $liveId, int $userId): bool
    {
        foreach ($this->liveUnlocks() as $unlock) {
            if ((int) ($unlock['live_id'] ?? 0) === $liveId && (int) ($unlock['user_id'] ?? 0) === $userId) {
                return true;
            }
        }

        return false;
    }

    private function latestConversationMessageUnlock(int $messageId): ?array
    {
        $latest = null;

        foreach ($this->messageUnlocks() as $unlock) {
            if ((int) ($unlock['message_id'] ?? 0) !== $messageId) {
                continue;
            }

            if ($latest === null || strcmp((string) ($unlock['created_at'] ?? ''), (string) ($latest['created_at'] ?? '')) > 0) {
                $latest = $unlock;
            }
        }

        return $latest;
    }

    private function announcementMatchesUser(array $announcement, array $user): bool
    {
        if ((string) ($user['status'] ?? 'active') !== 'active') {
            return false;
        }

        $audience = (string) ($announcement['audience'] ?? 'all');
        if ($audience === 'all') {
            return true;
        }

        return (string) ($user['role'] ?? '') === $audience;
    }

    private function normalizeConversationAttachment(?array $attachment): ?array
    {
        if (! is_array($attachment) || trim((string) ($attachment['path'] ?? '')) === '') {
            return null;
        }

        $path = trim((string) ($attachment['path'] ?? ''));
        $extension = strtolower(trim((string) ($attachment['extension'] ?? pathinfo($path, PATHINFO_EXTENSION))));
        $mimeType = trim((string) ($attachment['mime_type'] ?? 'application/octet-stream'));
        $kind = (string) ($attachment['kind'] ?? uploaded_asset_kind($extension, $mimeType));

        return [
            'path' => $path,
            'original_name' => trim((string) ($attachment['original_name'] ?? basename($path))),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => max(0, (int) ($attachment['size'] ?? private_media_file_bytes($path))),
            'kind' => in_array($kind, ['image', 'video', 'document'], true) ? $kind : 'document',
        ];
    }

    private function normalizeAnnouncementAttachment(?array $attachment): ?array
    {
        return $this->normalizeConversationAttachment($attachment);
    }

    private function messageNotificationPreview(string $messageType, ?array $attachment, int $unlockPrice, int $requiredPlanId): string
    {
        if ($attachment === null) {
            return 'Nova mensagem recebida.';
        }

        if ($unlockPrice > 0) {
            return 'Conteudo instantaneo enviado por ' . $unlockPrice . ' LuaCoins.';
        }

        if ($requiredPlanId > 0) {
            return 'Conteudo exclusivo enviado no chat.';
        }

        return match ($messageType) {
            'attachment' => 'Novo anexo enviado no chat.',
            default => 'Nova mensagem recebida.',
        };
    }

    private function findAnnouncementById(int $announcementId): ?array
    {
        foreach ($this->announcements() as $announcement) {
            if ((int) ($announcement['id'] ?? 0) === $announcementId) {
                return $announcement;
            }
        }

        return null;
    }

    private function findMessageById(int $messageId): ?array
    {
        foreach ($this->messages() as $message) {
            if ((int) ($message['id'] ?? 0) === $messageId) {
                return $message;
            }
        }

        return null;
    }

    private function notificationFeedForUser(array $user, int $limit = 6): array
    {
        if ((string) ($user['role'] ?? '') === 'admin') {
            return $this->adminNotificationFeed($limit);
        }

        $userId = (int) ($user['id'] ?? 0);
        $rows = array_values(array_filter($this->notifications(), static fn (array $notification): bool => (int) ($notification['user_id'] ?? 0) === $userId));
        $rows = $this->sortByDate($rows, 'created_at');
        $items = array_map(function (array $notification): array {
            $marker = $this->feedMarker((string) ($notification['created_at'] ?? ''), (int) ($notification['id'] ?? 0));

            return [
                'id' => (int) ($notification['id'] ?? 0),
                'marker' => $marker,
                'title' => (string) ($notification['title'] ?? 'Atualizacao'),
                'body' => (string) ($notification['body'] ?? ''),
                'href' => (string) ($notification['href'] ?? ''),
                'icon' => $this->notificationIconForKind((string) ($notification['kind'] ?? 'notification')),
                'time' => format_datetime((string) ($notification['created_at'] ?? ''), 'd/m H:i'),
            ];
        }, array_slice($rows, 0, $limit));

        return [
            'items' => $items,
            'latest_marker' => (int) ($items[0]['marker'] ?? 0),
        ];
    }

    private function messageFeedForUser(array $user, int $limit = 6): array
    {
        $role = (string) ($user['role'] ?? 'subscriber');
        $items = [];

        if ($role === 'admin') {
            $announcements = array_slice($this->sortByDate($this->announcements(), 'created_at'), 0, $limit);
            $items = array_map(function (array $announcement): array {
                return [
                    'id' => (int) ($announcement['id'] ?? 0),
                    'marker' => $this->feedMarker((string) ($announcement['created_at'] ?? ''), (int) ($announcement['id'] ?? 0)),
                    'title' => (string) ($announcement['title'] ?? 'Comunicado'),
                    'body' => (string) ($announcement['body'] ?? ''),
                    'href' => '/admin/messages',
                    'icon' => 'campaign',
                    'time' => format_datetime((string) ($announcement['created_at'] ?? ''), 'd/m H:i'),
                ];
            }, $announcements);
        } elseif ($role === 'creator') {
            $announcements = array_map(function (array $announcement): array {
                return [
                    'id' => 'announcement-' . (int) ($announcement['id'] ?? 0),
                    'marker' => $this->feedMarker((string) ($announcement['created_at'] ?? ''), (int) ($announcement['id'] ?? 0)),
                    'title' => (string) ($announcement['title'] ?? 'Comunicado'),
                    'body' => excerpt((string) ($announcement['body'] ?? ''), 80),
                    'href' => path_with_query('/creator/messages', ['announcement' => (int) ($announcement['id'] ?? 0)]),
                    'icon' => 'campaign',
                    'time' => format_datetime((string) ($announcement['created_at'] ?? ''), 'd/m H:i'),
                ];
            }, array_slice($this->announcementsForUser($user, 2, '/creator/messages'), 0, 2));
            $conversations = array_slice($this->creatorConversationList((int) ($user['id'] ?? 0)), 0, $limit);
            $conversationItems = array_map(function (array $conversation): array {
                $lastMessage = $conversation['latest_message'] ?? null;

                return [
                    'id' => (int) ($conversation['id'] ?? 0),
                    'marker' => max(
                        (int) ($lastMessage['id'] ?? 0),
                        $this->feedMarker((string) ($conversation['updated_at'] ?? ''), (int) ($conversation['id'] ?? 0))
                    ),
                    'title' => (string) ($conversation['subscriber']['name'] ?? 'Assinante'),
                    'body' => excerpt((string) ($lastMessage['body'] ?? 'Sem mensagens ainda.'), 80),
                    'href' => path_with_query('/creator/messages', ['conversation' => (int) ($conversation['id'] ?? 0)]),
                    'icon' => 'chat',
                    'time' => format_datetime((string) ($lastMessage['created_at'] ?? $conversation['updated_at'] ?? ''), 'd/m H:i'),
                ];
            }, $conversations);
            $items = array_slice(array_values($this->sortFeedItemsByMarker(array_merge($announcements, $conversationItems))), 0, $limit);
        } else {
            $announcements = array_map(function (array $announcement): array {
                return [
                    'id' => 'announcement-' . (int) ($announcement['id'] ?? 0),
                    'marker' => $this->feedMarker((string) ($announcement['created_at'] ?? ''), (int) ($announcement['id'] ?? 0)),
                    'title' => (string) ($announcement['title'] ?? 'Comunicado'),
                    'body' => excerpt((string) ($announcement['body'] ?? ''), 80),
                    'href' => path_with_query('/subscriber/messages', ['announcement' => (int) ($announcement['id'] ?? 0)]),
                    'icon' => 'campaign',
                    'time' => format_datetime((string) ($announcement['created_at'] ?? ''), 'd/m H:i'),
                ];
            }, array_slice($this->announcementsForUser($user, 2, '/subscriber/messages'), 0, 2));
            $conversations = array_slice($this->conversationList((int) ($user['id'] ?? 0)), 0, $limit);
            $conversationItems = array_map(function (array $conversation): array {
                $lastMessage = $conversation['latest_message'] ?? null;

                return [
                    'id' => (int) ($conversation['id'] ?? 0),
                    'marker' => max(
                        (int) ($lastMessage['id'] ?? 0),
                        $this->feedMarker((string) ($conversation['updated_at'] ?? ''), (int) ($conversation['id'] ?? 0))
                    ),
                    'title' => (string) ($conversation['creator']['name'] ?? 'Criador'),
                    'body' => excerpt((string) ($lastMessage['body'] ?? 'Sem mensagens ainda.'), 80),
                    'href' => path_with_query('/subscriber/messages', ['conversation' => (int) ($conversation['id'] ?? 0)]),
                    'icon' => 'chat',
                    'time' => format_datetime((string) ($lastMessage['created_at'] ?? $conversation['updated_at'] ?? ''), 'd/m H:i'),
                ];
            }, $conversations);
            $items = array_slice(array_values($this->sortFeedItemsByMarker(array_merge($announcements, $conversationItems))), 0, $limit);
        }

        return [
            'items' => $items,
            'latest_marker' => (int) ($items[0]['marker'] ?? 0),
        ];
    }

    private function sortFeedItemsByMarker(array $items): array
    {
        usort($items, static fn (array $left, array $right): int => ((int) ($right['marker'] ?? 0)) <=> ((int) ($left['marker'] ?? 0)));

        return $items;
    }

    private function adminNotificationFeed(int $limit = 6): array
    {
        $finance = $this->financeData();
        $items = [];

        foreach (array_slice(array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (string) ($item['status'] ?? '') === 'pending')), 0, 3) as $item) {
            $items[] = [
                'id' => 'content-' . (int) ($item['id'] ?? 0),
                'marker' => $this->feedMarker((string) ($item['created_at'] ?? ''), (int) ($item['id'] ?? 0)),
                'title' => 'Conteudo aguardando revisao',
                'body' => (string) ($item['title'] ?? 'Conteudo') . ' de ' . (string) ($item['creator']['name'] ?? 'criador'),
                'href' => '/admin/moderation',
                'icon' => 'gavel',
                'time' => format_datetime((string) ($item['created_at'] ?? ''), 'd/m H:i'),
            ];
        }

        foreach (array_slice((array) ($finance['pending_payouts'] ?? []), 0, 2) as $transaction) {
            $items[] = [
                'id' => 'payout-' . (int) ($transaction['id'] ?? 0),
                'marker' => $this->feedMarker((string) ($transaction['created_at'] ?? ''), (int) ($transaction['id'] ?? 0)),
                'title' => 'Saque pendente',
                'body' => (string) ($transaction['user']['name'] ?? 'Criador') . ' solicitou ' . (int) ($transaction['amount'] ?? 0) . ' LuaCoins.',
                'href' => '/admin/finance',
                'icon' => 'payments',
                'time' => format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m H:i'),
            ];
        }

        foreach (array_slice((array) ($finance['pending_topups'] ?? []), 0, 2) as $transaction) {
            $items[] = [
                'id' => 'topup-' . (int) ($transaction['id'] ?? 0),
                'marker' => $this->feedMarker((string) ($transaction['created_at'] ?? ''), (int) ($transaction['id'] ?? 0)),
                'title' => 'Recarga aguardando acao',
                'body' => (string) ($transaction['user']['name'] ?? 'Assinante') . ' tentou recarregar ' . (int) ($transaction['amount'] ?? 0) . ' LuaCoins.',
                'href' => '/admin/finance',
                'icon' => 'account_balance_wallet',
                'time' => format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m H:i'),
            ];
        }

        foreach (array_slice($this->sortByDate($this->users(), 'created_at'), 0, 2) as $user) {
            $items[] = [
                'id' => 'user-' . (int) ($user['id'] ?? 0),
                'marker' => $this->feedMarker((string) ($user['created_at'] ?? ''), (int) ($user['id'] ?? 0)),
                'title' => 'Novo usuario na plataforma',
                'body' => (string) ($user['name'] ?? 'Usuario') . ' entrou como ' . role_label((string) ($user['role'] ?? 'subscriber')) . '.',
                'href' => '/admin/users',
                'icon' => 'person_add',
                'time' => format_datetime((string) ($user['created_at'] ?? ''), 'd/m H:i'),
            ];
        }

        foreach (array_slice(array_values(array_filter($this->users(), fn (array $user): bool => $this->verificationStatusValue((string) ($user['verification_status'] ?? 'pending')) === 'pending' && is_array($user['identity_document'] ?? null))), 0, 2) as $user) {
            $items[] = [
                'id' => 'verification-' . (int) ($user['id'] ?? 0),
                'marker' => $this->feedMarker((string) ($user['verification_requested_at'] ?? $user['created_at'] ?? ''), (int) ($user['id'] ?? 0)),
                'title' => 'Documento aguardando analise',
                'body' => (string) ($user['name'] ?? 'Usuario') . ' enviou documentacao para verificacao.',
                'href' => '/admin/users',
                'icon' => 'badge',
                'time' => format_datetime((string) ($user['verification_requested_at'] ?? $user['created_at'] ?? ''), 'd/m H:i'),
            ];
        }

        usort($items, static fn (array $left, array $right): int => ((int) ($right['marker'] ?? 0)) <=> ((int) ($left['marker'] ?? 0)));
        $items = array_slice($items, 0, $limit);

        return [
            'items' => $items,
            'latest_marker' => (int) ($items[0]['marker'] ?? 0),
        ];
    }

    private function notifyAdmins(string $kind, string $title, string $body, string $href = '/admin'): void
    {
        foreach ($this->users() as $user) {
            if ((string) ($user['role'] ?? '') !== 'admin' || (string) ($user['status'] ?? 'active') !== 'active') {
                continue;
            }

            $this->notifyUser((int) ($user['id'] ?? 0), $kind, $title, $body, $href);
        }
    }

    private function notifyUser(
        int $userId,
        string $kind,
        string $title,
        string $body,
        string $href = '',
        array $meta = [],
        ?string $createdAt = null
    ): ?array {
        if ($userId <= 0 || $this->findUserById($userId) === null) {
            return null;
        }

        $notifications = $this->notifications();
        $createdAt = $createdAt !== null && $createdAt !== '' ? $createdAt : date('Y-m-d H:i:s');
        $notification = [
            'id' => $this->store->nextId($notifications),
            'user_id' => $userId,
            'kind' => $kind,
            'title' => $title,
            'body' => $body,
            'href' => $href,
            'meta' => $meta,
            'created_at' => $createdAt,
        ];
        $notifications[] = $notification;
        $this->save('notifications', $notifications);

        return $notification;
    }

    private function notificationIconForKind(string $kind): string
    {
        return match ($kind) {
            'message' => 'chat',
            'subscription' => 'workspace_premium',
            'wallet' => 'account_balance_wallet',
            'tip' => 'local_fire_department',
            'sale' => 'paid',
            'payout' => 'payments',
            'moderation' => 'gavel',
            'announcement' => 'campaign',
            'verification' => 'badge',
            default => 'notifications',
        };
    }

    private function feedMarker(string $createdAt, int $fallback): int
    {
        $timestamp = strtotime($createdAt);

        if ($timestamp === false) {
            return $fallback;
        }

        return ((int) $timestamp * 1000) + max(0, $fallback);
    }

    private function cleanupLiveRtcData(): void
    {
        $this->enforceLiveDurationLimits();
        $now = time();
        $presence = $this->livePresence();
        $freshPresence = array_values(array_filter(
            $presence,
            fn (array $row): bool => $this->timestampAgeSeconds((string) ($row['last_seen'] ?? ''), $now) <= self::LIVE_PRESENCE_TIMEOUT_SECONDS
        ));

        if (count($freshPresence) !== count($presence)) {
            $this->save('live_presence', $freshPresence);
        }

        $signals = $this->liveSignals();
        $freshSignals = array_values(array_filter(
            $signals,
            fn (array $row): bool => $this->timestampAgeSeconds((string) ($row['created_at'] ?? ''), $now) <= self::LIVE_SIGNAL_TIMEOUT_SECONDS
        ));

        if (count($freshSignals) !== count($signals)) {
            $this->save('live_signals', $freshSignals);
        }

        $streams = $this->liveStreams();
        $changed = false;
        foreach ($streams as &$stream) {
            if ((string) ($stream['status'] ?? 'idle') !== 'live') {
                continue;
            }

            $peerId = (string) ($stream['broadcaster_peer_id'] ?? '');
            $creatorPresence = $this->activeCreatorPresenceForLive((int) ($stream['live_id'] ?? 0));

            if ($peerId === '' || $creatorPresence === null || (string) ($creatorPresence['peer_id'] ?? '') !== $peerId) {
                $stream['status'] = 'idle';
                $stream['broadcaster_peer_id'] = '';
                $stream['updated_at'] = date('Y-m-d H:i:s');
                $changed = true;
            }
        }
        unset($stream);

        if ($changed) {
            $this->save('live_streams', $streams);
        }
    }

    private function baseAccessStateForLive(array $live, ?int $userId): array
    {
        $accessMode = (string) ($live['access_mode'] ?? 'public');
        $vipPriceTokens = max(1, (int) ($live['price_tokens'] ?? 0));

        if ($accessMode === 'public') {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
                'requires_vip_unlock' => false,
                'vip_unlocked' => false,
                'vip_price_tokens' => 0,
            ];
        }

        if ($userId === null) {
            return [
                'granted' => false,
                'requires_login' => true,
                'requires_subscription' => false,
                'requires_vip_unlock' => $accessMode === 'vip',
                'vip_unlocked' => false,
                'vip_price_tokens' => $accessMode === 'vip' ? $vipPriceTokens : 0,
            ];
        }

        $user = $this->findUserById($userId);
        if (! $user || (string) ($user['status'] ?? 'active') !== 'active') {
            return [
                'granted' => false,
                'requires_login' => false,
                'requires_subscription' => $accessMode === 'subscriber',
                'requires_vip_unlock' => $accessMode === 'vip',
                'vip_unlocked' => false,
                'vip_price_tokens' => $accessMode === 'vip' ? $vipPriceTokens : 0,
            ];
        }

        if ((int) ($live['creator_id'] ?? 0) === $userId || (string) ($user['role'] ?? '') === 'admin') {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
                'requires_vip_unlock' => false,
                'vip_unlocked' => false,
                'vip_price_tokens' => 0,
            ];
        }

        if ($accessMode === 'subscriber' && (string) ($user['role'] ?? '') === 'subscriber' && $this->activeSubscriptionFor($userId, (int) ($live['creator_id'] ?? 0)) !== null) {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
                'requires_vip_unlock' => false,
                'vip_unlocked' => false,
                'vip_price_tokens' => 0,
            ];
        }

        if ($accessMode === 'vip') {
            $hasUnlock = $this->hasLiveUnlock((int) ($live['id'] ?? 0), $userId);

            return [
                'granted' => $hasUnlock,
                'requires_login' => false,
                'requires_subscription' => false,
                'requires_vip_unlock' => ! $hasUnlock,
                'vip_unlocked' => $hasUnlock,
                'vip_price_tokens' => $vipPriceTokens,
            ];
        }

        return [
            'granted' => false,
            'requires_login' => false,
            'requires_subscription' => true,
            'requires_vip_unlock' => false,
            'vip_unlocked' => false,
            'vip_price_tokens' => 0,
        ];
    }

    private function accessStateForLive(array $live, ?int $userId): array
    {
        $base = $this->baseAccessStateForLive($live, $userId);
        $darkroomPriceTokens = max(0, (int) ($live['darkroom_price_tokens'] ?? 0));
        $darkroomDurationMinutes = $this->sanitizeDarkroomDurationMinutes((int) ($live['darkroom_duration_minutes'] ?? 0));
        $darkroomAvailable = $darkroomPriceTokens > 0 && $darkroomDurationMinutes > 0;
        $activeDarkroom = $darkroomAvailable ? $this->activeDarkroomForLive((int) ($live['id'] ?? 0)) : null;
        $user = $userId !== null ? $this->findUserById($userId) : null;
        $isPrivilegedViewer = $userId !== null
            && (
                (int) ($live['creator_id'] ?? 0) === $userId
                || (string) ($user['role'] ?? '') === 'admin'
            );
        $isDarkroomOwner = $activeDarkroom !== null && $userId !== null && (int) ($activeDarkroom['user_id'] ?? 0) === $userId;
        $requiresDarkroomWait = false;

        if ((bool) ($base['granted'] ?? false) && $activeDarkroom !== null && ! $isPrivilegedViewer && ! $isDarkroomOwner) {
            $base['granted'] = false;
            $requiresDarkroomWait = true;
        }

        $ownerName = trim((string) ($activeDarkroom['user']['name'] ?? ''));
        if ($ownerName === '' && $activeDarkroom !== null) {
            $ownerName = 'um espectador';
        }

        $access = $base + [
            'darkroom_available' => $darkroomAvailable,
            'darkroom_active' => $activeDarkroom !== null,
            'requires_darkroom_wait' => $requiresDarkroomWait,
            'darkroom_is_owner' => $isDarkroomOwner,
            'darkroom_price_tokens' => $darkroomPriceTokens,
            'darkroom_duration_minutes' => $darkroomDurationMinutes,
            'darkroom_remaining_seconds' => max(0, (int) ($activeDarkroom['remaining_seconds'] ?? 0)),
            'darkroom_owner_name' => $ownerName,
            'darkroom_started_at' => (string) ($activeDarkroom['started_at'] ?? ''),
            'darkroom_ends_at' => (string) ($activeDarkroom['ends_at'] ?? ''),
        ];
        $access['access_message'] = $this->liveAccessMessage($live, $access);

        return $access;
    }

    private function liveAccessMessage(array $live, array $access): string
    {
        if ((bool) ($access['requires_login'] ?? false)) {
            return (bool) ($access['requires_vip_unlock'] ?? false)
                ? 'Entre para desbloquear esta Live VIP.'
                : 'Entre para assistir esta live exclusiva.';
        }

        if ((bool) ($access['requires_subscription'] ?? false)) {
            return 'Esta live e exclusiva para assinantes ativos.';
        }

        if ((bool) ($access['requires_vip_unlock'] ?? false)) {
            return 'Desbloqueie esta Live VIP para assistir agora.';
        }

        if ((bool) ($access['requires_darkroom_wait'] ?? false)) {
            $ownerName = trim((string) ($access['darkroom_owner_name'] ?? ''));
            $remainingSeconds = max(0, (int) ($access['darkroom_remaining_seconds'] ?? 0));
            $remainingLabel = $remainingSeconds > 0 ? $this->formatLiveDuration($remainingSeconds) : 'alguns instantes';
            $prefix = $ownerName !== '' ? $ownerName . ' ativou o darkroom.' : 'O darkroom esta ativo.';

            return $prefix . ' A live volta para os demais em ' . $remainingLabel . '.';
        }

        if ((bool) ($access['darkroom_active'] ?? false) && (bool) ($access['darkroom_is_owner'] ?? false)) {
            $remainingSeconds = max(0, (int) ($access['darkroom_remaining_seconds'] ?? 0));
            $remainingLabel = $remainingSeconds > 0 ? $this->formatLiveDuration($remainingSeconds) : 'alguns instantes';

            return 'Darkroom ativo para voce. Aproveite a sala privada por ' . $remainingLabel . '.';
        }

        return 'Aguardando o criador iniciar a live.';
    }

    private function activeDarkroomForLive(int $liveId): ?array
    {
        $darkrooms = $this->liveDarkrooms();
        $changed = false;
        $nowTimestamp = time();

        foreach ($darkrooms as &$darkroom) {
            if ((int) ($darkroom['live_id'] ?? 0) !== $liveId || (string) ($darkroom['status'] ?? 'ended') !== 'active') {
                continue;
            }

            $endsAt = strtotime((string) ($darkroom['ends_at'] ?? ''));
            if ($endsAt !== false && $endsAt <= $nowTimestamp) {
                $darkroom['status'] = 'ended';
                $darkroom['ended_at'] = date('Y-m-d H:i:s', $endsAt);
                $changed = true;
            }
        }
        unset($darkroom);

        if ($changed) {
            $this->save('live_darkrooms', $darkrooms);
        }

        $active = array_values(array_filter(
            $darkrooms,
            static function (array $darkroom) use ($liveId, $nowTimestamp): bool {
                if ((int) ($darkroom['live_id'] ?? 0) !== $liveId || (string) ($darkroom['status'] ?? 'ended') !== 'active') {
                    return false;
                }

                $endsAt = strtotime((string) ($darkroom['ends_at'] ?? ''));

                return $endsAt !== false && $endsAt > $nowTimestamp;
            }
        ));

        if ($active === []) {
            return null;
        }

        usort($active, static fn (array $left, array $right): int => strcmp((string) ($right['started_at'] ?? ''), (string) ($left['started_at'] ?? '')));
        $darkroom = $active[0];
        $endsAt = strtotime((string) ($darkroom['ends_at'] ?? ''));
        $darkroom['remaining_seconds'] = $endsAt !== false ? max(0, $endsAt - time()) : 0;
        $darkroom['user'] = $this->findUserById((int) ($darkroom['user_id'] ?? 0));

        return $darkroom;
    }

    private function sanitizeDarkroomDurationMinutes(int $minutes): int
    {
        if ($minutes <= 0) {
            return 0;
        }

        return max(1, min(240, $minutes));
    }

    private function hydrateLiveRuntime(array $live): array
    {
        $state = $this->publicLiveStreamState((int) ($live['id'] ?? 0));

        return array_merge($live, [
            'viewer_count' => (int) ($state['viewer_count'] ?? (int) ($live['viewer_count'] ?? 0)),
            'stream_status' => (string) ($state['status'] ?? 'idle'),
            'broadcaster_peer_id' => (string) ($state['broadcaster_peer_id'] ?? ''),
            'broadcaster_online' => (bool) ($state['broadcaster_online'] ?? false),
            'stream_mode' => (string) ($state['stream_mode'] ?? ($live['stream_mode'] ?? 'p2p_mesh')),
            'max_bitrate_kbps' => (int) ($state['max_bitrate_kbps'] ?? ($live['max_bitrate_kbps'] ?? 1500)),
            'video_width' => (int) ($state['video_width'] ?? ($live['video_width'] ?? self::LIVE_DEFAULT_WIDTH)),
            'video_height' => (int) ($state['video_height'] ?? ($live['video_height'] ?? self::LIVE_DEFAULT_HEIGHT)),
            'video_fps' => (int) ($state['video_fps'] ?? ($live['video_fps'] ?? self::LIVE_DEFAULT_FPS)),
            'segment_duration_seconds' => (int) ($state['segment_duration_seconds'] ?? ($live['segment_duration_seconds'] ?? self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS)),
            'latest_sequence' => (int) ($state['latest_sequence'] ?? 0),
            'stream_path' => (string) ($state['path_name'] ?? ($live['stream_path'] ?? '')),
            'stream_key' => (string) ($live['stream_key'] ?? ''),
            'ingest_url' => (string) ($state['ingest_url'] ?? ''),
            'hls_url' => (string) ($state['hls_url'] ?? ''),
            'stream_ready' => (bool) ($state['ready'] ?? false),
            'video_gop_seconds' => (int) ($live['video_gop_seconds'] ?? self::LIVE_DEFAULT_GOP_SECONDS),
            'audio_bitrate_kbps' => (int) ($live['audio_bitrate_kbps'] ?? self::LIVE_DEFAULT_AUDIO_BITRATE_KBPS),
            'audio_sample_rate' => (int) ($live['audio_sample_rate'] ?? self::LIVE_DEFAULT_AUDIO_SAMPLE_RATE),
        ]);
    }

    private function publicLiveStreamState(int $liveId): array
    {
        $live = $this->findLiveById($liveId) ?? [];
        $stream = $this->findLiveStreamRecord($liveId);
        $viewerCount = $this->activeViewerCountForLive($liveId);
        $driver = $this->liveDriver();
        $mediamtxState = $driver === 'mediamtx' ? $this->mediaMtxPathState($live) : [];
        $broadcaster = $driver === 'mediamtx' ? null : $this->activeCreatorPresenceForLive($liveId);
        $status = (string) ($stream['status'] ?? ((string) ($live['status'] ?? '') === 'ended' ? 'ended' : 'idle'));

        if ($driver === 'mediamtx') {
            if ((string) ($live['status'] ?? '') === 'ended' || (string) ($stream['status'] ?? '') === 'ended') {
                $status = 'ended';
            } elseif ((string) ($live['status'] ?? '') === 'live' || (string) ($stream['status'] ?? '') === 'live') {
                $status = 'live';
            } elseif ((string) ($live['status'] ?? '') === 'scheduled' || (string) ($stream['status'] ?? '') === 'scheduled') {
                $status = 'scheduled';
            } else {
                $status = 'idle';
            }
        } elseif ($status === 'live' && $broadcaster === null) {
            $status = 'idle';
        }

        $broadcasterPeerId = $driver === 'mediamtx'
            ? ''
            : ($status === 'live' && $broadcaster !== null
                ? (string) ($broadcaster['peer_id'] ?? $stream['broadcaster_peer_id'] ?? '')
                : '');
        $pathName = trim((string) ($stream['stream_path'] ?? $live['stream_path'] ?? ''));
        $publishedPath = $driver === 'mediamtx'
            ? trim((string) ($mediamtxState['path_name'] ?? $this->mediaMtxPublishedPath($pathName)))
            : $pathName;

        return [
            'status' => $status,
            'broadcaster_peer_id' => $broadcasterPeerId,
            'broadcaster_online' => $driver === 'mediamtx' ? (bool) ($mediamtxState['ready'] ?? false) : ($status === 'live' && $broadcaster !== null),
            'viewer_count' => $viewerCount,
            'started_at' => (string) ($stream['started_at'] ?? $live['started_at'] ?? ''),
            'stopped_at' => (string) ($stream['stopped_at'] ?? $live['ended_at'] ?? ''),
            'stream_mode' => (string) ($stream['stream_mode'] ?? $live['stream_mode'] ?? $driver),
            'max_bitrate_kbps' => (int) ($stream['max_bitrate_kbps'] ?? $live['max_bitrate_kbps'] ?? self::LIVE_DEFAULT_BITRATE_KBPS),
            'video_width' => (int) ($stream['video_width'] ?? $live['video_width'] ?? self::LIVE_DEFAULT_WIDTH),
            'video_height' => (int) ($stream['video_height'] ?? $live['video_height'] ?? self::LIVE_DEFAULT_HEIGHT),
            'video_fps' => (int) ($stream['video_fps'] ?? $live['video_fps'] ?? self::LIVE_DEFAULT_FPS),
            'segment_duration_seconds' => (int) ($stream['segment_duration_seconds'] ?? $live['segment_duration_seconds'] ?? self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS),
            'latest_sequence' => (int) ($stream['latest_sequence'] ?? 0),
            'segments' => $driver === 'mediamtx' ? [] : $this->normalizeLiveSegments((array) ($stream['segments'] ?? [])),
            'path_name' => $publishedPath,
            'ingest_url' => $pathName !== '' ? $this->mediaMtxIngestUrl($pathName) : '',
            'hls_url' => $pathName !== '' ? $this->mediaMtxHlsUrl($pathName) : '',
            'ready' => (bool) ($mediamtxState['ready'] ?? false),
            'bytes_received' => (int) ($mediamtxState['bytes_received'] ?? 0),
            'source_type' => (string) ($mediamtxState['source_type'] ?? ''),
            'source_id' => (string) ($mediamtxState['source_id'] ?? ''),
        ];
    }

    private function activeViewerCountForLive(int $liveId): int
    {
        return count(array_filter(
            $this->livePresence(),
            fn (array $row): bool => (int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['role'] ?? '') === 'viewer'
                && $this->timestampAgeSeconds((string) ($row['last_seen'] ?? '')) <= self::LIVE_PRESENCE_TIMEOUT_SECONDS
        ));
    }

    private function activeCreatorPresenceForLive(int $liveId): ?array
    {
        $presence = array_values(array_filter(
            $this->livePresence(),
            fn (array $row): bool => (int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['role'] ?? '') === 'creator'
                && $this->timestampAgeSeconds((string) ($row['last_seen'] ?? '')) <= self::LIVE_PRESENCE_TIMEOUT_SECONDS
        ));

        if ($presence === []) {
            return null;
        }

        usort($presence, static fn (array $left, array $right): int => strcmp((string) ($right['last_seen'] ?? ''), (string) ($left['last_seen'] ?? '')));
        $stream = $this->findLiveStreamRecord($liveId);
        $preferredPeerId = (string) ($stream['broadcaster_peer_id'] ?? '');

        if ($preferredPeerId !== '') {
            foreach ($presence as $row) {
                if ((string) ($row['peer_id'] ?? '') === $preferredPeerId) {
                    return $row;
                }
            }
        }

        return $presence[0] ?? null;
    }

    private function findLiveStreamRecord(int $liveId): ?array
    {
        foreach ($this->liveStreams() as $row) {
            if ((int) ($row['live_id'] ?? 0) === $liveId) {
                return $row;
            }
        }

        return null;
    }

    private function findLivePresencePeer(int $liveId, string $peerId, string $sessionId, ?int $userId): ?array
    {
        foreach ($this->livePresence() as $row) {
            if (
                (int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['peer_id'] ?? '') === $peerId
                && (string) ($row['session_id'] ?? '') === $sessionId
                && (int) ($row['user_id'] ?? 0) === (int) ($userId ?? 0)
            ) {
                return $row;
            }
        }

        return null;
    }

    private function removeLivePresencePeer(int $liveId, string $peerId, string $sessionId, ?int $userId): void
    {
        $presence = array_values(array_filter(
            $this->livePresence(),
            static fn (array $row): bool => ! (
                (int) ($row['live_id'] ?? 0) === $liveId
                && (string) ($row['peer_id'] ?? '') === $peerId
                && (string) ($row['session_id'] ?? '') === $sessionId
                && (int) ($row['user_id'] ?? 0) === (int) ($userId ?? 0)
            )
        ));

        $this->save('live_presence', $presence);
    }

    private function clearLiveSignals(int $liveId): void
    {
        $signals = array_values(array_filter(
            $this->liveSignals(),
            static fn (array $row): bool => (int) ($row['live_id'] ?? 0) !== $liveId
        ));

        $this->save('live_signals', $signals);
    }

    private function updateLiveRuntimeFields(int $liveId, array $changes): void
    {
        $lives = $this->liveSessions();
        foreach ($lives as &$live) {
            if ((int) ($live['id'] ?? 0) !== $liveId) {
                continue;
            }

            $live = array_merge($live, $changes);
            break;
        }
        unset($live);

        $this->save('live_sessions', $lives);
    }

    private function normalizeLiveSegments(array $segments): array
    {
        $segments = array_values(array_filter(array_map(static function (array $segment): array {
            return [
                'sequence' => max(1, (int) ($segment['sequence'] ?? 0)),
                'url' => trim((string) ($segment['url'] ?? '')),
                'duration_ms' => max(1000, (int) ($segment['duration_ms'] ?? (self::LIVE_DEFAULT_SEGMENT_DURATION_SECONDS * 1000))),
                'mime_type' => trim((string) ($segment['mime_type'] ?? 'video/webm')),
                'bytes' => max(0, (int) ($segment['bytes'] ?? 0)),
                'created_at' => trim((string) ($segment['created_at'] ?? '')),
            ];
        }, $segments), static fn (array $segment): bool => (string) ($segment['url'] ?? '') !== ''));

        usort($segments, static fn (array $left, array $right): int => ((int) ($left['sequence'] ?? 0)) <=> ((int) ($right['sequence'] ?? 0)));

        return $segments;
    }

    private function clearLiveSegmentFiles(int $liveId): void
    {
        $stream = $this->findLiveStreamRecord($liveId);

        foreach ($this->normalizeLiveSegments((array) ($stream['segments'] ?? [])) as $segment) {
            $this->deletePublicMediaFile((string) ($segment['url'] ?? ''));
        }
    }

    private function deletePublicMediaFile(string $url): void
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return;
        }

        $path = public_path(ltrim($url, '/'));
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function creatorContentStorageLimitBytes(): int
    {
        return max(10485760, ((int) ($this->settings()['creator_content_storage_limit_mb'] ?? 50)) * 1024 * 1024);
    }

    private function verificationStatusValue(string $status): string
    {
        return match ($status) {
            'approved', 'rejected', 'pending' => $status,
            default => 'pending',
        };
    }

    private function settingsRouteForRole(string $role): string
    {
        return match ($role) {
            'creator' => '/creator/settings',
            'subscriber' => '/subscriber/settings',
            'admin' => '/admin/settings#perfil',
            default => '/',
        };
    }

    private function creatorContentUsageBytes(int $creatorId, int $excludeContentId = 0): int
    {
        $total = 0;

        foreach ($this->contentItems() as $item) {
            if ((int) ($item['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            if ($excludeContentId > 0 && (int) ($item['id'] ?? 0) === $excludeContentId) {
                continue;
            }

            $total += $this->contentStorageBytes($item);
        }

        return $total;
    }

    private function contentStorageBytes(array $item): int
    {
        $mediaFileBytes = \public_media_file_bytes((string) ($item['media_url'] ?? ''));
        $mediaBytes = $mediaFileBytes > 0 ? $mediaFileBytes : max(0, (int) ($item['media_bytes'] ?? 0));

        $thumbnailFileBytes = \public_media_file_bytes((string) ($item['thumbnail_url'] ?? ''));
        $thumbnailBytes = $thumbnailFileBytes > 0 ? $thumbnailFileBytes : max(0, (int) ($item['thumbnail_bytes'] ?? 0));

        $textBytes = strlen((string) ($item['title'] ?? '') . (string) ($item['excerpt'] ?? '') . (string) ($item['body'] ?? ''));

        return $mediaBytes + $thumbnailBytes + $textBytes;
    }

    private function clearReplayReferenceForContent(int $creatorId, array $content): void
    {
        $sourceLiveId = (int) ($content['source_live_id'] ?? 0);

        if ($sourceLiveId <= 0) {
            return;
        }

        $lives = $this->liveSessions();
        $changed = false;

        foreach ($lives as &$live) {
            if ((int) ($live['id'] ?? 0) !== $sourceLiveId || (int) ($live['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            $this->deletePublicMediaFile((string) ($live['recording_url'] ?? ''));
            $this->deletePublicMediaFile((string) ($live['recording_thumbnail_url'] ?? ''));
            $live['replay_content_id'] = 0;
            $live['recording_enabled'] = false;
            $live['recording_status'] = 'deleted';
            $live['recording_url'] = '';
            $live['recording_thumbnail_url'] = '';
            $live['recording_bytes'] = 0;
            $live['recording_thumbnail_bytes'] = 0;
            $changed = true;
            break;
        }
        unset($live);

        if ($changed) {
            $this->save('live_sessions', $lives);
        }
    }

    private function liveDriver(): string
    {
        $driver = strtolower(trim((string) ($this->config['app']['live_driver'] ?? 'segment_queue')));

        return $driver === 'mediamtx' ? 'mediamtx' : 'segment_queue';
    }

    private function generateLiveStreamKey(): string
    {
        return bin2hex(random_bytes(12));
    }

    private function buildMediaMtxPath(int $liveId, string $streamKey): string
    {
        $streamKey = trim($streamKey) !== '' ? trim($streamKey) : $this->generateLiveStreamKey();

        return 'live-' . $liveId . '-' . strtolower($streamKey);
    }

    private function mediaMtxApiUrl(string $path = ''): string
    {
        $base = rtrim((string) ($this->config['app']['mediamtx_api_url'] ?? 'http://127.0.0.1:9997'), '/');

        return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    private function mediaMtxIngestUrl(string $pathName): string
    {
        $base = rtrim((string) ($this->config['app']['mediamtx_rtmp_url'] ?? 'rtmp://127.0.0.1:1935/live'), '/');

        return $base . '/' . ltrim($pathName, '/');
    }

    private function mediaMtxHlsUrl(string $pathName): string
    {
        $base = rtrim((string) ($this->config['app']['mediamtx_hls_url'] ?? 'http://127.0.0.1:8888'), '/');

        return $base . '/' . ltrim($this->mediaMtxPublishedPath($pathName), '/') . '/index.m3u8';
    }

    private function mediaMtxPathState(array $live): array
    {
        $pathName = trim((string) ($live['stream_path'] ?? ''));
        if ($pathName === '') {
            return [];
        }

        $publishedPath = $this->mediaMtxPublishedPath($pathName);
        $payload = $this->mediaMtxApiRequest('v3/paths/list');
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        foreach ($items as $item) {
            $itemName = trim((string) ($item['name'] ?? ''));
            if (! is_array($item) || ! in_array($itemName, [$pathName, $publishedPath], true)) {
                continue;
            }

            $source = is_array($item['source'] ?? null) ? $item['source'] : [];

            return [
                'path_name' => $itemName,
                'ready' => (bool) ($item['ready'] ?? false),
                'bytes_received' => (int) ($item['bytesReceived'] ?? 0),
                'source_type' => (string) ($source['type'] ?? ($item['sourceType'] ?? '')),
                'source_id' => (string) ($source['id'] ?? ($item['sourceId'] ?? '')),
            ];
        }

        return [];
    }

    private function mediaMtxApiRequest(string $path): array
    {
        $url = $this->mediaMtxApiUrl($path);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return [];
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);

            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if (! is_string($response) || $status < 200 || $status >= 300) {
                return [];
            }

            $decoded = json_decode($response, true);

            return is_array($decoded) ? $decoded : [];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 4,
                'header' => "Accept: application/json\r\n",
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        if (! is_string($response) || trim($response) === '') {
            return [];
        }

        $decoded = json_decode($response, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function attachLatestMediaMtxRecordingToLive(int $creatorId, int $liveId, int $durationSeconds): void
    {
        // O replay automatico foi desabilitado para reduzir uso de armazenamento na VPS.
        return;

        $live = $this->findLiveById($liveId);
        if (! $live || (int) ($live['creator_id'] ?? 0) !== $creatorId || ! (bool) ($live['recording_enabled'] ?? false)) {
            return;
        }

        $pathName = trim((string) ($live['stream_path'] ?? ''));
        if ($pathName === '') {
            return;
        }

        $recordingDir = public_path('uploads/live/recordings/' . $this->mediaMtxPublishedPath($pathName));
        if (! is_dir($recordingDir)) {
            return;
        }

        $candidates = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($recordingDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (! in_array($extension, ['mp4', 'm4s', 'ts', 'webm', 'mov', 'mkv'], true)) {
                continue;
            }

            $candidates[] = $file->getPathname();
        }

        if ($candidates === []) {
            return;
        }

        usort($candidates, static fn (string $left, string $right): int => (@filemtime($right) ?: 0) <=> (@filemtime($left) ?: 0));
        $recordingPath = $candidates[0] ?? '';
        if ($recordingPath === '' || ! is_file($recordingPath)) {
            return;
        }

        $publicRoot = public_path();
        $relative = str_replace('\\', '/', ltrim(str_replace($publicRoot, '', $recordingPath), '/'));
        if ($relative === '') {
            return;
        }

        $recordingUrl = '/' . $relative;
        $mimeType = detect_uploaded_mime_type($recordingPath, 'video/mp4');

        $lives = $this->liveSessions();
        foreach ($lives as &$row) {
            if ((int) ($row['id'] ?? 0) !== $liveId || (int) ($row['creator_id'] ?? 0) !== $creatorId) {
                continue;
            }

            $row['recording_enabled'] = true;
            $row['recording_url'] = $recordingUrl;
            $row['recording_status'] = 'ready';
            $row['recording_mime_type'] = $mimeType;
            $row['recording_bytes'] = public_media_file_bytes($recordingUrl);
            $row['recording_duration_seconds'] = max(0, $durationSeconds);
            $row['recording_label'] = 'Replay automatico';
            $row['recorded_at'] = date('Y-m-d H:i:s');
            $this->upsertReplayContentFromLive($row);
            break;
        }
        unset($row);

        $this->save('live_sessions', $lives);
    }

    private function generateLivePeerId(string $role): string
    {
        return $role . '-' . bin2hex(random_bytes(8));
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);

        return $user;
    }

    private function timestampAgeSeconds(string $value, ?int $now = null): int
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return PHP_INT_MAX;
        }

        return max(0, ($now ?? time()) - $timestamp);
    }

    private function uniqueSlug(string $name, ?int $ignoreUserId = null): string
    {
        $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name), '-'));
        $slug = $base !== '' ? $base : 'criador';
        $existing = array_map(
            static fn (array $profile): string => (string) $profile['slug'],
            array_values(array_filter(
                $this->creatorProfiles(),
                static fn (array $profile): bool => $ignoreUserId === null || (int) ($profile['user_id'] ?? 0) !== $ignoreUserId
            ))
        );
        $counter = 2;

        while (in_array($slug, $existing, true)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function uniqueUsername(string $seed, ?int $ignoreUserId = null, string $fallback = 'usuario'): string
    {
        $base = $this->normalizeUsername($seed);
        if ($base === '') {
            $base = $this->normalizeUsername($fallback);
        }
        if ($base === '') {
            $base = 'usuario';
        }

        $username = $base;
        $counter = 2;

        while ($this->usernameInUse($username, $ignoreUserId)) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function mediaMtxPublishedPath(string $pathName): string
    {
        $pathName = trim($pathName, " \t\n\r\0\x0B/");
        if ($pathName === '') {
            return '';
        }

        $basePath = trim((string) parse_url((string) ($this->config['app']['mediamtx_rtmp_url'] ?? ''), PHP_URL_PATH), '/');
        if ($basePath === '') {
            return $pathName;
        }

        if ($pathName === $basePath || str_starts_with($pathName, $basePath . '/')) {
            return $pathName;
        }

        return $basePath . '/' . $pathName;
    }

    private function sortByDate(array $rows, string $field): array
    {
        usort($rows, static fn (array $left, array $right): int => strcmp((string) ($right[$field] ?? ''), (string) ($left[$field] ?? '')));

        return $rows;
    }

    private function paginateItems(array $items, int $page = 1, int $perPage = 10): array
    {
        $total = count($items);
        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        return [
            'items' => array_slice($items, ($page - 1) * $perPage, $perPage),
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'pages' => $pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $pages,
            'prev_page' => max(1, $page - 1),
            'next_page' => min($pages, $page + 1),
        ];
    }
}
