<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SiteLogic - Dashboard</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#1e40af"/>
        <link rel="manifest" href="/manifest.json">
    </head>
    <body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-screen flex flex-col">
        <header class="bg-blue-800 text-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold">SiteLogic</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Eingeloggt als {{ Auth::user()->name ?? 'Benutzer' }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-grow container mx-auto px-4 py-8">
            <div id="app">
                <!-- JS Components will mount here -->
                <div class="flex justify-center items-center h-64">
                    <div class="text-gray-500">Lade Dashboard...</div>
                </div>
            </div>
        </main>

        <footer class="bg-gray-800 text-white text-center py-4 mt-auto">
            <p class="text-sm">&copy; {{ date('Y') }} SiteLogic</p>
        </footer>

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/service-worker.js')
                        .then((registration) => {
                            console.log('ServiceWorker registration successful with scope: ', registration.scope);
                        })
                        .catch((error) => {
                            console.log('ServiceWorker registration failed: ', error);
                        });
                });
            }
        </script>
    </body>
</html>
