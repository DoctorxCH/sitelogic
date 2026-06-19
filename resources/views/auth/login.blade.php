<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiteLogic - Login</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-2xl shadow-md border border-gray-100">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">SiteLogic Mobile</h2>
            <p class="text-sm text-gray-500 mt-1">Bitte loggen Sie sich ein, um Ihre Aufträge zu sehen.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-medium">
                Die eingegebenen Zugangsdaten sind nicht korrekt.
            </div>
        @endif

        @if(session('error'))
            <div class="bg-amber-50 text-amber-700 p-3 rounded-lg text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail-Adresse</label>
                <input type="email" name="email" id="email" required autocomplete="email" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" value="{{ old('email') }}">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Passwort</label>
                <input type="password" name="password" id="password" required autocomplete="current-password" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl shadow-sm transition-colors mt-2">Einloggen</button>
        </form>
    </div>
</body>
</html>
