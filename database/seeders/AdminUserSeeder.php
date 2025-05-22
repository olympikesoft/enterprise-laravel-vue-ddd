<?php
use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(2)->create([
            'is_admin' => true,
        ]);
    }
}
