<?php

namespace App\Http\Controllers\Reports;

use App\Actions\Reports\GetReportData;
use App\Actions\Reports\Support\ReportTicketQuery;
use App\Exports\Reports\AuditActivitiesExport;
use App\Exports\Reports\OperationalTicketsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportRequest;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(ReportRequest $request, GetReportData $getReportData): Response
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return Inertia::render('reports/Index', $getReportData->handle($user, $request->toFilters(), $request));
    }

    public function exportOperational(ReportRequest $request, ReportTicketQuery $ticketQuery): BinaryFileResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $filters = $request->toFilters();

        return Excel::download(
            new OperationalTicketsExport($user, $filters, $ticketQuery),
            "operational-report-{$filters->period->mode}-{$filters->period->year}.xlsx",
        );
    }

    public function exportAudit(ReportRequest $request, ReportTicketQuery $ticketQuery): BinaryFileResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $filters = $request->toFilters();

        return Excel::download(
            new AuditActivitiesExport($user, $filters, $ticketQuery),
            "audit-report-{$filters->period->mode}-{$filters->period->year}.xlsx",
        );
    }
}
