<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\StatsController;


Route::get('/', function () {
    return 'start and this is super';
});


Route::post('/task', [TaskController::class, 'store']);
Route::post('/tag', [TagController::class, 'store']);
Route::post('/task2', [TaskController::class, 'multiStore']);

Route::get('/post', [PostController::class, 'index'])->name('main.index1');
Route::get('/post/create', [PostController::class, 'create']);
Route::get('/post/update', [PostController::class, 'update']);
Route::get('/post/delete', [PostController::class, 'delete']);

Route::get('/get/data', [StatsController::class, 'getAllData']);

Route::get('/get/user/tasks', [StatsController::class, 'infoABoutUserTags']);
Route::get('/get/user/tasks/count', [StatsController::class, 'countUsersTasks']);
Route::get('/get/user/tasks/finished', [StatsController::class, 'getUsersTaskFinishedPersentage']);

Route::get('/get/tasks/exist', [StatsController::class, 'tasksExist']);
Route::get('/get/tasks/status', [StatsController::class, 'tasksStatus']);
Route::get('/get/tasks/title', [StatsController::class, 'tasksTitle']);
Route::get('/get/tasks/before', [StatsController::class, 'getTasksBeforeDate']);
Route::get('/get/tasks/user/before', [StatsController::class, 'getTasksOfUserBeforeData']);
Route::get('/get/tasks/by/tag', [StatsController::class, 'getTasksByTag']);
Route::get('/get/tasks/by/user/tag', [StatsController::class, 'getTasksByUserAndTag']);
Route::get('/get/tasks/with/tags', [StatsController::class, 'getTasksWithTags']);
Route::get('/get/tasks/with/TimeInterval', [StatsController::class, 'getTasksWithTimeInterval']);
Route::get('/get/tasks/time/spend', [StatsController::class, 'timeSpendOnTask']);
Route::get('/get/tasks/time/average/by/tag', [StatsController::class, 'getAverageTaskTimeByTag']);
Route::get('/get/tasks/time/average/by/tag/user', [StatsController::class, 'getAverageTaskTimeByTagAndUser']);

Route::get('/get/tags', [StatsController::class, 'tagsOfUser']);
Route::get('/get/tags/by/task', [StatsController::class, 'getTagsByTask']);
Route::get('/get/tags/by/user/task', [StatsController::class, 'getTagsByUserAndTask']);


Route::get('/tester', [StatsController::class, 'd']);
