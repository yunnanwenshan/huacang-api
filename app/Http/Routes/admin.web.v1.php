<?php

/******************************************************************************

用户模块(/admin/v1/user/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/user',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 用户登录
    Route::any('login',             ['uses' => 'AdminController@login',                 'middleware' => []]);
    // 后台注册
    Route::any('register',          ['uses' => 'AdminController@registerLogin',         'middleware' => []]);
    // 用户详情
    Route::any('detail',            ['uses' => 'AdminController@getUserInfo',           'middleware' => 'admin_wauth.kol']);
});


/******************************************************************************

模版模块(/admin/v1/template/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/template',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 模版删除
    Route::any('update',          ['uses' => 'TemplateController@updateTemplate',     'middleware' => 'admin_wauth.kol']);
    // 模版编辑
    Route::any('delete',          ['uses' => 'TemplateController@deleteTemplate',     'middleware' => 'admin_wauth.kol']);
    // 增加模版
    Route::any('add',             ['uses' => 'TemplateController@addTemplate',        'middleware' => 'admin_wauth.kol']);
    // 模版列表
    Route::any('list',            ['uses' => 'TemplateController@templateList',       'middleware' => 'admin_wauth.kol']);
    //template/name/list
    Route::any('name/list',       ['uses' => 'TemplateController@nameList',           'middleware' => 'admin_wauth.kol']);
});

/******************************************************************************

订单模块(/admin/v1/order/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/order',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 取消订单
    Route::any('remove',             ['uses' => 'OrderController@Cancel',                'middleware' => 'admin_wauth.kol']);
    // 订单完成
    Route::any('finish',             ['uses' => 'OrderController@orderFinish',           'middleware' => 'admin_wauth.kol']);
    // 订单列表
    Route::any('detail/list',        ['uses' => 'OrderController@orderList',             'middleware' => 'admin_wauth.kol']);
});

/******************************************************************************

产品模块(/admin/v1/product/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/product',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 增加产品
    Route::any('add',                ['uses' => 'ProductController@addProduct',                'middleware' => 'admin_wauth.kol']);
    // 更新产品
    Route::any('update',             ['uses' => 'ProductController@updateProduct',             'middleware' => 'admin_wauth.kol']);
    // 删除产品
    Route::any('delete',             ['uses' => 'ProductController@delProduct',                'middleware' => 'admin_wauth.kol']);
    // 产品列表
    Route::any('list',               ['uses' => 'ProductController@productList',                'middleware' => 'admin_wauth.kol']);
    // 产品上架
    Route::any('selling/up',         ['uses' => 'ProductController@productSellingUp',           'middleware' => 'admin_wauth.kol']);
    // 产品下架
    Route::any('selling/down',       ['uses' => 'ProductController@productSellingDown',         'middleware' => 'admin_wauth.kol']);
    // 批量产品上架
    Route::any('selling/up/batch',   ['uses' => 'ProductController@productSellingUpBatch',      'middleware' => 'admin_wauth.kol']);
    // 批量产品下架
    Route::any('selling/down/batch', ['uses' => 'ProductController@productSellingDownBatch',     'middleware' => 'admin_wauth.kol']);
    // 全部产品详情
    Route::any('detail/all',         ['uses' => 'ProductController@productDetail',               'middleware' => 'admin_wauth.kol']);
    // 分享产品
    Route::any('share',              ['uses' => 'ProductController@shareProduct',                'middleware' => 'admin_wauth.kol']);
});

/******************************************************************************

分类模块(/admin/v1/class/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 取消订单
    Route::any('user/class/list',              ['uses' => 'ClassController@classList',             'middleware' => 'admin_wauth.kol']);
    // 订单完成
    Route::any('user/brands/list',             ['uses' => 'ClassController@brandsList',             'middleware' => 'admin_wauth.kol']);
});

/******************************************************************************

上传文件/admin/v1/upload/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/upload',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 上传文件
    Route::Post('upload',                       ['uses' => 'UploadController@uploadFile']);
});

/******************************************************************************

商城相关/admin/v1/market/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/market',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 商城列表
    Route::any('list',                       ['uses' => 'ShopController@shopList',                    'middleware' => 'admin_wauth.kol']);
    // 商城列表
    Route::any('name/list',                  ['uses' => 'ShopController@marketNameList',              'middleware' => 'admin_wauth.kol']);
    // 商城创建
    Route::any('add',                        ['uses' => 'ShopController@create',                      'middleware' => 'admin_wauth.kol']);
    // 商城更新
    Route::any('update',                     ['uses' => 'ShopController@update',                      'middleware' => 'admin_wauth.kol']);
    // 商城商品价格更新
    Route::any('price/update',               ['uses' => 'ShopController@priceUpdate',                 'middleware' => 'admin_wauth.kol']);
    // 商城更新
    Route::any('delete',                     ['uses' => 'ShopController@delete',                      'middleware' => 'admin_wauth.kol']);
    // 增加商品到商城
    Route::any('product/add',                ['uses' => 'ShopController@productAdd',                  'middleware' => 'admin_wauth.kol']);
    // 从商城移除商品
    Route::any('product/remove',             ['uses' => 'ShopController@productRemove',               'middleware' => 'admin_wauth.kol']);
});

/******************************************************************************

系统模块(/admin/v1/sys/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/sys',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 获取七牛云存储上传的key
    Route::any('upload_key',                       ['uses' => 'AdminServerController@getUploadKey']);
});


/******************************************************************************

统计相关(/admin/v1/)

 ******************************************************************************/
Route::group([
    'prefix'        => 'admin/v1/client',
    'namespace'     => 'Admin',
    'middleware'    => ['admin_wauth'],
], function () {
    // 商家用户列表
    Route::any('list',                       ['uses' => 'AdminUserController@userList',          'middleware' => 'admin_wauth.kol']);
    // 商家单个用户列表
    Route::any('info',                       ['uses' => 'AdminUserController@clientInfo',        'middleware' => 'admin_wauth.kol']);
    // 商家单个用户信息
    Route::any('info/update',                ['uses' => 'AdminUserController@updateClientInfo',   'middleware' => 'admin_wauth.kol']);
});
