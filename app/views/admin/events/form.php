<?php
$isEdit = isset($event);
$event = $event ?? [];
$content = ob_start();
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Add' ?> Event</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/events<?= $isEdit ? '/' . $event['id'] : '' ?>" enctype="multipart/form-data" data-validate>
        <?= $this->csrf->getTokenField() ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" required class="form-input" value="<?= htmlspecialchars($event['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" value="<?= htmlspecialchars($event['subtitle'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="event_date" required class="form-input" value="<?= htmlspecialchars($event['event_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" name="event_time" class="form-input" value="<?= htmlspecialchars($event['event_time'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="upcoming" <?= (isset($event['status']) && $event['status'] === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                        <option value="completed" <?= (isset($event['status']) && $event['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="draft" <?= (isset($event['status']) && $event['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="<?= htmlspecialchars($event['location'] ?? '') ?>" placeholder="Venue, hall or online link">
            </div>
            
            <div class="form-group">
                <label class="form-label">Featured Image</label>
                <input type="file" name="image" accept="image/*" class="form-input" data-preview="event-image-preview">
                <?php if (!empty($event['image'])): ?>
                    <img id="event-image-preview" src="<?= BASE_URL ?>/public/<?= htmlspecialchars($event['image']) ?>" alt="Event image" class="image-preview">
                <?php else: ?>
                    <img id="event-image-preview" src="" alt="Preview" class="image-preview" style="display:none;">
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/events" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Event
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>
<?php
$isEdit = isset($event);
$event = $event ?? [];
$content = ob_start();
?>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold mb-6"><?= $isEdit ? 'Edit' : 'Add' ?> Event</h1>
    
    <form method="POST" action="<?= BASE_URL ?>/admin/events<?= $isEdit ? '/' . $event['id'] : '' ?>" enctype="multipart/form-data" data-validate>
        <?= $this->csrf->getTokenField() ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
            <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" required class="form-input" value="<?= htmlspecialchars($event['title'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-input" value="<?= htmlspecialchars($event['subtitle'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="event_date" required class="form-input" value="<?= htmlspecialchars($event['event_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" name="event_time" class="form-input" value="<?= htmlspecialchars($event['event_time'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="<?= htmlspecialchars($event['location'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Event Image</label>
                <input type="file" name="image" accept="image/*" class="form-input" data-preview="event-image-preview">
                <p class="text-sm text-gray-500 mt-1">Recommended size: 1200x700px</p>
                <?php if (!empty($event['image'])): ?>
                    <img id="event-image-preview" src="<?= BASE_URL ?>/public/<?= htmlspecialchars($event['image']) ?>" alt="Event image" class="image-preview">
                <?php else: ?>
                    <img id="event-image-preview" src="" alt="Preview" class="image-preview" style="display:none;">
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="upcoming" <?= (isset($event['status']) && $event['status'] === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                        <option value="completed" <?= (isset($event['status']) && $event['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="draft" <?= (isset($event['status']) && $event['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="<?= BASE_URL ?>/admin/events" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Event
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

