<?php

namespace App\Actions\MasterData\Queues;

use App\Models\Queue;

class CreateQueue
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Queue
    {
        return Queue::query()->create($data);
    }
}
