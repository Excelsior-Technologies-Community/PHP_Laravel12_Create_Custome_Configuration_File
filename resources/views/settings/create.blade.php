@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            Create New Setting
        </h1>

        <p class="text-gray-500 mt-1">
            Add a new dynamic application configuration setting.
        </p>
    </div>

    <form
        action="{{ route('settings.store') }}"
        method="POST"
        class="bg-white shadow-sm rounded-xl border border-gray-200 p-6"
    >
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Key --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Configuration Key
                </label>

                <input
                    type="text"
                    name="key"
                    value="{{ old('key') }}"
                    placeholder="google_api_token"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >

                @error('key')
                    <p class="text-red-600 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Type
                </label>

                <select
                    name="type"
                    id="type"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="boolean">Boolean</option>
                    <option value="select">Select</option>
                    <option value="textarea">Textarea</option>
                    <option value="json">JSON</option>
                    <option value="color">Color</option>
                    <option value="email">Email</option>
                    <option value="url">URL</option>
                </select>
            </div>

            {{-- Group --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Group
                </label>

                <input
                    type="text"
                    name="group"
                    value="{{ old('group', 'general') }}"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- Label --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Label
                </label>

                <input
                    type="text"
                    name="label"
                    value="{{ old('label') }}"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- Environment --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Environment
                </label>

                <select
                    name="environment_id"
                    class="w-full rounded-lg border-gray-300"
                >
                    <option value="">
                        Global Setting
                    </option>

                    @foreach($environments as $environment)
                        <option
                            value="{{ $environment->id }}"
                            @selected(old('environment_id') == $environment->id)
                        >
                            {{ $environment->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>

                <label class="flex items-center gap-2 mt-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                        class="rounded border-gray-300 text-indigo-600"
                    >

                    <span class="text-sm text-gray-700">
                        Active
                    </span>
                </label>
            </div>

        </div>

        {{-- Description --}}
        <div class="mt-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>

            <textarea
                name="description"
                rows="3"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('description') }}</textarea>

        </div>

        {{-- Sensitive --}}
        <div class="mt-6">

            <label class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">

                <input
                    type="checkbox"
                    name="is_sensitive"
                    value="1"
                    class="mt-1 rounded border-gray-300 text-amber-600"
                >

                <span>
                    <span class="block font-semibold text-amber-900">
                        Sensitive Configuration
                    </span>

                    <span class="block text-sm text-amber-800 mt-1">
                        Encrypt this value in the database and mask it
                        throughout the configuration dashboard.
                    </span>

                    <span class="block text-xs text-amber-700 mt-2">
                        Recommended for API tokens, passwords, private keys,
                        credentials and other secrets.
                    </span>
                </span>

            </label>

        </div>

        {{-- Value --}}
        <div class="mt-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Default Value
            </label>

            <textarea
                name="value"
                rows="4"
                placeholder="Enter configuration value..."
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('value') }}</textarea>

            @error('value')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <div class="flex justify-end gap-3 mt-8">

            <a
                href="{{ route('settings.index') }}"
                class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
            >
                Create Setting
            </button>

        </div>

    </form>

</div>

@endsection