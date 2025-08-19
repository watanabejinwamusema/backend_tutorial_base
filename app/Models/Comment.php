<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// どちらでも：論理削除にする場合はSoftDeletesを有効化
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $fillable = ['article_id', 'user_id', 'body'];

    public function article() { return $this->belongsTo(Article::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
