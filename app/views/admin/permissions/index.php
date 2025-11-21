<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Permissions</h1>
    <a href="<?= BASE_URL ?>/admin/permissions/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> New Permission
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($permissions as $permission): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($permission['name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($permission['slug']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($permission['description'] ?? '—') ?></td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="<?= BASE_URL ?>/admin/permissions/<?= $permission['id'] ?>/edit" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/permissions/<?= $permission['id'] ?>/delete" class="inline" data-confirm-delete>
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

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>


