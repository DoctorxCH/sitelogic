<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ChecklistItemController extends Controller
{
    public function index()
    {
        $items = DB::table('checklist_items')->get();
        return response()->json(['data' => $items], 200);
    }
}
