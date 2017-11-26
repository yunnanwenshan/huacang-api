<?php

/**
 * 首页.
 */
Route::any('/', 'ServerController@welcome');

/*
 * 反向代理服务器心跳检测.
 */
Route::any('heartbeat', 'ServerController@heartbeat');
