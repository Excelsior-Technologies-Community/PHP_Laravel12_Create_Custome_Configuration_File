<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\Setting;
use App\Models\SettingHistory;
use App\Services\EnvWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    protected EnvWriter $envWriter;

    public function __construct(EnvWriter $envWriter)
    {
        $this->envWriter = $envWriter;
    }

    public function index(): View
    {
        $activeEnvironment = Environment::where('is_active', true)->first();
        $environments = Environment::all();

        $query = Setting::query();

        if ($activeEnvironment) {
            $query->where(function ($q) use ($activeEnvironment) {
                $q->where('environment_id', $activeEnvironment->id)
                  ->orWhereNull('environment_id');
            });
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        $groupedSettings = $settings->groupBy('group');

        return view('settings.index', compact('settings', 'groupedSettings', 'environments', 'activeEnvironment'));
    }

    public function create(): View
    {
        $environments = Environment::all();
        return view('settings.create', compact('environments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,select,textarea,json,color,email,url,file',
            'group' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'environment_id' => 'nullable|exists:environments,id',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['options']) && is_array($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        $setting = Setting::create($validated);

        SettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => null,
            'new_value' => $validated['value'],
            'changed_by' => Auth::user()?->email ?? 'system',
            'action' => 'created',
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Setting created successfully.');
    }

    public function edit(Setting $setting): View
    {
        $environments = Environment::all();
        $setting->load('environment');
        return view('settings.edit', compact('setting', 'environments'));
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,select,textarea,json,color,email,url,file',
            'group' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'environment_id' => 'nullable|exists:environments,id',
            'is_active' => 'boolean',
        ]);

        $oldValue = $setting->value;

        if (isset($validated['options']) && is_array($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        $setting->update($validated);

        SettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $oldValue,
            'new_value' => $validated['value'],
            'changed_by' => Auth::user()?->email ?? 'system',
            'action' => 'updated',
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $oldValue = $setting->value;
        $key = $setting->key;

        SettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $oldValue,
            'new_value' => null,
            'changed_by' => Auth::user()?->email ?? 'system',
            'action' => 'deleted',
        ]);

        $setting->delete();

        return redirect()->route('settings.index')
            ->with('success', "Setting [{$key}] deleted successfully.");
    }

    public function updateEnv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'env_key' => 'required|string|max:255',
            'env_value' => 'nullable|string',
        ]);

        $this->envWriter->set($validated['env_key'], $validated['env_value']);
        $this->envWriter->clearConfigCache();

        return back()->with('success', "Environment variable [{$validated['env_key']}] updated and config cache cleared.");
    }

    public function switchEnvironment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
        ]);

        Environment::where('is_active', true)->update(['is_active' => false]);

        $environment = Environment::findOrFail($validated['environment_id']);
        $environment->update(['is_active' => true]);

        return redirect()->route('settings.index')
            ->with('success', "Switched to [{$environment->name}] environment.");
    }

    public function history(Setting $setting): View
    {
        $histories = $setting->histories()->latest()->get();
        return view('settings.history', compact('setting', 'histories'));
    }

    public function rollback(Setting $setting, SettingHistory $history): RedirectResponse
    {
        $oldValue = $history->old_value;
        $newValue = $setting->value;

        $setting->update(['value' => $oldValue]);

        SettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $newValue,
            'new_value' => $oldValue,
            'changed_by' => Auth::user()?->email ?? 'system',
            'action' => 'rollback',
            'meta' => json_encode(['rolled_back_from' => $history->id]),
        ]);

        return redirect()->route('settings.index')
            ->with('success', "Setting [{$setting->key}] rolled back successfully.");
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $item) {
            $setting = Setting::where('key', $item['key'])->first();

            if ($setting) {
                $oldValue = $setting->value;
                $setting->update(['value' => $item['value']]);

                SettingHistory::create([
                    'setting_id' => $setting->id,
                    'old_value' => $oldValue,
                    'new_value' => $item['value'],
                    'changed_by' => Auth::user()?->email ?? 'system',
                    'action' => 'bulk_updated',
                ]);
            }
        }

        $this->envWriter->clearConfigCache();

        return redirect()->route('settings.index')
            ->with('success', 'Bulk settings updated successfully.');
    }
}