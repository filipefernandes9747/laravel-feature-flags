<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('runs the install command and publishes config and migrations', function () {
    // Clean up first
    $publishedConfig = config_path('feature-flags.php');
    $migrationDir = database_path('migrations');
    File::delete($publishedConfig);

    // Simulate running the command
    $output = Artisan::call('feature-flag:install');
    $this->assertEquals(0, $output);

    // Assert config file is published
    expect(File::exists($publishedConfig))->toBeTrue();

    // Assert migration file is published
    $migrationPublished = collect(File::files($migrationDir))
        ->contains(fn ($file) => str_contains($file->getFilename(), 'create_feature_flags_table'));

    // Assert views directory and some files were created
    expect(File::isDirectory(resource_path('views/vendor/feature-flags')))->toBeTrue();

    expect(File::exists(resource_path('views/vendor/feature-flags/index.blade.php')))->toBeTrue();

    expect($migrationPublished)->toBeTrue();
});
