<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        if (User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->exists()) {
            return;
        }

        $attributes = config('initial-admin');

        if (collect(['name', 'email', 'phone', 'password'])
            ->contains(fn (string $key): bool => blank($attributes[$key] ?? null))) {
            throw new RuntimeException('Set INITIAL_ADMIN_NAME, INITIAL_ADMIN_EMAIL, INITIAL_ADMIN_PHONE, and INITIAL_ADMIN_PASSWORD before running this seeder.');
        }

        $existingUser = User::query()->where('email', $attributes['email'])->first();

        if ($existingUser) {
            throw new RuntimeException('The configured initial admin email already belongs to a user. No account or role was changed.');
        }

        $admin = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'password' => $attributes['password'],
        ]);

        $admin->assignRole(Role::query()
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->firstOrFail());
    }
}
