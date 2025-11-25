<?php
$content = ob_start();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">Navigation Management</h1>
    <p class="text-gray-600">Manage which categories appear in the main website navigation. Maximum 3 categories can be displayed.</p>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success mb-6"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error mb-6"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold">Main Navigation Categories</h2>
                <p class="text-sm text-gray-600 mt-1">These categories appear as direct links in the header</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Currently showing:</span>
                <span class="bg-brand text-white px-3 py-1 rounded-full text-sm font-semibold" id="nav-count">
                    <?= count(array_filter($categories, fn($c) => !empty($c['show_in_nav']))) ?>
                </span>
                <span class="text-sm text-gray-600">/ 3</span>
            </div>
        </div>
    </div>

    <div class="divide-y divide-gray-200">
        <?php 
        $navCategories = array_filter($categories, fn($c) => !empty($c['show_in_nav']));
        $otherCategories = array_filter($categories, fn($c) => empty($c['show_in_nav']));
        ?>
        
        <!-- Navigation Categories (Top Section) -->
        <div class="p-6 bg-brand/5">
            <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-bars mr-2 text-brand"></i>
                In Navigation (<?= count($navCategories) ?>)
            </h3>
            
            <?php if (!empty($navCategories)): ?>
                <div class="space-y-3" id="nav-categories-list">
                    <?php foreach ($navCategories as $category): ?>
                        <div class="bg-white rounded-lg p-4 border-2 border-brand shadow-sm" data-category-id="<?= $category['id'] ?>">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand text-white font-bold mr-4">
                                        <?= $category['nav_order'] ?? 0 ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($category['name']) ?></h4>
                                            <span class="badge badge-info">In Nav</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?= $category['product_count'] ?? 0 ?> products
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600">Order:</label>
                                        <input type="number" 
                                               class="w-20 border border-gray-300 rounded px-2 py-1 text-sm nav-order-input" 
                                               value="<?= $category['nav_order'] ?? 0 ?>" 
                                               min="1" 
                                               max="100"
                                               data-category-id="<?= $category['id'] ?>">
                                    </div>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="w-5 h-5 text-brand border-gray-300 rounded focus:ring-brand show-in-nav-checkbox" 
                                               checked 
                                               data-category-id="<?= $category['id'] ?>">
                                        <span class="ml-2 text-sm text-gray-700">Show in Nav</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No categories in navigation. Select categories below to add them.</p>
            <?php endif; ?>
        </div>

        <!-- Other Categories (Bottom Section) -->
        <div class="p-6">
            <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-list mr-2"></i>
                All Categories (<?= count($otherCategories) ?>)
            </h3>
            
            <?php if (!empty($otherCategories)): ?>
                <div class="space-y-3">
                    <?php foreach ($otherCategories as $category): ?>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200" data-category-id="<?= $category['id'] ?>">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold mr-4">
                                        —
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($category['name']) ?></h4>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?= $category['product_count'] ?? 0 ?> products
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600">Order:</label>
                                        <input type="number" 
                                               class="w-20 border border-gray-300 rounded px-2 py-1 text-sm nav-order-input" 
                                               value="<?= $category['nav_order'] ?? 0 ?>" 
                                               min="1" 
                                               max="100"
                                               data-category-id="<?= $category['id'] ?>"
                                               disabled>
                                    </div>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="w-5 h-5 text-brand border-gray-300 rounded focus:ring-brand show-in-nav-checkbox" 
                                               data-category-id="<?= $category['id'] ?>">
                                        <span class="ml-2 text-sm text-gray-700">Show in Nav</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No other categories available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">How it works:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Maximum 3 categories can be shown in the main navigation</li>
                <li>Categories are ordered by "Navigation Order" (lower numbers appear first)</li>
                <li>Changes are saved automatically</li>
                <li>Categories not in navigation appear in the "More" dropdown menu</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    let updateTimeout;

    // Handle show in nav checkbox
    document.querySelectorAll('.show-in-nav-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const categoryId = this.dataset.categoryId;
            const showInNav = this.checked ? 1 : 0;
            const orderInput = document.querySelector(`.nav-order-input[data-category-id="${categoryId}"]`);
            const navOrder = orderInput ? parseInt(orderInput.value) || 0 : 0;

            // Enable/disable order input
            if (orderInput) {
                orderInput.disabled = !this.checked;
                if (!this.checked) {
                    orderInput.value = 0;
                }
            }

            updateNavigation(categoryId, showInNav, navOrder);
        });
    });

    // Handle navigation order input
    document.querySelectorAll('.nav-order-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.disabled) return;
            
            const categoryId = this.dataset.categoryId;
            const checkbox = document.querySelector(`.show-in-nav-checkbox[data-category-id="${categoryId}"]`);
            const showInNav = checkbox && checkbox.checked ? 1 : 0;
            const navOrder = parseInt(this.value) || 0;

            updateNavigation(categoryId, showInNav, navOrder);
        });
    });

    function updateNavigation(categoryId, showInNav, navOrder) {
        clearTimeout(updateTimeout);
        
        updateTimeout = setTimeout(() => {
            fetch('<?= BASE_URL ?>/admin/navigation/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${csrfToken}&category_id=${categoryId}&show_in_nav=${showInNav}&nav_order=${navOrder}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update nav count
                    const navCount = document.querySelectorAll('.show-in-nav-checkbox:checked').length;
                    document.getElementById('nav-count').textContent = navCount;
                    
                    // Reload page to reflect changes
                    if (navCount <= 3) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        alert(data.message || 'Maximum 3 categories allowed in navigation');
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to update navigation');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }, 500);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

