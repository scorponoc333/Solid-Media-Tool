<?php

ob_start();
session_start();

define('APP_ROOT', dirname(__DIR__));

// Load environment config
require_once APP_ROOT . '/config/env.php';

// Autoload core classes
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/Router.php';

// Load models
require_once APP_ROOT . '/app/models/User.php';
require_once APP_ROOT . '/app/models/Post.php';
require_once APP_ROOT . '/app/models/BrandingSetting.php';
require_once APP_ROOT . '/app/models/ContentMemory.php';

// Load services
require_once APP_ROOT . '/app/services/AIService.php';
require_once APP_ROOT . '/app/services/ZernioService.php';
require_once APP_ROOT . '/app/services/ContentMemoryService.php';
require_once APP_ROOT . '/app/services/BrandingService.php';
require_once APP_ROOT . '/app/services/ModalService.php';

// Single client mode
$GLOBALS['client_id'] = CLIENT_ID;
// Multi-client support (FUTURE)
// $GLOBALS['client_id'] = $_SESSION['client_id'] ?? null;

$router = new Router();
require_once APP_ROOT . '/config/routes.php';
$router->resolve();
