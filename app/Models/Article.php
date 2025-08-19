<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;
    protected $fillable = ['title','body','user_id'];

    public function comments() { return $this->hasMany(Comment::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
