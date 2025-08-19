<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    // 1) メール登録（トークン発行＆送信）
    public function requestEmail(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        // 既存ユーザーなら弾く
        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'このメールは登録済みです。ログインしてください。'], 409);
        }

        // トークン生成＆保存
        $token = Str::random(32);
        $expiresAt = now()->addMinutes(30);

        EmailVerificationToken::create([
            'email'      => $data['email'],
            'token'      => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        // Blade なしのメール送信
        Mail::raw("会員登録用トークン: {$token}\n有効期限: {$expiresAt}", function ($message) use ($data) {
            $message->to($data['email'])->subject('会員登録用トークン');
        });

        return response()->json([
            'message'    => '確認用トークンを送信しました（Mailpitを確認）',
            'expires_at' => $expiresAt,
        ], 200);
    }

    // 2) トークン検証
    public function verifyToken(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $hashed = hash('sha256', $data['token']);
        $row = EmailVerificationToken::where('email', $data['email'])
            ->where('token', $hashed)
            ->whereNull('used_at')
            ->first();

        if (!$row || $row->expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => 'トークンが無効か期限切れです。']);
        }

        // トークンは使用済みに
        $row->update(['used_at' => now()]);

        // 登録用のチケットを発行（30分有効）
        $ticket = Str::random(40);
        Cache::put('reg_ticket:'.$ticket, $data['email'], now()->addMinutes(30));

        return response()->json([
            'message' => 'メール確認OK。ticket を使って register してください。',
            'ticket'  => $ticket,
            'expires_in_minutes' => 30,
        ], 200);
    }

    // 3) 会員情報登録（名前/パスワード）
    public function register(Request $request)
    {
        $data = $request->validate([
            'ticket'   => 'required|string',
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // ticket からメールを取り出し（同時に無効化）
        $cacheKey = 'reg_ticket:'.$data['ticket'];
        $email = Cache::pull($cacheKey);

        if (!$email) {
            return response()->json(['message' => '登録チケットが無効か期限切れです。'], 403);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['message' => 'このメールは既に登録済みです。'], 409);
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $email,
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    // 4) ログイン
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => '認証に失敗しました。']);
        }

        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    // 5) ログアウト
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'ログアウトしました'], 200);
    }

    // 認証確認用
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }
}
