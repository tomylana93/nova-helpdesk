<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\TicketCategories\CreateTicketCategory;
use App\Actions\MasterData\TicketCategories\GetTicketCategoryParentOptions;
use App\Actions\MasterData\TicketCategories\UpdateTicketCategory;
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

    public function create(GetTicketCategoryParentOptions $parentOptionsAction): Response
    {
        $this->authorize('create', TicketCategory::class);

        return Inertia::render('admin/master-data/ticket-categories/Create', [
            'statusOptions' => GeneralStatus::options(),
            'parentOptions' => $parentOptionsAction->handle(),
        ]);
    }

    public function store(StoreTicketCategoryRequest $request, CreateTicketCategory $createCategory): RedirectResponse
    {
        $this->authorize('create', TicketCategory::class);

        $createCategory->handle($request->validated());

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

    public function edit(TicketCategory $ticketCategory, GetTicketCategoryParentOptions $parentOptionsAction): Response
    {
        $this->authorize('update', $ticketCategory);

        return Inertia::render('admin/master-data/ticket-categories/Edit', [
            'category' => TicketCategoryResource::make($ticketCategory)->resolve(),
            'statusOptions' => GeneralStatus::options(),
            'parentOptions' => $parentOptionsAction->handle($ticketCategory->id),
        ]);
    }

    public function update(UpdateTicketCategoryRequest $request, TicketCategory $ticketCategory, UpdateTicketCategory $updateCategory): RedirectResponse
    {
        $this->authorize('update', $ticketCategory);

        $updateCategory->handle($ticketCategory, $request->validated());

        Inertia::flash('success', trans('admin.master_data.ticket_category.message.updated.success'));

        return to_route('admin.master-data.ticket-categories.index');
    }
}
