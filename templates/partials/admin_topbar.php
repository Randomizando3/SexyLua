<?php

declare(strict_types=1);

$appTopbarUser = $adminTopbarUser ?? ($app->auth->user() ?? []);
$appTopbarRole = 'admin';
$appTopbarSearch = (string) ($adminTopbarSearch ?? '');
$appTopbarAction = is_array($adminTopbarAction ?? null) ? $adminTopbarAction : null;
$appTopbarSettingsHref = '/admin/settings#perfil';
$appTopbarAccountLabel = 'Perfil do admin';
$appTopbarMessagesHref = '/admin/messages';
$appTopbarNotificationsHref = '/admin';

require BASE_PATH . '/templates/partials/app_topbar.php';
