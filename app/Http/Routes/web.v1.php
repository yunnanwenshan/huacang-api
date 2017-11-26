<?php

/******************************************************************************

用户模块(/web/v1/user/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'web/v1/user',
    'namespace'     => 'Auth',
    'middleware'    => ['wauth'],
], function () {
    // 发验证码
    Route::any('seccode',           ['uses' => 'UserController@sendSeccode',           'middleware' => []]);
    // 用户登录
    Route::any('login',             ['uses' => 'UserController@login',                 'middleware' => []]);
    // 获取用户信息
    Route::any('certificate',       ['uses' => 'UserController@certificate',           'middleware' => 'wauth.kol']);
    // 用户详情
    Route::any('detail',            ['uses' => 'UserController@getUserInfo',           'middleware' => 'wauth.kol']);
});

/******************************************************************************

购物车模块(/web/v1/cart/)

 ******************************************************************************/
Route::group([
    'prefix' => 'web/v1/shopping/cart',
    'middleware' => ['wauth'],
], function () {
    // 购物车详情
    Route::any('detail',              ['uses' => 'CartController@cartDetail',               'middleware' => 'wauth.kol']);
    // 增加新商品到购物车
    Route::any('add',                 ['uses' => 'CartController@addProductToCart',         'middleware' => 'wauth.kol']);
    // 删除购物车中的商品项
    Route::any('remove',              ['uses' => 'CartController@delProductFromCart',       'middleware' => 'wauth.kol']);
    // 增加单件商品数量
//    Route::post('incr_product',      ['uses' => 'CartController@incrProductToCart',        'middleware' => 'wauth.kol']);
    // 清空购物车
//    Route::post('clear',             ['uses' => 'CartController@clearCart',                'middleware' => 'wauth.kol']);
});

/******************************************************************************

购物车模块(/web/v1/product/)

 ******************************************************************************/
Route::group([
    'prefix' => 'web/v1/product',
    'middleware' => ['wauth'],
], function () {
    // 产品列表
    Route::any('share/list',          ['uses' => 'ProductController@productList',            'middleware' => 'wauth.kol']);
    // 产品详情
    Route::any('detail',              ['uses' => 'ProductController@productDetail',          'middleware' => 'wauth.kol']);
});

/******************************************************************************

购物车模块(/web/v1/order/)

 ******************************************************************************/
Route::group([
    'prefix' => 'web/v1/order',
    'middleware' => ['wauth'],
], function () {
    // 创建订单
    Route::any('add',                         ['uses' => 'OrderController@create',             'middleware' => 'wauth.kol']);
    // 申请取消订单
    Route::any('cancel/request',              ['uses' => 'OrderController@requestCancel',      'middleware' => 'wauth.kol']);
    // 订单详情
    Route::any('detail',                      ['uses' => 'OrderController@detail',             'middleware' => 'wauth.kol']);
    // 订单列表
    Route::any('list',                        ['uses' => 'OrderController@orderList',          'middleware' => 'wauth.kol']);
});
