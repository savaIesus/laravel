<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Task; // Убедитесь, что это правильно указано


class TaskTag extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'task_tags';
    protected $guarded = [];

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
}
