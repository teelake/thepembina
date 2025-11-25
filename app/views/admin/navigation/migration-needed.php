<?php
$content = ob_start();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">Navigation Management</h1>
    <p class="text-gray-600">Set up the navigation menu system</p>
</div>

<div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg mb-6">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-2xl mr-4 mt-1"></i>
        <div>
            <h3 class="font-bold text-lg mb-2">Database Migration Required</h3>
            <p class="mb-4">The navigation menu table hasn't been created yet. You need to run the database migration first.</p>
            
            <div class="bg-white p-4 rounded border border-yellow-200 mb-4">
                <h4 class="font-semibold mb-2">Migration Steps:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Access your database (phpMyAdmin, MySQL command line, or cPanel)</li>
                    <li>Run the migration SQL file: <code class="bg-gray-100 px-2 py-1 rounded">database/migrations/create_navigation_menu_table.sql</code></li>
                    <li>Refresh this page</li>
                </ol>
            </div>

            <div class="bg-gray-800 text-white p-4 rounded font-mono text-sm overflow-x-auto">
                <p class="mb-2">Command line:</p>
                <code>mysql -u username -p database_name < database/migrations/create_navigation_menu_table.sql</code>
            </div>

            <div class="mt-4">
                <p class="text-sm mb-2"><strong>Or via phpMyAdmin:</strong></p>
                <ol class="list-decimal list-inside space-y-1 text-sm">
                    <li>Go to phpMyAdmin</li>
                    <li>Select your database</li>
                    <li>Click "SQL" tab</li>
                    <li>Copy and paste the contents of <code class="bg-gray-100 px-1 rounded">database/migrations/create_navigation_menu_table.sql</code></li>
                    <li>Click "Go"</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">What this migration does:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Creates the <code>navigation_menu_items</code> table</li>
                <li>Migrates existing category-based navigation items</li>
                <li>Sets up foreign keys for categories and pages</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

