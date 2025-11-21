<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Newsletter Subscribers</h1>
    <div class="flex items-center gap-4 text-sm text-gray-500">
        <span>Total: <?= count($subscribers) ?></span>
        <a href="mailto:info@thepembina.ca" class="btn btn-secondary">
            <i class="fas fa-envelope mr-2"></i> Export via Email Client
        </a>
    </div>
</div>

<?php if (!empty($subscribers)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Subscribed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($subscribers as $subscriber): ?>
                    <tr>
                        <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($subscriber['email']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($subscriber['name'] ?? '—') ?></td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $subscriber['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($subscriber['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= date('M d, Y g:i A', strtotime($subscriber['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-envelope-open-text text-6xl text-gray-200 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No subscribers yet</h2>
        <p class="text-gray-500">Once guests subscribe from the homepage, they will appear here.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

