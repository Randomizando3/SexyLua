<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class CreatorController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $dashboard = $this->app->repository->creatorDashboardData((int) $this->user()['id']);

        $this->render('pages/creator/dashboard', [
            'title' => 'Dashboard do Criador',
            'data' => $dashboard,
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.dashboard',
                'creator_live_quick' => true,
            ],
        ], null);
    }

    public function content(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $creatorId = (int) $this->user()['id'];

        $this->render('pages/creator/content', [
            'title' => 'Gestao de Conteudo',
            'data' => $this->app->repository->creatorContentData($creatorId, [
                'q' => (string) $request->query('q', ''),
                'status' => (string) $request->query('status', ''),
                'kind' => (string) $request->query('kind', ''),
                'edit' => (int) $request->query('edit', 0),
            ]),
            'open_form' => (bool) $request->query('open_form', false) || (int) $request->query('edit', 0) > 0,
            'form_mode' => (string) $request->query('form_mode', ((int) $request->query('edit', 0) > 0) ? 'edit' : 'new'),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.content',
            ],
        ], null);
    }

    public function favorites(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/favorites', [
            'title' => 'Favoritos do Criador',
            'data' => $this->app->repository->creatorFavoritesData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.favorites',
            ],
        ], null);
    }

    public function messages(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $conversationId = $request->query('conversation') !== null ? (int) $request->query('conversation') : null;
        $messages = $this->app->repository->creatorConversationsData(
            (int) $this->user()['id'],
            $conversationId,
            [
                'q' => (string) $request->query('q', ''),
                'announcement' => (int) $request->query('announcement', 0),
            ]
        );

        $this->render('pages/creator/messages', [
            'title' => 'Mensagens do Criador',
            'data' => $messages,
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.messages',
            ],
        ], null);
    }

    public function memberships(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $creatorId = (int) $this->user()['id'];

        $this->render('pages/creator/memberships', [
            'title' => 'Gestao de Assinaturas',
            'data' => $this->app->repository->creatorPlansData($creatorId, [
                'q' => (string) $request->query('q', ''),
                'subscriber_status' => (string) $request->query('subscriber_status', ''),
                'plan' => (int) $request->query('plan', 0),
            ]),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.memberships',
            ],
        ], null);
    }

    public function live(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $creatorId = (int) $this->user()['id'];

        $this->render('pages/creator/live', [
            'title' => 'Configuracao de Live',
            'data' => $this->app->repository->creatorLiveData($creatorId, [
                'q' => (string) $request->query('q', ''),
                'status' => (string) $request->query('status', ''),
                'live' => (int) $request->query('live', 0),
                'page' => (int) $request->query('page', 1),
            ]),
            'open_form' => $request->query('open_form', '') === '1',
            'form_mode' => (string) $request->query('form_mode', ''),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.live',
            ],
        ], null);
    }

    public function liveStudio(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $creatorId = (int) $this->user()['id'];
        $liveId = (int) $request->query('live', 0);

        $this->render('pages/creator/live_studio', [
            'title' => 'Estudio da Live',
            'data' => $this->app->repository->creatorLiveData($creatorId, [
                'live' => $liveId,
            ]),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.live_studio',
            ],
        ], null);
    }

    public function wallet(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $wallet = $this->app->repository->creatorWalletData((int) $this->user()['id'], [
            'q' => (string) $request->query('q', ''),
            'type' => (string) $request->query('type', ''),
        ]);

        $this->render('pages/creator/wallet', [
            'title' => 'Carteira Lunar',
            'data' => $wallet,
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.wallet',
            ],
        ], null);
    }

    public function settings(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/settings', [
            'title' => 'Configuracoes do Criador',
            'data' => $this->app->repository->creatorSettingsData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.settings',
                'creator_settings' => true,
            ],
        ], null);
    }

    public function createContent(Request $request): void
    {
        $this->saveContent($request);
    }

    public function saveContent(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/content');
        $creatorId = (int) $this->user()['id'];
        $payload = $request->all();
        $contentId = (int) ($payload['id'] ?? 0);
        $kind = (string) ($payload['kind'] ?? 'gallery');
        $uploadedMediaUrl = '';
        $uploadedThumbUrl = '';
        $uploadedPackItems = [];
        $removedPackItems = [];

        if ($request->hasFile('media_file')) {
            $payload['media_bytes'] = max(0, (int) (($request->file('media_file') ?? [])['size'] ?? 0));
            $mediaPath = store_uploaded_file($request->file('media_file'), 'creator/content', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm', 'mp3', 'wav', 'm4a']);
            if ($mediaPath !== null) {
                $payload['media_url'] = $mediaPath;
                $uploadedMediaUrl = $mediaPath;
            }
        }

        if ($request->hasFile('thumbnail_file')) {
            $payload['thumbnail_bytes'] = max(0, (int) (($request->file('thumbnail_file') ?? [])['size'] ?? 0));
            $thumbPath = store_uploaded_file($request->file('thumbnail_file'), 'creator/content/thumbs', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($thumbPath !== null) {
                $payload['thumbnail_url'] = $thumbPath;
                $uploadedThumbUrl = $thumbPath;
            }
        }

        if ($kind === 'pack') {
            $existingItem = $contentId > 0 ? $this->app->repository->findCreatorContentById($creatorId, $contentId) : null;
            $packItems = array_values(array_filter((array) ($existingItem['pack_items'] ?? []), static fn (mixed $item): bool => is_array($item)));
            $existingTitles = (array) $request->input('existing_pack_titles', []);
            $removePackIds = array_map('strval', (array) $request->input('pack_remove_ids', []));
            $removedPackItems = array_values(array_filter($packItems, static fn (array $item): bool => in_array((string) ($item['id'] ?? ''), $removePackIds, true)));
            $packItems = array_values(array_filter(array_map(static function (array $item) use ($existingTitles): array {
                $itemId = (string) ($item['id'] ?? '');
                if ($itemId !== '' && array_key_exists($itemId, $existingTitles)) {
                    $title = trim((string) $existingTitles[$itemId]);
                    if ($title !== '') {
                        $item['title'] = $title;
                    }
                }

                return $item;
            }, $packItems), static fn (array $item): bool => ! in_array((string) ($item['id'] ?? ''), $removePackIds, true)));

            $newPackTitles = array_values(array_map(static fn (mixed $value): string => trim((string) $value), (array) $request->input('new_pack_titles', [])));
            $packUploads = normalize_uploaded_files($request->file('new_pack_files'));

            foreach ($packUploads as $index => $packFile) {
                $packPath = store_uploaded_file($packFile, 'creator/content/packs', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm']);
                if ($packPath === null) {
                    continue;
                }

                $newPackItem = [
                    'id' => 'pack-' . substr(bin2hex(random_bytes(8)), 0, 12),
                    'title' => $newPackTitles[$index] ?? trim((string) (($packFile['name'] ?? '') !== '' ? pathinfo((string) $packFile['name'], PATHINFO_FILENAME) : ('Item ' . ($index + 1)))),
                    'url' => $packPath,
                    'thumbnail_url' => media_is_video($packPath) ? '' : $packPath,
                    'kind' => media_is_video($packPath) ? 'video' : 'image',
                    'bytes' => max(0, (int) ($packFile['size'] ?? public_media_file_bytes($packPath))),
                ];
                $packItems[] = $newPackItem;
                $uploadedPackItems[] = $newPackItem;
            }

            $payload['pack_items'] = $packItems;
        }

        $result = $this->app->repository->saveContent($creatorId, $payload);

        if (! (bool) ($result['ok'] ?? false)) {
            delete_public_media_file($uploadedMediaUrl);
            delete_public_media_file($uploadedThumbUrl);
            foreach ($uploadedPackItems as $packItem) {
                if (is_array($packItem)) {
                    delete_public_media_file((string) ($packItem['url'] ?? ''));
                    delete_public_media_file((string) ($packItem['thumbnail_url'] ?? ''));
                }
            }
            $this->redirect('/creator/content', (string) ($result['message'] ?? 'Não foi possível salvar o conteúdo.'), 'error');
        }

        foreach ($removedPackItems as $packItem) {
            if (is_array($packItem)) {
                delete_public_media_file((string) ($packItem['url'] ?? ''));
                delete_public_media_file((string) ($packItem['thumbnail_url'] ?? ''));
            }
        }

        $this->redirect('/creator/content', isset($payload['id']) && (int) $payload['id'] > 0 ? 'Conteúdo atualizado com sucesso.' : 'Conteúdo criado com sucesso.');
    }

    public function updateContentStatus(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/content');
        $this->validateCsrf($request, $redirect);
        $ok = $this->app->repository->updateContentStatus((int) $this->user()['id'], (int) $request->input('content_id', 0), (string) $request->input('status', 'draft'));

        $this->redirect($redirect, $ok ? 'Status do conteudo atualizado.' : 'Nao foi possivel atualizar o conteudo.', $ok ? 'success' : 'error');
    }

    public function deleteContent(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/content');
        $this->validateCsrf($request, $redirect);
        $ok = $this->app->repository->deleteContent((int) $this->user()['id'], (int) $request->input('content_id', 0));

        $this->redirect($redirect, $ok ? 'Conteudo removido.' : 'Nao foi possivel remover o conteudo.', $ok ? 'success' : 'error');
    }

    public function savePlan(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/memberships');
        $this->app->repository->savePlan((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/memberships', 'Plano salvo com sucesso.');
    }

    public function deletePlan(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/memberships');
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->deletePlan((int) $this->user()['id'], (int) $request->input('plan_id', 0));

        $this->redirect($redirect, $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function updateSubscription(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/memberships');
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->updateSubscriptionAccess((int) $this->user()['id'], (int) $request->input('subscription_id', 0), $request->all());

        $this->redirect($redirect, $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function sendMemberMessage(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/memberships');
        $this->validateCsrf($request, $redirect);
        $options = [
            'unlock_price' => (int) $request->input('unlock_price', 0),
        ];

        if ($request->hasFile('attachment_file')) {
            $attachment = \store_private_uploaded_file(
                $request->file('attachment_file'),
                'messages/creator',
                ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z'],
                52428800
            );
            if ($attachment !== null) {
                $options['attachment'] = $attachment;
            }
        }

        $result = $this->app->repository->sendMessageToSubscriber(
            (int) $this->user()['id'],
            (int) $request->input('subscriber_id', 0),
            (string) $request->input('body', ''),
            $options
        );

        $this->redirect($redirect, $result['message'] ?? 'Nao foi possivel enviar a mensagem.', (bool) ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function sendConversationMessage(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $conversationId = (int) $request->input('conversation_id', 0);
        $redirect = path_with_query('/creator/messages', ['conversation' => $conversationId]);
        $expectsJson = $this->wantsJson($request);
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            if ($expectsJson) {
                $this->json([
                    'ok' => false,
                    'message' => 'Sessao expirada. Atualize a conversa e tente novamente.',
                ], 419);
            }

            $this->redirect($redirect, 'Sessao expirada. Envie o formulario novamente.', 'error');
        }
        $options = [
            'required_plan_id' => (int) $request->input('required_plan_id', 0),
            'unlock_price' => (int) $request->input('unlock_price', 0),
        ];

        if ($request->hasFile('attachment_file')) {
            $attachment = \store_private_uploaded_file(
                $request->file('attachment_file'),
                'messages/creator',
                ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z'],
                52428800
            );
            if ($attachment !== null) {
                $options['attachment'] = $attachment;
            }
        }

        $result = $this->app->repository->sendConversationMessageWithPayload(
            $conversationId,
            (int) ($this->user()['id'] ?? 0),
            (string) $request->input('body', ''),
            $options,
            (int) ($this->user()['id'] ?? 0)
        );

        if ($expectsJson) {
            $this->json([
                'ok' => (bool) ($result['ok'] ?? false),
                'message' => (string) ($result['message'] ?? 'Nao foi possivel enviar a mensagem.'),
                'chat_message' => is_array($result['message_data'] ?? null) ? $result['message_data'] : null,
                'preview_text' => (string) ($result['preview_text'] ?? ''),
            ], (bool) ($result['ok'] ?? false) ? 200 : 422);
        }

        $ok = (bool) ($result['ok'] ?? false);
        $this->redirect($redirect, $ok ? 'Mensagem enviada.' : ((string) ($result['message'] ?? 'Nao foi possivel enviar a mensagem.')), $ok ? 'success' : 'error');
    }

    public function saveLive(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/live');
        $payload = $request->all();

        if ($request->hasFile('cover_file')) {
            $coverUpload = store_cover_media_file($request->file('cover_file'), 'creator/live');
            if (is_array($coverUpload) && (bool) ($coverUpload['ok'] ?? false)) {
                $payload['cover_url'] = (string) ($coverUpload['path'] ?? '');
            } elseif (is_array($coverUpload) && trim((string) ($coverUpload['error'] ?? '')) !== '') {
                $this->redirect('/creator/live', (string) $coverUpload['error'], 'error');
            }
        }

        $live = $this->app->repository->saveLive((int) $this->user()['id'], $payload);

        $isNewInstantLive = !isset($payload['id'])
            && (string) ($payload['live_type'] ?? 'scheduled') === 'instant'
            && (int) ($live['id'] ?? 0) > 0;

        $redirect = $isNewInstantLive
            ? path_with_query('/creator/live/studio', ['live' => (int) ($live['id'] ?? 0)])
            : path_with_query('/creator/live', ['live' => (int) ($live['id'] ?? 0)]);

        $this->redirect($redirect, 'Live salva com sucesso.');
    }

    public function updateLiveStatus(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/live');
        $this->validateCsrf($request, $redirect);
        $ok = $this->app->repository->updateLiveStatus((int) $this->user()['id'], (int) $request->input('live_id', 0), (string) $request->input('status', 'scheduled'));

        $this->redirect($redirect, $ok ? 'Status da live atualizado.' : 'Nao foi possivel atualizar a live.', $ok ? 'success' : 'error');
    }

    public function deleteLive(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/live');
        $this->validateCsrf($request, $redirect);
        $ok = $this->app->repository->deleteLive((int) $this->user()['id'], (int) $request->input('live_id', 0));

        $this->redirect($redirect, $ok ? 'Live removida.' : 'Nao foi possivel remover a live.', $ok ? 'success' : 'error');
    }

    public function updateLiveStudio(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = path_with_query('/creator/live/studio', ['live' => (int) $request->input('live_id', 0)]);
        $this->validateCsrf($request, $redirect);
        $ok = $this->app->repository->updateLiveStudioSettings((int) $this->user()['id'], (int) $request->input('live_id', 0), $request->all());

        $this->redirect($redirect, $ok ? 'Configuracoes da sala atualizadas.' : 'Nao foi possivel salvar a sala.', $ok ? 'success' : 'error');
    }

    public function startLiveDarkroom(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = path_with_query('/creator/live/studio', ['live' => (int) $request->input('live_id', 0)]);
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->creatorActivateLiveDarkroom(
            (int) $request->input('live_id', 0),
            (int) $this->user()['id'],
            (int) $request->input('target_user_id', 0)
        );

        $this->redirect($redirect, (string) ($result['message'] ?? 'Nao foi possivel iniciar a darkroom.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function cancelLiveDarkroom(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = path_with_query('/creator/live/studio', ['live' => (int) $request->input('live_id', 0)]);
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->cancelLiveDarkroom((int) $request->input('live_id', 0), (int) $this->user()['id']);

        $this->redirect($redirect, (string) ($result['message'] ?? 'Nao foi possivel encerrar a darkroom.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function requestPayout(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/wallet');
        $result = $this->app->repository->requestPayout((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/wallet', $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function toggleFavorite(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/favorites');
        $this->validateCsrf($request, $redirect);
        $active = $this->app->repository->toggleFavoriteCreator((int) $this->user()['id'], (int) $request->input('creator_id', 0));

        $this->redirect($redirect, $active ? 'Criador adicionado aos favoritos.' : 'Criador removido dos favoritos.');
    }

    public function toggleSaved(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $redirect = (string) $request->input('redirect', '/creator/favorites');
        $this->validateCsrf($request, $redirect);
        $active = $this->app->repository->toggleSavedContent((int) $this->user()['id'], (int) $request->input('content_id', 0));

        $this->redirect($redirect, $active ? 'Conteudo salvo com sucesso.' : 'Conteudo removido dos salvos.');
    }

    public function updateSettings(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/settings');
        $payload = $request->all();
        $payload['payout_method'] = 'pix';

        foreach (['avatar_url', 'cover_url'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = trim((string) ($payload[$field] ?? ''));
                if ($payload[$field] !== '') {
                    $payload[$field] = media_url((string) $payload[$field]);
                }
            }
        }

        if ((string) ($payload['new_password'] ?? '') !== '' && (string) ($payload['new_password'] ?? '') !== (string) ($payload['new_password_confirmation'] ?? '')) {
            $this->redirect('/creator/settings', 'Confirme a nova senha corretamente.', 'error');
        }

        if ($request->hasFile('avatar_file')) {
            $avatarPath = store_uploaded_file($request->file('avatar_file'), 'creator/profile/avatar', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($avatarPath !== null) {
                $payload['avatar_url'] = $avatarPath;
            }
        }

        if ($request->hasFile('cover_file')) {
            $coverUpload = store_cover_media_file($request->file('cover_file'), 'creator/profile/cover');
            if (is_array($coverUpload) && (bool) ($coverUpload['ok'] ?? false)) {
                $payload['cover_url'] = (string) ($coverUpload['path'] ?? '');
            } elseif (is_array($coverUpload) && trim((string) ($coverUpload['error'] ?? '')) !== '') {
                $this->redirect('/creator/settings', (string) $coverUpload['error'], 'error');
            }
        }

        if ($request->hasFile('identity_document_file')) {
            $identityDocument = store_private_uploaded_file(
                $request->file('identity_document_file'),
                'users/identity',
                ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                10485760
            );

            if ($identityDocument !== null) {
                $payload['identity_document'] = $identityDocument;
            }
        }

        $ok = $this->app->repository->updateCreatorSettings((int) $this->user()['id'], $payload);

        $this->redirect('/creator/settings', $ok ? 'Configuracoes atualizadas com sucesso.' : 'Nao foi possivel salvar as configuracoes.', $ok ? 'success' : 'error');
    }
}
