<?php
$content = ob_start();
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-3xl font-bold mb-4">Canadian Tax Rates</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Province</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">GST</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">PST</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">HST</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($taxRates as $tax): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold"><?= htmlspecialchars($tax['province']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($tax['province_code']) ?></p>
                        </td>
                        <td class="px-4 py-3"><?= number_format($tax['gst_rate'] * 100, 2) ?>%</td>
                        <td class="px-4 py-3"><?= number_format($tax['pst_rate'] * 100, 2) ?>%</td>
                        <td class="px-4 py-3"><?= number_format($tax['hst_rate'] * 100, 2) ?>%</td>
                        <td class="px-4 py-3 text-right">
                            <button 
                                class="text-brand hover:text-brand-dark font-semibold"
                                onclick="openTaxModal('<?= $tax['province_code'] ?>', <?= $tax['gst_rate'] ?>, <?= $tax['pst_rate'] ?>, <?= $tax['hst_rate'] ?>)">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="tax-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button class="absolute top-3 right-3 text-gray-500" onclick="closeTaxModal()">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="text-xl font-bold mb-4">Update Tax Rates</h2>
        <form method="POST" action="<?= BASE_URL ?>/admin/settings/tax">
            <?= $csrfField ?? '' ?>
            <input type="hidden" name="province_code" id="tax-province-code">
            <div class="space-y-3">
                <div class="form-group">
                    <label class="form-label">GST (%)</label>
                    <input type="number" step="0.01" name="gst_rate" id="gst-input" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">PST (%)</label>
                    <input type="number" step="0.01" name="pst_rate" id="pst-input" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">HST (%)</label>
                    <input type="number" step="0.01" name="hst_rate" id="hst-input" class="form-input">
                </div>
                <div class="flex justify-end pt-4">
                    <button type="button" class="btn btn-secondary mr-2" onclick="closeTaxModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openTaxModal(code, gst, pst, hst) {
    document.getElementById('tax-modal').classList.remove('hidden');
    document.getElementById('tax-modal').classList.add('flex');
    document.getElementById('tax-province-code').value = code;
    document.getElementById('gst-input').value = (gst * 100).toFixed(2);
    document.getElementById('pst-input').value = (pst * 100).toFixed(2);
    document.getElementById('hst-input').value = (hst * 100).toFixed(2);
}

function closeTaxModal() {
    document.getElementById('tax-modal').classList.add('hidden');
    document.getElementById('tax-modal').classList.remove('flex');
}
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

