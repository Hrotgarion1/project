<!DOCTYPE html>
<html>
<head>
    <title>Términos y Condiciones - {{ $type }}</title>
</head>
<body class="p-6 bg-neutral-3 dark:bg-neutral-1">
    <h1 class="text-2xl font-bold text-neutral-1 dark:text-neutral-0 mb-4">Términos y Condiciones - {{ $type }}</h1>
    <div class="prose dark:prose-invert max-w-none text-neutral-2 dark:text-neutral-0">
        {!! $content !!}
    </div>
</body>
</html>