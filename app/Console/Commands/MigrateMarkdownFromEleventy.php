<?php

namespace App\Console\Commands;

use BenBjurstrom\Prezet\Actions\GetPrezetDisk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateMarkdownFromEleventy extends Command
{
    protected $signature = 'app:migrate-markdown-from-eleventy';

    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = collect(
            Storage::disk(GetPrezetDisk::handle())
                ->allFiles('content')
        )->each(function ($filePath) {
            // get directory
            $directory = pathinfo($filePath, PATHINFO_DIRNAME);
            $exploded = explode('/', $directory);

            if (str_ends_with($filePath, 'index.md')) {
                $newFilename = last($exploded).'.md';

                Storage::disk(GetPrezetDisk::handle())
                    ->move($filePath, 'content/'.$newFilename);
            }

            if (str_ends_with($filePath, '.jpg') || str_ends_with($filePath, '.png')) {
                Storage::disk(GetPrezetDisk::handle())
                    ->move($filePath, 'images/'.last($exploded).'/'.pathinfo($filePath, PATHINFO_BASENAME));

            }
        });

    }
}
