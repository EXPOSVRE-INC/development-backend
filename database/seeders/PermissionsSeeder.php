<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'account-reports flagged']);
        Permission::create(['name' => 'account-reports warnings']);
        Permission::create(['name' => 'account-reports banned']);

        Permission::create(['name' => 'post-reports flagged']);

        Permission::create(['name' => 'post-articles create']);
        Permission::create(['name' => 'post-articles scheduled']);
        Permission::create(['name' => 'post-articles published']);
        Permission::create(['name' => 'post-articles archive']);

        Permission::create(['name' => 'post-ads create']);
        Permission::create(['name' => 'post-ads scheduled']);
        Permission::create(['name' => 'post-ads published']);
        Permission::create(['name' => 'post-ads archive']);

        $admin = Role::create(['name' => 'admin']);

        $admin->givePermissionTo('account-reports flagged');
        $admin->givePermissionTo('account-reports warnings');
        $admin->givePermissionTo('account-reports banned');

        $admin->givePermissionTo('post-reports flagged');

        $admin->givePermissionTo('post-articles create');
        $admin->givePermissionTo('post-articles scheduled');
        $admin->givePermissionTo('post-articles published');
        $admin->givePermissionTo('post-articles archive');

        $admin->givePermissionTo('post-ads create');
        $admin->givePermissionTo('post-ads scheduled');
        $admin->givePermissionTo('post-ads published');
        $admin->givePermissionTo('post-ads archive');

        $moderator = Role::create(['name' => 'moderator']);

        $moderator->givePermissionTo('account-reports flagged');
        $moderator->givePermissionTo('account-reports warnings');
        $moderator->givePermissionTo('account-reports banned');

        $moderator->givePermissionTo('post-reports flagged');

        $user = User::where(['id' => 1])->first();
        $user->assignRole($admin);

        $user2 = User::where(['id' => 3])->first();
        $user2->assignRole($moderator);

//        $moderator->givePermissionTo('post-articles create');
//        $moderator->givePermissionTo('post-articles scheduled');
//        $moderator->givePermissionTo('post-articles published');
//        $moderator->givePermissionTo('post-articles archive');
//
//        $moderator->givePermissionTo('post-ads create');
//        $moderator->givePermissionTo('post-ads scheduled');
//        $moderator->givePermissionTo('post-ads published');
//        $moderator->givePermissionTo('post-ads archive');
    }
}
