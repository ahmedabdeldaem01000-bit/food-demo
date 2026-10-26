<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('demo.enabled') || app()->environment('production')) {
            return;
        }

        $this->createConfiguredUser('admin');
        $this->createConfiguredUser('employee');
    }

    private function createConfiguredUser(string $role): void
    {
        $attributes = config("demo.users.{$role}");

        if (
            ! is_array($attributes)
            || collect(['name', 'email', 'phone', 'password'])
                ->contains(fn (string $key): bool => blank($attributes[$key] ?? null))
        ) {
            throw new \RuntimeException("Demo {$role} account configuration is incomplete. Set its name, email, phone, and password before enabling demo seeding.");
        }

        $otherRole = $role === 'admin' ? 'employee' : 'admin';
        $otherAttributes = config("demo.users.{$otherRole}");

        if (
            is_array($otherAttributes)
            && (
                strcasecmp((string) $attributes['email'], (string) ($otherAttributes['email'] ?? '')) === 0
                || (string) $attributes['phone'] === (string) ($otherAttributes['phone'] ?? '')
            )
        ) {
            throw new \RuntimeException('Demo admin and employee accounts must use different email addresses and phone numbers.');
        }

        $user = User::query()->firstOrCreate(
            ['email' => $attributes['email']],
            [
                'name' => $attributes['name'],
                'phone' => $attributes['phone'],
                'password' => $attributes['password'],
                'address' => null,
            ],
        );

        if ($user->wasRecentlyCreated) {
            $user->assignRole($role);
        }
    }
}
