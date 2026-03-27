<?php

// Auth routes
$router->get('/login', 'AuthController', 'loginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Dashboard
$router->get('/', 'DashboardController', 'index');
$router->get('/dashboard', 'DashboardController', 'index');

// Generator
$router->get('/generator', 'GeneratorController', 'index');
$router->post('/generator/week', 'GeneratorController', 'generateWeek');
$router->post('/generator/single', 'GeneratorController', 'generateSingle');
$router->post('/generator/regenerate-text', 'GeneratorController', 'regenerateText');
$router->post('/generator/regenerate-image', 'GeneratorController', 'regenerateImage');

// Posts / Editor
$router->get('/posts', 'PostController', 'index');
$router->get('/posts/edit/{id}', 'PostController', 'edit');
$router->post('/posts/save', 'PostController', 'save');
$router->post('/posts/update/{id}', 'PostController', 'update');
$router->post('/posts/delete/{id}', 'PostController', 'delete');
$router->post('/posts/schedule/{id}', 'PostController', 'schedule');
$router->post('/posts/post-now/{id}', 'PostController', 'postNow');
$router->post('/posts/retry/{id}', 'PostController', 'retry');
$router->get('/posts/logs/{id}', 'PostController', 'logs');

// Calendar
$router->get('/calendar', 'CalendarController', 'index');
$router->get('/calendar/events', 'CalendarController', 'events');

// Reporting
$router->get('/reporting', 'ReportingController', 'index');

// Branding
$router->get('/branding', 'BrandingController', 'index');
$router->post('/branding/save', 'BrandingController', 'save');
$router->post('/branding/save-api', 'BrandingController', 'saveApi');
$router->post('/branding/test-api', 'BrandingController', 'testApi');

// Memory
$router->get('/memory', 'MemoryController', 'index');

// Documentation
$router->get('/docs', 'DocumentationController', 'index');

// API endpoints
$router->get('/api/posts', 'PostController', 'apiList');
$router->get('/api/stats', 'DashboardController', 'apiStats');
