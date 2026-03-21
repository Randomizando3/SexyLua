<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\JsonStore;
use App\Support\SeedFactory;

final class PlatformRepository
{
    public function __construct(
        private readonly JsonStore $store,
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
                return $user;
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

        return $user;
    }

    public function homepageData(): array
    {
        $creators = array_values(array_filter($this->creators(), static fn (array $creator): bool => (bool) ($creator['featured'] ?? false)));
        $liveNow = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => $live['status'] === 'live'));
        $featuredContent = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => $item['status'] === 'approved'));

        return [
            'featured_creators' => array_slice($creators, 0, 4),
            'live_now' => array_slice($liveNow, 0, 3),
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
        $live = $this->findLiveById($liveId);

        if (! $live) {
            return null;
        }

        $decoratedLive = $this->decorateLive($live);
        $messages = array_values(array_filter($this->liveMessages(), static fn (array $message): bool => (int) $message['live_id'] === $liveId));
        $messages = $this->sortByDate($messages, 'created_at');
        $messages = array_map(function (array $message): array {
            $sender = $this->findUserById((int) $message['sender_id']);
            $message['sender'] = $sender;

            return $message;
        }, $messages);

        $related = array_values(array_filter($this->livesWithCreators(), static fn (array $item): bool => (int) $item['id'] !== $liveId));

        return [
            'live' => $decoratedLive,
            'messages' => array_slice($messages, -20),
            'related_lives' => array_slice($related, 0, 5),
            'can_chat' => $viewerId !== null,
            'can_tip' => $viewerId !== null,
        ];
    }

    public function postLiveMessage(int $liveId, int $userId, string $body): bool
    {
        $body = trim($body);

        if ($body === '' || ! $this->findLiveById($liveId) || ! $this->findUserById($userId)) {
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
        $activeSubscriptions = $this->activeSubscriptionsForSubscriber($subscriberId);
        $upcomingLives = array_values(array_filter($this->livesWithCreators(), function (array $live) use ($activeSubscriptions): bool {
            foreach ($activeSubscriptions as $subscription) {
                if ((int) $subscription['creator_id'] === (int) $live['creator_id'] && in_array($live['status'], ['live', 'scheduled'], true)) {
                    return true;
                }
            }

            return false;
        }));

        $favorites = $this->favoritesData($subscriberId);
        $conversations = $this->conversationList($subscriberId);

        return [
            'wallet_balance' => $this->walletBalance($subscriberId),
            'subscriptions' => array_slice($activeSubscriptions, 0, 4),
            'favorites_count' => count($favorites['favorite_creators']),
            'saved_count' => count($favorites['saved_content']),
            'conversations' => array_slice($conversations, 0, 3),
            'upcoming_lives' => array_slice($upcomingLives, 0, 4),
            'transactions' => array_slice($this->walletTransactionsFor($subscriberId), 0, 5),
        ];
    }

    public function subscriberSubscriptionsData(int $subscriberId): array
    {
        $active = $this->activeSubscriptionsForSubscriber($subscriberId);
        $activeCreatorIds = array_map(static fn (array $item): int => (int) $item['creator_id'], $active);
        $availablePlans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => ! in_array((int) $plan['creator_id'], $activeCreatorIds, true) && (bool) $plan['active']));

        return [
            'active_subscriptions' => $active,
            'available_plans' => $availablePlans,
        ];
    }

    public function favoritesData(int $subscriberId): array
    {
        $favoriteRows = array_values(array_filter($this->favorites(), static fn (array $favorite): bool => (int) $favorite['user_id'] === $subscriberId));
        $savedRows = array_values(array_filter($this->savedItems(), static fn (array $saved): bool => (int) $saved['user_id'] === $subscriberId));

        $favoriteCreators = array_map(fn (array $row): ?array => $this->findCreatorBySlugOrId(null, (int) $row['creator_id']), $favoriteRows);
        $savedContent = array_map(fn (array $row): ?array => $this->findContentWithCreator((int) $row['content_id']), $savedRows);

        return [
            'favorite_creators' => array_values(array_filter($favoriteCreators)),
            'saved_content' => array_values(array_filter($savedContent)),
        ];
    }

    public function conversationsData(int $subscriberId, ?int $conversationId = null): array
    {
        $conversations = $this->conversationList($subscriberId);
        $selected = null;

        if ($conversationId !== null) {
            foreach ($conversations as $conversation) {
                if ((int) $conversation['id'] === $conversationId) {
                    $selected = $conversation;
                    break;
                }
            }
        }

        if ($selected === null) {
            $selected = $conversations[0] ?? null;
        }

        $messages = [];

        if ($selected) {
            $messages = array_values(array_filter($this->messages(), static fn (array $message): bool => (int) $message['conversation_id'] === (int) $selected['id']));
            $messages = $this->sortByDate($messages, 'created_at');
            $messages = array_map(function (array $message): array {
                $message['sender'] = $this->findUserById((int) $message['sender_id']);

                return $message;
            }, $messages);
        }

        return [
            'conversations' => $conversations,
            'selected_conversation' => $selected,
            'messages' => $messages,
        ];
    }

    public function walletData(int $userId): array
    {
        $transactions = $this->walletTransactionsFor($userId);
        $inflow = 0;
        $outflow = 0;

        foreach ($transactions as $transaction) {
            if ($transaction['direction'] === 'in') {
                $inflow += (int) $transaction['amount'];
            } else {
                $outflow += (int) $transaction['amount'];
            }
        }

        return [
            'balance' => $this->walletBalance($userId),
            'inflow' => $inflow,
            'outflow' => $outflow,
            'transactions' => $transactions,
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

    public function creatorContentData(int $creatorId): array
    {
        $contents = array_values(array_filter($this->contentsWithCreators(), static fn (array $item): bool => (int) $item['creator_id'] === $creatorId));
        $contents = $this->sortByDate($contents, 'created_at');

        return [
            'items' => $contents,
            'counts' => [
                'approved' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'approved')),
                'pending' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'pending')),
                'draft' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'draft')),
                'rejected' => count(array_filter($contents, static fn (array $item): bool => $item['status'] === 'rejected')),
            ],
        ];
    }

    public function createContent(int $creatorId, array $data): array
    {
        $items = $this->contentItems();
        $status = in_array(($data['status'] ?? 'draft'), ['draft', 'pending'], true) ? $data['status'] : 'draft';
        $visibility = in_array(($data['visibility'] ?? 'public'), ['public', 'subscriber', 'premium'], true) ? $data['visibility'] : 'public';
        $kind = in_array(($data['kind'] ?? 'gallery'), ['gallery', 'video', 'audio', 'article', 'live_teaser'], true) ? $data['kind'] : 'gallery';

        $item = [
            'id' => $this->store->nextId($items),
            'creator_id' => $creatorId,
            'title' => trim((string) ($data['title'] ?? 'Novo conteudo')),
            'excerpt' => trim((string) ($data['excerpt'] ?? 'Descricao rapida do conteudo.')),
            'body' => trim((string) ($data['body'] ?? '')),
            'visibility' => $visibility,
            'status' => $status,
            'kind' => $kind,
            'created_at' => date('Y-m-d H:i:s'),
            'saved_count' => 0,
        ];

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

        return true;
    }

    public function creatorPlansData(int $creatorId): array
    {
        $plans = array_values(array_filter($this->plansWithCreators(), static fn (array $plan): bool => (int) $plan['creator_id'] === $creatorId));

        return [
            'plans' => $plans,
            'active_subscribers' => count(array_filter($this->subscriptions(), static fn (array $subscription): bool => (int) $subscription['creator_id'] === $creatorId && $subscription['status'] === 'active')),
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
            'perks' => $perks !== [] ? $perks : ['Conteudo exclusivo', 'Mensagens prioritarias'],
        ];
        $plans[] = $plan;
        $this->save('plans', $plans);

        return $plan;
    }

    public function creatorLiveData(int $creatorId): array
    {
        $lives = array_values(array_filter($this->livesWithCreators(), static fn (array $live): bool => (int) $live['creator_id'] === $creatorId));
        $lives = $this->sortByDate($lives, 'scheduled_for');

        return [
            'lives' => $lives,
            'active_live' => array_values(array_filter($lives, static fn (array $live): bool => $live['status'] === 'live'))[0] ?? null,
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
        ];

        if ($liveId > 0) {
            foreach ($lives as &$live) {
                if ((int) $live['id'] === $liveId && (int) $live['creator_id'] === $creatorId) {
                    $live = array_merge($live, $payload);
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
                if ($status === 'live' && (int) $live['viewer_count'] === 0) {
                    $live['viewer_count'] = random_int(20, 180);
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

    public function creatorWalletData(int $creatorId): array
    {
        $wallet = $this->walletData($creatorId);
        $minWithdrawal = (int) ($this->settings()['withdraw_min_tokens'] ?? 50);

        return $wallet + [
            'can_withdraw' => $wallet['balance'] >= $minWithdrawal,
            'min_withdrawal' => $minWithdrawal,
        ];
    }

    public function requestPayout(int $creatorId, int $tokens): array
    {
        $minWithdrawal = (int) ($this->settings()['withdraw_min_tokens'] ?? 50);

        if ($tokens < $minWithdrawal) {
            return ['ok' => false, 'message' => 'O valor minimo para saque nao foi atingido.'];
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
            'note' => 'Pedido de saque enviado pelo criador',
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
        ];
    }

    public function adminUsersData(string $search = ''): array
    {
        $search = mb_strtolower(trim($search));
        $users = array_values(array_filter($this->users(), function (array $user) use ($search): bool {
            if ($search === '') {
                return true;
            }

            $haystack = mb_strtolower($user['name'] . ' ' . $user['email'] . ' ' . $user['role']);

            return str_contains($haystack, $search);
        }));

        return $this->sortByDate($users, 'created_at');
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

    public function moderationData(): array
    {
        $content = $this->contentsWithCreators();

        return [
            'pending' => array_values(array_filter($content, static fn (array $item): bool => $item['status'] === 'pending')),
            'recent' => array_values(array_filter($content, static fn (array $item): bool => in_array($item['status'], ['approved', 'rejected'], true))),
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

    public function financeData(): array
    {
        $transactions = $this->sortByDate($this->walletTransactions(), 'created_at');
        $decorated = array_map(function (array $transaction): array {
            $transaction['user'] = $this->findUserById((int) $transaction['user_id']);
            $transaction['creator'] = isset($transaction['creator_id']) ? $this->findCreatorBySlugOrId(null, (int) $transaction['creator_id']) : null;

            return $transaction;
        }, $transactions);

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

        return [
            'summary' => [
                'gross_volume' => $subscriberSpend,
                'creator_income' => $creatorIncome,
                'platform_result' => max(0, $subscriberSpend - $creatorIncome),
                'top_ups' => count(array_filter($transactions, static fn (array $transaction): bool => $transaction['type'] === 'top_up')),
            ],
            'transactions' => $decorated,
        ];
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

        return array_merge($user, $profile, [
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

    private function uniqueSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name), '-'));
        $slug = $base !== '' ? $base : 'criador';
        $existing = array_map(static fn (array $profile): string => (string) $profile['slug'], $this->creatorProfiles());
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
