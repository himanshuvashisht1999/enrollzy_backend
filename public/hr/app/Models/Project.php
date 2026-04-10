<?php

namespace App\Models;

use App\Models\Client;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function project_category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function lead_source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }
}
