<?php
/**
 * Application Routes File
 * Declares all web and administrative endpoints.
 */

// --- Public Frontend ---
$router->get('/', 'HomeController@index');
$router->get('/sitemap.xml', 'HomeController@sitemap');
$router->post('/contact', 'HomeController@contactSubmit');

// --- Public Blog ---
$router->get('/blog', 'BlogController@frontendIndex');
$router->get('/blog/{slug}', 'BlogController@frontendPost');
$router->post('/blog/comment', 'BlogController@submitComment');

// --- Public Réalisations ---
$router->get('/realisations', 'ProjectController@publicIndex');
$router->get('/realisations/{slug}', 'ProjectController@publicShow');

// --- Admin Authentication ---
$router->get('/admin/login', 'AdminController@loginForm');
$router->post('/admin/login', 'AdminController@loginSubmit');
$router->get('/admin/logout', 'AdminController@logout');

// --- Admin Panel (Dashboard) ---
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/settings', 'AdminController@settingsForm');
$router->post('/admin/settings', 'AdminController@settingsSubmit');

// --- Admin Media Library ---
$router->get('/admin/media', 'MediaController@index');
$router->post('/admin/media/upload', 'MediaController@upload');
$router->post('/admin/media/delete', 'MediaController@delete');

// --- Admin Projects (Réalisations) CRUD Module ---
$router->get('/admin/projects', 'ProjectController@index');
$router->get('/admin/projects/create', 'ProjectController@createForm');
$router->post('/admin/projects/create', 'ProjectController@createSubmit');
$router->get('/admin/projects/edit/{id}', 'ProjectController@editForm');
$router->post('/admin/projects/edit/{id}', 'ProjectController@editSubmit');
$router->post('/admin/projects/delete/{id}', 'ProjectController@delete');

// --- Admin Page Manager & Custom Block Builder ---
$router->get('/admin/pages', 'PageController@index');
$router->get('/admin/pages/create', 'PageController@createForm');
$router->post('/admin/pages/create', 'PageController@createSubmit');
$router->get('/admin/pages/edit/{id}', 'PageController@editForm');
$router->post('/admin/pages/edit/{id}', 'PageController@editSubmit');
$router->post('/admin/pages/delete/{id}', 'PageController@deletePage');

// --- Section/Block Builders (AJAX API routes) ---
$router->post('/admin/pages/sections/add', 'PageController@addSection');
$router->post('/admin/pages/sections/sort', 'PageController@sortSections');
$router->post('/admin/pages/sections/delete', 'PageController@deleteSection');
$router->post('/admin/pages/blocks/update', 'PageController@updateBlocks');
$router->post('/admin/pages/blocks/group-add', 'PageController@addGroup');
$router->post('/admin/pages/blocks/group-delete', 'PageController@deleteGroup');

// --- Admin Messages (Contact inbox) ---
$router->get('/admin/messages', 'MessageController@index');
$router->get('/admin/messages/{id}', 'MessageController@show');
$router->post('/admin/messages/mark-read/{id}', 'MessageController@markRead');
$router->post('/admin/messages/archive/{id}', 'MessageController@archive');
$router->post('/admin/messages/delete/{id}', 'MessageController@delete');

// --- Admin Menus ---
$router->get('/admin/menus', 'MenuController@index');
$router->post('/admin/menus/create', 'MenuController@create');
$router->get('/admin/menus/edit/{id}', 'MenuController@editForm');
$router->post('/admin/menus/update/{id}', 'MenuController@updateMenu');
$router->post('/admin/menus/delete/{id}', 'MenuController@delete');
$router->post('/admin/menus/{id}/items/save', 'MenuController@saveItems');

// --- Admin Blog (Articles) ---
$router->get('/admin/blog', 'BlogController@index');
$router->get('/admin/blog/create', 'BlogController@createForm');
$router->post('/admin/blog/create', 'BlogController@createSubmit');
$router->get('/admin/blog/edit/{id}', 'BlogController@editForm');
$router->post('/admin/blog/edit/{id}', 'BlogController@editSubmit');
$router->post('/admin/blog/delete/{id}', 'BlogController@delete');
$router->get('/admin/blog/categories', 'BlogController@categories');
$router->post('/admin/blog/categories/create', 'BlogController@createCategory');
$router->post('/admin/blog/categories/delete/{id}', 'BlogController@deleteCategory');

// --- Admin Blog Comments Moderation ---
$router->get('/admin/blog/comments', 'BlogController@commentsIndex');
$router->post('/admin/blog/comments/approve/{id}', 'BlogController@approveComment');
$router->post('/admin/blog/comments/reject/{id}', 'BlogController@rejectComment');
$router->post('/admin/blog/comments/delete/{id}', 'BlogController@deleteComment');

// --- Hero Slide Builder AJAX routes ---
$router->post('/admin/pages/slides/add', 'PageController@addSlide');
$router->post('/admin/pages/slides/update', 'PageController@updateSlides');
$router->post('/admin/pages/slides/delete', 'PageController@deleteSlide');

// --- Dynamic Slug Route (Frontend Pages) ---
// This must be placed last as it captures generic parameters.
$router->get('/{slug}', 'HomeController@renderPage');
