<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NotifyLatestPatchNote extends Command
{
    protected $signature = 'notify:latest-patch-note';

    protected $description = 'Send latest patch note notification to all users';

    public function handle(): int
    {
        $this->info('Starting patch note notification process...');

        $patchNote = [
            'title'   => 'Latest Update',
            'message' => 'System update deployed successfully.',
            'version' => 'v1.0.0',
        ];

        $this->line('Patch Note:');
        $this->line('Title: ' . $patchNote['title']);
        $this->line('Version: ' . $patchNote['version']);
        $this->line('Message: ' . $patchNote['message']);

        $this->info('Patch note notification completed.');

        return Command::SUCCESS;
    }
}