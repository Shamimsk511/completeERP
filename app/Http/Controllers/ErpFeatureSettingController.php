<?php

namespace App\Http\Controllers;

use App\Models\ErpFeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErpFeatureSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:erp-features-view', ['only' => ['index']]);
        $this->middleware('permission:erp-features-edit', ['only' => ['update', 'toggle']]);
    }

    /**
     * Display feature settings page
     */
    public function index()
    {
        // Ensure any newly added defaults appear in the settings list
        ErpFeatureSetting::seedDefaults();

        $featuresGrouped = ErpFeatureSetting::getAllGrouped();
        $groups = ErpFeatureSetting::GROUPS;

        return view('erp-settings.features', compact('featuresGrouped', 'groups'));
    }

    /**
     * Update all feature settings
     */
    public function update(Request $request)
    {
        try {
            $enabledFeatures = $request->input('features', []);

            // Get all features
            $allFeatures = ErpFeatureSetting::all();

            foreach ($allFeatures as $feature) {
                $isEnabled = in_array($feature->feature_key, $enabledFeatures);
                $feature->is_enabled = $isEnabled;
                $feature->save();
            }

            // Clear cache
            ErpFeatureSetting::clearCache();

            Log::info('ERP Feature settings updated by user ' . auth()->id());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature settings updated successfully.'
                ]);
            }

            return redirect()->back()->with('success', 'Feature settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update ERP feature settings: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update settings.');
        }
    }

    /**
     * Toggle a single feature
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'feature_key' => 'required|string|exists:erp_feature_settings,feature_key',
            'is_enabled' => 'required|boolean',
        ]);

        try {
            $updated = ErpFeatureSetting::updateFeature(
                $request->feature_key,
                $request->is_enabled
            );

            if ($updated) {
                Log::info("Feature '{$request->feature_key}' " . ($request->is_enabled ? 'enabled' : 'disabled') . " by user " . auth()->id());

                return response()->json([
                    'success' => true,
                    'message' => 'Feature updated successfully.',
                    'is_enabled' => $request->is_enabled
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to toggle feature: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature.'
            ], 500);
        }
    }

    /**
     * Seed default features (useful for first-time setup)
     */
    public function seedDefaults(Request $request)
    {
        try {
            ErpFeatureSetting::seedDefaults();

            return response()->json([
                'success' => true,
                'message' => 'Default features seeded successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to seed defaults: ' . $e->getMessage()
            ], 500);
        }
    }
}
