@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            Edit Configuration
        </h1>

        <p class="text-gray-500 mt-1">
            Update the selected application configuration.
        </p>
    </div>

    <form
        action="{{ route('settings.update', $setting) }}"
        method="POST"
        class="bg-white shadow-sm rounded-xl border border-gray-200 p-6"
    >
        @csrf
        @method('PUT')

        {{-- Key --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Configuration Key
            </label>

            <input
                type="text"
                value="{{ $setting->key }}"
                readonly
                class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600"
            >
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

            {{-- Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Type
                </label>

                <select
                    name="type"
                    class="w-full rounded-lg border-gray-300"
                >
                    @foreach([
                        'text',
                        'number',
                        'boolean',
                        'select',
                        'textarea',
                        'json',
                        'color',
                        'email',
                        'url'
                    ] as $type)

                        <option
                            value="{{ $type }}"
                            @selected($setting->type === $type)
                        >
                            {{ ucfirst($type) }}
                        </option>

                    @endforeach
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
                    value="{{ old('group', $setting->group) }}"
                    required
                    class="w-full rounded-lg border-gray-300"
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
                    value="{{ old('label', $setting->label) }}"
                    required
                    class="w-full rounded-lg border-gray-300"
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
                            @selected(
                                old(
                                    'environment_id',
                                    $setting->environment_id
                                ) == $environment->id
                            )
                        >
                            {{ $environment->name }}
                        </option>

                    @endforeach

                </select>
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
                class="w-full rounded-lg border-gray-300"
            >{{ old('description', $setting->description) }}</textarea>

        </div>

        {{-- Sensitive --}}
        <div class="mt-6">

            <label class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">

                <input
                    type="checkbox"
                    name="is_sensitive"
                    value="1"
                    class="mt-1 rounded border-gray-300 text-amber-600"
                    @checked(old('is_sensitive', $setting->is_sensitive))
                >

                <span>

                    <span class="block font-semibold text-amber-900">
                        Sensitive Configuration
                    </span>

                    <span class="block text-sm text-amber-800 mt-1">
                        Encrypt this value and mask it from the dashboard.
                    </span>

                </span>

            </label>

        </div>

        {{-- Current Value --}}
        <div class="mt-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Configuration Value
            </label>

            @if($setting->is_sensitive)

                <div class="mb-3 p-3 bg-gray-100 rounded-lg text-sm text-gray-600">
                    <i class="fa-solid fa-lock mr-1"></i>

                    Current value is protected.

                    Enter a new value below only if you want to replace it.
                </div>

            @endif

            @if($setting->type === 'boolean')

                <select
                    name="value"
                    class="w-full rounded-lg border-gray-300"
                >
                    <option
                        value="true"
                        @selected((string) $setting->value === 'true')
                    >
                        True
                    </option>

                    <option
                        value="false"
                        @selected((string) $setting->value === 'false')
                    >
                        False
                    </option>
                </select>

            @elseif($setting->type === 'textarea' || $setting->type === 'json')

                <textarea
                    name="value"
                    rows="6"
                    class="w-full rounded-lg border-gray-300"
                >{{ $setting->is_sensitive ? '' : (is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value) }}</textarea>

            @else

                <input
                    type="{{ $setting->type === 'number' ? 'number' : 'text' }}"
                    name="value"
                    value="{{ $setting->is_sensitive ? '' : $setting->value }}"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="{{ $setting->is_sensitive ? 'Enter new protected value' : 'Enter value' }}"
                >

            @endif

        </div>

        {{-- Status --}}
        <div class="mt-6">

            <label class="flex items-center gap-2">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="rounded border-gray-300 text-indigo-600"
                    @checked($setting->is_active)
                >

                <span class="text-sm text-gray-700">
                    Active
                </span>

            </label>

        </div>

        <div class="flex justify-between mt-8">

            <a
                href="{{ route('settings.history', $setting) }}"
                class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
                <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                View History
            </a>

            <div class="flex gap-3">

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
                    Update Setting
                </button>

            </div>

        </div>

    </form>

</div>

@endsection