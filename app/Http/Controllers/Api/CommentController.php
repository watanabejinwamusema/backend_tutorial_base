<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // 一覧
    public function index(Article $article)
    {
        $comments = $article->comments()
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($comments, 200);
    }

    // 作成
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'body' => 'required|string|min:10|max:100',
        ]);

        $userId = auth()->id() ?? 1;

        $comment = Comment::create([
            'article_id' => $article->id,
            'user_id'    => $userId,
            'body'       => $validated['body'],
        ]);

        return response()->json($comment, 201);
    }

    // 更新
    public function update(Request $request, Article $article, Comment $comment)
    {
        if ($comment->article_id !== $article->id) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        // 認可チェック（Policy 呼び出し）
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => 'required|string|min:10|max:100',
        ]);

        $comment->update($validated);

        return response()->json($comment, 200);
    }

    // 削除
    public function destroy(Article $article, Comment $comment)
    {
        if ($comment->article_id !== $article->id) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        // 認可チェック（Policy 呼び出し）
        $this->authorize('delete', $comment);

        $comment->delete(); // 論理削除 or 物理削除

        return response()->json(['message' => 'Deleted'], 200);
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            //auth()->loginUsingId(1);  // ← 開発用：ID=1 でログイン（コメント作成者IDに合わせてOK）
            return $next($request);
        });
    }
}
