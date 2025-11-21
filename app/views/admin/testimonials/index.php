<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Testimonials</h1>
    <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Testimonial
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($testimonials as $testimonial): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($testimonial['name']) ?></div>
                            <?php if ($testimonial['title']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($testimonial['title']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <?= htmlspecialchars(mb_strimwidth($testimonial['message'], 0, 120, '...')) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <i class="fas fa-star text-yellow-400"></i>
                            <?php endfor; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $testimonial['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($testimonial['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/testimonials/<?= $testimonial['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/testimonials/<?= $testimonial['id'] ?>/delete" class="inline" data-confirm-delete>
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
        <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No testimonials yet</h2>
        <p class="text-gray-500 mb-6">Add customer reviews to build trust on the homepage.</p>
        <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Testimonial
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>
<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Testimonials</h1>
    <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Testimonial
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($testimonials as $testimonial): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($testimonial['name']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($testimonial['title']) ?></td>
                        <td class="px-6 py-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-yellow-500' : 'text-gray-300' ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $testimonial['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($testimonial['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= $testimonial['sort_order'] ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/testimonials/<?= $testimonial['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/testimonials/<?= $testimonial['id'] ?>/delete" class="inline" data-confirm-delete>
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
        <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No testimonials yet</h2>
        <p class="text-gray-500 mb-6">Start sharing what your guests are saying about The Pembina Pint.</p>
        <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Testimonial
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

