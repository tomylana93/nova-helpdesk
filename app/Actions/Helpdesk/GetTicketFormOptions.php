<?php

namespace App\Actions\Helpdesk;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Http\Resources\BranchOptionResource;
use App\Http\Resources\DepartmentOptionResource;
use App\Http\Resources\UserOptionResource;
use App\Models\Branch;
use App\Models\Department;
use App\Models\TicketCategory;
use App\Models\User;

class GetTicketFormOptions
{
    /**
     * @param  bool  $includeAgents  Whether to include agent options (for edit forms)
     * @return array<string, list<array<string, mixed>>>
     */
    public function handle(bool $includeAgents = false): array
    {
        $options = [
            'branchOptions' => Branch::query()
                ->where('status', GeneralStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapInto(BranchOptionResource::class)
                ->map->resolve()
                ->all(),

            'departmentOptions' => Department::query()
                ->where('status', GeneralStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name', 'branch_id'])
                ->mapInto(DepartmentOptionResource::class)
                ->map->resolve()
                ->all(),

            'categoryOptions' => TicketCategory::query()
                ->where('status', GeneralStatus::Active)
                ->whereNull('parent_id')
                ->with(['subcategories' => function ($q): void {
                    $q->where('status', GeneralStatus::Active)->oldest();
                }])
                ->oldest()
                ->get()
                ->map(fn (TicketCategory $parent): array => [
                    'label' => $parent->name,
                    'options' => $parent->subcategories->map(fn (TicketCategory $sub): array => [
                        'value' => $sub->id,
                        'label' => $sub->name,
                    ])->all(),
                ])
                ->filter(fn (array $group): bool => ! empty($group['options']))
                ->values()
                ->all(),
        ];

        if ($includeAgents) {
            $options['agentOptions'] = User::query()
                ->role(UserRole::ItAgent->value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapInto(UserOptionResource::class)
                ->map->resolve()
                ->all();
        }

        return $options;
    }
}
