<?php

namespace App\Models;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TaskTag; // Убедитесь, что это правильно указано

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tasks';
    protected $fillable = ['id_user', 'title', 'description', 'status', 'finished_at'];

    protected $primaryKey = 'id_task';
    public $incrementing = true;
    protected $keyType = 'int';

    public function time_intervals()
    {
        return $this->hasMany(TimeInterval::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'task_tags', 'task_id_task', 'tag_id');
    }
}
