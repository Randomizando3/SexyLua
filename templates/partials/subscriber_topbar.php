<?php

declare(strict_types=1);

$appTopbarUser = is_array($subscriberTopbarUser ?? null) ? $subscriberTopbarUser : ($subscriber ?? []);
$appTopbarRole = 'subscriber';
$appTopbarSearch = (string) ($subscriberTopbarSearch ?? '');
$appTopbarAction = is_array($subscriberTopbarAction ?? null) ? $subscriberTopbarAction : null;
$appTopbarSettingsHref = '/subscriber/settings';
$appTopbarAccountLabel = 'Perfil do assinante';
$appTopbarMessagesHref = '/subscriber/messages';
$appTopbarNotificationsHref = '/subscriber';

require BASE_PATH . '/templates/partials/app_topbar.php';
