@extends('layouts.admin')

@section('content')

<div class="max-w-6xl mx-auto">

    <!-- ========================================================= -->
    <!-- Header -->
    <!-- ========================================================= -->

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-2xl font-bold text-gray-900">
                Configuration History
            </h1>

            <p class="text-gray-500 mt-1">
                {{ $setting->label }}

                <span class="text-gray-400">
                    ({{ $setting->key }})
                </span>
            </p>

        </div>


        <a
            href="{{ route('settings.index') }}"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
        >

            <i class="fa-solid fa-arrow-left mr-1"></i>

            Back

        </a>

    </div>


    <!-- ========================================================= -->
    <!-- Sensitive Configuration Notice -->
    <!-- ========================================================= -->

    @if($setting->is_sensitive)

        <div class="mb-5 p-4 rounded-lg bg-amber-50 border border-amber-200">

            <div class="flex items-center gap-2 text-amber-900 font-semibold">

                <i class="fa-solid fa-lock"></i>

                Sensitive Configuration

            </div>

            <p class="text-sm text-amber-800 mt-1">

                Historical values are encrypted and are never displayed
                in plaintext. Rollback restores the selected version
                without exposing the secret.

            </p>

        </div>

    @endif


    <!-- ========================================================= -->
    <!-- Current Configuration -->
    <!-- ========================================================= -->

    <div class="mb-5 bg-white border border-gray-200 rounded-xl shadow-sm">

        <div class="px-6 py-4 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-sm font-bold text-gray-800">
                        Current Configuration
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        Current value stored for this setting
                    </p>

                </div>

                @if($setting->is_sensitive)

                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">

                        <i class="fa-solid fa-lock mr-1"></i>

                        Protected

                    </span>

                @endif

            </div>

        </div>


        <div class="px-6 py-4">

            @if($setting->is_sensitive)

                <span class="font-mono text-gray-400 tracking-wider">
                    ••••••••••••
                </span>

            @else

                @if(is_array($setting->value))

                    <pre class="font-mono text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</pre>

                @else

                    <span class="font-mono text-sm text-gray-600 whitespace-pre-wrap">
                        {{ $setting->value ?? '—' }}
                    </span>

                @endif

            @endif

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- History Table -->
    <!-- ========================================================= -->

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-sm font-bold text-gray-800">
                        Change History
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        Review previous configuration versions and restore
                        an earlier value.
                    </p>

                </div>

                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">

                    {{ $histories->count() }} Records

                </span>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <!-- ================================================= -->
                <!-- Table Header -->
                <!-- ================================================= -->

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="text-left px-6 py-4">
                            Action
                        </th>

                        <th class="text-left px-6 py-4">
                            Previous Value
                        </th>

                        <th class="text-left px-6 py-4">
                            New Value
                        </th>

                        <th class="text-left px-6 py-4">
                            Changed By
                        </th>

                        <th class="text-left px-6 py-4">
                            Date
                        </th>

                        <th class="text-right px-6 py-4">
                            Action
                        </th>

                    </tr>

                </thead>


                <!-- ================================================= -->
                <!-- Table Body -->
                <!-- ================================================= -->

                <tbody class="divide-y divide-gray-100">

                    @forelse($histories as $history)

                        <tr class="hover:bg-gray-50 transition">


                            <!-- ===================================== -->
                            <!-- Action -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4">

                                @php

                                    $badge = match($history->action) {

                                        'created'
                                            => 'bg-green-100 text-green-700',

                                        'updated'
                                            => 'bg-blue-100 text-blue-700',

                                        'deleted'
                                            => 'bg-red-100 text-red-700',

                                        'rollback'
                                            => 'bg-purple-100 text-purple-700',

                                        'bulk_updated'
                                            => 'bg-indigo-100 text-indigo-700',

                                        'imported'
                                            => 'bg-orange-100 text-orange-700',

                                        default
                                            => 'bg-gray-100 text-gray-700',

                                    };

                                @endphp


                                <div class="flex items-center gap-2">

                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">

                                        {{ ucfirst(str_replace('_', ' ', $history->action)) }}

                                    </span>


                                    @if($history->is_sensitive)

                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700"
                                            title="Sensitive values are encrypted"
                                        >

                                            <i class="fa-solid fa-lock"></i>

                                        </span>

                                    @endif

                                </div>

                            </td>


                            <!-- ===================================== -->
                            <!-- Previous Value -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4 text-gray-600 max-w-xs">

                                @if($history->is_sensitive)

                                    <span class="font-mono text-gray-400 tracking-wider">
                                        ••••••••••••
                                    </span>

                                @else

                                    @if(is_null($history->old_value))

                                        <span class="text-gray-400">
                                            —
                                        </span>

                                    @else

                                        <span class="font-mono whitespace-pre-wrap break-words">
                                            {{ $history->old_value }}
                                        </span>

                                    @endif

                                @endif

                            </td>


                            <!-- ===================================== -->
                            <!-- New Value -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4 text-gray-600 max-w-xs">

                                @if($history->is_sensitive)

                                    <span class="font-mono text-gray-400 tracking-wider">
                                        ••••••••••••
                                    </span>

                                @else

                                    @if(is_null($history->new_value))

                                        <span class="text-gray-400">
                                            —
                                        </span>

                                    @else

                                        <span class="font-mono whitespace-pre-wrap break-words">
                                            {{ $history->new_value }}
                                        </span>

                                    @endif

                                @endif

                            </td>


                            <!-- ===================================== -->
                            <!-- Changed By -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4 text-gray-600">

                                <div class="flex items-center gap-2">

                                    <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">

                                        <i class="fa-solid fa-user text-xs"></i>

                                    </div>

                                    <span>
                                        {{ $history->changed_by ?? 'system' }}
                                    </span>

                                </div>

                            </td>


                            <!-- ===================================== -->
                            <!-- Date -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4 text-gray-500 whitespace-nowrap">

                                {{ $history->created_at->format('d M Y, h:i A') }}

                            </td>


                            <!-- ===================================== -->
                            <!-- Rollback Action -->
                            <!-- ===================================== -->

                            <td class="px-6 py-4 text-right">

                                @php

                                    /*
                                     * Rollback is available only when:
                                     *
                                     * - The record has a previous value.
                                     * - It is not a deleted record.
                                     * - It is not the initial created record.
                                     */

                                    $canRollback =
                                        $history->old_value !== null
                                        && !in_array(
                                            $history->action,
                                            ['created', 'deleted']
                                        );

                                @endphp


                                @if($canRollback)

                                    <form
                                        action="{{ route('settings.rollback', [
                                            'setting' => $setting->id,
                                            'history' => $history->id,
                                        ]) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to rollback this configuration to this version?')"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-600 text-white hover:bg-purple-700 text-xs font-medium transition"
                                            title="Rollback to this version"
                                        >

                                            <i class="fa-solid fa-rotate-left"></i>

                                            Rollback

                                        </button>

                                    </form>

                                @elseif($history->action === 'rollback')

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-600 text-xs font-medium">

                                        <i class="fa-solid fa-rotate-left"></i>

                                        Rolled Back

                                    </span>

                                @else

                                    <span class="text-xs text-gray-400">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-gray-500"
                            >

                                <div class="flex flex-col items-center">

                                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">

                                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>

                                    </div>

                                    <p class="mt-3 font-medium text-gray-600">
                                        No configuration history found.
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Changes to this setting will appear here.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection