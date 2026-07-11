<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AICommandService;

class AIController extends Controller
{
    protected AICommandService $aiService;

    public function __construct(AICommandService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function execute(Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:1000'
        ]);

        $result = $this->aiService->process(
            $request->command
        );

        return response()->json($result);
    }
}