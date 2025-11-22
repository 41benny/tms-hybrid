<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Accounting\ChartOfAccount;

class VerifyCoaCommand extends Command
{
    protected $signature = 'acc:verify-coa';
    protected $description = 'Verifikasi keberadaan akun penting & tambahan bank.';

    private array $requiredCodes = [
        '1630','1640','2411','2412','2413','2414','2511','2512','2513','2514'
    ];

    public function handle(): int
    {
        $missing = [];
        foreach ($this->requiredCodes as $code) {
            $exists = ChartOfAccount::where('code',$code)->exists();
            $this->line($code.' => '.($exists ? 'OK' : 'MISSING'));
            if (! $exists) $missing[] = $code;
        }
        if ($missing) {
            $this->error('Missing codes: '.implode(', ',$missing));
            return Command::FAILURE;
        }
        $this->info('Semua akun wajib tersedia.');
        return Command::SUCCESS;
    }
}
