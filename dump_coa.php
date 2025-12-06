<?php
use App\Models\Accounting\ChartOfAccount;

$accounts = ChartOfAccount::orderBy('code')->get();
foreach ($accounts as $a) {
    echo $a->code . " | " . $a->name . " | " . $a->type . " | " . ($a->is_cash ? 'C' : '') . ($a->is_bank ? 'B' : '') . PHP_EOL;
}
