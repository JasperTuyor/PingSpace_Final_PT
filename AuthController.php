<?php
session_start();

// APP_ROOT = the same folder as this index.php
define('APP_ROOT', '/home/srainsti/try.jaguimitan');

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/controllers/BaseController.php';

$route = $_GET['route'] ?? 'feed';

$routes = [
    // Auth
    'login'             => ['AuthController',         'showLogin'],
    'login.post'        => ['AuthController',         'login'],
    'register'          => ['AuthController',         'showRegister'],
    'register.post'     => ['AuthController',         'register'],
    'logout'            => ['AuthController',         'logout'],

    // Feed / Posts
    'feed'              => ['PostController',          'feed'],
    'post.create'       => ['PostController',          'createPost'],
    'post.update'       => ['PostController',          'updatePost'],
    'post.delete'       => ['PostController',          'deletePost'],
    'post.like'         => ['PostController',          'toggleLike'],

    // Comments
    'comment.add'       => ['PostController',          'addComment'],
    'comment.edit'      => ['PostController',          'editComment'],
    'comment.delete'    => ['PostController',          'deleteComment'],
    'comment.get'       => ['PostController',          'getComments'],

    // Profile
    'profile'           => ['ProfileController',       'view'],
    'profile.edit'      => ['ProfileController',       'editForm'],
    'profile.update'    => ['ProfileController',       'update'],
    'follow.toggle'     => ['ProfileController',       'toggleFollow'],
    'darkmode.toggle'   => ['ProfileController',       'toggleDarkMode'],

    // Messages
    'messages'          => ['MessageController',       'inbox'],
    'messages.view'     => ['MessageController',       'conversation'],
    'messages.send'     => ['MessageController',       'send'],
    'messages.poll'     => ['MessageController',       'poll'],

    // Notifications
    'notifications'     => ['NotificationController',  'index'],
    'notifications.poll'=> ['NotificationController',  'poll'],
    'notifications.read'=> ['NotificationController',  'markRead'],

    // Search (NEW ROUTE)
    'search'            => ['SearchController',        'index'],
];

// POST routes mapping
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postRouteMap = [
        'login'          => 'login.post',
        'register'       => 'register.post',
        'profile.update' => 'profile.update',
    ];
    if (isset($postRouteMap[$route])) {
        $route = $postRouteMap[$route];
    }
}

if (!isset($routes[$route])) {
    $route = 'feed';
}

[$controllerName, $method] = $routes[$route];
$controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    die("Controller not found: $controllerName");
}
require_once $controllerFile;

$controller = new $controllerName();
$controller->$method();