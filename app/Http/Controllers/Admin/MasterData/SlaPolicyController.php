<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\SlaPolicies\CreateSlaPolicy;
use App\Actions\MasterData\SlaPolicies\GetSlaPolicyFormOptions;
use App\Actions\MasterData\SlaPolicies\UpdateSlaPolicy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreSlaPolicyRequest;
use App\Http\Requests\Admin\MasterData\UpdateSlaPolicyRequest;
use App\Http\Resources\SlaPolicyResource;
use App\Models\SlaPolicy;
use App\Tables\MasterData\SlaPolicyTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SlaPolicyController extends Controller
{
    public function index(SlaPolicyTable $table): Response
    {
        $this->authorize('viewAny', SlaPolicy::class);

        return Inertia::render('admin/master-data/sla-policies/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(GetSlaPolicyFormOptions $formOptions): Response
    {
        $this->authorize('create', SlaPolicy::class);

        return Inertia::render('admin/master-data/sla-policies/Create', $formOptions->handle());
    }

    public function store(StoreSlaPolicyRequest $request, CreateSlaPolicy $createPolicy): RedirectResponse
    {
        $this->authorize('create', SlaPolicy::class);

        $createPolicy->handle($request->validated());

        Inertia::flash('success', trans('admin.master_data.sla_policy.message.created.success'));

        return to_route('admin.master-data.sla-policies.index');
    }

    public function show(SlaPolicy $slaPolicy): Response
    {
        $this->authorize('view', $slaPolicy);

        return Inertia::render('admin/master-data/sla-policies/Show', [
            'slaPolicy' => SlaPolicyResource::make($slaPolicy)->resolve(),
        ]);
    }

    public function edit(SlaPolicy $slaPolicy, GetSlaPolicyFormOptions $formOptions): Response
    {
        $this->authorize('update', $slaPolicy);

        return Inertia::render('admin/master-data/sla-policies/Edit', [
            'slaPolicy' => SlaPolicyResource::make($slaPolicy)->resolve(),
            ...$formOptions->handle(),
        ]);
    }

    public function update(UpdateSlaPolicyRequest $request, SlaPolicy $slaPolicy, UpdateSlaPolicy $updatePolicy): RedirectResponse
    {
        $this->authorize('update', $slaPolicy);

        $updatePolicy->handle($slaPolicy, $request->validated());

        Inertia::flash('success', trans('admin.master_data.sla_policy.message.updated.success'));

        return to_route('admin.master-data.sla-policies.index');
    }
}
