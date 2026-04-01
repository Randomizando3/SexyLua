<?php

declare(strict_types=1);

$appTopbarUser = $creatorShellCreator ?? ($creator ?? []);
$appTopbarRole = 'creator';
$appTopbarSearch = (string) ($creatorTopbarSearch ?? '');
$appTopbarAction = is_array($creatorTopbarAction ?? null) ? $creatorTopbarAction : null;
$appTopbarSettingsHref = '/creator/settings';
$appTopbarAccountLabel = 'Perfil do criador';
$appTopbarMessagesHref = '/creator/messages';
$appTopbarNotificationsHref = '/creator';

require BASE_PATH . '/templates/partials/app_topbar.php';
