<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateDbVersion extends Command 
{

    protected $signature = 'db:v';
    protected $description = 'Populate db_versions table using semantic versioning from migrations';

    private int $major = 0;
    private int $minor = 0;
    private int $patch = 0;

    public function handle(): int {
        if ($this->isAlreadySynced()) {
            $this->error('db_versions already matches migrations table. Command aborted.');
            return Command::FAILURE;
        }

        $migrations = DB::table('migrations')->orderBy('id')->get();

        foreach ($migrations as $migration) {
            $this->processMigration($migration);
        }

        $this->info('DB version populated successfully.');
        return Command::SUCCESS;
    }

    private function isAlreadySynced(): bool {
        return DB::table('migrations')->count() === DB::table('db_versions')->count();
    }

    private function processMigration(object $migration): void {
        $description = $this->buildDescription($migration->migration);

        if ($this->alreadyExists($description)) {
            $this->info("Skipped: {$migration->migration}");
            return;
        }

        $version = $this->generateVersion($migration->migration);

        $this->insertVersion($version, $description);

        $this->info("Inserted {$version} - {$migration->migration}");
    }

    private function buildDescription(string $migrationName): string {
        $clean = preg_replace('/^[^a-zA-Z]+/', '', $migrationName);
        return str_replace('_', ' ', $clean);
    }

    private function alreadyExists(string $description): bool {
        return DB::table('db_versions')
            ->where('description', $description)
            ->exists();
    }

    private function generateVersion(string $migrationName): string {
        if ($this->isMajorChange($migrationName)) {
            $this->major++;
            $this->minor = 0;
            $this->patch = 0;
        } 
		elseif ($this->isMinorChange($migrationName)) {
            $this->minor++;
            $this->patch = 0;
        } 
		else {
            $this->patch++;
        }

        return "{$this->major}.{$this->minor}.{$this->patch}";
    }

    private function isMajorChange(string $name): bool {
        return str_contains($name, 'drop_') || str_contains($name, 'delete_');
    }

    private function isMinorChange(string $name): bool {
        return str_contains($name, 'create_');
    }

    private function insertVersion(string $version, string $description): void {
        DB::table('db_versions')->insert([
            'version' => $version,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
