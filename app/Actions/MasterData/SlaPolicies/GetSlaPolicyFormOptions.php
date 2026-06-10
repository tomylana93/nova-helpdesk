<?php

namespace App\Actions\MasterData\SlaPolicies;

use App\Enums\GeneralStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Http\Resources\QueueOptionResource;
use App\Models\Queue;

class GetSlaPolicyFormOptions
{
    /**
     * @return array{typeOptions: array<int, array<string, mixed>>, priorityOptions: array<int, array<string, mixed>>, queueOptions: list<array<string, mixed>>}
     */
    public function handle(): array
    {
        $queueOptions = Queue::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(QueueOptionResource::class)
            ->map->resolve()
            ->all();

        return [
            'typeOptions' => [
                ['value' => '', 'label' => __('admin.master_data.sla_policy.label.all_types')],
                ...TicketType::options(),
            ],
            'priorityOptions' => TicketPriority::options(),
            'queueOptions' => $queueOptions,
        ];
    }
}
