# DB 物理設計（Comments API）

## 方針（運用ルール）

-   外部キー:
    -   `comments.article_id` → `articles.id` (**ON DELETE CASCADE**)
    -   `comments.user_id` → `users.id` (削除制御はアプリ側で)
-   並び順: コメント一覧は **created_at DESC**（投稿時刻優先）
-   論理削除: 今回は **物理削除**。将来必要なら `comments.deleted_at` + `SoftDeletes` を導入
-   バリデーション対応: `comments.body` は DB `VARCHAR(100)`、アプリで `min:10|max:100`

---

## テーブル定義

### users（Laravel 標準）

| カラム            | 型              | NULL | デフォルト | キー   | 備考 |
| ----------------- | --------------- | ---- | ---------- | ------ | ---- |
| id                | BIGINT UNSIGNED | NO   | AI         | PK     |      |
| name              | VARCHAR(255)    | NO   |            |        |      |
| email             | VARCHAR(255)    | NO   |            | UNIQUE |      |
| password          | VARCHAR(255)    | NO   |            |        |      |
| email_verified_at | DATETIME        | YES  | NULL       |        |      |
| remember_token    | VARCHAR(100)    | YES  | NULL       |        |      |
| created_at        | TIMESTAMP       | YES  | NULL       |        |      |
| updated_at        | TIMESTAMP       | YES  | NULL       |        |      |

**Index**: `email (UNIQUE)`

---

### articles

| カラム     | 型              | NULL | デフォルト | キー | 備考                    |
| ---------- | --------------- | ---- | ---------- | ---- | ----------------------- |
| id         | BIGINT UNSIGNED | NO   | AI         | PK   |                         |
| title      | VARCHAR(255)    | NO   |            |      |                         |
| body       | TEXT            | YES  | NULL       |      |                         |
| user_id    | BIGINT UNSIGNED | NO   |            | FK   | → users.id, **CASCADE** |
| created_at | TIMESTAMP       | YES  | NULL       |      |                         |
| updated_at | TIMESTAMP       | YES  | NULL       |      |                         |

**Index**: `user_id`, `created_at`

---

### comments

| カラム     | 型              | NULL | デフォルト | キー | 備考                             |
| ---------- | --------------- | ---- | ---------- | ---- | -------------------------------- |
| id         | BIGINT UNSIGNED | NO   | AI         | PK   |                                  |
| article_id | BIGINT UNSIGNED | NO   |            | FK   | → articles.id, **CASCADE**       |
| user_id    | BIGINT UNSIGNED | NO   |            | FK   | → users.id                       |
| body       | VARCHAR(100)    | NO   |            |      | **10〜100 文字**（アプリで検証） |
| created_at | TIMESTAMP       | YES  | NULL       |      |                                  |
| updated_at | TIMESTAMP       | YES  | NULL       |      |                                  |

**Index**: `(article_id, created_at)`（一覧の新しい順を高速化）

---

## モデル対応（概要）

-   `User hasMany Article / Comment`
-   `Article belongsTo User; hasMany Comment`
-   `Comment belongsTo Article, belongsTo User`

---

## マイグレーション実装との対応（抜粋）

-   `articles`: `foreignId('user_id')->constrained()->cascadeOnDelete();`
-   `comments`:
    -   `foreignId('article_id')->constrained()->cascadeOnDelete();`
    -   `foreignId('user_id')->constrained();`
    -   `string('body', 100);`
    -   `index(['article_id', 'created_at']);`

---

## 変更履歴

-   2025-08-17: 初版作成（ER 図 + 物理設計を追加）
