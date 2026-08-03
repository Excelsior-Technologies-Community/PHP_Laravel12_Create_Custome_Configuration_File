@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Setting History</h1>
            <p class="text-sm text-slate-500 mt-1">
                Audit log for <code class="bg-slate-100 px-2 py-0.5 rounded text-sm font-mono">{{ $setting->key }}</code>
                <span class="ml-2 text-slate-400">- {{ $setting->label }}</span>
            </p>
        </div>
        <a href="{{ route('settings.index') }}" class="btn-secondary flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-800">Change History</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($histories as $history)
            <div class="px-5 py-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        @php
                            $actionColor = match($history->action) {
                                'created' => 'badge-green',
                                'updated' => 'badge-blue',
                                'deleted' => 'badge-red',
                                'rollback' => 'badge-purple',
                                'bulk_updated' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $actionColor }}">{{ $history->action }}</span>
                        <span class="text-xs text-slate-400">{{ $history->created_at->format('M d, Y \a\t H:i') }}</span>
                    </div>
                    @if($history->action !== 'deleted')
                        <form action="{{ route('settings.rollback', [$setting, $history]) }}" method="POST" class="inline" onsubmit="return confirm('Rollback this setting to the previous value?')">
                            @csrf @method('POST')
                            <button type="submit" class="text-xs font-semibold text-purple-600 hover:text-purple-800 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Rollback
                            </button>
                        </form>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex-1">
                        <span class="font-semibold text-slate-700">From:</span>
                        <code class="ml-1 text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono text-slate-600">{{ $history->old_value ?? '(empty)' }}</code>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <div class="flex-1">
                        <span class="font-semibold text-slate-700">To:</span>
                        <code class="ml-1 text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono text-slate-600">{{ $history->new_value ?? '(empty)' }}</code>
                    </div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Changed by: <span class="font-medium text-slate-500">{{ $history->changed_by ?? 'system' }}</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-slate-400">No history records found for this setting.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection