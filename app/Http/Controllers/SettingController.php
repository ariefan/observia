<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$request->user()->is_super_user) {
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $settings = Setting::getAllGroupedByCategory();
        
        return Inertia::render('AdminSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function create()
    {
        return Inertia::render('AdminSettings/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key|max:255',
            'label' => 'required|string|max:255',
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,textarea,select',
            'options' => 'nullable|json',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['options'] = $validated['options'] ? json_decode($validated['options'], true) : null;

        Setting::create($validated);

        return redirect()->route('admin.settings.index')->with('message', 'Setting berhasil dibuat.');
    }

    public function show(Setting $setting)
    {
        return Inertia::render('AdminSettings/Show', [
            'setting' => $setting,
        ]);
    }

    public function edit(Setting $setting)
    {
        return Inertia::render('AdminSettings/Form', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'label' => 'required|string|max:255',
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,textarea,select',
            'options' => 'nullable|json',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['options'] = $validated['options'] ? json_decode($validated['options'], true) : null;

        $setting->update($validated);

        return redirect()->route('admin.settings.index')->with('message', 'Setting berhasil diperbarui.');
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return redirect()->route('admin.settings.index')->with('message', 'Setting berhasil dihapus.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.value' => 'nullable|string',
        ]);
    
        foreach ($validated['settings'] as $id => $data) {
            $setting = Setting::find($id);
            if ($setting) {
                $setting->update(['value' => $data['value']]);
            }
        }
    
        return redirect()->route('admin.settings.index')->with('message', 'Pengaturan berhasil disimpan.');
    }
}
