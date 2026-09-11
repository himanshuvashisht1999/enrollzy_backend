<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;
    
    protected $table = 'task_comments';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_comment_id');
    }

    public function getDocumentsListAttribute()
    {
        if (empty($this->documents)) {
            return [];
        }
        $decoded = json_decode($this->documents, true);
        $rawList = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [$this->documents];

        $items = [];
        foreach ($rawList as $path) {
            if (!$path) continue;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'jfif']);
            $items[] = [
                'path' => $path,
                'url' => asset($path),
                'name' => basename($path),
                'ext' => $ext,
                'is_image' => $isImg,
            ];
        }
        return $items;
    }
}
