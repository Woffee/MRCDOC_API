<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

/** 登录 注册 */
$app->post('/login', 'AuthController@login');
$app->post('/register', 'AuthController@register');

$app->group([
    'middleware'=> 'auth'
], function ($app) {

    /** 文件列表 */
    $app->get('/desk', 'FileController@desk');
    $app->get('/folder/{folderid}', 'FileController@folder');

    /** 文件 */
    $app->get('/files/{fileid}', 'FileController@index');
    $app->post('/files', 'FileController@createFile');
    $app->put('/files', 'FileController@updateFile');

    /** 批量文件移动和删除 */
    $app->delete('/files', 'FileController@deleteFiles');
    $app->post('/files/move', 'FileController@moveFiles');

    /** 退出登录 */
    $app->get('/logout', 'AuthController@logout');

    /** 好友 */
    $app->get('/search/{search}', 'FriendController@searchFriend');
    $app->get('/friends', 'FriendController@index');
    $app->post('/friends', 'FriendController@addFriend');
    $app->post('/friends/reply', 'FriendController@replyFriend');
    $app->delete('/friends', 'FriendController@deleteFriend');

    /** 协作者 */
    $app->get('/writers', 'WriterController@writers');
    $app->post('/writers', 'WriterController@createWriter');
    $app->delete('/writers', 'WriterController@deleteWriter');
});