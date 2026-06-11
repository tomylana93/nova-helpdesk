<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Department;
use App\Models\SlaPolicy;
use App\Models\TicketCategory;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('master-data:import')]
#[Description('Import master data (branches, departments, categories) from CSV files')]
class ImportMasterDataCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting master data import...');

        DB::transaction(function (): void {
            $this->importBranches();
            $this->importDepartments();
            $this->importCategories();
            $this->importSlaPolicies();
        });

        $this->info('Master data import completed successfully!');

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, string>>
     */
    private function parseCsv(string $path): array
    {
        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return [];
        }

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, escape: '\\');
            if ($headers !== false) {
                while (($data = fgetcsv($handle, escape: '\\')) !== false) {
                    if (count($headers) === count($data)) {
                        $rows[] = array_combine($headers, $data);
                    }
                }
            }

            fclose($handle);
        }

        return $rows;
    }

    private function importBranches(): void
    {
        $path = database_path('data/branches.csv');
        $rows = $this->parseCsv($path);

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $branch = Branch::query()->find($row['id']) ?: new Branch;
            $branch->forceFill([
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'status' => $row['status'],
                'created_at' => $row['created_at'] ?: null,
                'updated_at' => $row['updated_at'] ?: null,
            ])->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line('Imported branches: '.count($rows));
    }

    private function importDepartments(): void
    {
        $path = database_path('data/departments.csv');
        $rows = $this->parseCsv($path);

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $department = Department::query()->find($row['id']) ?: new Department;
            $department->forceFill([
                'id' => $row['id'],
                'branch_id' => $row['branch_id'] ?: null,
                'code' => $row['code'],
                'name' => $row['name'],
                'status' => $row['status'],
                'created_at' => $row['created_at'] ?: null,
                'updated_at' => $row['updated_at'] ?: null,
            ])->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line('Imported departments: '.count($rows));
    }

    private function importCategories(): void
    {
        $path = database_path('data/ticket_categories.csv');
        $rows = $this->parseCsv($path);

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $category = TicketCategory::query()->find($row['id']) ?: new TicketCategory;
            $category->forceFill([
                'id' => $row['id'],
                'parent_id' => $row['parent_id'] ?: null,
                'name' => $row['name'],
                'description' => $row['description'] ?: null,
                'status' => $row['status'],
                'created_at' => $row['created_at'] ?: null,
                'updated_at' => $row['updated_at'] ?: null,
            ])->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line('Imported ticket categories: '.count($rows));
    }

    private function importSlaPolicies(): void
    {
        $path = database_path('data/sla_policies.csv');
        if (! file_exists($path)) {
            return;
        }

        $rows = $this->parseCsv($path);

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $policy = SlaPolicy::query()->find($row['id']) ?: new SlaPolicy;
            $policy->forceFill([
                'id' => $row['id'],
                'name' => $row['name'],
                'ticket_type' => $row['ticket_type'] ?: null,
                'priority' => $row['priority'],
                'first_response_target_minutes' => (int) $row['first_response_target_minutes'],
                'resolution_target_minutes' => (int) $row['resolution_target_minutes'],
                'is_active' => filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN),
                'created_at' => $row['created_at'] ?: null,
                'updated_at' => $row['updated_at'] ?: null,
            ])->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line('Imported SLA policies: '.count($rows));
    }
}
