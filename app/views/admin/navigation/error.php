<?php
$content = ob_start();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">Navigation Management - Error</h1>
</div>

<div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-lg">
    <div class="flex items-start">
        <i class="fas fa-exclamation-circle text-2xl mr-4 mt-1"></i>
        <div>
            <h3 class="font-bold text-lg mb-2">Error Loading Navigation</h3>
            <p class="mb-2">An error occurred while loading the navigation management page:</p>
            <code class="bg-red-100 px-3 py-2 rounded block mt-2"><?= htmlspecialchars($error ?? 'Unknown error') ?></code>
            <p class="mt-4 text-sm">Please check:</p>
            <ul class="list-disc list-inside mt-2 text-sm space-y-1">
                <li>That the database migration has been run</li>
                <li>That the database connection is working</li>
                <li>Check the error logs for more details</li>
            </ul>
            <a href="<?= BASE_URL ?>/admin/navigation" class="btn btn-primary mt-4">
                <i class="fas fa-refresh mr-2"></i> Try Again
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

