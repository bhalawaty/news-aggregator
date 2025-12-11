<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model {
    use HasFactory;
    protected $fillable = ['name','slug','provider_key','config','enabled','last_success_at'];
    protected $casts = ['config' => 'array', 'enabled' => 'boolean', 'last_success_at' => 'datetime'];
}
