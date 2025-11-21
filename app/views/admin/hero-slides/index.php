<?php
use App\Core\Helper;
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Hero Slider</h1>
    <a href="<?= BASE_URL ?>/admin/hero-slides/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Slide
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($slides)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Preview</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($slides as $slide): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <?php if ($slide['image']): ?>
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($slide['image']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>" class="w-24 h-16 object-cover rounded">
                            <?php else: ?>
                                <div class="w-24 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($slide['title']) ?></div>
                            <?php if ($slide['subtitle']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($slide['subtitle']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $slide['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($slide['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= $slide['sort_order'] ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/hero-slides/<?= $slide['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/hero-slides/<?= $slide['id'] ?>/delete" class="inline" data-confirm-delete>
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
        <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No slides yet</h2>
        <p class="text-gray-500 mb-6">Create your first hero slide to showcase featured content on the homepage.</p>
        <a href="<?= BASE_URL ?>/admin/hero-slides/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Slide
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

