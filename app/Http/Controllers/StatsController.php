<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TimeInterval;
use App\Models\TaskTag;
use Carbon\Carbon;

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


    public function getTimeSpendOnTasks()
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


    public function getTimeSpendOnTask(){
        $totalDuration = TimeInterval::where('task_id_task', 1)
            ->selectRaw('SUM(EXTRACT(EPOCH FROM (finish_at - start_at))) as total_duration')
            ->first();
                
        return response()->json($totalDuration);
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


    public function getUserTimeOnTasksForLastTenDays(){
        $start_date = Carbon::today()->subDays(10)->startOfDay();
        $end_date = Carbon::today()->endOfDay();
    
        $total_time = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task') // Исправлено поле для соединения
            ->where('tasks.id_user', 1)
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select(DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->first();

            return response()->json($total_time);
    }
    

    public function getUserTimeOnTasksForDay(){
        $start_date = Carbon::create(2025, 3, 14)->startOfDay();
        $end_date = Carbon::create(2025, 3, 14)->endOfDay();
    
        $total_time = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task') // Исправлено поле для соединения
            ->where('tasks.id_user', 1)
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select(DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->first();

            return response()->json($total_time);
    }


    public function getUserTimeOnTasksForEveryDayForLastTenDays(){
        $start_date = Carbon::today()->subDays(10)->startOfDay();
        $end_date = Carbon::today()->endOfDay();
    
        // Получаем все временные интервалы за последние 7 дней
        $time_intervals = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->where('time_intervals.start_at', '<=', $end_date) // Интервал начался до конца периода
            ->where(function ($query) use ($start_date) {
                $query->where('time_intervals.finish_at', '>=', $start_date) // Интервал завершился после начала периода
                      ->orWhereNull('time_intervals.finish_at'); // Или ещё не завершился
            })
            ->select(
                'time_intervals.start_at',
                'time_intervals.finish_at'
            )
            ->get();
    
        // Массив для хранения времени по дням
        $time_by_days = [];
    
        foreach ($time_intervals as $interval) {
            $current_start = Carbon::parse($interval->start_at);
            $current_end = $interval->finish_at ? Carbon::parse($interval->finish_at) : Carbon::now(); // Если finish_at = null, используем текущее время
    
            while ($current_start->lt($current_end)) {
                // Определяем конец текущего дня
                $day_end = $current_start->copy()->endOfDay();
    
                // Определяем фактический конец интервала для текущего дня
                $interval_end = $current_end->lt($day_end) ? $current_end : $day_end;
    
                // Добавляем время выполнения для текущего дня
                $day_key = $current_start->toDateString();
                $time_spent = $current_start->diffInSeconds($interval_end);
    
                if (!isset($time_by_days[$day_key])) {
                    $time_by_days[$day_key] = 0;
                }
                $time_by_days[$day_key] += $time_spent;
    
                // Переходим к следующему дню
                $current_start = $current_start->addDay()->startOfDay();
            }
        }
    
        // Преобразуем массив в формат, подходящий для JSON-ответа
        $result = [];
        foreach ($time_by_days as $day => $total_time) {
            $result[] = [
                'day' => $day,
                'total_time' => $total_time,
            ];
        }
    
        return response()->json($result);
    }


    public function getUserTimeOnTasksForEveryDayForMonth(){
        $start_date = Carbon::create(2025, 3, 1)->startOfMonth()->startOfDay();
        $end_date = Carbon::create(2025, 3, 1)->endOfMonth()->endOfDay();
    
        // Получаем все временные интервалы за месяц дней
        $time_intervals = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->where('time_intervals.start_at', '<=', $end_date) // Интервал начался до конца периода
            ->where(function ($query) use ($start_date) {
                $query->where('time_intervals.finish_at', '>=', $start_date) // Интервал завершился после начала периода
                      ->orWhereNull('time_intervals.finish_at'); // Или ещё не завершился
            })
            ->select(
                'time_intervals.start_at',
                'time_intervals.finish_at'
            )
            ->get();
    
        // Массив для хранения времени по дням
        $time_by_days = [];
    
        foreach ($time_intervals as $interval) {
            $current_start = Carbon::parse($interval->start_at);
            $current_end = $interval->finish_at ? Carbon::parse($interval->finish_at) : Carbon::now(); // Если finish_at = null, используем текущее время
    
            while ($current_start->lt($current_end)) {
                // Определяем конец текущего дня
                $day_end = $current_start->copy()->endOfDay();
    
                // Определяем фактический конец интервала для текущего дня
                $interval_end = $current_end->lt($day_end) ? $current_end : $day_end;
    
                // Добавляем время выполнения для текущего дня
                $day_key = $current_start->toDateString();
                $time_spent = $current_start->diffInSeconds($interval_end);
    
                if (!isset($time_by_days[$day_key])) {
                    $time_by_days[$day_key] = 0;
                }
                $time_by_days[$day_key] += $time_spent;
    
                // Переходим к следующему дню
                $current_start = $current_start->addDay()->startOfDay();
            }
        }
    
        // Преобразуем массив в формат, подходящий для JSON-ответа
        $result = [];
        foreach ($time_by_days as $day => $total_time) {
            $result[] = [
                'day' => $day,
                'total_time' => $total_time,
            ];
        }
    
        return response()->json($result);
    }


    public function getUserTimeOnTasksForEveryDayForCurrentMonth(){
        $start_date = Carbon::now()->startOfMonth()->startOfDay();
        $end_date = Carbon::now()->endOfMonth()->endOfDay();
    
        // Получаем все временные интервалы за месяц дней
        $time_intervals = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->where('time_intervals.start_at', '<=', $end_date) // Интервал начался до конца периода
            ->where(function ($query) use ($start_date) {
                $query->where('time_intervals.finish_at', '>=', $start_date) // Интервал завершился после начала периода
                      ->orWhereNull('time_intervals.finish_at'); // Или ещё не завершился
            })
            ->select(
                'time_intervals.start_at',
                'time_intervals.finish_at'
            )
            ->get();
    
        // Массив для хранения времени по дням
        $time_by_days = [];
    
        foreach ($time_intervals as $interval) {
            $current_start = Carbon::parse($interval->start_at);
            $current_end = $interval->finish_at ? Carbon::parse($interval->finish_at) : Carbon::now(); // Если finish_at = null, используем текущее время
    
            while ($current_start->lt($current_end)) {
                // Определяем конец текущего дня
                $day_end = $current_start->copy()->endOfDay();
    
                // Определяем фактический конец интервала для текущего дня
                $interval_end = $current_end->lt($day_end) ? $current_end : $day_end;
    
                // Добавляем время выполнения для текущего дня
                $day_key = $current_start->toDateString();
                $time_spent = $current_start->diffInSeconds($interval_end);
    
                if (!isset($time_by_days[$day_key])) {
                    $time_by_days[$day_key] = 0;
                }
                $time_by_days[$day_key] += $time_spent;
    
                // Переходим к следующему дню
                $current_start = $current_start->addDay()->startOfDay();
            }
        }
    
        // Преобразуем массив в формат, подходящий для JSON-ответа
        $result = [];
        foreach ($time_by_days as $day => $total_time) {
            $result[] = [
                'day' => $day,
                'total_time' => $total_time,
            ];
        }
    
        return response()->json($result);
    }


    public function getUserTimeOnTasksForCurrentMonth() {
        $start_date = Carbon::now()->startOfMonth()->startOfDay();
        $end_date = Carbon::now()->endOfMonth()->endOfDay();
    
        $total_time = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select(DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->first();
    
        return response()->json($total_time);
    }


    public function getUserTimeOnTasksForMonth() {
        $start_date = Carbon::create(2025, 3, 1)->startOfMonth()->startOfDay();
        $end_date = Carbon::create(2025, 3, 1)->endOfMonth()->endOfDay();
    
        $total_time = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select(DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->first();
    
        return response()->json($total_time);
    }


    public function getAllUsersTimeOnTasksForMonth() {
        $start_date = Carbon::create(2025, 3, 1)->startOfMonth()->startOfDay();
        $end_date = Carbon::create(2025, 3, 1)->endOfMonth()->endOfDay();
        
        $total_time_per_user = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select('tasks.id_user', DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->groupBy('tasks.id_user')
            ->get();
        
        return response()->json($total_time_per_user);
        
    }


    public function getAllUsersTimeOnTasksForCurrentMonth() {
        $start_date = Carbon::now()->startOfMonth()->startOfDay();
        $end_date = Carbon::now()->endOfMonth()->endOfDay();
        
        $total_time_per_user = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select('tasks.id_user', DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->groupBy('tasks.id_user')
            ->get();
        
        return response()->json($total_time_per_user);
        
    }


    public function getAllUsersTimeOnTasksForLastTenDays() {
        $start_date = Carbon::today()->subDays(10)->startOfDay();
        $end_date = Carbon::today()->endOfDay();
        
        $total_time_per_user = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select('tasks.id_user', DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->groupBy('tasks.id_user')
            ->get();
        
        return response()->json($total_time_per_user);
    }


    public function getAllUsersTimeOnTasksForCurrentWeek() {
        $start_date = Carbon::today()->startOfWeek()->startOfDay();
        $end_date = Carbon::today()->endOfWeek()->endOfDay();
        
        $total_time_per_user = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select('tasks.id_user', DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->groupBy('tasks.id_user')
            ->get();
        
        return response()->json($total_time_per_user);
    }


    public function getAllFinishedTasksForCurrentWeek() {
        $tasks = DB::table('tasks')
        ->whereBetween('finish_at', [
            Carbon::now()->startOfWeek()->startOfDay(),
            Carbon::now()->endOfWeek()->endOfDay()
        ])
        ->whereNotNull('finish_at') // Убедимся, что задача завершена
        ->get();
    
        return response()->json($tasks);
    }


    public function getAllCreatedTasksForCurrentWeek() {
        $start_date = Carbon::today()->startOfWeek()->startOfDay();
        $end_date = Carbon::today()->endOfWeek()->endOfDay();

        $tasks = DB::table('tasks')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->startOfDay(),
                Carbon::now()->endOfWeek()->endOfDay()
            ])
            ->whereNotNull('finish_at') // Убедимся, что задача завершена
            ->get();
    
        return response()->json($tasks);
    }


    public function getAllUserFinishedTasksForCurrentWeek() {
        $tasks = DB::table('tasks')
        ->whereBetween('finish_at', [
            Carbon::now()->startOfWeek()->startOfDay(),
            Carbon::now()->endOfWeek()->endOfDay()
        ])
        ->whereNotNull('finish_at') // Убедимся, что задача завершена
        ->where('id_user', 1)
        ->get();
    
        return response()->json($tasks);
    }


    public function getAllUserCreatedTasksForCurrentWeek() {
        $start_date = Carbon::today()->startOfWeek()->startOfDay();
        $end_date = Carbon::today()->endOfWeek()->endOfDay();

        $tasks = DB::table('tasks')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->startOfDay(),
                Carbon::now()->endOfWeek()->endOfDay()
            ])
            ->whereNotNull('finish_at') // Убедимся, что задача завершена
            ->where('id_user', 1)
            ->get();
    
        return response()->json($tasks);
    }


    public function getUserTimeOnTasksForEveryDayForLastweek(){
        $start_date = Carbon::today()->startOfWeek()->startOfDay();
        $end_date = Carbon::today()->endOfWeek()->endOfDay();
        
        // Получаем все временные интервалы за последние 7 дней
        $time_intervals = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task')
            ->where('tasks.id_user', 1)
            ->where('time_intervals.start_at', '<=', $end_date) // Интервал начался до конца периода
            ->where(function ($query) use ($start_date) {
                $query->where('time_intervals.finish_at', '>=', $start_date) // Интервал завершился после начала периода
                      ->orWhereNull('time_intervals.finish_at'); // Или ещё не завершился
            })
            ->select(
                'time_intervals.start_at',
                'time_intervals.finish_at'
            )
            ->get();
    
        // Массив для хранения времени по дням
        $time_by_days = [];
    
        foreach ($time_intervals as $interval) {
            $current_start = Carbon::parse($interval->start_at);
            $current_end = $interval->finish_at ? Carbon::parse($interval->finish_at) : Carbon::now(); // Если finish_at = null, используем текущее время
    
            while ($current_start->lt($current_end)) {
                // Определяем конец текущего дня
                $day_end = $current_start->copy()->endOfDay();
    
                // Определяем фактический конец интервала для текущего дня
                $interval_end = $current_end->lt($day_end) ? $current_end : $day_end;
    
                // Добавляем время выполнения для текущего дня
                $day_key = $current_start->toDateString();
                $time_spent = $current_start->diffInSeconds($interval_end);
    
                if (!isset($time_by_days[$day_key])) {
                    $time_by_days[$day_key] = 0;
                }
                $time_by_days[$day_key] += $time_spent;
    
                // Переходим к следующему дню
                $current_start = $current_start->addDay()->startOfDay();
            }
        }
    
        // Преобразуем массив в формат, подходящий для JSON-ответа
        $result = [];
        foreach ($time_by_days as $day => $total_time) {
            $result[] = [
                'day' => $day,
                'total_time' => $total_time,
            ];
        }
    
        return response()->json($result);
    }


    public function getUserTimeOnTasksForLastWeek(){
        $start_date = Carbon::today()->startOfWeek()->startOfDay();
        $end_date = Carbon::today()->endOfWeek()->endOfDay();

        $total_time = DB::table('tasks')
            ->join('time_intervals', 'id_task', '=', 'time_intervals.task_id_task') // Исправлено поле для соединения
            ->where('tasks.id_user', 1)
            ->whereBetween('time_intervals.start_at', [$start_date, $end_date])
            ->select(DB::raw('SUM(EXTRACT(EPOCH FROM (time_intervals.finish_at - time_intervals.start_at))) as total_time'))
            ->first();

            return response()->json($total_time);
    }


//id_task
    public function d()
    {
       
    }

}