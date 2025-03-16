<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Task; // Убедитесь, что это правильно указано
use App\Models\TaskTag; // Убедитесь, что это правильно указано

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tags';
    protected $fillable = ['id_user', 'tag'];

    public $timestamps = false;

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_tags', 'tag_id', 'task_id_task');
    }
}
