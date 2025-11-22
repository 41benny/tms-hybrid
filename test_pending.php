<?php
require __DIR__.'/vendor/autoload.php';
use App\Services\Ai\AiAnalysisService;

$service = new AiAnalysisService();
$question = 'job order yang belum invoice';
$result = $service->analyze($question);
print_r($result);
?>
