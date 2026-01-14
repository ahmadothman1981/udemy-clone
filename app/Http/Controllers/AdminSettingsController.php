<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    // ==========================================
    // CATEGORY MANAGEMENT
    // ==========================================

    public function categories()
    {
        $categories = Category::withCount('courses')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'sometimes|string|max:255|unique:categories,slug',
            'description' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|exists:categories,id',
        ]);

        if (!isset($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|exists:categories,id',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh()
        ]);
    }

    public function destroyCategory(Category $category)
    {
        // Check if category has courses
        if ($category->courses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing courses. Please move courses first.'
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    // ==========================================
    // PLATFORM SETTINGS
    // ==========================================

    public function platformSettings()
    {
        return response()->json([
            'payment' => PlatformSetting::getGroup('payment'),
            'security' => PlatformSetting::getGroup('security'),
            'localization' => PlatformSetting::getGroup('localization'),
            'general' => PlatformSetting::getGroup('general'),
        ]);
    }

    public function updatePlatformSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($validated['settings'] as $setting) {
            PlatformSetting::setValue($setting['key'], $setting['value']);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $this->platformSettings()->original
        ]);
    }

    // ==========================================
    // PAYMENT SETTINGS
    // ==========================================

    public function paymentSettings()
    {
        return response()->json(PlatformSetting::getGroup('payment'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $validated = $request->validate([
            'platform_commission' => 'sometimes|integer|min:0|max:100',
            'payout_minimum' => 'sometimes|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::setValue($key, $value);
        }

        return response()->json([
            'message' => 'Payment settings updated',
            'settings' => PlatformSetting::getGroup('payment')
        ]);
    }

    // ==========================================
    // SECURITY SETTINGS
    // ==========================================

    public function securitySettings()
    {
        return response()->json(PlatformSetting::getGroup('security'));
    }

    public function updateSecuritySettings(Request $request)
    {
        $validated = $request->validate([
            'password_min_length' => 'sometimes|integer|min:6|max:32',
            'session_timeout' => 'sometimes|integer|min:5|max:1440',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::setValue($key, $value);
        }

        return response()->json([
            'message' => 'Security settings updated',
            'settings' => PlatformSetting::getGroup('security')
        ]);
    }

    // ==========================================
    // LOCALIZATION SETTINGS
    // ==========================================

    public function localizationSettings()
    {
        return response()->json(PlatformSetting::getGroup('localization'));
    }

    public function updateLocalizationSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled_languages' => 'sometimes|array',
            'enabled_languages.*' => 'string|max:10',
            'default_language' => 'sometimes|string|max:10',
        ]);

        if (isset($validated['enabled_languages'])) {
            PlatformSetting::setValue('enabled_languages', $validated['enabled_languages']);
        }

        if (isset($validated['default_language'])) {
            PlatformSetting::setValue('default_language', $validated['default_language']);
        }

        return response()->json([
            'message' => 'Localization settings updated',
            'settings' => PlatformSetting::getGroup('localization')
        ]);
    }
}
