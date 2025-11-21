<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Users</h1>
    <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-primary">
        <i class="fas fa-user-plus mr-2"></i> Add User
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="px-4 py-3 text-sm uppercase"><?= htmlspecialchars($user['role_id']) ?></td>
                    <td class="px-4 py-3">
                        <span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ucfirst($user['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? null)): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/delete" class="inline" data-confirm-delete>
                            <?= $csrfField ?? '' ?>
                            <button type e="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
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

