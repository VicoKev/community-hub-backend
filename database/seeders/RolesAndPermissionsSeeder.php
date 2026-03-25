<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset du cache Spatie avant de seeder
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
 
        $permissions = [
            // Profils
            'profile.view_any',         
            'profile.view',             
            'profile.create',           
            'profile.update_own',       
            'profile.update_any',       
            'profile.delete_own',       
            'profile.delete_any',       
            'profile.approve',          
            'profile.reject',           
 
            // Annonces
            'announcement.view_any',
            'announcement.create',
            'announcement.update_any',
            'announcement.delete_any',
            'announcement.publish',
 
            // Utilisateurs
            'user.view_any',
            'user.update_any',
            'user.delete_any',
            'user.assign_role',
 
            // Statistiques & Export
            'stats.view',
            'export.profiles',
            'export.stats',
 
            // Catégories
            'category.manage',
 
            // Logs
            'logs.view',
 
            // Newsletter
            'newsletter.send',
            'newsletter.manage',
        ];
 
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['uuid' => (string) Str::uuid()]
            );
        }
 
        // super_admin
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'api'],
            ['uuid' => (string) Str::uuid()]
        );
        $superAdmin->syncPermissions(Permission::where('guard_name', 'api')->get());
 
        // admin
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'api'],
            ['uuid' => (string) Str::uuid()]
        );
        $admin->syncPermissions([
            'profile.view_any',
            'profile.view',
            'profile.update_any',
            'profile.delete_any',
            'profile.approve',
            'profile.reject',
            'announcement.view_any',
            'announcement.create',
            'announcement.update_any',
            'announcement.delete_any',
            'announcement.publish',
            'user.view_any',
            'user.update_any',
            'stats.view',
            'export.profiles',
            'export.stats',
            'category.manage',
            'newsletter.send',
            'newsletter.manage',
        ]);
 
        // moderator
        $moderator = Role::firstOrCreate(
            ['name' => 'moderator', 'guard_name' => 'api'],
            ['uuid' => (string) Str::uuid()]
        );
        $moderator->syncPermissions([
            'profile.view_any',
            'profile.view',
            'profile.approve',
            'profile.reject',
            'announcement.view_any',
            'stats.view',
        ]);
 
        // user
        $user = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'api'],
            ['uuid' => (string) Str::uuid()]
        );
        $user->syncPermissions([
            'profile.create',
            'profile.update_own',
            'profile.delete_own',
            'profile.view_any',
            'profile.view',
            'announcement.view_any',
        ]);
 
        $this->command->info('✅ Rôles et permissions créés avec succès.');
        $this->command->table(
            ['Rôle', 'Permissions'],
            [
                ['super_admin', 'Toutes (' . Permission::count() . ')'],
                ['admin', $admin->permissions()->count()],
                ['moderator', $moderator->permissions()->count()],
                ['user', $user->permissions()->count()],
            ]
        );
    }
}
