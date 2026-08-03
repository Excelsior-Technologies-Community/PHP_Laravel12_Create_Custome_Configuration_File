@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Setting</h1>
            <p class="text-sm text-slate-500 mt-1">Update configuration for <code class="bg-slate-100 px-2 py-0.5 rounded text-sm font-mono">{{ $setting->key }}</code></p>
        </div>
        <a href="{{ route('settings.index') }}" class="btn-secondary flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('settings.update', $setting) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Key</label>
                        <input type="text" readonly class="input-field bg-slate-50 text-slate-500 cursor-not-allowed" value="{{ $setting->key }}">
                        <p class="mt-1 text-xs text-slate-400">Key cannot be changed after creation</p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-semibold text-slate-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required class="input-field @error('type') error @enderror">
                            <option value="text" {{ $setting->type == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="number" {{ $setting->type == 'number' ? 'selected' : '' }}>Number</option>
                            <option value="boolean" {{ $setting->type == 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="select" {{ $setting->type == 'select' ? 'selected' : '' }}>Select</option>
                            <option value="textarea" {{ $setting->type == 'textarea' ? 'selected' : '' }}>Textarea</option>
                            <option value="json" {{ $setting->type == 'json' ? 'selected' : '' }}>JSON</option>
                            <option value="color" {{ $setting->type == 'color' ? 'selected' : '' }}>Color</option>
                            <option value="email" {{ $setting->type == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="url" {{ $setting->type == 'url' ? 'selected' : '' }}>URL</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group" class="block text-sm font-semibold text-slate-700 mb-1.5">Group <span class="text-red-500">*</span></label>
                        <input type="text" name="group" id="group" required class="input-field @error('group') error @enderror" value="{{ old('group', $setting->group) }}">
                        @error('group')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-semibold text-slate-700 mb-1.5">Label <span class="text-red-500">*</span></label>
                        <input type="text" name="label" id="label" required class="input-field @error('label') error @enderror" value="{{ old('label', $setting->label) }}">
                        @error('label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                        <textarea name="description" id="description" rows="2" class="input-field @error('description') error @enderror">{{ old('description', $setting->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="value" class="block text-sm font-semibold text-slate-700 mb-1.5">Value</label>
                        @if($setting->type == 'boolean')
                            <select name="value" id="value" class="input-field">
                                <option value="1" {{ $setting->value == '1' || $setting->value === true ? 'selected' : '' }}>true</option>
                                <option value="0" {{ $setting->value == '0' || $setting->value === false ? 'selected' : '' }}>false</option>
                            </select>
                        @elseif($setting->type == 'textarea')
                            <textarea name="value" id="value" rows="4" class="input-field font-mono @error('value') error @enderror">{{ old('value', $setting->value) }}</textarea>
                        @else
                            <input type="text" name="value" id="value" class="input-field font-mono @error('value') error @enderror" value="{{ old('value', $setting->value) }}">
                        @endif
                        @error('value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="environment_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Environment</label>
                        <select name="environment_id" id="environment_id" class="input-field">
                            <option value="">Global (all environments)</option>
                            @foreach($environments as $env)
                                <option value="{{ $env->id }}" {{ $setting->environment_id == $env->id ? 'selected' : '' }}>
                                    {{ $env->name }} ({{ $env->key }})
                                </option>
                            @endforeach
                        </select>
                        @error('environment_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Update Setting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection