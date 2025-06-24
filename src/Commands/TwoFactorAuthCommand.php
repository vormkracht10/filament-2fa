<?php

namespace Backstage\TwoFactorAuth\Commands;

use Illuminate\Console\Command;

class TwoFactorAuthCommand extends Command
{
    public $signature = 'filament-two-factor-auth';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
