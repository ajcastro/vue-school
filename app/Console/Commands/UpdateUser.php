<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::findOrFail($this->argument('user'));

        $user->firstname = $this->ask('Enter first name', $user->firstname);
        $user->lastname = $this->ask('Enter last name', $user->lastname);
        $user->timezone = $this->choice(
            'Enter timezone',
            User::TIMEZONES,
            array_flip(User::TIMEZONES)[$user->timezone]
        );
        $user->save();
    }
}
