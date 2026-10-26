<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class FilamentAdminPermissionsSeeder extends Seeder
{
    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            'Product' => ['ViewAny', 'View', 'Create', 'Update'],
            'Order' => ['ViewAny', 'View'],
            'User' => ['ViewAny', 'View', 'Create', 'Update'],
            'Category' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'Payment' => ['ViewAny', 'View'],
            'Role' => ['ViewAny', 'View', 'Create', 'Update', 'Delete'],
            'AdminReports' => ['View'],
            'AdminDashboard' => ['View'],
            'OperationalDashboard' => ['View'],
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::definitions() as $subject => $abilities) {
            foreach ($abilities as $ability) {
                Permission::findOrCreate($ability.':'.$subject, 'web');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
