<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge users from Wesum and Optimech databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting User Migration...');

        // 1. Migrate Wesum Users
        $this->info('Migrating Wesum Users...');
        $wesumUsers = DB::connection('wesum_db')->table('users')->get();

        foreach ($wesumUsers as $wUser) {
            $this->migrateUser($wUser, 'wesum');
        }

        // 2. Migrate Optimech Users
        $this->info('Migrating Optimech Users...');
        $optimechUsers = DB::connection('optimech_db')->table('users')->get();

        foreach ($optimechUsers as $oUser) {
            $this->migrateUser($oUser, 'optimech');
        }

        $this->info('User Migration Completed!');
    }

    private function migrateUser($sourceUser, $source)
    {
        // Check for duplicate email
        $existingUser = User::where('email', $sourceUser->email)->first();

        $email = $sourceUser->email;
        if ($existingUser) {
            $this->warn("Duplicate email found: {$email}. Appending source tag.");
            $email = str_replace('@', "+{$source}@", $email);
        }

        // Create User
        $user = User::create([
            'name' => $sourceUser->name,
            'email' => $email,
            'password' => $sourceUser->password ?? bcrypt('password'), // Use source hash if compatible or default
            // 'created_at' => $sourceUser->created_at, // Preserve dates if column exists
        ]);

        // Create Default Company for User
        $company = TenantCompany::create([
            'owner_id' => $user->id,
            'name' => $sourceUser->company_name ?? $user->name."'s Company",
            'email' => $user->email,
        ]);

        // Update User Context
        $user->update([
            'current_tenant_company_id' => $company->id,
            'current_scope' => 'company',
        ]);
    }
}
