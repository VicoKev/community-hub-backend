<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création du Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@communityhub.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => 'P@ssw0rd',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');
        \App\Models\Profile::firstOrCreate(
            ['user_id' => $superAdmin->id],
            ['category' => \App\Enums\ProfileCategory::ADMINISTRATIVE_EXECUTIVE, 'status' => \App\Enums\ProfileStatus::APPROVED]
        );

        // Création de l'Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@communityhub.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => 'P@ssw0rd',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
        \App\Models\Profile::firstOrCreate(
            ['user_id' => $admin->id],
            ['category' => \App\Enums\ProfileCategory::TECHNICAL_EXECUTIVE, 'status' => \App\Enums\ProfileStatus::APPROVED]
        );

        // Création du Moderator
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@communityhub.com'],
            [
                'first_name' => 'Moderator',
                'last_name' => 'User',
                'password' => 'P@ssw0rd',
                'email_verified_at' => now(),
            ]
        );
        $moderator->assignRole('moderator');
        \App\Models\Profile::firstOrCreate(
            ['user_id' => $moderator->id],
            ['category' => \App\Enums\ProfileCategory::MERCHANT, 'status' => \App\Enums\ProfileStatus::APPROVED]
        );

        // Création du User
        $user = User::firstOrCreate(
            ['email' => 'user@communityhub.com'],
            [
                'first_name' => 'Regular',
                'last_name' => 'User',
                'password' => 'P@ssw0rd',
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('user');
        \App\Models\Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['category' => \App\Enums\ProfileCategory::YOUNG_ENTREPRENEUR, 'status' => \App\Enums\ProfileStatus::APPROVED]
        );
    }
}
