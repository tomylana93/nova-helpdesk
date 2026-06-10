<?php

namespace App\Actions\MasterData\Queues;

use App\Models\Queue;

class UpdateQueue
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Queue $queue, array $data): void
    {
        $queue->update($data);
    }
}
