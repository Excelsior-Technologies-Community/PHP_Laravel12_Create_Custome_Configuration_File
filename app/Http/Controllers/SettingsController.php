<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\Setting;
use App\Models\SettingHistory;
use App\Services\EnvWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    protected EnvWriter $envWriter;

    public function __construct(EnvWriter $envWriter)
    {
        $this->envWriter = $envWriter;
    }

    /**
     * Settings dashboard.
     */
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

        $settings = $query
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $groupedSettings = $settings->groupBy('group');

        return view(
            'settings.index',
            compact(
                'settings',
                'groupedSettings',
                'environments',
                'activeEnvironment'
            )
        );
    }

    /**
     * Create setting page.
     */
    public function create(): View
    {
        $environments = Environment::all();

        return view('settings.create', compact('environments'));
    }

    /**
     * Store setting.
     */
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
            'is_active' => 'nullable|boolean',
            'is_sensitive' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_sensitive'] = $request->boolean('is_sensitive');

        if (isset($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        /*
         * Encrypt sensitive values before saving.
         */
        $plainValue = $validated['value'] ?? null;

        if ($validated['is_sensitive'] && $plainValue !== null) {
            $validated['value'] = Crypt::encryptString((string) $plainValue);
        }

        $setting = Setting::create($validated);

        $this->createHistory(
            setting: $setting,
            oldValue: null,
            newValue: $plainValue,
            action: 'created',
            isSensitive: $setting->is_sensitive
        );

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Edit setting.
     */
    public function edit(Setting $setting): View
    {
        $environments = Environment::all();

        $setting->load('environment');

        return view(
            'settings.edit',
            compact('setting', 'environments')
        );
    }

    /**
     * Update setting.
     */
    public function update(
        Request $request,
        Setting $setting
    ): RedirectResponse {
        $validated = $request->validate([
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,select,textarea,json,color,email,url,file',
            'group' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'environment_id' => 'nullable|exists:environments,id',
            'is_active' => 'nullable|boolean',
            'is_sensitive' => 'nullable|boolean',
        ]);

        $oldValue = $setting->value;
        $oldSensitive = $setting->is_sensitive;

        $newValue = $validated['value'] ?? null;
        $newSensitive = $request->boolean('is_sensitive');

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_sensitive'] = $newSensitive;

        if (isset($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        /*
         * Encrypt the new value when sensitive protection is enabled.
         */
        if ($newSensitive && $newValue !== null) {
            $validated['value'] = Crypt::encryptString((string) $newValue);
        } else {
            $validated['value'] = $newValue;
        }

        $setting->update($validated);

        $this->createHistory(
            setting: $setting,
            oldValue: $oldValue,
            newValue: $newValue,
            action: 'updated',
            isSensitive: $oldSensitive || $newSensitive
        );

        $this->envWriter->clearConfigCache();

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Delete setting.
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        $oldValue = $setting->value;
        $key = $setting->key;
        $isSensitive = $setting->is_sensitive;

        $this->createHistory(
            setting: $setting,
            oldValue: $oldValue,
            newValue: null,
            action: 'deleted',
            isSensitive: $isSensitive
        );

        $setting->delete();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                "Setting [{$key}] deleted successfully."
            );
    }

    /**
     * Update .env variable.
     */
    public function updateEnv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'env_key' => 'required|string|max:255',
            'env_value' => 'nullable|string',
        ]);

        $this->envWriter->set(
            $validated['env_key'],
            $validated['env_value']
        );

        $this->envWriter->clearConfigCache();

        return back()->with(
            'success',
            "Environment variable [{$validated['env_key']}] updated and config cache cleared."
        );
    }

    /**
     * Switch application configuration environment.
     */
    public function switchEnvironment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
        ]);

        Environment::where('is_active', true)
            ->update(['is_active' => false]);

        $environment = Environment::findOrFail(
            $validated['environment_id']
        );

        $environment->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                "Switched to [{$environment->name}] environment."
            );
    }

    /**
     * Configuration history.
     */
    public function history(Setting $setting): View
    {
        $histories = $setting
            ->histories()
            ->latest()
            ->get();

        return view(
            'settings.history',
            compact('setting', 'histories')
        );
    }

    /**
     * Rollback setting.
     */
    public function rollback(
        Setting $setting,
        SettingHistory $history
    ): RedirectResponse {
        abort_unless(
            $history->setting_id === $setting->id,
            404
        );

        $currentValue = $setting->value;

        /*
         * History values are encrypted when the setting was sensitive.
         */
        $rollbackValue = $this->decryptHistoryValue(
            $history->old_value,
            $history->is_sensitive
        );

        $storedValue = $rollbackValue;

        if ($setting->is_sensitive && $rollbackValue !== null) {
            $storedValue = Crypt::encryptString(
                (string) $rollbackValue
            );
        }

        $setting->update([
            'value' => $storedValue,
        ]);

        $this->createHistory(
            setting: $setting,
            oldValue: $currentValue,
            newValue: $rollbackValue,
            action: 'rollback',
            isSensitive: $setting->is_sensitive,
            meta: [
                'rolled_back_from' => $history->id,
            ]
        );

        $this->envWriter->clearConfigCache();

        return redirect()
            ->route('settings.history', $setting)
            ->with(
                'success',
                "Setting [{$setting->key}] rolled back successfully."
            );
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $item) {
            $setting = Setting::where(
                'key',
                $item['key']
            )->first();

            if (!$setting) {
                continue;
            }

            $oldValue = $setting->value;
            $newValue = $item['value'] ?? null;

            $storedValue = $newValue;

            if ($setting->is_sensitive && $newValue !== null) {
                $storedValue = Crypt::encryptString(
                    (string) $newValue
                );
            }

            $setting->update([
                'value' => $storedValue,
            ]);

            $this->createHistory(
                setting: $setting,
                oldValue: $oldValue,
                newValue: $newValue,
                action: 'bulk_updated',
                isSensitive: $setting->is_sensitive
            );
        }

        $this->envWriter->clearConfigCache();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Bulk settings updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration Export
    |--------------------------------------------------------------------------
    */

    /**
     * Export settings for the active environment.
     */
    public function export(Request $request): StreamedResponse
    {
        $environmentId = $request->input('environment_id');

        $environment = $environmentId
            ? Environment::findOrFail($environmentId)
            : Environment::where('is_active', true)->first();

        $query = Setting::query();

        if ($environment) {
            $query->where(function ($q) use ($environment) {
                $q->where('environment_id', $environment->id)
                    ->orWhereNull('environment_id');
            });
        }

        $settings = $query
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $exportData = [
            'application' => config('app.name'),
            'exported_at' => now()->toIso8601String(),
            'environment' => $environment
                ? [
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'key' => $environment->key,
                ]
                : null,
            'settings' => [],
        ];

        foreach ($settings as $setting) {
            /*
             * Never export plaintext sensitive values.
             */
            $exportData['settings'][] = [
                'key' => $setting->key,
                'value' => $setting->is_sensitive
                    ? null
                    : $this->normalizeValue($setting->value),
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'options' => $setting->options,
                'is_active' => $setting->is_active,
                'is_sensitive' => $setting->is_sensitive,
                'environment_key' => $setting->environment?->key,
                'value_protected' => $setting->is_sensitive,
            ];
        }

        $filename = 'configuration-' .
            ($environment?->key ?? 'all') .
            '-' .
            now()->format('Y-m-d-His') .
            '.json';

        return response()->streamDownload(
            function () use ($exportData) {
                echo json_encode(
                    $exportData,
                    JSON_PRETTY_PRINT |
                        JSON_UNESCAPED_SLASHES
                );
            },
            $filename,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration Import
    |--------------------------------------------------------------------------
    */

    /**
     * Import settings from JSON configuration file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'configuration_file' => [
                'required',
                'file',
                'mimes:json,txt',
                'max:5120',
            ],
            'environment_id' => [
                'nullable',
                'exists:environments,id',
            ],
        ]);

        $file = $request->file('configuration_file');

        try {
            $content = File::get($file->getRealPath());

            $data = json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'Invalid configuration file. Please upload a valid JSON file.'
            );
        }

        if (
            !is_array($data) ||
            !isset($data['settings']) ||
            !is_array($data['settings'])
        ) {
            return back()->with(
                'error',
                'Invalid configuration structure. The file must contain a settings array.'
            );
        }

        $targetEnvironment = null;

        if ($request->filled('environment_id')) {
            $targetEnvironment = Environment::findOrFail(
                $request->environment_id
            );
        } elseif (
            isset($data['environment']['key']) &&
            $data['environment']['key']
        ) {
            $targetEnvironment = Environment::where(
                'key',
                $data['environment']['key']
            )->first();
        }

        if (!$targetEnvironment) {
            $targetEnvironment = Environment::where(
                'is_active',
                true
            )->first();
        }

        $imported = 0;
        $created = 0;
        $skipped = 0;

        foreach ($data['settings'] as $item) {
            if (
                !is_array($item) ||
                empty($item['key'])
            ) {
                $skipped++;
                continue;
            }

            $key = $item['key'];

            $setting = Setting::where(
                'key',
                $key
            )->first();

            /*
             * Determine target environment.
             */
            $itemEnvironment = null;

            if (
                !empty($item['environment_key'])
            ) {
                $itemEnvironment = Environment::where(
                    'key',
                    $item['environment_key']
                )->first();
            }

            $environment = $itemEnvironment
                ?? $targetEnvironment;

            /*
             * Do not allow a sensitive exported setting to overwrite
             * its secret with null.
             */
            $isSensitive = (bool) (
                $item['is_sensitive'] ?? false
            );

            if ($setting) {
                $oldValue = $setting->value;

                $updateData = [
                    'type' => $item['type'] ?? $setting->type,
                    'group' => $item['group'] ?? $setting->group,
                    'label' => $item['label'] ?? $setting->label,
                    'description' => $item['description']
                        ?? $setting->description,
                    'options' => isset($item['options'])
                        ? $item['options']
                        : $setting->options,
                    'is_active' => $item['is_active'] ?? $setting->is_active,
                    'is_sensitive' => $isSensitive,
                ];

                if ($environment) {
                    $updateData['environment_id'] = $environment->id;
                }

                /*
                 * Only import value when a real value exists.
                 */
                if (
                    array_key_exists('value', $item) &&
                    $item['value'] !== null &&
                    !$isSensitive
                ) {
                    $updateData['value'] = $this->normalizeImportValue(
                        $item['value'],
                        $updateData['type']
                    );
                }

                /*
                 * If a non-sensitive existing value becomes sensitive,
                 * encrypt its current value.
                 */
                if (
                    $isSensitive &&
                    !$setting->is_sensitive &&
                    $setting->value !== null
                ) {
                    $updateData['value'] = Crypt::encryptString(
                        (string) $setting->value
                    );
                }

                $setting->update($updateData);

                $newValue = $setting->value;

                $this->createHistory(
                    setting: $setting,
                    oldValue: $oldValue,
                    newValue: $newValue,
                    action: 'imported',
                    isSensitive: $setting->is_sensitive,
                    meta: [
                        'source' => 'configuration_import',
                    ]
                );

                $imported++;
            } else {
                /*
                 * Do not create a sensitive setting without a real secret.
                 * The setting itself can still be created with null value.
                 */
                $plainValue = (
                    !$isSensitive &&
                    array_key_exists('value', $item)
                )
                    ? $this->normalizeImportValue(
                        $item['value'],
                        $item['type'] ?? 'text'
                    )
                    : null;

                $storedValue = $plainValue;

                if (
                    $isSensitive &&
                    $plainValue !== null
                ) {
                    $storedValue = Crypt::encryptString(
                        (string) $plainValue
                    );
                }

                $setting = Setting::create([
                    'key' => $key,
                    'value' => $storedValue,
                    'type' => $item['type'] ?? 'text',
                    'group' => $item['group'] ?? 'general',
                    'label' => $item['label'] ?? $key,
                    'description' => $item['description'] ?? null,
                    'options' => $item['options'] ?? null,
                    'is_active' => $item['is_active'] ?? true,
                    'is_sensitive' => $isSensitive,
                    'environment_id' => $environment?->id,
                ]);

                $this->createHistory(
                    setting: $setting,
                    oldValue: null,
                    newValue: $plainValue,
                    action: 'imported',
                    isSensitive: $isSensitive,
                    meta: [
                        'source' => 'configuration_import',
                    ]
                );

                $created++;
            }
        }

        $this->envWriter->clearConfigCache();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                "Configuration imported successfully. Updated: {$imported}, Created: {$created}, Skipped: {$skipped}."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Create a history entry.
     */
    protected function createHistory(
        Setting $setting,
        mixed $oldValue,
        mixed $newValue,
        string $action,
        bool $isSensitive,
        array $meta = []
    ): SettingHistory {
        $oldValue = $this->normalizeValue($oldValue);
        $newValue = $this->normalizeValue($newValue);

        if ($isSensitive) {
            $oldValue = $oldValue !== null
                ? Crypt::encryptString((string) $oldValue)
                : null;

            $newValue = $newValue !== null
                ? Crypt::encryptString((string) $newValue)
                : null;
        }

        return SettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => Auth::user()?->email ?? 'system',
            'action' => $action,
            'is_sensitive' => $isSensitive,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Decrypt a history value internally.
     */
    protected function decryptHistoryValue(
        mixed $value,
        bool $isSensitive
    ): mixed {
        if ($value === null) {
            return null;
        }

        if (!$isSensitive) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normalize values before storing/exporting history.
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_SLASHES
            );
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * Normalize imported values.
     */
    protected function normalizeImportValue(
        mixed $value,
        string $type
    ): mixed {
        if ($value === null) {
            return null;
        }

        if ($type === 'json') {
            if (is_array($value)) {
                return json_encode(
                    $value,
                    JSON_UNESCAPED_SLASHES
                );
            }

            json_decode((string) $value);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException(
                    'Invalid JSON configuration value.'
                );
            }

            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
