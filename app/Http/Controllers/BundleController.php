<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\GiftPurchase;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BundleController extends Controller
{
    /**
     * List all active bundles
     */
    public function index()
    {
        $bundles = Bundle::where('active', true)
            ->with('courses:id,title,slug,thumbnail,price')
            ->get()
            ->map(function ($bundle) {
                $bundle->savings_percent = $bundle->savings_percent;
                $bundle->course_count = $bundle->courses->count();
                return $bundle;
            });

        return response()->json($bundles);
    }

    /**
     * Get single bundle details
     */
    public function show(Bundle $bundle)
    {
        $bundle->load('courses:id,title,slug,thumbnail,price,subtitle');
        $bundle->savings_percent = $bundle->savings_percent;

        return response()->json($bundle);
    }
}
