<?php
use App\Models\Accounting\ChartOfAccount;

$accounts = ChartOfAccount::orderBy('code')->get();
foreach ($accounts as $a) {
    echo $a->code . " | " . $a->name . " | " . $a->type . PHP_EOL;
}
