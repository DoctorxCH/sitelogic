<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Techniker Arbeitsmaske</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full flex flex-col">
    <header class="bg-slate-800 text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="font-bold text-lg">SiteLogic Mobile</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-300">{{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-white font-medium">Logout</button>
            </form>
        </div>
    </header>
    <main class="flex-1 p-4 max-w-md mx-auto w-full">
        @yield('content')
    </main>
</body>
</html>
