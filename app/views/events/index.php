<?php
use App\Core\Helper;
$content = ob_start();
?>

<!-- Hero -->
<section class="hero-brand-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-4 text-sm text-white/80">
            <a href="<?= BASE_URL ?>" class="hover:underline">Home</a>
            <span class="mx-1">/</span>
            <span>Events & Cultural Nights</span>
        </nav>
        <div class="max-w-2xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Events & Cultural Nights</h1>
            <p class="text-lg md:text-xl text-white/90">
                Live music, game nights, cultural celebrations and special experiences at
                The Pembina Pint &amp; Restaurant.
            </p>
        </div>
    </div>
</section>

<!-- Events List -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($events)): ?>
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-brand font-semibold uppercase tracking-wide text-sm">Upcoming Events</p>
                    <h2 class="text-2xl font-bold text-gray-900">Event Calendar</h2>
                    <p class="text-gray-600 mt-1 text-sm">
                        <?= count($events) ?> upcoming <?= count($events) === 1 ? 'event' : 'events' ?> scheduled.
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                <?php foreach ($events as $event): ?>
                    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col md:flex-row md:items-center gap-6">
                        <!-- Date block -->
                        <div class="w-full md:w-48 flex md:block items-center md:items-start gap-4">
                            <div class="flex items-center md:block">
                                <div class="bg-brand text-white rounded-xl px-4 py-3 text-center w-24 shadow-sm">
                                    <div class="text-xs uppercase tracking-wide opacity-90">
                                        <?= date('D', strtotime($event['event_date'])) ?>
                                    </div>
                                    <div class="text-2xl font-extrabold leading-tight">
                                        <?= date('d', strtotime($event['event_date'])) ?>
                                    </div>
                                    <div class="text-xs uppercase tracking-wide opacity-90">
                                        <?= date('M Y', strtotime($event['event_date'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($event['event_time'])): ?>
                                <div class="text-sm text-gray-600 md:mt-3 flex items-center gap-2">
                                    <i class="fas fa-clock text-brand"></i>
                                    <span><?= date('g:i A', strtotime($event['event_time'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-1">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h3>
                                    <?php if (!empty($event['subtitle'])): ?>
                                        <p class="text-brand text-sm font-semibold">
                                            <?= htmlspecialchars($event['subtitle']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (!empty($event['status'])): ?>
                                        <?php
                                            $status = $event['status'];
                                            $badgeClass = 'badge-info';
                                            if ($status === 'completed') $badgeClass = 'badge-success';
                                            if ($status === 'draft') $badgeClass = 'badge-warning';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> text-xs uppercase tracking-wide">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($event['description'])): ?>
                                <p class="text-gray-600 mb-4">
                                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($event['location'])): ?>
                                <p class="text-sm text-gray-700 flex items-center gap-2 mb-2">
                                    <i class="fas fa-map-marker-alt text-brand"></i>
                                    <span><?= htmlspecialchars($event['location']) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Optional image -->
                        <?php if (!empty($event['image'])): ?>
                            <div class="w-full md:w-56">
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($event['image']) ?>"
                                     alt="<?= htmlspecialchars($event['title']) ?>"
                                     class="w-full h-40 md:h-32 object-cover rounded-xl shadow-sm">
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
                <i class="fas fa-calendar-alt text-5xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">No upcoming events yet</h2>
                <p class="text-gray-600 mb-6">
                    Check back soon for cultural nights, live music, and special experiences at The Pembina Pint &amp; Restaurant.
                </p>
                <a href="<?= BASE_URL ?>/menu" class="inline-flex items-center px-6 py-3 bg-brand text-white rounded-lg font-semibold hover:bg-brand-dark transition">
                    <i class="fas fa-utensils mr-2"></i> Explore the Menu
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
$page_title = 'Events & Cultural Nights';
require_once APP_PATH . '/views/layouts/main.php';
?>


