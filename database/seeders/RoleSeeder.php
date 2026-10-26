<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FilamentAdminPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'employee', 'user', 'driver'] as $name) {
            Role::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $managedNames = collect(FilamentAdminPermissionsSeeder::definitions())
            ->flatMap(fn (array $abilities, string $subject) => collect($abilities)
                ->map(fn (string $ability) => $ability.':'.$subject))
            ->all();

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->firstOrFail();
        $admin->givePermissionTo(Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $managedNames)
            ->get());

        $employeePermissions = [
            'ViewAny:Product',
            'View:Product',
            'ViewAny:Order',
            'View:Order',
            'View:OperationalDashboard',
        ];

        $employee = Role::query()->where('name', 'employee')->where('guard_name', 'web')->firstOrFail();
        $employee->revokePermissionTo(
            $employee->permissions()->whereIn('name', array_diff($managedNames, $employeePermissions))->get()
        );
        $employee->givePermissionTo(Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $employeePermissions)
            ->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
