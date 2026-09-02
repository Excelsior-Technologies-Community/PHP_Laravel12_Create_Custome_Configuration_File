@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    <!-- ========================================================= -->
    <!-- Top Stats -->
    <!-- ========================================================= -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Total Settings -->
        <div class="card p-5 flex items-center justify-between">

            <div>
                <p class="text-xs text-slate-500 font-medium">
                    Total Settings
                </p>

                <h3 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ $groupedSettings->sum->count() }}
                </h3>
            </div>

            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-list-check"></i>
            </div>

        </div>


        <!-- Environments -->
        <div class="card p-5 flex items-center justify-between">

            <div>
                <p class="text-xs text-slate-500 font-medium">
                    Environments
                </p>

                <h3 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ $environments->count() }}
                </h3>
            </div>

            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-server"></i>
            </div>

        </div>


        <!-- Setting Groups -->
        <div class="card p-5 flex items-center justify-between">

            <div>
                <p class="text-xs text-slate-500 font-medium">
                    Setting Groups
                </p>

                <h3 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ $groupedSettings->keys()->count() }}
                </h3>
            </div>

            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-folder-tree"></i>
            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Controls Row -->
    <!-- ========================================================= -->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- ===================================================== -->
        <!-- Environment Switcher -->
        <!-- ===================================================== -->

        <div class="card p-5 space-y-4">

            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 border-b pb-3">

                <i class="fa-solid fa-arrows-rotate text-blue-500"></i>

                Environment Switcher

            </h2>

            <form
                action="{{ route('settings.switchEnvironment') }}"
                method="POST"
                class="flex items-center gap-3"
            >

                @csrf

                <select
                    name="environment_id"
                    class="input-field"
                >

                    @foreach($environments as $env)

                        <option
                            value="{{ $env->id }}"
                            {{ $env->is_active ? 'selected' : '' }}
                        >
                            {{ $env->name }} ({{ $env->key }})
                        </option>

                    @endforeach

                </select>

                <button
                    type="submit"
                    class="btn-primary whitespace-nowrap text-xs"
                >
                    Switch
                </button>

            </form>

        </div>


        <!-- ===================================================== -->
        <!-- .env Direct Writer -->
        <!-- ===================================================== -->

        <div class="card p-5 space-y-4">

            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 border-b pb-3">

                <i class="fa-solid fa-file-pen text-amber-500"></i>

                Quick .env Editor

            </h2>

            <form
                action="{{ route('settings.envUpdate') }}"
                method="POST"
                class="flex items-center gap-3"
            >

                @csrf

                <input
                    type="text"
                    name="env_key"
                    required
                    placeholder="KEY (e.g. APP_DEBUG)"
                    class="input-field font-mono text-xs"
                >

                <input
                    type="text"
                    name="env_value"
                    placeholder="VALUE"
                    class="input-field font-mono text-xs"
                >

                <button
                    type="submit"
                    class="btn-primary whitespace-nowrap text-xs"
                >
                    Save .env
                </button>

            </form>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Configuration Backup -->
    <!-- ========================================================= -->

    <div class="card p-5">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

            <!-- Information -->
            <div>

                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">

                    <i class="fa-solid fa-shield-halved text-indigo-500"></i>

                    Configuration Backup

                </h2>

                <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                    Export or import environment-specific application
                    settings. Sensitive configuration values are encrypted
                    and protected automatically.
                </p>

            </div>


            <!-- Actions -->
            <div class="flex flex-wrap gap-3">

                <!-- Export -->
                <a
                    href="{{ route('settings.export', [
                        'environment_id' => $activeEnvironment?->id
                    ]) }}"
                    class="btn-primary whitespace-nowrap text-xs inline-flex items-center"
                >

                    <i class="fa-solid fa-download mr-2"></i>

                    Export Configuration

                </a>


                <!-- Import -->
                <button
                    type="button"
                    onclick="document.getElementById('importConfigurationModal').classList.remove('hidden')"
                    class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 whitespace-nowrap text-xs inline-flex items-center"
                >

                    <i class="fa-solid fa-upload mr-2"></i>

                    Import Configuration

                </button>

            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Security Information -->
    <!-- ========================================================= -->

    <div class="card p-4 border border-amber-100 bg-amber-50">

        <div class="flex items-start gap-3">

            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">

                <i class="fa-solid fa-lock"></i>

            </div>

            <div>

                <h3 class="text-sm font-bold text-amber-900">
                    Sensitive Configuration Protection
                </h3>

                <p class="text-xs text-amber-800 mt-1 leading-5">
                    Settings marked as sensitive are encrypted in the
                    database and masked on this dashboard. Their historical
                    values are also protected and sensitive values are never
                    included in configuration exports as plaintext.
                </p>

            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Grouped Settings Tables -->
    <!-- ========================================================= -->

    @forelse($groupedSettings as $group => $groupSettings)

        <div class="card overflow-hidden">

            <!-- Group Header -->
            <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex items-center justify-between">

                <h2 class="text-sm font-bold text-slate-700 capitalize flex items-center gap-2">

                    <i class="fa-solid fa-layer-group text-slate-400"></i>

                    {{ $group }} Group

                </h2>

                <span class="badge badge-gray">
                    {{ $groupSettings->count() }} Items
                </span>

            </div>


            <!-- Table -->
            <div class="overflow-x-auto">

                <table class="w-full text-left text-xs">

                    <!-- Table Header -->
                    <thead class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">

                        <tr>

                            <th class="px-5 py-3">
                                Key
                            </th>

                            <th class="px-5 py-3">
                                Label
                            </th>

                            <th class="px-5 py-3">
                                Type
                            </th>

                            <th class="px-5 py-3">
                                Value
                            </th>

                            <th class="px-5 py-3">
                                Scope
                            </th>

                            <th class="px-5 py-3 text-right">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- Table Body -->
                    <tbody class="divide-y divide-slate-100">

                        @foreach($groupSettings as $setting)

                            <tr class="hover:bg-slate-50 transition">


                                <!-- ================================================= -->
                                <!-- Key -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3">

                                    <div class="flex items-center gap-2">

                                        <span class="font-mono font-medium text-slate-800">
                                            {{ $setting->key }}
                                        </span>

                                        @if($setting->is_sensitive)

                                            <span
                                                class="text-amber-600"
                                                title="Sensitive configuration"
                                            >
                                                <i class="fa-solid fa-lock text-[11px]"></i>
                                            </span>

                                        @endif

                                    </div>

                                </td>


                                <!-- ================================================= -->
                                <!-- Label -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3 text-slate-600">
                                    {{ $setting->label }}
                                </td>


                                <!-- ================================================= -->
                                <!-- Type -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3">

                                    <div class="flex items-center gap-2">

                                        <span class="badge badge-blue">
                                            {{ $setting->type }}
                                        </span>

                                        @if($setting->is_sensitive)

                                            <span
                                                class="badge badge-gray"
                                                title="Sensitive configuration"
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </span>

                                        @endif

                                    </div>

                                </td>


                                <!-- ================================================= -->
                                <!-- Value -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3 font-mono text-slate-600 max-w-xs">

                                    @if($setting->is_sensitive)

                                        <!-- Protected Value -->

                                        <div class="flex items-center gap-2">

                                            <span class="text-slate-400 tracking-wider">
                                                ••••••••••••
                                            </span>

                                            <span class="badge badge-gray">

                                                <i class="fa-solid fa-lock mr-1"></i>

                                                Protected

                                            </span>

                                        </div>

                                    @else

                                        <!-- Normal Value -->

                                        @if(is_array($setting->value))

                                            <pre class="text-[11px] whitespace-pre-wrap max-w-md overflow-hidden">{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</pre>

                                        @else

                                            <span
                                                class="truncate block max-w-xs"
                                                title="{{ $setting->value }}"
                                            >
                                                {{ $setting->value ?? 'null' }}
                                            </span>

                                        @endif

                                    @endif

                                </td>


                                <!-- ================================================= -->
                                <!-- Scope -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3">

                                    @if($setting->environment)

                                        <span class="inline-flex items-center gap-1 text-slate-500">

                                            <i class="fa-solid fa-server text-[10px]"></i>

                                            {{ $setting->environment->name }}

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1 text-slate-400">

                                            <i class="fa-solid fa-globe text-[10px]"></i>

                                            Global

                                        </span>

                                    @endif

                                </td>


                                <!-- ================================================= -->
                                <!-- Actions -->
                                <!-- ================================================= -->

                                <td class="px-5 py-3 text-right whitespace-nowrap">

                                    <!-- History -->

                                    <a
                                        href="{{ route('settings.history', $setting) }}"
                                        class="text-slate-400 hover:text-blue-600 p-1"
                                        title="History"
                                    >

                                        <i class="fa-solid fa-clock-rotate-left"></i>

                                    </a>


                                    <!-- Edit -->

                                    <a
                                        href="{{ route('settings.edit', $setting) }}"
                                        class="text-slate-400 hover:text-blue-600 p-1"
                                        title="Edit"
                                    >

                                        <i class="fa-solid fa-pen-to-square"></i>

                                    </a>


                                    <!-- Delete -->

                                    <form
                                        action="{{ route('settings.destroy', $setting) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Delete this setting?')"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="text-slate-400 hover:text-rose-600 p-1"
                                            title="Delete"
                                        >

                                            <i class="fa-solid fa-trash-can"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @empty

        <!-- No Settings -->
        <div class="card p-10 text-center">

            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">

                <i class="fa-solid fa-sliders"></i>

            </div>

            <h3 class="text-sm font-bold text-slate-700 mt-4">
                No Settings Found
            </h3>

            <p class="text-xs text-slate-500 mt-1">
                Create your first configuration setting to get started.
            </p>

            <a
                href="{{ route('settings.create') }}"
                class="btn-primary inline-flex items-center mt-4 text-xs"
            >

                <i class="fa-solid fa-plus mr-2"></i>

                Add New Setting

            </a>

        </div>

    @endforelse

</div>


<!-- ============================================================= -->
<!-- Import Configuration Modal -->
<!-- ============================================================= -->

<div
    id="importConfigurationModal"
    class="hidden fixed inset-0 z-50 overflow-y-auto"
>

    <div class="flex items-center justify-center min-h-screen px-4">

        <!-- Overlay -->

        <div
            class="fixed inset-0 bg-black/50"
            onclick="document.getElementById('importConfigurationModal').classList.add('hidden')"
        ></div>


        <!-- Modal -->

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 z-10">


            <!-- ================================================= -->
            <!-- Modal Header -->
            <!-- ================================================= -->

            <div class="flex items-center justify-between mb-5">

                <div>

                    <h2 class="text-lg font-bold text-slate-800">

                        <i class="fa-solid fa-upload text-indigo-500 mr-1"></i>

                        Import Configuration

                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        Upload a previously exported JSON configuration.
                    </p>

                </div>


                <!-- Close -->

                <button
                    type="button"
                    onclick="document.getElementById('importConfigurationModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-700 text-xl"
                >

                    &times;

                </button>

            </div>


            <!-- ================================================= -->
            <!-- Security Notice -->
            <!-- ================================================= -->

            <div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-lg">

                <div class="flex items-center gap-2 text-amber-800 font-semibold text-sm">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Sensitive values remain protected
                    </span>

                </div>

                <p class="text-xs text-amber-700 mt-2 leading-5">

                    Sensitive values are never exported as plaintext.
                    Importing a protected configuration will preserve
                    the existing secret instead of replacing it with null.

                </p>

            </div>


            <!-- ================================================= -->
            <!-- Import Form -->
            <!-- ================================================= -->

            <form
                action="{{ route('settings.import') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf


                <!-- Configuration File -->

                <div>

                    <label class="block text-xs font-semibold text-slate-700 mb-2">

                        Configuration File

                    </label>

                    <input
                        type="file"
                        name="configuration_file"
                        accept=".json,.txt,application/json"
                        required
                        class="block w-full text-xs text-slate-600 border border-slate-300 rounded-lg cursor-pointer bg-white focus:outline-none"
                    >

                    <p class="text-[11px] text-slate-400 mt-1">

                        Upload a valid JSON configuration file.

                    </p>

                </div>


                <!-- Target Environment -->

                <div class="mt-5">

                    <label class="block text-xs font-semibold text-slate-700 mb-2">

                        Target Environment

                    </label>

                    <select
                        name="environment_id"
                        class="input-field w-full"
                    >

                        <option value="">
                            Use environment from file
                        </option>

                        @foreach($environments as $environment)

                            <option
                                value="{{ $environment->id }}"
                                {{ $environment->is_active ? 'selected' : '' }}
                            >

                                {{ $environment->name }}
                                ({{ $environment->key }})

                            </option>

                        @endforeach

                    </select>

                    <p class="text-[11px] text-slate-400 mt-1">

                        Choose where the imported configuration should be applied.

                    </p>

                </div>


                <!-- Warning -->

                <div class="mt-5 p-3 bg-slate-50 border border-slate-200 rounded-lg">

                    <div class="flex gap-2">

                        <i class="fa-solid fa-circle-info text-slate-400 mt-0.5"></i>

                        <p class="text-[11px] text-slate-500 leading-5">

                            Existing settings with matching keys will be updated.
                            New settings will be created automatically.
                            Protected values from the export will not overwrite
                            existing sensitive values.

                        </p>

                    </div>

                </div>


                <!-- Buttons -->

                <div class="flex justify-end gap-3 mt-7">

                    <button
                        type="button"
                        onclick="document.getElementById('importConfigurationModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 text-xs"
                    >

                        <i class="fa-solid fa-upload mr-1"></i>

                        Import Configuration

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection