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
    return 'Welcome to API.MRCDOC :)';
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

    /** 最近编辑文件 */
    $app->get('/recent', 'FileController@recentFiles');

    /** 回收站 */
    $app->get('/recycle', 'RecycleController@files');
    $app->post('/restore', 'RecycleController@restore');

    /** 批量文件移动和删除 */
    $app->post('/files/move', 'FileController@moveFiles');
    $app->delete('/files', 'FileController@deleteFiles');
    $app->delete('/files/destroy', 'FileController@destroyFiles');

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

    /** 个人中心 */
    $app->get('/user', 'UserController@index');
    $app->put('/user', 'UserController@update');
    $app->put('/user/password', 'UserController@changePassword');

    /** 收藏文件 */
    $app->get('/stars', 'StarController@starFilesList');
    $app->post('/stars', 'StarController@starFile');

    /** 评论 */
    $app->get('/comments/{fileId}', 'CommentController@commentList');
    $app->post('/comments', 'CommentController@createComment');
    $app->delete('/comments/{cid}', 'CommentController@deleteComment');

    /** 通知 */
    $app->get('/notices', 'NoticeController@noticesList');
    $app->delete('/notices/{id}', 'NoticeController@readNotice');
});