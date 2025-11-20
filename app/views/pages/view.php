<?php
$content = ob_start();
?>

<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 bg-white rounded-lg shadow-md p-8">
        <h1 class="text-4xl font-bold mb-6 text-gray-900"><?= htmlspecialchars($page['title']) ?></h1>
        <div class="prose prose-lg max-w-none text-gray-700">
            <?= $page['content'] ?>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>

