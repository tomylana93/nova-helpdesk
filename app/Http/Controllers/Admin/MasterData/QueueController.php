<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreQueueRequest;
use App\Http\Requests\Admin\MasterData\UpdateQueueRequest;
use App\Http\Resources\QueueResource;
use App\Models\Queue;
use App\Tables\MasterData\QueueTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function index(QueueTable $table): Response
    {
        $this->authorize('viewAny', Queue::class);

        return Inertia::render('admin/master-data/queues/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Queue::class);

        return Inertia::render('admin/master-data/queues/Create', [
            'statusOptions' => GeneralStatus::options(),
        ]);
    }

    public function store(StoreQueueRequest $request): RedirectResponse
    {
        $this->authorize('create', Queue::class);

        Queue::query()->create($request->validated());

        Inertia::flash('success', trans('admin.master_data.queue.message.created.success'));

        return to_route('admin.master-data.queues.index');
    }

    public function show(Queue $queue): Response
    {
        $this->authorize('view', $queue);

        return Inertia::render('admin/master-data/queues/Show', [
            'queue' => QueueResource::make($queue)->resolve(),
        ]);
    }

    public function edit(Queue $queue): Response
    {
        $this->authorize('update', $queue);

        return Inertia::render('admin/master-data/queues/Edit', [
            'queue' => QueueResource::make($queue)->resolve(),
            'statusOptions' => GeneralStatus::options(),
        ]);
    }

    public function update(UpdateQueueRequest $request, Queue $queue): RedirectResponse
    {
        $this->authorize('update', $queue);

        $queue->update($request->validated());

        Inertia::flash('success', trans('admin.master_data.queue.message.updated.success'));

        return to_route('admin.master-data.queues.index');
    }
}
