<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // $this->call([
        //     ExpenseSeeder::class
        // ]);
        // Create the 'admin' and 'user' roles
        $adminRole = Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create the user
        $user = User::create([
            'name' => 'The Developer',
            'username' => 'developer',
            'password' => bcrypt('h5o3iamm2f')
        ]);

        // Assign the 'admin' role to the newly created user
        $user->assignRole($adminRole);

    }
}
