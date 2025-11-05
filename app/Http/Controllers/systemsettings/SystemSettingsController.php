<?php

namespace App\Http\Controllers\systemsettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\system_settings;
use Illuminate\Support\Facades\Storage;

class SystemSettingsController extends Controller
{
    //
    public function addSystemSettings(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $query = system_settings::query();
        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('key', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%");
            });
        }

        $systemSettings = $query->orderBy('created_at', 'desc')->get();
        return view('system_settings.system_settings', compact('systemSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:system_settings,id',
        ]);

        $setting = system_settings::findOrFail($request->id);

        try {
            if (strtolower($setting->type) === 'image') {
                $request->validate([
                    'description_file' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
                ]);
                $path = $request->file('description_file')->store('public/system_settings');
                // Normalize to public path
                $publicPath = str_replace('public/', 'storage/', $path);
                $setting->description = $publicPath;
            } else {
                $request->validate([
                    'description_text' => 'required|string',
                ]);
                $setting->description = $request->description_text;
            }
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully.',
                'data' => [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get($id)
    {
        try {
            $setting = system_settings::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'status' => $setting->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load setting: ' . $e->getMessage()
            ], 404);
        }
    }
}
