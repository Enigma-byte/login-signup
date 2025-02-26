<?php
$pageTitle = 'Server Error';

// Fix for relative paths
$basePath = dirname(__DIR__);
require_once $basePath . '/includes/error-header.php';
?>

<div class="min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-950 via-gray-950 to-gray-950 flex items-center justify-center p-6">
    <div class="text-center space-y-6 max-w-lg">
        <div class="flex flex-col items-center justify-center space-y-4">
            <i class="fas fa-exclamation-triangle glitch-icon"></i>
            <div class="glitch-wrapper">
                <div class="glitch-number">500</div>
            </div>
        </div>
        <h1 class="text-3xl font-bold text-white">Server Error</h1>
        <p class="text-blue-300/80">Something went wrong on our end. Please try again later or contact support if the problem persists.</p>
        <div class="pt-4">
            <a href="/" class="inline-flex items-center px-6 py-3 bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 text-base font-medium rounded-md shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 text-white transition-all duration-300">
                Back to Home
            </a>
        </div>
    </div>
</div>

<?php require_once $basePath . '/includes/error-footer.php'; ?>
