<?php
/**
 * Application Routes File
 * Declares all web and administrative endpoints.
 */

// --- Public Frontend ---
$router->get('/', 'HomeController@index');
$router->get('/sitemap.xml', 'HomeController@sitemap');
$router->post('/contact', 'HomeController@contactSubmit');

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

// --- Dynamic Slug Route (Frontend Pages) ---
// This must be placed last as it captures generic parameters.
$router->get('/{slug}', 'HomeController@renderPage');
