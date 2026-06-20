<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BepType;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobDetailController extends Controller
{
    /**
     * Get all active BEP types as JSON
     */
    public function getBepTypes(): JsonResponse
    {
        $types = BepType::where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return response()->json([
            'types' => $types,
        ]);
    }

    /**
     * Update job's BEP type
     */
    public function updateJobType(Request $request, Job $job): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|exists:bep_types,name',
        ]);

        $bepType = BepType::where('name', $validated['type'])->firstOrFail();
        $job->update(['bep_type_id' => $bepType->id]);

        return response()->json([
            'success' => true,
            'message' => 'BEP Typ erfolgreich aktualisiert',
            'type' => $validated['type'],
        ]);
    }
}
