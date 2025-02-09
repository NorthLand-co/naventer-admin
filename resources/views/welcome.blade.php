<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>NorthLand</title>

    @vite('resources/css/app.css');
</head>

<body class="h-screen overflow-hidden font-sans antialiased dark:bg-black dark:text-white/50">
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg" />

        <div class="flex items-center justify-center h-screen">
            <p class="text-5xl font-extralight opacity-60">Nothing Important Just Doing My Job</p>
        </div>
    </div>
</body>

</html>
