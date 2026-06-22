<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', static fn () => redirect()->to(url_to('admin.profile')));

$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    $routes->get('login', 'AuthController::login', ['as' => 'auth.login']);
    $routes->post('login', 'AuthController::attemptLogin', ['as' => 'auth.login.attempt']);
});

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('profile', 'ProfileController::show', ['as' => 'admin.profile']);
    $routes->get('dashboard', 'OrderController::index', ['as' => 'admin.dashboard']);
    $routes->post('logout', 'AuthController::logout', ['as' => 'auth.logout']);
    $routes->get('logout', 'AuthController::logout');

    $routes->get('categories', 'CategoryController::index', ['as' => 'categories.index']);
    $routes->get('categories/create', 'CategoryController::create', ['as' => 'categories.create']);
    $routes->post('categories', 'CategoryController::store', ['as' => 'categories.store']);
    $routes->get('categories/(:num)/edit', 'CategoryController::edit/$1', ['as' => 'categories.edit']);
    $routes->post('categories/(:num)', 'CategoryController::update/$1', ['as' => 'categories.update']);
    $routes->post('categories/(:num)/delete', 'CategoryController::delete/$1', ['as' => 'categories.delete']);

    $routes->get('branches', 'BranchController::index', ['as' => 'branches.index']);
    $routes->get('branches/create', 'BranchController::create', ['as' => 'branches.create']);
    $routes->post('branches', 'BranchController::store', ['as' => 'branches.store']);
    $routes->get('branches/(:num)/edit', 'BranchController::edit/$1', ['as' => 'branches.edit']);
    $routes->post('branches/(:num)', 'BranchController::update/$1', ['as' => 'branches.update']);
    $routes->post('branches/(:num)/delete', 'BranchController::delete/$1', ['as' => 'branches.delete']);

    $routes->get('warehouses', 'WarehouseController::index', ['as' => 'warehouses.index']);
    $routes->get('warehouses/create', 'WarehouseController::create', ['as' => 'warehouses.create']);
    $routes->post('warehouses', 'WarehouseController::store', ['as' => 'warehouses.store']);
    $routes->get('warehouses/(:num)/edit', 'WarehouseController::edit/$1', ['as' => 'warehouses.edit']);
    $routes->post('warehouses/(:num)', 'WarehouseController::update/$1', ['as' => 'warehouses.update']);
    $routes->post('warehouses/(:num)/delete', 'WarehouseController::delete/$1', ['as' => 'warehouses.delete']);

    $routes->get('employee', 'EmployeeController::index', ['as' => 'employee.index']);
	$routes->get('employee/create', 'EmployeeController::create', ['as' => 'employee.create']);
	$routes->post('employee', 'EmployeeController::store', ['as' => 'employee.store']);
    $routes->get('employee/(:num)/profile', 'EmployeeProfileController::show/$1', ['as' => 'employee.profile']);
    $routes->get('employee/(:num)/edit', 'EmployeeController::edit/$1', ['as' => 'employee.edit']);
    $routes->post('employee/(:num)', 'EmployeeController::update/$1', ['as' => 'employee.update']);
    $routes->post('employee/(:num)/delete', 'EmployeeController::delete/$1', ['as' => 'employee.delete']);
	
	
    $routes->get('products', 'ProductController::index', ['as' => 'products.index']);
    $routes->get('products/create', 'ProductController::create', ['as' => 'products.create']);
    $routes->post('products', 'ProductController::store', ['as' => 'products.store']);
    $routes->get('products/(:num)/edit', 'ProductController::edit/$1', ['as' => 'products.edit']);
    $routes->post('products/(:num)', 'ProductController::update/$1', ['as' => 'products.update']);
    $routes->post('products/(:num)/delete', 'ProductController::delete/$1', ['as' => 'products.delete']);

    $routes->get('product-history', 'ProductUpdateHistoryController::index', ['as' => 'product-history.index']);
	
	$routes->get('expenses', 'ExpenseController::index', ['as' => 'expenses.index']);
    $routes->get('expenses/create', 'ExpenseController::create', ['as' => 'expenses.create']);
    $routes->post('expenses', 'ExpenseController::store', ['as' => 'expenses.store']);
    $routes->get('expenses/(:num)/edit', 'ExpenseController::edit/$1', ['as' => 'expenses.edit']);
    $routes->post('expenses/(:num)', 'ExpenseController::update/$1', ['as' => 'expenses.update']);
    $routes->post('expenses/(:num)/delete', 'ExpenseController::delete/$1', ['as' => 'expenses.delete']);

});

$routes->get('test-doctrine', 'TestDoctrineController::index');
