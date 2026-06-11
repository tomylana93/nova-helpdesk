<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:bump-version {type : The type of bump (major, minor, patch) or a specific version}')]
#[Description('Bump the application semantic version, update version.json, commit and tag locally')]
class BumpVersionCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $versionFile = base_path('version.json');

        $currentVersion = '0.8.0';
        if (file_exists($versionFile)) {
            $data = json_decode(file_get_contents($versionFile), true);
            $currentVersion = $data['version'] ?? '0.8.0';
        }

        $newVersion = $this->bump($currentVersion, $type);
        if (! $newVersion) {
            $this->error("Invalid bump type or version format. Use 'major', 'minor', 'patch', or a specific version like '0.8.0'.");

            return Command::FAILURE;
        }

        // Save new version to version.json
        file_put_contents($versionFile, json_encode(['version' => $newVersion], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        $this->info("Version bumped from v{$currentVersion} to v{$newVersion}");

        // Git commands
        if (! app()->environment('testing')) {
            exec('git add version.json');
            exec('git commit -m "chore(release): v'.$newVersion.'"');
            exec('git tag v'.$newVersion);
        }

        $this->info("Created commit and Git tag v{$newVersion} locally.");

        return Command::SUCCESS;
    }

    /**
     * Parse and bump the version string.
     */
    private function bump(string $current, string $type): ?string
    {
        if (preg_match('/^\d+\.\d+\.\d+$/', $type)) {
            return $type;
        }

        if (! preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $current, $matches)) {
            return null;
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3];

        switch (strtolower($type)) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
                $patch++;
                break;
            default:
                return null;
        }

        return "{$major}.{$minor}.{$patch}";
    }
}
