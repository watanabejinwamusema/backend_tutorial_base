<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Comment;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * モデルとポリシーの対応付け
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
    ];

    /**
     * 認可関連の初期化
     */
    public function boot(): void
    {
        // ここに Gate 定義などを追加したい場合は記述
        // 例) Gate::define('something', fn ($user) => true);
    }
}
