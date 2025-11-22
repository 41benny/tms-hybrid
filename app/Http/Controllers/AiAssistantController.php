<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiAnalysisService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index()
    {
        return view('ai-assistant/index');
    }

    public function ask(Request $request, AiAnalysisService $ai)
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:1000']]);
        $result = $ai->analyze($data['question']);

        return response()->json($result);
    }
}
