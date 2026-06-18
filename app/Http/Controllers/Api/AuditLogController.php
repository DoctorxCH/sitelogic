<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'wer' => 'nullable|string',
            'wann' => 'required|date',
            'punkt_deaktiviert' => 'nullable|string',
            'status_geaendert' => 'nullable|string',
            'action_type_local' => 'nullable|string',
            'details' => 'nullable|string',
            'entity_id_local' => 'nullable|integer',
        ]);

        try {
            DB::table('audit_logs')->insert([
                'wer' => $validatedData['wer'] ?? null,
                'wann' => $validatedData['wann'],
                'punkt_deaktiviert' => $validatedData['punkt_deaktiviert'] ?? null,
                'status_geaendert' => $validatedData['status_geaendert'] ?? null,
                'action_type_local' => $validatedData['action_type_local'] ?? null,
                'details' => $validatedData['details'] ?? null,
                'entity_id_local' => $validatedData['entity_id_local'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Audit log successfully created'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create audit log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
