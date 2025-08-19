<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * コメント一覧の閲覧可否（誰でもOK）
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * 個別コメントの閲覧可否（誰でもOK）
     */
    public function view(?User $user, Comment $comment): bool
    {
        return true;
    }

    /**
     * 作成可否（ログインしていればOK）
     */
    public function create(?User $user): bool
    {
        return (bool) $user;
    }

    /**
     * 更新可否（本人のみ）
     */
    public function update(?User $user, Comment $comment): bool
    {
        return $user && $user->id === $comment->user_id;
    }

    /**
     * 削除可否（本人のみ）
     */
    public function delete(?User $user, Comment $comment): bool
    {
        return $user && $user->id === $comment->user_id;
    }

    /**
     * 復元可否（論理削除時／本人のみ）
     */
    public function restore(?User $user, Comment $comment): bool
    {
        return $user && $user->id === $comment->user_id;
    }

    /**
     * 完全削除可否（本人のみ）
     */
    public function forceDelete(?User $user, Comment $comment): bool
    {
        return $user && $user->id === $comment->user_id;
    }
}
