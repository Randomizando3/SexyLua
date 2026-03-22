<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CreatorController;
use App\Controllers\LiveRtcController;
use App\Controllers\PublicController;
use App\Controllers\SubscriberController;

$router->get('/', [PublicController::class, 'home']);
$router->get('/explore', [PublicController::class, 'explore']);
$router->get('/profile', [PublicController::class, 'profile']);
$router->get('/live', [PublicController::class, 'live']);
$router->post('/live/chat', [PublicController::class, 'postLiveMessage']);
$router->get('/live/rtc/poll', [LiveRtcController::class, 'poll']);
$router->post('/live/rtc/join', [LiveRtcController::class, 'join']);
$router->post('/live/rtc/start', [LiveRtcController::class, 'start']);
$router->post('/live/rtc/stop', [LiveRtcController::class, 'stop']);
$router->post('/live/rtc/signal', [LiveRtcController::class, 'signal']);
$router->post('/live/rtc/heartbeat', [LiveRtcController::class, 'heartbeat']);
$router->post('/live/rtc/leave', [LiveRtcController::class, 'leave']);
$router->post('/live/rtc/recording', [LiveRtcController::class, 'recording']);
$router->post('/profile/subscribe', [PublicController::class, 'postSubscribe']);
$router->post('/profile/message', [PublicController::class, 'postStartConversation']);
$router->post('/tip', [PublicController::class, 'postTip']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/subscriber', [SubscriberController::class, 'dashboard']);
$router->get('/subscriber/subscriptions', [SubscriberController::class, 'subscriptions']);
$router->get('/subscriber/favorites', [SubscriberController::class, 'favorites']);
$router->get('/subscriber/messages', [SubscriberController::class, 'messages']);
$router->get('/subscriber/wallet', [SubscriberController::class, 'wallet']);
$router->get('/subscriber/settings', [SubscriberController::class, 'settings']);
$router->post('/subscriber/subscriptions/subscribe', [SubscriberController::class, 'subscribe']);
$router->post('/subscriber/subscriptions/cancel', [SubscriberController::class, 'cancelSubscription']);
$router->post('/subscriber/favorites/toggle', [SubscriberController::class, 'toggleFavorite']);
$router->post('/subscriber/saved/toggle', [SubscriberController::class, 'toggleSaved']);
$router->post('/subscriber/messages/send', [SubscriberController::class, 'sendMessage']);
$router->post('/subscriber/wallet/add-funds', [SubscriberController::class, 'addFunds']);
$router->post('/subscriber/settings/update', [SubscriberController::class, 'updateSettings']);

$router->get('/creator', [CreatorController::class, 'dashboard']);
$router->get('/creator/content', [CreatorController::class, 'content']);
$router->get('/creator/favorites', [CreatorController::class, 'favorites']);
$router->get('/creator/memberships', [CreatorController::class, 'memberships']);
$router->get('/creator/live', [CreatorController::class, 'live']);
$router->get('/creator/wallet', [CreatorController::class, 'wallet']);
$router->get('/creator/settings', [CreatorController::class, 'settings']);
$router->post('/creator/content/save', [CreatorController::class, 'saveContent']);
$router->post('/creator/content/create', [CreatorController::class, 'createContent']);
$router->post('/creator/content/status', [CreatorController::class, 'updateContentStatus']);
$router->post('/creator/content/delete', [CreatorController::class, 'deleteContent']);
$router->post('/creator/memberships/save', [CreatorController::class, 'savePlan']);
$router->post('/creator/memberships/delete', [CreatorController::class, 'deletePlan']);
$router->post('/creator/memberships/subscription', [CreatorController::class, 'updateSubscription']);
$router->post('/creator/live/save', [CreatorController::class, 'saveLive']);
$router->post('/creator/live/status', [CreatorController::class, 'updateLiveStatus']);
$router->post('/creator/live/delete', [CreatorController::class, 'deleteLive']);
$router->post('/creator/wallet/payout', [CreatorController::class, 'requestPayout']);
$router->post('/creator/favorites/toggle', [CreatorController::class, 'toggleFavorite']);
$router->post('/creator/saved/toggle', [CreatorController::class, 'toggleSaved']);
$router->post('/creator/settings/update', [CreatorController::class, 'updateSettings']);

$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/moderation', [AdminController::class, 'moderation']);
$router->get('/admin/finance', [AdminController::class, 'finance']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/users/update', [AdminController::class, 'updateUser']);
$router->post('/admin/moderation/review', [AdminController::class, 'reviewContent']);
$router->post('/admin/finance/review-payout', [AdminController::class, 'reviewPayout']);
$router->post('/admin/settings/update', [AdminController::class, 'updateSettings']);
$router->post('/admin/profile/update', [AdminController::class, 'updateProfile']);
