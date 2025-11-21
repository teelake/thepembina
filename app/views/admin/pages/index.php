<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Pages</h1>
    <a href="<?= BASE_URL ?>/admin/pages/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Create Page
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($pages)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Updated</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($pages as $page): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($page['title']) ?></td>
                        <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($page['slug']) ?></td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $page['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($page['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= date('M d, Y', strtotime($page['updated_at'])) ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/page/<?= $page['slug'] ?>" target="_blank" class="text-gray-500 hover:text-brand mr-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/pages/<?= $page['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/pages/<?= $page['id'] ?>/delete" class="inline" data-confirm-delete>
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-file-alt text-6xl text-gray-200 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No pages yet</h2>
        <p class="text-gray-500">Create pages for Terms, Privacy, Catering, and more.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

