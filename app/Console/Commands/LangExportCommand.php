<?php

namespace App\Console\Commands;

use App\Support\Localization\FrontendLocaleExporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('lang:export
    {--locale=* : The locales to export}
    {--path= : Override the output directory}')]
#[Description('Export Laravel language files to frontend JSON assets')]
class LangExportCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(FrontendLocaleExporter $exporter): int
    {
        $locales = $this->locales();
        $outputPath = $this->outputPath();

        try {
            $writtenFiles = $exporter->export($locales, $outputPath);
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        foreach ($writtenFiles as $locale => $path) {
            $this->info("Exported [{$locale}] to [{$path}].");
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>|null
     */
    private function locales(): ?array
    {
        /** @var array<int, string|null> $locales */
        $locales = $this->option('locale');

        /** @var list<string> $filteredLocales */
        $filteredLocales = array_values(array_filter(
            $locales,
            static fn (mixed $locale): bool => is_string($locale) && $locale !== '',
        ));

        return $filteredLocales === [] ? null : $filteredLocales;
    }

    private function outputPath(): ?string
    {
        $path = $this->option('path');

        return is_string($path) && $path !== '' ? $path : null;
    }
}
