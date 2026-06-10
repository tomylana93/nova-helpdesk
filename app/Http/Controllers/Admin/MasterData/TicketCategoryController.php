<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreTicketCategoryRequest;
use App\Http\Requests\Admin\MasterData\UpdateTicketCategoryRequest;
use App\Http\Resources\TicketCategoryResource;
use App\Models\TicketCategory;
use App\Tables\MasterData\TicketCategoryTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TicketCategoryController extends Controller
{
    public function index(TicketCategoryTable $table): Response
    {
        $this->authorize('viewAny', TicketCategory::class);

        return Inertia::render('admin/master-data/ticket-categories/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', TicketCategory::class);

        $parentOptions = TicketCategory::query()
            ->whereNull('parent_id')
            ->where('status', GeneralStatus::Active->value)
            ->orderBy('name')
            ->get()
            ->map(fn (TicketCategory $cat): array => [
                'value' => $cat->id,
                'label' => $cat->name,
            ])
            ->all();

        return Inertia::render('admin/master-data/ticket-categories/Create', [
            'statusOptions' => GeneralStatus::options(),
            'parentOptions' => $parentOptions,
        ]);
    }

    public function store(StoreTicketCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', TicketCategory::class);

        TicketCategory::query()->create($request->validated());

        Inertia::flash('success', trans('admin.master_data.ticket_category.message.created.success'));

        return to_route('admin.master-data.ticket-categories.index');
    }

    public function show(TicketCategory $ticketCategory): Response
    {
        $this->authorize('view', $ticketCategory);

        $ticketCategory->load('parent');

        return Inertia::render('admin/master-data/ticket-categories/Show', [
            'category' => TicketCategoryResource::make($ticketCategory)->resolve(),
        ]);
    }

    public function edit(TicketCategory $ticketCategory): Response
    {
        $this->authorize('update', $ticketCategory);

        $parentOptions = TicketCategory::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $ticketCategory->id)
            ->where('status', GeneralStatus::Active->value)
            ->orderBy('name')
            ->get()
            ->map(fn (TicketCategory $cat): array => [
                'value' => $cat->id,
                'label' => $cat->name,
            ])
            ->all();

        return Inertia::render('admin/master-data/ticket-categories/Edit', [
            'category' => TicketCategoryResource::make($ticketCategory)->resolve(),
            'statusOptions' => GeneralStatus::options(),
            'parentOptions' => $parentOptions,
        ]);
    }

    public function update(UpdateTicketCategoryRequest $request, TicketCategory $ticketCategory): RedirectResponse
    {
        $this->authorize('update', $ticketCategory);

        $ticketCategory->update($request->validated());

        Inertia::flash('success', trans('admin.master_data.ticket_category.message.updated.success'));

        return to_route('admin.master-data.ticket-categories.index');
    }
}
