@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Settings</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $groupedSettings->sum->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">Environments</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $environments->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-server"></i>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">Setting Groups</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $groupedSettings->keys()->count() }}</h3>
            </div>
            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
        </div>
    </div>

    <!-- Controls Row: Env Switcher & .env Editor -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Environment Switcher -->
        <div class="card p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 border-b pb-3">
                <i class="fa-solid fa-arrows-rotate text-blue-500"></i> Environment Switcher
            </h2>
            <form action="{{ route('settings.switchEnvironment') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <select name="environment_id" class="input-field">
                    @foreach($environments as $env)
                        <option value="{{ $env->id }}" {{ $env->is_active ? 'selected' : '' }}>
                            {{ $env->name }} ({{ $env->key }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary whitespace-nowrap text-xs">Switch</button>
            </form>
        </div>

        <!-- .env Direct Writer -->
        <div class="card p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 border-b pb-3">
                <i class="fa-solid fa-file-pen text-amber-500"></i> Quick .env Editor
            </h2>
            <form action="{{ route('settings.envUpdate') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="text" name="env_key" required placeholder="KEY (e.g. APP_DEBUG)" class="input-field font-mono text-xs">
                <input type="text" name="env_value" placeholder="VALUE" class="input-field font-mono text-xs">
                <button type="submit" class="btn-primary whitespace-nowrap text-xs">Save .env</button>
            </form>
        </div>

    </div>

    <!-- Grouped Settings Tables -->
    @foreach($groupedSettings as $group => $groupSettings)
    <div class="card overflow-hidden">
        <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700 capitalize flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-slate-400"></i> {{ $group }} Group
            </h2>
            <span class="badge badge-gray">{{ $groupSettings->count() }} Items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Key</th>
                        <th class="px-5 py-3">Label</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Value</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($groupSettings as $setting)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 font-mono font-medium text-slate-800">{{ $setting->key }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $setting->label }}</td>
                        <td class="px-5 py-3"><span class="badge badge-blue">{{ $setting->type }}</span></td>
                        <td class="px-5 py-3 font-mono text-slate-600 max-w-xs truncate">{{ $setting->value ?? 'null' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $setting->environment ? $setting->environment->name : 'Global' }}</td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <a href="{{ route('settings.history', $setting) }}" class="text-slate-400 hover:text-blue-600 p-1" title="History">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </a>
                            <a href="{{ route('settings.edit', $setting) }}" class="text-slate-400 hover:text-blue-600 p-1" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('Delete this setting?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Delete">
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
    @endforeach

</div>
@endsection