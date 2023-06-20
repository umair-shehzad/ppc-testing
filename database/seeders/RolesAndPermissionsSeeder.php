<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view main menu']);
        Permission::create(['name' => 'view crm']);
        Permission::create(['name' => 'view fixed price mode']);
        Permission::create(['name' => 'view premium mode']);
        Permission::create(['name' => 'view packages']);
        Permission::create(['name' => 'view profile']);
        Permission::create(['name' => 'view integrations']);
        Permission::create(['name' => 'view faq']);
        Permission::create(['name' => 'view support ticket']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('view main menu');

        // or may be done by chaining
        $role = Role::create(['name' => 'moderator'])
            ->givePermissionTo(['view crm', 'view premium mode']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
