<?php require_once __DIR__ . '/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0f">
    <title>Enigma - <?php echo $pageTitle ?? 'Error'; ?></title>

    <link rel="stylesheet" href="/assets/css/tailwind.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="sticky-header">
        <div class="container mx-auto flex justify-between items-center h-16 px-4">
            <a href="/" class="flex items-center space-x-2 text-2xl font-bold text-[var(--text-primary)] hover:text-[var(--accent)] transition-all duration-300">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--accent)] bg-opacity-10">
                    <i class="fas fa-lock-alt text-[var(--accent)]"></i>
                </span>
                <span>Enigma</span>
            </a>
        </div>
    </nav>
