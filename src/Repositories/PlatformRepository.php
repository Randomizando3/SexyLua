<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\StoreInterface;
use App\Support\SeedFactory;

final class PlatformRepository
{
    private const LIVE_PRESENCE_TIMEOUT_SECONDS = 90;
    private const LIVE_SIGNAL_TIMEOUT_SECONDS = 180;

    public function __construct(
        private readonly StoreInterface $store,
        private readonly array $config,
    ) {
    }

    public function seedIfMissing(): void
    {
        foreach (SeedFactory::build() as $collection => $payload) {
            if (! $this->store->exists($collection)) {
                $this->store->write($collection, $payload);
            }
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

    public function registerUser(array $data): array
    {
        $users = $this->users();
        $userId = $this->store->nextId($users);
        $role = in_array(($data['role'] ?? 'subscriber'), ['subscriber', 'creator'], true) ? $data['role'] : 'subscriber';

        $user = [
            'id' => $userId,
            'name' => trim((string) ($data['name'] ?? 'Novo Usuario')),
            'email' => mb_strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'active',
            'headline' => $role === 'creator' ? 'Novo criador em fase de estreia.' : 'Novo assinante da comunidade SexyLua.',
            'bio' => $role === 'creator' ? 'Perfil criado para publicar conteudo, planos e lives.' : 'Perfil criado para acompanhar criadores, salvar colecoes e conversar.',
            'city' => trim((string) ($data['city'] ?? 'Brasil')),
            'created_at' => date('Y-m-d H:i:s'),
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
            $transactions = $this->walletTransactions();
            $transactions[] = [
                'id' => $this->store->nextId($transactions),
                'user_id' => $userId,
                'type' => 'welcome_bonus',
                'direction' => 'in',
                'amount' => 120,
                'note' => 'Bonus de boas-vindas',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->save('wallet_transactions', $transactions);
        }

        return $this->sanitizeUser($user);
    }

    public function homepageData(): array
    {
        $creators = array_values(array_filter($this->creators(), static fn (array $creator): bool => (bool) ($creator['featured'] ?? false)));
        $liveNow = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => $live['status'] === 'live'));
        $featuredContent = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => $item['status'] === 'approved'));

        return [
            'featured_creators' => array_slice($creators, 0, 5),
            'live_now' => array_slice($liveNow, 0, 4),
            'featured_content' => array_slice($featuredContent, 0, 6),
            'stats' => [
                'creators' => count($this->creators()),
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

        $creators = array_values(array_filter($this->creators(), function (array $creator) use ($query): bool {
            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower($creator['name'] . ' ' . $creator['headline'] . ' ' . $creator['bio']);

            return str_contains($haystack, $query);
        }));

        $content = array_values(array_filter($this->contentsWithCreators(), function (array $item) use ($query, $kind): bool {
            if ($item['status'] !== 'approved') {
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

        $lives = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => in_array($live['status'], ['live', 'scheduled'], true)));

        if ($liveOnly) {
            $content = array_values(array_filter($content, static fn (array $item): bool => $item['kind'] === 'live_teaser'));
        }

        return [
            'creators' => $creators,
            'content' => $content,
            'lives' => $lives,
            'filters' => [
                'q' => $query,
                'kind' => $kind,
                'live_only' => $liveOnly,
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

    public function creatorProfileData(int $creatorId, ?int $viewerId = null): ?array
    {
        $creator = $this->findCreatorBySlugOrId(null, $creatorId);

        if (! $creator) {
            return null;
        }

        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId && (bool) $plan['active']));
        $content = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId && $item['status'] === 'approved'));
        $relatedCreators = array_values(array_filter($this->creators(), static fn (array $item): bool => (int) $item['id'] !== $creatorId));

        return [
            'creator' => $creator,
            'plans' => $plans,
            'content' => $content,
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
        $messages = array_values(array_filter($this->liveMessages(), static fn (array $message): bool => (int) $message['live_id'] === $liveId));
        $messages = $this->sortByDate($messages, 'created_at');
        $messages = array_map(function (array $message): array {
            $sender = $this->findUserById((int) $message['sender_id']);
            $message['sender'] = $sender;

            return $message;
        }, $messages);

        $related = array_values(array_filter($this->livesWithCreators(), static fn (array $item): bool => (int) $item['id'] !== $liveId));
        $tipTransactions = array_values(array_filter($this->walletTransactions(), static fn (array $transaction): bool => $transaction['type'] === 'tip' && (int) ($transaction['creator_id'] ?? 0) === (int) $decoratedLive['creator_id']));
        $tipTransactions = $this->sortByDate($tipTransactions, 'created_at');
        $tipTransactions = array_map(function (array $transaction): array {
            $transaction['sender'] = $this->findUserById((int) $transaction['user_id']);

            return $transaction;
        }, $tipTransactions);

        $supporters = [];

        foreach ($tipTransactions as $transaction) {
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

            $supporters[$senderId]['amount'] += (int) $transaction['amount'];
        }

        usort($supporters, static fn (array $left, array $right): int => $right['amount'] <=> $left['amount']);

        return [
            'live' => $decoratedLive,
            'messages' => array_slice($messages, -20),
            'related_lives' => array_slice($related, 0, 5),
            'recent_tips' => array_slice($tipTransactions, 0, 5),
            'top_supporters' => array_slice($supporters, 0, 3),
            'can_watch' => (bool) ($access['granted'] ?? false),
            'requires_login' => (bool) ($access['requires_login'] ?? false),
            'requires_subscription' => (bool) ($access['requires_subscription'] ?? false),
            'can_chat' => $viewerId !== null && (bool) ($access['granted'] ?? false) && (bool) ($decoratedLive['chat_enabled'] ?? false),
            'can_tip' => $viewerId !== null && (bool) ($access['granted'] ?? false),
            'stream' => $this->publicLiveStreamState($liveId),
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
            || ! (bool) ($live['chat_enabled'] ?? false)
            || ! (bool) ($this->accessStateForLive($live, $userId)['granted'] ?? false)
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

        if ($selected === null) {
            $selected = $filteredConversations[0] ?? null;
        }

        $messages = [];

        if ($selected) {
            $messages = array_values(array_filter($this->messages(), static fn (array $message): bool => (int) ($message['conversation_id'] ?? 0) === (int) ($selected['id'] ?? 0)));
            $messages = $this->sortByDate($messages, 'created_at');
            $messages = array_map(function (array $message): array {
                $message['sender'] = $this->findUserById((int) ($message['sender_id'] ?? 0));

                return $message;
            }, $messages);
        }

        return [
            'subscriber' => $this->findUserById($subscriberId),
            'conversations' => $conversations,
            'filtered_conversations' => $filteredConversations,
            'selected_conversation' => $selected,
            'messages' => $messages,
            'filters' => [
                'q' => $query,
            ],
            'summary' => [
                'conversation_count' => count($conversations),
                'visible_count' => count($filteredConversations),
            ],
        ];
    }

    public function walletData(int $userId, array $filters = []): array
    {
        $transactions = $this->walletTransactionsFor($userId);
        $inflow = 0;
        $outflow = 0;
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $type = trim((string) ($filters['type'] ?? ''));

        foreach ($transactions as $transaction) {
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
            array_filter($transactions, static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'top_up'),
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
            'note' => 'Recarga manual de tokens',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->save('wallet_transactions', $transactions);

        return true;
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

        return ['ok' => true, 'message' => 'Assinatura ativada com sucesso.'];
    }

    public function cancelSubscription(int $subscriberId, int $subscriptionId): bool
    {
        $subscriptions = $this->subscriptions();
        $changed = false;

        foreach ($subscriptions as &$subscription) {
            if ((int) $subscription['id'] === $subscriptionId && (int) $subscription['subscriber_id'] === $subscriberId) {
                $subscription['status'] = 'cancelled';
                $changed = true;
                break;
            }
        }
        unset($subscription);

        if ($changed) {
            $this->save('subscriptions', $subscriptions);
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

    public function sendConversationMessage(int $conversationId, int $senderId, string $body): bool
    {
        $body = trim($body);

        if ($body === '') {
            return false;
        }

        $conversation = $this->findConversationById($conversationId);

        if (! $conversation) {
            return false;
        }

        $messages = $this->messages();
        $messages[] = [
            'id' => $this->store->nextId($messages),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ];
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

    public function tipCreator(int $subscriberId, int $creatorId, int $amount, string $note = 'Gorjeta enviada'): array
    {
        if ($amount <= 0) {
            return ['ok' => false, 'message' => 'Informe uma quantidade valida de tokens.'];
        }

        if ($this->walletBalance($subscriberId) < $amount) {
            return ['ok' => false, 'message' => 'Saldo insuficiente para enviar a gorjeta.'];
        }

        $this->chargeSubscriberAndCreditCreator($subscriberId, $creatorId, $amount, 'tip', $note);

        return ['ok' => true, 'message' => 'Gorjeta enviada com sucesso.'];
    }

    public function creatorDashboardData(int $creatorId): array
    {
        $contents = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId));
        $lives = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => (int) $live['creator_id'] === $creatorId));
        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId));
        $wallet = $this->creatorWalletData($creatorId);

        $approved = count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'approved'));
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
        $storageUnits = array_reduce($contents, static function (float $carry, array $item): float {
            $bodyUnits = mb_strlen((string) ($item['body'] ?? '')) / 9000;
            $mediaUnits = (string) ($item['media_url'] ?? '') !== '' ? 0.7 : 0.15;
            $thumbUnits = (string) ($item['thumbnail_url'] ?? '') !== '' ? 0.1 : 0;

            return $carry + $bodyUnits + $mediaUnits + $thumbUnits;
        }, 0.0);

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'items' => $contents,
            'filtered_items' => $filtered,
            'selected_item' => $selectedItem,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'kind' => $kind,
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
                'storage_gb' => max(0.2, round($storageUnits, 1)),
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
        $payload = [
            'title' => trim((string) ($data['title'] ?? 'Novo conteudo')),
            'excerpt' => trim((string) ($data['excerpt'] ?? 'Descricao rapida do conteudo.')),
            'body' => trim((string) ($data['body'] ?? '')),
            'visibility' => $visibility,
            'status' => $status,
            'kind' => $kind,
            'duration' => trim((string) ($data['duration'] ?? '')),
            'media_url' => trim((string) ($data['media_url'] ?? '')),
            'thumbnail_url' => trim((string) ($data['thumbnail_url'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($contentId > 0) {
            foreach ($items as &$item) {
                if ((int) $item['id'] === $contentId && (int) $item['creator_id'] === $creatorId) {
                    $item = array_merge($item, array_filter($payload, static fn (mixed $value): bool => $value !== ''));
                    $this->save('content_items', $items);

                    return $item;
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

        return $item;
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
        $items = array_values(array_filter($items, static fn (array $item): bool => ! ((int) $item['id'] === $contentId && (int) $item['creator_id'] === $creatorId)));

        if (count($items) === $before) {
            return false;
        }

        $this->save('content_items', $items);
        $savedItems = array_values(array_filter($this->savedItems(), static fn (array $saved): bool => (int) ($saved['content_id'] ?? 0) !== $contentId));
        $this->save('saved_items', $savedItems);

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
                    $plan['price_tokens'] = max(1, (int) ($data['price_tokens'] ?? $plan['price_tokens']));
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
            'price_tokens' => max(1, (int) ($data['price_tokens'] ?? 49)),
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
        $lives = $this->sortByDate($lives, 'scheduled_for');
        $lives = array_map(fn (array $live): array => $this->hydrateLiveRuntime($live), $lives);
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $selectedId = (int) ($filters['live'] ?? 0);

        $filtered = array_values(array_filter($lives, static function (array $live) use ($query, $status): bool {
            if ($status !== '' && (string) ($live['status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower((string) ($live['title'] ?? '') . ' ' . (string) ($live['description'] ?? ''));

            return str_contains($haystack, $query);
        }));

        $selectedLive = null;
        foreach ($lives as $live) {
            if ($selectedId > 0 && (int) ($live['id'] ?? 0) === $selectedId) {
                $selectedLive = $live;
                break;
            }
        }

        $selectedLive ??= $lives[0] ?? null;
        $selectedMessages = [];
        if ($selectedLive !== null) {
            $selectedMessages = array_values(array_filter($this->liveMessages(), static fn (array $message): bool => (int) ($message['live_id'] ?? 0) === (int) ($selectedLive['id'] ?? 0)));
            $selectedMessages = $this->sortByDate($selectedMessages, 'created_at');
            $selectedMessages = array_map(function (array $message): array {
                $message['sender'] = $this->findUserById((int) ($message['sender_id'] ?? 0));

                return $message;
            }, $selectedMessages);
        }

        return [
            'creator' => $this->findCreatorBySlugOrId(null, $creatorId),
            'lives' => $lives,
            'filtered_lives' => $filtered,
            'selected_live' => $selectedLive,
            'active_live' => array_values(array_filter($lives, static fn (array $live): bool => $live['status'] === 'live'))[0] ?? null,
            'messages' => $selectedMessages,
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
            'summary' => [
                'scheduled' => count(array_filter($lives, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'scheduled')),
                'live' => count(array_filter($lives, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'live')),
                'ended' => count(array_filter($lives, static fn (array $live): bool => (string) ($live['status'] ?? '') === 'ended')),
                'chat_enabled' => count(array_filter($lives, static fn (array $live): bool => (bool) ($live['chat_enabled'] ?? false))),
            ],
        ];
    }

    public function saveLive(int $creatorId, array $data): array
    {
        $lives = $this->liveSessions();
        $liveId = isset($data['id']) ? (int) $data['id'] : 0;
        $status = in_array(($data['status'] ?? 'scheduled'), ['scheduled', 'live', 'ended'], true) ? $data['status'] : 'scheduled';
        $payload = [
            'creator_id' => $creatorId,
            'title' => trim((string) ($data['title'] ?? 'Nova live')),
            'description' => trim((string) ($data['description'] ?? 'Sessao criada pelo painel do criador.')),
            'status' => $status,
            'scheduled_for' => trim((string) ($data['scheduled_for'] ?? date('Y-m-d H:i:s', strtotime('+1 day')))),
            'viewer_count' => max(0, (int) ($data['viewer_count'] ?? 0)),
            'price_tokens' => max(0, (int) ($data['price_tokens'] ?? 0)),
            'chat_enabled' => ($data['chat_enabled'] ?? '1') === '1',
            'category' => trim((string) ($data['category'] ?? 'Chatting & Chill')),
            'access_mode' => in_array(($data['access_mode'] ?? 'public'), ['public', 'subscriber'], true) ? (string) $data['access_mode'] : 'public',
            'goal_tokens' => max(0, (int) ($data['goal_tokens'] ?? 0)),
            'cover_url' => trim((string) ($data['cover_url'] ?? '')),
            'pinned_notice' => trim((string) ($data['pinned_notice'] ?? '')),
            'recording_enabled' => ($data['recording_enabled'] ?? '0') === '1',
            'stream_mode' => 'p2p_mesh',
            'max_bitrate_kbps' => max(300, min(2500, (int) ($data['max_bitrate_kbps'] ?? 1500))),
            'video_width' => max(320, min(1920, (int) ($data['video_width'] ?? 960))),
            'video_height' => max(240, min(1080, (int) ($data['video_height'] ?? 540))),
            'video_fps' => max(12, min(30, (int) ($data['video_fps'] ?? 24))),
        ];

        if ($liveId > 0) {
            foreach ($lives as &$live) {
                if ((int) $live['id'] === $liveId && (int) $live['creator_id'] === $creatorId) {
                    if ((int) ($payload['viewer_count'] ?? 0) === 0) {
                        $payload['viewer_count'] = (int) ($live['viewer_count'] ?? 0);
                    }
                    $live = array_merge($live, array_filter($payload, static fn (mixed $value): bool => $value !== ''));
                    $this->save('live_sessions', $lives);

                    return $live;
                }
            }
            unset($live);
        }

        $live = ['id' => $this->store->nextId($lives)] + $payload;
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
                'message' => (bool) ($access['requires_login'] ?? false) ? 'Entre para acessar esta live.' : 'Esta live exige assinatura ativa.',
                'requires_login' => (bool) ($access['requires_login'] ?? false),
                'requires_subscription' => (bool) ($access['requires_subscription'] ?? false),
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
        $streams = $this->liveStreams();
        $updated = false;
        $now = date('Y-m-d H:i:s');

        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            $stream['status'] = 'live';
            $stream['broadcaster_peer_id'] = $peerId;
            $stream['updated_at'] = $now;
            $stream['started_at'] = (string) ($stream['started_at'] ?? $now);
            $stream['max_bitrate_kbps'] = max(300, min(2500, (int) ($settings['max_bitrate_kbps'] ?? $stream['max_bitrate_kbps'] ?? $live['max_bitrate_kbps'] ?? 1500)));
            $stream['video_width'] = max(320, min(1920, (int) ($settings['video_width'] ?? $stream['video_width'] ?? $live['video_width'] ?? 960)));
            $stream['video_height'] = max(240, min(1080, (int) ($settings['video_height'] ?? $stream['video_height'] ?? $live['video_height'] ?? 540)));
            $stream['video_fps'] = max(12, min(30, (int) ($settings['video_fps'] ?? $stream['video_fps'] ?? $live['video_fps'] ?? 24)));
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
                'max_bitrate_kbps' => max(300, min(2500, (int) ($settings['max_bitrate_kbps'] ?? $live['max_bitrate_kbps'] ?? 1500))),
                'video_width' => max(320, min(1920, (int) ($settings['video_width'] ?? $live['video_width'] ?? 960))),
                'video_height' => max(240, min(1080, (int) ($settings['video_height'] ?? $live['video_height'] ?? 540))),
                'video_fps' => max(12, min(30, (int) ($settings['video_fps'] ?? $live['video_fps'] ?? 24))),
            ];
        }

        $this->save('live_streams', $streams);
        $this->updateLiveRuntimeFields($liveId, [
            'status' => 'live',
            'viewer_count' => $this->activeViewerCountForLive($liveId),
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
        foreach ($streams as &$stream) {
            if ((int) ($stream['live_id'] ?? 0) !== $liveId) {
                continue;
            }

            $stream['status'] = 'ended';
            $stream['broadcaster_peer_id'] = '';
            $stream['updated_at'] = $now;
            $stream['stopped_at'] = $now;
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
        $this->updateLiveRuntimeFields($liveId, [
            'status' => 'ended',
            'viewer_count' => 0,
        ]);

        return [
            'ok' => true,
            'message' => 'Transmissao encerrada.',
            'live' => $this->hydrateLiveRuntime($this->decorateLive($this->findLiveById($liveId) ?? $live)),
            'stream' => $this->publicLiveStreamState($liveId),
        ];
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
        if ((string) ($presence['role'] ?? 'viewer') === 'viewer' && ! (bool) ($access['granted'] ?? false)) {
            return ['ok' => false, 'message' => 'Seu acesso a esta live nao esta mais ativo.'];
        }

        $signals = array_values(array_filter(
            $this->liveSignals(),
            static fn (array $signal): bool => (int) ($signal['live_id'] ?? 0) === $liveId
                && (string) ($signal['to_peer_id'] ?? '') === $peerId
                && (int) ($signal['id'] ?? 0) > $afterId
        ));
        usort($signals, static fn (array $left, array $right): int => ((int) ($left['id'] ?? 0)) <=> ((int) ($right['id'] ?? 0)));

        return [
            'ok' => true,
            'messages' => array_map(static fn (array $signal): array => [
                'id' => (int) ($signal['id'] ?? 0),
                'kind' => (string) ($signal['kind'] ?? ''),
                'from_peer_id' => (string) ($signal['from_peer_id'] ?? ''),
                'payload' => is_array($signal['payload'] ?? null) ? $signal['payload'] : [],
                'created_at' => (string) ($signal['created_at'] ?? ''),
            ], $signals),
            'live' => $this->hydrateLiveRuntime($this->decorateLive($live)),
            'stream' => $this->publicLiveStreamState($liveId),
            'viewer_count' => $this->activeViewerCountForLive($liveId),
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
        $minWithdrawal = (int) ($this->settings()['withdraw_min_tokens'] ?? 50);
        $creator = $this->findCreatorBySlugOrId(null, $creatorId);
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
            array_filter($wallet['transactions'], static fn (array $transaction): bool => (string) ($transaction['type'] ?? '') === 'tip_income'),
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
            'can_withdraw' => $wallet['balance'] >= $minWithdrawal,
            'min_withdrawal' => $minWithdrawal,
            'filters' => [
                'q' => $query,
                'type' => $type,
            ],
            'summary' => [
                'subscription_income' => $subscriptionIncome,
                'tips_income' => $tipsIncome,
                'pending_payouts' => $pendingPayouts,
                'available_brl' => round((float) $wallet['balance'] * (float) ($this->settings()['token_price_brl'] ?? 0.35), 2),
            ],
            'payout_profile' => [
                'method' => (string) ($creator['payout_method'] ?? 'pix'),
                'key' => (string) ($creator['payout_key'] ?? ''),
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
                'token_price_brl' => (float) ($settings['token_price_brl'] ?? 0.35),
                'withdraw_min_tokens' => (int) ($settings['withdraw_min_tokens'] ?? 50),
                'withdraw_max_tokens' => (int) ($settings['withdraw_max_tokens'] ?? 25000),
            ],
            'security' => [
                'has_stream_key' => trim((string) ($creator['stream_key'] ?? '')) !== '',
                'has_payout_key' => trim((string) ($creator['payout_key'] ?? '')) !== '',
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
        $avatarUrl = trim((string) ($data['avatar_url'] ?? ''));
        $coverUrl = trim((string) ($data['cover_url'] ?? ''));
        $payoutMethod = trim((string) ($data['payout_method'] ?? ''));
        $payoutKey = trim((string) ($data['payout_key'] ?? ''));
        $instagram = trim((string) ($data['instagram'] ?? ''));
        $telegram = trim((string) ($data['telegram'] ?? ''));
        $streamKey = trim((string) ($data['stream_key'] ?? ''));
        $newPassword = (string) ($data['new_password'] ?? '');

        foreach ($users as &$user) {
            if ((int) $user['id'] !== $creatorId || ($user['role'] ?? null) !== 'creator') {
                continue;
            }

            $foundCreator = true;

            if ($name !== '' && $name !== (string) $user['name']) {
                $user['name'] = $name;
                $changedUsers = true;
            }

            if ($headline !== '' && $headline !== (string) ($user['headline'] ?? '')) {
                $user['headline'] = $headline;
                $changedUsers = true;
            }

            if ($bio !== '' && $bio !== (string) ($user['bio'] ?? '')) {
                $user['bio'] = $bio;
                $changedUsers = true;
            }

            if ($city !== '' && $city !== (string) ($user['city'] ?? '')) {
                $user['city'] = $city;
                $changedUsers = true;
            }

            if ($newPassword !== '' && ! password_verify($newPassword, (string) ($user['password'] ?? ''))) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $changedUsers = true;
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

            if ($avatarUrl !== '' && $avatarUrl !== (string) ($profile['avatar_url'] ?? '')) {
                $profile['avatar_url'] = $avatarUrl;
                $changedProfiles = true;
            }

            if ($coverUrl !== '' && $coverUrl !== (string) ($profile['cover_url'] ?? '')) {
                $profile['cover_url'] = $coverUrl;
                $changedProfiles = true;
            }

            if ($payoutMethod !== '' && $payoutMethod !== (string) ($profile['payout_method'] ?? '')) {
                $profile['payout_method'] = $payoutMethod;
                $changedProfiles = true;
            }

            if ($payoutKey !== '' && $payoutKey !== (string) ($profile['payout_key'] ?? '')) {
                $profile['payout_key'] = $payoutKey;
                $changedProfiles = true;
            }

            if ($instagram !== '' && $instagram !== (string) ($profile['instagram'] ?? '')) {
                $profile['instagram'] = $instagram;
                $changedProfiles = true;
            }

            if ($telegram !== '' && $telegram !== (string) ($profile['telegram'] ?? '')) {
                $profile['telegram'] = $telegram;
                $changedProfiles = true;
            }

            if ($streamKey !== '' && $streamKey !== (string) ($profile['stream_key'] ?? '')) {
                $profile['stream_key'] = $streamKey;
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
            ];
            $changedProfiles = true;
        }

        if ($changedUsers) {
            $this->save('users', $users);
        }

        if ($changedProfiles) {
            $this->save('creator_profiles', $profiles);
        }

        return true;
    }

    public function requestPayout(int $creatorId, array $data): array
    {
        $tokens = (int) ($data['tokens'] ?? 0);
        $minWithdrawal = (int) ($this->settings()['withdraw_min_tokens'] ?? 50);
        $creator = $this->findCreatorBySlugOrId(null, $creatorId) ?? [];
        $payoutMethod = trim((string) ($data['payout_method'] ?? ($creator['payout_method'] ?? 'pix')));
        $payoutKey = trim((string) ($data['payout_key'] ?? ($creator['payout_key'] ?? '')));
        $note = trim((string) ($data['note'] ?? ''));

        if ($tokens < $minWithdrawal) {
            return ['ok' => false, 'message' => 'O valor minimo para saque nao foi atingido.'];
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

    public function adminUsersData(string|array $filters = ''): array
    {
        $filters = is_array($filters) ? $filters : ['q' => $filters];
        $search = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $role = trim((string) ($filters['role'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $allUsers = $this->users();
        $users = array_values(array_filter($allUsers, function (array $user) use ($search, $role, $status): bool {
            if ($role !== '' && (string) ($user['role'] ?? '') !== $role) {
                return false;
            }

            if ($status !== '' && (string) ($user['status'] ?? '') !== $status) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $haystack = mb_strtolower((string) (($user['name'] ?? '') . ' ' . ($user['email'] ?? '') . ' ' . ($user['role'] ?? '')));

            return str_contains($haystack, $search);
        }));

        return [
            'items' => $this->sortByDate($users, 'created_at'),
            'summary' => [
                'total' => count($allUsers),
                'creators' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['role'] ?? '') === 'creator')),
                'subscribers' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['role'] ?? '') === 'subscriber')),
                'suspended' => count(array_filter($allUsers, static fn (array $user): bool => (string) ($user['status'] ?? '') === 'suspended')),
            ],
            'filters' => [
                'q' => $search,
                'role' => $role,
                'status' => $status,
            ],
        ];
    }

    public function updateUser(int $userId, array $data): bool
    {
        $users = $this->users();
        $changed = false;

        foreach ($users as &$user) {
            if ((int) $user['id'] === $userId) {
                $user['status'] = in_array(($data['status'] ?? $user['status']), ['active', 'suspended'], true) ? $data['status'] : $user['status'];
                $user['headline'] = trim((string) ($data['headline'] ?? $user['headline']));
                if (isset($data['role']) && in_array($data['role'], ['subscriber', 'creator', 'admin'], true)) {
                    $user['role'] = $data['role'];
                }
                $changed = true;
                break;
            }
        }
        unset($user);

        if ($changed) {
            $this->save('users', $users);
        }

        return $changed;
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

        foreach ($items as &$item) {
            if ((int) $item['id'] === $contentId) {
                $item['status'] = $decision;
                if ($feedback !== '') {
                    $item['moderation_feedback'] = trim($feedback);
                }
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

    public function financeData(array $filters = []): array
    {
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
            if (in_array($transaction['type'], ['subscription', 'tip'], true) && $transaction['direction'] === 'out') {
                $subscriberSpend += (int) $transaction['amount'];
            }

            if (in_array($transaction['type'], ['subscription_income', 'tip_income'], true) && $transaction['direction'] === 'in') {
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

        return [
            'summary' => [
                'gross_volume' => $subscriberSpend,
                'creator_income' => $creatorIncome,
                'platform_result' => max(0, $subscriberSpend - $creatorIncome),
                'top_ups' => count(array_filter($transactions, static fn (array $transaction): bool => $transaction['type'] === 'top_up')),
                'pending_payout_tokens' => array_reduce($pendingPayouts, static fn (int $carry, array $transaction): int => $carry + (int) ($transaction['amount'] ?? 0), 0),
                'pending_payout_count' => count($pendingPayouts),
            ],
            'transactions' => $decorated,
            'filtered_transactions' => $filteredTransactions,
            'pending_payouts' => $pendingPayouts,
            'filters' => [
                'q' => $query,
                'type' => $type,
                'status' => $status,
            ],
        ];
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

        return true;
    }

    public function updateSettings(array $data): array
    {
        $settings = $this->settings();
        $settings['platform_fee_percent'] = max(0, min(95, (int) ($data['platform_fee_percent'] ?? $settings['platform_fee_percent'])));
        $settings['token_price_brl'] = max(0.01, (float) ($data['token_price_brl'] ?? $settings['token_price_brl']));
        $settings['withdraw_min_tokens'] = max(1, (int) ($data['withdraw_min_tokens'] ?? $settings['withdraw_min_tokens']));
        $settings['withdraw_max_tokens'] = max($settings['withdraw_min_tokens'], (int) ($data['withdraw_max_tokens'] ?? $settings['withdraw_max_tokens']));
        $settings['maintenance_mode'] = ($data['maintenance_mode'] ?? '0') === '1';
        $settings['slow_mode_seconds'] = max(0, (int) ($data['slow_mode_seconds'] ?? $settings['slow_mode_seconds']));
        $settings['auto_moderation'] = ($data['auto_moderation'] ?? '0') === '1';
        $settings['blur_sensitive_thumbs'] = ($data['blur_sensitive_thumbs'] ?? '0') === '1';
        $settings['live_chat_enabled'] = ($data['live_chat_enabled'] ?? '0') === '1';
        $settings['announcement'] = trim((string) ($data['announcement'] ?? $settings['announcement']));

        $this->save('settings', $settings);

        return $settings;
    }

    private function users(): array
    {
        return $this->store->read('users');
    }

    private function creatorProfiles(): array
    {
        return $this->store->read('creator_profiles');
    }

    private function contentItems(): array
    {
        return $this->store->read('content_items');
    }

    private function plans(): array
    {
        return $this->store->read('plans');
    }

    private function subscriptions(): array
    {
        return $this->store->read('subscriptions');
    }

    private function liveSessions(): array
    {
        return $this->store->read('live_sessions');
    }

    private function favorites(): array
    {
        return $this->store->read('favorites');
    }

    private function savedItems(): array
    {
        return $this->store->read('saved_items');
    }

    private function conversations(): array
    {
        return $this->store->read('conversations');
    }

    private function messages(): array
    {
        return $this->store->read('messages');
    }

    private function liveMessages(): array
    {
        return $this->store->read('live_messages');
    }

    private function liveSignals(): array
    {
        return $this->store->read('live_signals');
    }

    private function livePresence(): array
    {
        return $this->store->read('live_presence');
    }

    private function liveStreams(): array
    {
        return $this->store->read('live_streams');
    }

    private function walletTransactions(): array
    {
        return $this->store->read('wallet_transactions');
    }

    public function settings(): array
    {
        return $this->store->read('settings');
    }

    private function save(string $collection, array $payload): void
    {
        $this->store->write($collection, $payload);
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
        $contentCount = 0;
        $subscriberCount = 0;

        foreach ($this->contentItems() as $item) {
            if ((int) $item['creator_id'] === (int) $user['id'] && $item['status'] === 'approved') {
                $contentCount++;
            }
        }

        foreach ($this->subscriptions() as $subscription) {
            if ((int) $subscription['creator_id'] === (int) $user['id'] && $subscription['status'] === 'active') {
                $subscriberCount++;
            }
        }

        return array_merge($this->sanitizeUser($user), $profile, [
            'subscriber_count' => $subscriberCount,
            'content_count' => $contentCount,
            'wallet_balance' => $this->walletBalance((int) $user['id']),
        ]);
    }

    private function decorateContent(array $item): array
    {
        return $item + ['creator' => $this->findCreatorBySlugOrId(null, (int) $item['creator_id'])];
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
        return $live + ['creator' => $this->findCreatorBySlugOrId(null, (int) $live['creator_id'])];
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

            $balance += $transaction['direction'] === 'in' ? (int) $transaction['amount'] : -1 * (int) $transaction['amount'];
        }

        return $balance;
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

    private function chargeSubscriberAndCreditCreator(int $subscriberId, int $creatorId, int $amount, string $type, string $note): void
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
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->save('wallet_transactions', $transactions);
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

            return $conversation + [
                'creator' => $creator,
                'last_message' => $lastMessage,
            ];
        }, $rows);
    }

    private function cleanupLiveRtcData(): void
    {
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

    private function accessStateForLive(array $live, ?int $userId): array
    {
        if ((string) ($live['access_mode'] ?? 'public') === 'public') {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
            ];
        }

        if ($userId === null) {
            return [
                'granted' => false,
                'requires_login' => true,
                'requires_subscription' => false,
            ];
        }

        $user = $this->findUserById($userId);
        if (! $user || (string) ($user['status'] ?? 'active') !== 'active') {
            return [
                'granted' => false,
                'requires_login' => false,
                'requires_subscription' => true,
            ];
        }

        if ((int) ($live['creator_id'] ?? 0) === $userId || (string) ($user['role'] ?? '') === 'admin') {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
            ];
        }

        if ((string) ($user['role'] ?? '') === 'subscriber' && $this->activeSubscriptionFor($userId, (int) ($live['creator_id'] ?? 0)) !== null) {
            return [
                'granted' => true,
                'requires_login' => false,
                'requires_subscription' => false,
            ];
        }

        return [
            'granted' => false,
            'requires_login' => false,
            'requires_subscription' => true,
        ];
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
            'video_width' => (int) ($state['video_width'] ?? ($live['video_width'] ?? 960)),
            'video_height' => (int) ($state['video_height'] ?? ($live['video_height'] ?? 540)),
            'video_fps' => (int) ($state['video_fps'] ?? ($live['video_fps'] ?? 24)),
        ]);
    }

    private function publicLiveStreamState(int $liveId): array
    {
        $live = $this->findLiveById($liveId) ?? [];
        $stream = $this->findLiveStreamRecord($liveId);
        $broadcaster = $this->activeCreatorPresenceForLive($liveId);
        $viewerCount = $this->activeViewerCountForLive($liveId);
        $status = (string) ($stream['status'] ?? ((string) ($live['status'] ?? '') === 'ended' ? 'ended' : 'idle'));

        if ($status === 'live' && $broadcaster === null) {
            $status = 'idle';
        }

        $broadcasterPeerId = $status === 'live' && $broadcaster !== null
            ? (string) ($broadcaster['peer_id'] ?? $stream['broadcaster_peer_id'] ?? '')
            : '';

        return [
            'status' => $status,
            'broadcaster_peer_id' => $broadcasterPeerId,
            'broadcaster_online' => $status === 'live' && $broadcaster !== null,
            'viewer_count' => $viewerCount,
            'stream_mode' => (string) ($live['stream_mode'] ?? $stream['stream_mode'] ?? 'p2p_mesh'),
            'max_bitrate_kbps' => (int) ($stream['max_bitrate_kbps'] ?? $live['max_bitrate_kbps'] ?? 1500),
            'video_width' => (int) ($stream['video_width'] ?? $live['video_width'] ?? 960),
            'video_height' => (int) ($stream['video_height'] ?? $live['video_height'] ?? 540),
            'video_fps' => (int) ($stream['video_fps'] ?? $live['video_fps'] ?? 24),
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

    private function sortByDate(array $rows, string $field): array
    {
        usort($rows, static fn (array $left, array $right): int => strcmp((string) ($right[$field] ?? ''), (string) ($left[$field] ?? '')));

        return $rows;
    }
}
