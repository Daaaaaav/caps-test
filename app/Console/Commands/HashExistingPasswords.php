<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HashExistingPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:hash-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash existing plain text passwords in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        $count = 0;
        foreach ($users as $user) {
            // Skip if already hashed
            if (!str_starts_with($user->password, '$2y$')) {
                $plainPassword = $user->password;
                $user->password = Hash::make($plainPassword);
                $user->save();
                
                $this->info("Hashed password for user: {$user->email}");
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("Successfully converted {$count} password(s) to bcrypt.");
        } else {
            $this->info('All passwords are already hashed.');
        }

        return 0;
    }
}
