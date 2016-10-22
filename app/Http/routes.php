<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



Route::get('/', 'HomeController@index');
Route::get('/bdcommerce', 'HomeController@ecommerce_site');
Route::get('/categoryJobs/{id}', 'HomeController@categorywiseJobs');       
Route::any('/search', 'HomeController@searchJob');  
Route::any('/getSearchedData/{any}', 'HomeController@getSearchedData'); 
Route::any('/getProducts', 'HomeController@getProducts'); 
	
//Tutorial
Route::any('/tutorial/{any}', 'BlogController@index');
Route::any('/tutorial/{category_name}/{post_title}', 'BlogController@postDetails');



//before it was, keet it as google indexed it.
Route::any('/MySQL', 'HomeController@MySQL');
Route::any('/MySQL-Tutorial/{post_title_link}', 'BlogController@postDetails');

Route::get('/product-details/{product_name}', 'HomeController@productDetails');
Route::get('/add-to-cart/{product_id}', 'CartController@addToCart');
Route::get('/mycart', 'CartController@mycart');
Route::get('/checkout', 'CartController@checkout');
Route::post('/update-cart', 'CartController@updateCart');
Route::post('/cartItemDelete', 'CartController@cartItemDelete');
Route::any('/cartItemDeleteAll', 'CartController@cartItemDeleteAll');

//order process
Route::any('/processPayment', 'OrderController@processPayment');    
Route::post('/confirmOrder/{id}', 'OrderController@confirmOrder');

//page
Route::get('/About-me', 'PageController@aboutUs');
Route::get('/Contact-me', 'PageController@contactUs');
Route::get('/pages/{page_link}', 'PageController@pageLink');







Route::auth();

Route::get('/home', 'HomeController@index');

Route::group(['middleware' => 'admin', 'namespace' => 'Admin'], function () {           
    Route::get('/admin', 'LoginController@login');
    Route::post('/postAdminLogin', 'LoginController@postAdminLogin'); 
    Route::get('/admin/logout', 'LoginController@logout');        
    Route::get('/admin/dashboard', 'DashboardController@index');
    
	Route::get('/admin/products', 'ProductController@index');	
	Route::get('/admin/products/create', 'ProductController@create');
    Route::post('/admin/products/store', 'ProductController@store');    
    Route::get('/admin/products/edit/{id}', 'ProductController@edit');
    Route::post('/admin/products/update', 'ProductController@update');
    Route::get('/admin/products/show/{id}', 'ProductController@show');
    Route::get('/admin/products/deleteRecord/{id}', 'ProductController@deleteRecord');
    Route::get('/admin/products/deleteImageOnly/{id}/{image_name}', 'ProductController@deleteImageOnly');
	
	Route::get('/admin/categories', 'CategoryController@index');
	Route::get('/admin/categories/create', 'CategoryController@create');
	Route::post('/admin/categories/store', 'CategoryController@store');	
	Route::get('/admin/categories/edit/{id}', 'CategoryController@edit');
	Route::post('/admin/categories/update', 'CategoryController@update');
	Route::get('/admin/categories/show/{id}', 'CategoryController@show');
    Route::get('/admin/categories/deleteRecord/{id}', 'CategoryController@deleteRecord');

    Route::resource('/admin/orders', 'OrderController@index');
    Route::resource('/admin/coupons', 'CouponController@index');

    
    
    Route::get('/admin/menus', 'MenuController@index');    
    Route::get('/admin/menus/create', 'MenuController@create');
    Route::post('/admin/menus/store', 'MenuController@store');    
    Route::get('/admin/menus/edit/{id}', 'MenuController@edit');
    Route::post('/admin/menus/update', 'MenuController@update');    
    Route::get('/admin/menus/deleteRecord/{id}', 'MenuController@deleteRecord');
	
	
	
	Route::get('/admin/blog', 'BlogController@index');    
    Route::get('/admin/blog/create', 'BlogController@create');
    Route::post('/admin/blog/store', 'BlogController@store');    
    Route::get('/admin/blog/edit/{id}', 'BlogController@edit');
    Route::post('/admin/blog/update', 'BlogController@update');    
    Route::get('/admin/blog/deleteRecord/{id}', 'BlogController@deleteRecord');
	

});




