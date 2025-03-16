<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskTag;

// Получаем все теги для пользователя с id_user = 3

class StatsController extends Controller
{
    public function tagsOfUser(){
        $userId = 3;
        $tags = Tag::where('id_user', $userId)->get();

        // Выводим теги
        foreach ($tags as $tag) {
            echo $tag->tag . PHP_EOL; // или используйте другой способ отображения
        }
    }


    public function infoABoutUserTags(){
        $userId = 3;
        $tasks = Task::where('id_user', $userId)->get();
        return response()->json($tasks);
    }


    public function tasksExist(){
        $tasks = Task::select(
            'id_user',
            'title',
            'description',
            'created_at',
            'finish_at',
            DB::raw('
            EXTRACT(DAY FROM (finish_at - created_at)) AS days,
            EXTRACT(HOUR FROM (finish_at - created_at)) AS hours,
            EXTRACT(MINUTE FROM (finish_at - created_at)) AS minutes,
            EXTRACT(SECOND FROM (finish_at - created_at)) AS seconds
        ')
        )
        ->get();
    
        return response()->json($tasks);
    }


    public function tasksStatus(){
        $tasks = Task::select(
            'id_task',
            'title',
            'description',
            'status')
            ->where('status', 'Создана')
            ->get();
    
            return response()->json($tasks);
    }


    public function tasksTitle(){
        $tasks = Task::select(
            'id_task',
            'title',
            'description',
            'status')
            ->where('title', 'Пятая задача')
            ->get();
    
            return response()->json($tasks);
    }


    public function countUsersTasks()
    {
        $uniqueTaskCount = DB::table('tasks')
            ->where('id_user', 1)
            ->distinct()
            ->count('id_task');

        // Возврат только id_user и количество уникальных task_id
        return response()->json([
            'id_user' => 1,
            'unique_task_count' => $uniqueTaskCount,
        ]);
    }


    public function getTasksBeforeDate() {
        $tasks = Task::where('created_at', '<', '2025-03-12 19:33:25')->get();
    
        return response()->json($tasks);
    }


    public function getTasksOfUserBeforeData(){
        $tasks = Task::where('created_at', '<', '2025-03-12 19:30:25')
        ->where('id_user', 1)
        ->get();
    
        return response()->json($tasks);
    }
    

    public function getTasksWithTagsBeforeDate() {
        $date = '2025-03-12 19:33:25';

        $tasks = Task::with('tags')
            ->where('created_at', '<', $date)
            ->get();

        return response()->json($tasks);
    }


    public function getTasksWithTags()
    {
        $tasks = Task::with('tags')->get(); // Загружаем задачи вместе с тегами
    
        return response()->json($tasks);
    }

    
    public function getTasksByTag()
    {
        // Фильтруем задачи, связанные с tag_id = 3
        $tags = Tag::whereHas('tasks', function ($query) {
            $query->where('tags.id', 2); // Фильтр по tag_id = 3
        })->with('tasks')->get(); // Загружаем связанные теги для отображения

        // Возвращаем данные в формате JSON
        return response()->json($tags);
    }


    public function getTagsByTask()
    {
        // Фильтруем задачи, связанные с tag_id = 3
        $tasks = Task::whereHas('tags', function ($query) {
            $query->where('id_task', 1); // Фильтр по tag_id = 3
        })->with('tags')->get(); // Загружаем связанные теги для отображения

        // Возвращаем данные в формате JSON
        return response()->json($tasks);
    }


    public function getUsersTaskFinishedPersentage()
    {
        $userId = 1;

        // Получаем общее количество заданий для пользователя
        $totalTasks = Task::where('id_user', $userId)->count();

        // Получаем количество завершенных заданий для пользователя
        $finishedTasks = Task::where('id_user', $userId)
            ->whereNotNull('finish_at')
            ->count();

        // Рассчитываем процент завершенных заданий
        if ($totalTasks > 0) {
            $percentage = ($finishedTasks * 100.0) / $totalTasks;
        } else {
            $percentage = 0;
        }

        // Получаем ID завершенных заданий пользователя
        $finishedTaskIds = Task::where('id_user', $userId)
            ->whereNotNull('finish_at')
            ->pluck('id_task');


        $rest = Task::select('id_task', 'created_at', 'status', 'finish_at')
            ->where('id_user', $userId)
            ->get();
            
        // Формируем результат
        $result = [
            'user_id' => $userId,
            'percentage' => round($percentage, 2),
            'finished_task_ids' => $finishedTaskIds,
            'info' => $rest,
        ];

        // Возвращаем результат в формате JSON
        return response()->json($result);
    }

    
    public function getTasksWithTimeInterval(){
        $tasks = Task::with('time_intervals')->get();

        // Вывод данных в JSON
        return response()->json($tasks);
    }


    public function timeSpendOnTask()
    {
        $tasks = Task::select(
            'id_user',
            'title',
            'description',
            'created_at',
            'tasks.finish_at',
            DB::raw('
                EXTRACT(DAY FROM (time_intervals.finish_at - time_intervals.start_at)) AS days,
                EXTRACT(HOUR FROM (time_intervals.finish_at - time_intervals.start_at)) AS hours,
                EXTRACT(MINUTE FROM (time_intervals.finish_at - time_intervals.start_at)) AS minutes,
                EXTRACT(SECOND FROM (time_intervals.finish_at - time_intervals.start_at)) AS seconds
            ')
        )
        ->leftJoin('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
        ->get();
    
        return response()->json($tasks);
    }


    public function getTasksByUserAndTag(){
        $tasks = ['tag_is' => 1, 
        'tasks_info' => Task::where('id_user', 1)
        ->whereHas('tags', function ($query) {
            $query->where('tags.id', 1);
        })
        ->get()];

       return response()->json($tasks);
    }


    public function getTagsByUserAndTask(){
        $tasks = ['task_is' => 1, 
        'tags_info' => Tag::where('id_user', 1)
        ->whereHas('tasks', function ($query) {
            $query->where('id_task', 1);
        })
        ->get()];

       return response()->json($tasks);
    }


    public function getAverageTaskTimeByTag(){
        $averageDuration = ['tag_id' => 1,
        'info' => DB::table('tasks')
            ->join('task_tags', 'id_task', '=', 'task_tags.task_id_task')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('task_tags.tag_id', 1)
            ->select(DB::raw('
                AVG(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as average_duration
            '))
            ->get()];

        return response()->json($averageDuration);
    }


    public function getAverageTaskTimeByTagAndUser(){
        $averageDuration = ['tag_id' => 1,
        'info' => DB::table('tasks')
            ->join('task_tags', 'id_task', '=', 'task_tags.task_id_task')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('task_tags.tag_id', 1)
            ->where('tasks.id_user', 1)
            ->select(DB::raw('
                AVG(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as average_duration
            '))
            ->get()];

        return response()->json($averageDuration);

    }

    public function getAllData(){
        $tasks = Task::with('tags')->with('time_intervals')->get();
    
        // Вывод данных в JSON
        return response()->json($tasks);
    }

    public function getTagByUserTitleStatus(){
        $tagIds = ['id_user' => 1,
        'title' => 'Вторая задача',
        'status' => 'Создана',
        'tag' => DB::table('tasks')
            ->join('task_tags', 'id_task', '=', 'task_tags.task_id_task')
            ->where('tasks.id_user', 1)
            ->where('tasks.title', 'Вторая задача')
            ->where('tasks.status', 'Создана')
            ->select('task_tags.tag_id')
            ->get()];
    
        return response()->json($tagIds);
    }

    
    public function d(){
        
    }

}
//