<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:bump-version {type=auto : The type of bump (major, minor, patch, auto) or a specific version}')]
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

        // Automatic detection mode
        if (strtolower($type) === 'auto') {
            $detectedType = $this->detectBumpType($currentVersion);
            if ($detectedType === null) {
                $this->info('No new commits since last release. No version bump needed.');

                return Command::SUCCESS;
            }

            $type = $detectedType;
            $this->info("Automatically detected bump type: {$type}");
        }

        $newVersion = $this->bump($currentVersion, $type);
        if (! $newVersion) {
            $this->error("Invalid bump type or version format. Use 'major', 'minor', 'patch', 'auto', or a specific version like '0.8.0'.");

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

    /**
     * Automatically detect the bump type based on Conventional Commits since the last release tag.
     */
    private function detectBumpType(string $currentVersion): ?string
    {
        $tag = 'v'.$currentVersion;
        $tagExists = false;

        exec('git tag -l '.escapeshellarg($tag), $tagOutput);
        if ($tagOutput !== [] && trim($tagOutput[0]) === $tag) {
            $tagExists = true;
        }

        $commits = [];
        if ($tagExists) {
            exec('git log '.escapeshellarg($tag).'..HEAD --oneline', $commits);
        } else {
            exec('git log --oneline', $commits);
        }

        if ($commits === []) {
            return null;
        }

        $this->info('Analyzing '.count($commits)." commit(s) since last release tag ({$tag}):");

        $bumpType = 'patch';
        $hasMatch = false;

        foreach ($commits as $commit) {
            $this->line('  - '.$commit);

            $message = preg_replace('/^[a-f0-9]+\s+/', '', $commit);

            // 1. Check for Major (Breaking Change)
            if (
                preg_match('/BREAKING CHANGE:/i', $message) ||
                preg_match('/BREAKING:/i', $message) ||
                preg_match('/^\w+(?:\([^)]+\))?!:/', $message)
            ) {
                return 'major'; // Breaking change takes absolute precedence
            }

            // 2. Check for Minor (feat)
            if (preg_match('/^feat(?:\([^)]+\))?:/', $message)) {
                $bumpType = 'minor';
                $hasMatch = true;
            }

            // 3. Check for Patch (fix, refactor, chore, etc.)
            if (! $hasMatch && preg_match('/^(fix|refactor|chore|style|test|docs|ci)(?:\([^)]+\))?:/', $message)) {
                $bumpType = 'patch';
            }
        }

        return $bumpType;
    }
}
