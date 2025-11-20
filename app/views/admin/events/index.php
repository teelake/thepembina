<?php
$content = ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Events & Cultural Nights</h1>
    <a href="<?= BASE_URL ?>/admin/events/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Event
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($events)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($events as $event): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($event['title']) ?></div>
                            <?php if ($event['subtitle']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($event['subtitle']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?= date('M d, Y', strtotime($event['event_date'])) ?>
                            <?php if ($event['event_time']): ?>
                                <span class="text-sm text-gray-500"><?= date('g:i A', strtotime($event['event_time'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><?= htmlspecialchars($event['location'] ?? 'On-site') ?></td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $event['status'] === 'upcoming' ? 'badge-info' : ($event['status'] === 'completed' ? 'badge-success' : 'badge-warning') ?>">
                                <?= ucfirst($event['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/events/<?= $event['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/events/<?= $event['id'] ?>/delete" class="inline" data-confirm-delete>
                                <?= $this->csrf->getTokenField() ?>
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
        <i class="fas fa-calendar text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No events scheduled</h2>
        <p class="text-gray-500 mb-6">Plan your first Afrobeat night or cultural tasting menu.</p>
        <a href="<?= BASE_URL ?>/admin/events/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Event
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
    <h1 class="text-3xl font-bold">Events & Cultural Nights</h1>
    <a href="<?= BASE_URL ?>/admin/events/create" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Event
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($events)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($events as $event): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($event['title']) ?></div>
                            <?php if ($event['subtitle']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($event['subtitle']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?= date('M d, Y', strtotime($event['event_date'])) ?>
                            <?php if (!empty($event['event_time'])): ?>
                                <div class="text-sm text-gray-500"><?= date('g:i A', strtotime($event['event_time'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($event['location']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                                $statusColors = [
                                    'upcoming' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'draft' => 'badge-warning'
                                ];
                            ?>
                            <span class="badge <?= $statusColors[$event['status']] ?? 'badge-info' ?>">
                                <?= ucfirst($event['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/events/<?= $event['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/events/<?= $event['id'] ?>/delete" class="inline" data-confirm-delete>
                                <?= $this->csrf->getTokenField() ?>
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
        <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">No events scheduled</h2>
        <p class="text-gray-500 mb-6">Highlight cultural nights, brunches, and tasting experiences for your guests.</p>
        <a href="<?= BASE_URL ?>/admin/events/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Event
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

