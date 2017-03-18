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

$app->group([
    'middleware'=> 'auth'
], function ($app) {
    $app->get('/home', 'HomeController@index');

    /** 文件列表 */
    $app->get('/desk', 'FileController@desk');
    $app->get('/folder/{folderid}', 'FileController@folder');

    /** 文件 */
    $app->get('/files/{file_id}', 'FileController@index');
    $app->post('/files', 'FileController@createFile');
    $app->put('/files', 'FileController@updateFile');

    /** 批量文件移动和删除 */
    $app->delete('/files', 'FileController@deleteFiles');
    $app->post('/files/move', 'FileController@moveFiles');


});