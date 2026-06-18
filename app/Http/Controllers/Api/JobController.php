<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::with('jobAssets')->get();
        return response()->json(['data' => $jobs], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pid' => 'required|string',
            'adresse' => 'required|string',
            'projekt_typ' => 'required|string',
            'bauleiter' => 'required|string',
            'technologie' => 'required|string',

            // Asset Daten (arrays von Werten)
            'asset_ids' => 'nullable|array',
            'flat_ids' => 'nullable|array',
            'kabel_bep_muffentypen' => 'nullable|array',
            'asset_metadaten' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $job = Job::create([
                'pid' => $validatedData['pid'],
                'adresse' => $validatedData['adresse'],
                'projekt_typ' => $validatedData['projekt_typ'],
                'bauleiter' => $validatedData['bauleiter'],
                'technologie' => $validatedData['technologie'],
            ]);

            JobAsset::create([
                'job_id' => $job->id,
                'asset_ids' => $validatedData['asset_ids'] ?? [],
                'flat_ids' => $validatedData['flat_ids'] ?? [],
                'kabel_bep_muffentypen' => $validatedData['kabel_bep_muffentypen'] ?? [],
                'asset_metadaten' => $validatedData['asset_metadaten'] ?? [],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Job successfully created',
                'job' => $job->load('jobAssets')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create job',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
