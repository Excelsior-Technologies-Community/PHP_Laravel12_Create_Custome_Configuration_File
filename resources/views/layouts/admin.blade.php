<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Admin Settings</title>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <!-- Tailwind CSS CDN for instant perfect layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar { width: 260px; min-height: 100vh; background: #0f172a; }
        .sidebar-link { transition: all 0.2s ease-in-out; border-left: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(59, 130, 246, 0.1); border-left-color: #3b82f6; color: #60a5fa; }
        .card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .badge-blue { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-yellow { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .input-field { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; transition: 0.2s; }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .btn-primary { background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .btn-secondary:hover { background: #e2e8f0; }
    </style>
</head>
<body class="flex text-slate-800">

    <!-- Sidebar -->
    <aside class="sidebar flex-shrink-0">
        <div class="p-5 border-b border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                <i class="fa-solid fa-gear"></i>
            </div>
            <span class="text-white font-semibold text-lg tracking-wide">Admin Portal</span>
        </div>
        <nav class="p-4 space-y-1">
            <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
            <a href="{{ url('/') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-md text-sm text-slate-300">
                <i class="fa-solid fa-house w-4 text-center"></i>
                Home
            </a>
            <a href="{{ route('settings.index') }}" class="sidebar-link active flex items-center gap-3 px-3 py-2.5 rounded-md text-sm text-slate-300">
                <i class="fa-solid fa-sliders w-4 text-center"></i>
                Settings Dashboard
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Dynamic Configuration</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage `.env` & runtime configurations easily</p>
            </div>
            <div class="flex items-center gap-3">
                @if(isset($activeEnvironment))
                    <span class="flex items-center gap-2 text-xs bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-full font-medium">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                        Env: <strong class="text-slate-800">{{ $activeEnvironment->name }}</strong>
                    </span>
                @endif
                <a href="{{ route('settings.create') }}" class="btn-primary flex items-center gap-2 text-xs">
                    <i class="fa-solid fa-plus"></i> Add New Setting
                </a>
            </div>
        </header>

        <main class="flex-1 p-6 space-y-6">
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-rose-50 border border-red-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>