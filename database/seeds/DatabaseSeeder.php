<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::query()->delete();
        // $this->call('UsersTableSeeder');
        $landRoles = config('roles.landRoles');
        $projectRoles = config('roles.projectRoles');
        $map = function ($role) {
            Role::FindOrCreate($role);
        };
        array_map($map, array_merge($landRoles, $projectRoles));


        $user = User::where('login', '=', 'dolzhenko.a.n')->first();
        $user ? $user->assignRole(Role::findByName('super-admin')) : null;

        $user = User::where('login', '=', 'mikheev.m.a')->first();
        $user ? $user->assignRole(Role::findByName('super-admin')) : null;

//       $sql = "update project_user set role_id = ? where role_id=1";
//       \Illuminate\Support\Facades\DB::select($sql,[Role::findByName('ownerP')->id]);
//
//        $sql = "update project_user set role_id = ? where role_id=2";
//        \Illuminate\Support\Facades\DB::select($sql,[Role::findByName('adminP')->id]);
//


//        $allProject = Permission::create(['name' => 'Get all project']);
//        $allProjectByUser = Permission::create(['name' => 'Get all project by user']);
//
//        $admin->givePermissionTo($allProject);
//        $user->givePermissionTo($allProjectByUser);

    }
}
