<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name} {--force : Overwrite existing files}';

    protected $description = 'Scaffold a new module using clean-architecture conventions';

    public function handle(Filesystem $files): int
    {
        $name = Str::studly($this->argument('name'));

        $stubs = [
            'controller' => 'Http/Controllers/{name}Controller.php',
            'service' => 'Domain/{name}/{name}Service.php',
            'dto' => 'Domain/{name}/DTOs/{name}Data.php',
            'request' => 'Http/Requests/{name}/{name}Request.php',
            'resource' => 'Http/Resources/{name}/{name}Resource.php',
        ];

        foreach ($stubs as $type => $path) {
            $target = app_path(str_replace(['{name}'], [$name], $path));

            if ($files->exists($target) && ! $this->option('force')) {
                $this->warn("Skipping existing {$target}");
                continue;
            }

            $stubPath = base_path("stubs/module/{$type}.stub");

            if (! $files->exists($stubPath)) {
                $this->error("Stub not found: {$stubPath}");
                continue;
            }

            $stub = str_replace(['{{ namespace }}', '{{ class }}', '{{ name }}'], [
                $this->namespaceFor($path, $name),
                $name,
                $name,
            ], $files->get($stubPath));

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $stub);

            $this->info("Created: {$target}");
        }

        return self::SUCCESS;
    }

    protected function namespaceFor(string $path, string $name): string
    {
        $segments = Str::of($path)->beforeLast('.php')->explode('/');
        $segments = $segments->map(fn ($segment) => $segment === '{name}' ? $name : $segment);

        return 'App\\'.implode('\\', $segments->toArray());
    }
}
