<?php require_once __DIR__ . '/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0f">
    <title>Enigma - <?php echo $pageTitle ?? 'Welcome'; ?></title>

    <link rel="stylesheet" href="/assets/css/tailwind.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/all.min.css">
</head>
<body class="min-h-screen flex flex-col">
    <nav class="sticky-header">
        <div class="container mx-auto flex justify-between items-center h-16 px-4">
            <a href="/" class="flex items-center space-x-2 text-2xl font-bold text-[var(--text-primary)] hover:text-[var(--accent)] transition-all duration-300">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--accent)] bg-opacity-10">
                    <i class="fas fa-lock-alt text-[var(--accent)]"></i>
                </span>
                <span>Enigma</span>
            </a>

            <div class="flex items-center space-x-8">
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserRole() === 'Admin'): ?>
                        <a href="/admin/" class="nav-link flex items-center space-x-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                            <i class="fas fa-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <a href="/logout.php" class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-[var(--primary-light)] hover:bg-[var(--accent)] text-[var(--text-primary)] transition-all duration-300">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                <?php else: ?>
                    <a href="/signup.php" class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-[var(--accent)] hover:bg-[var(--accent-dark)] text-white transition-all duration-300">
                        <i class="fas fa-user-plus"></i>
                        <span>Sign Up</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 flex-grow">
        <?php
        $flash = getFlashMessage();
        if ($flash):
            $flashClass = match($flash['type']) {
                'error' => 'bg-[var(--error)] bg-opacity-10 text-[var(--error)]',
                'success' => 'bg-[var(--success)] bg-opacity-10 text-[var(--success)]',
                'warning' => 'bg-[var(--warning)] bg-opacity-10 text-[var(--warning)]',
                default => 'bg-[var(--accent)] bg-opacity-10 text-[var(--accent)]'
            };
        ?>
            <div class="mb-6 p-4 rounded-lg border border-current border-opacity-10 <?php echo $flashClass; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-<?php echo $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                    <p><?php echo $flash['message']; ?></p>
                </div>
            </div>
        <?php endif; ?>
