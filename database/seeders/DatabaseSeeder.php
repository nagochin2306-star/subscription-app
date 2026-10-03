<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription; // ←★この行を追加します
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ユーザー作成（メールアドレスの重複を避けるため firstOrCreate を使用）
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // サブスクリプションのダミーデータ作成
        \App\Models\Subscription::factory(10)->create();
    }
}
