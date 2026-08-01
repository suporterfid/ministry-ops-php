<?php
// Ministry Ops PHP - Front Controller Entry Point

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Router.php';

$router = new Router();

// Public Auth Routes
$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'handleLogin']);
$router->get('/register', ['AuthController', 'showRegister']);
$router->post('/register', ['AuthController', 'handleRegister']);
$router->post('/logout', ['AuthController', 'handleLogout']);

// Tenant Context Routes
$router->get('/tenant/join', ['AuthController', 'showJoinTenant']);
$router->post('/tenant/join', ['AuthController', 'handleJoinTenant']);
$router->post('/tenant/select', ['AuthController', 'handleSelectTenant']);

// Volunteer Main App Routes
$router->get('/', ['DashboardController', 'index']);
$router->get('/dashboard', ['DashboardController', 'index']);

$router->get('/schedule', ['ScheduleController', 'index']);
$router->post('/schedule/confirm', ['ScheduleController', 'handleConfirm']);
$router->post('/schedule/decline', ['ScheduleController', 'handleDecline']);

$router->get('/swaps', ['SwapsController', 'index']);
$router->post('/swaps/create', ['SwapsController', 'handleCreateRequest']);
$router->post('/swaps/cover', ['SwapsController', 'handleCoverOffer']);

$router->get('/checkin', ['CheckinController', 'index']);
$router->post('/checkin', ['CheckinController', 'handleCheckin']);

$router->get('/bulletins', ['BulletinsController', 'index']);
$router->post('/bulletins/acknowledge', ['BulletinsController', 'handleAcknowledge']);

$router->get('/gamification', ['GamificationController', 'index']);

$router->get('/profile', ['ProfileController', 'index']);
$router->post('/profile/update', ['ProfileController', 'handleUpdate']);

// Admin Console Routes
$router->get('/admin/dashboard', ['AdminController', 'dashboard']);
$router->get('/admin/members', ['AdminController', 'members']);
$router->post('/admin/join-request/review', ['AdminController', 'handleReviewJoinRequest']);
$router->get('/admin/confirmations', ['AdminController', 'confirmations']);
$router->post('/admin/swap/approve', ['AdminController', 'handleApproveSwap']);
$router->post('/admin/bulletin/create', ['AdminController', 'handleCreateBulletin']);

$router->get('/admin/operations', ['AdminController', 'operations']);
$router->post('/admin/operation/create', ['AdminController', 'handleCreateOperation']);
$router->post('/admin/event/create', ['AdminController', 'handleCreateEvent']);
$router->post('/admin/shift/create', ['AdminController', 'handleCreateShift']);
$router->post('/admin/assignment/create', ['AdminController', 'handleCreateAssignment']);
$router->get('/admin/attendance', ['AdminController', 'attendance']);

// Dispatch Request
$router->dispatch();
