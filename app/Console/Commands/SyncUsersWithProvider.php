<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SyncUsersWithProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-users-with-provider';

    private const API_URL = 'https://acme.com/sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users with third-party provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator = User::query()->paginate(perPage: 1000, page: $page);
            $users = $paginator->getCollection();

            $response = Http::post(self::API_URL, [
                'batches' => [
                    'subscribers' => $users->map(fn (User $user) => [
                        'email' => $user->email,
                        'name' => $user->name,
                        'time_zone' => $user->timezone,
                    ])
                ]
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Third-party provider API encountered an error.');
            }

            if (($page % 50) === 0) { // if we reached the 50th request
                sleep(3600); // wait for another 1 hour
            }

            $page++;
            $hasMorePages = $paginator->hasMorePages();
        }
    }
}
