<?php
$content = ob_start();
?>

<div class="max-w-6xl">
    <h1 class="text-3xl font-bold mb-6">Tax Management</h1>

    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
            <p><?= htmlspecialchars($_GET['success']) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <p><?= htmlspecialchars($_GET['error']) ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Tax Rate Configuration</p>
                <p>Manage tax rates for all Canadian provinces and territories. Tax rates are automatically applied during checkout based on the customer's billing province.</p>
                <p class="mt-2"><strong>Note:</strong> GST (Goods and Services Tax) applies to all provinces. PST (Provincial Sales Tax) applies to certain provinces. HST (Harmonized Sales Tax) replaces both GST and PST in some provinces.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Province/Territory</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GST Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">PST Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">HST Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tax</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($taxRates as $tax): 
                        $totalTax = ($tax['hst_rate'] > 0) ? $tax['hst_rate'] : ($tax['gst_rate'] + $tax['pst_rate']);
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($tax['province']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($tax['province_code']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900"><?= number_format($tax['gst_rate'] * 100, 2) ?>%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900"><?= number_format($tax['pst_rate'] * 100, 2) ?>%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900"><?= number_format($tax['hst_rate'] * 100, 2) ?>%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand text-white">
                                    <?= number_format($totalTax * 100, 2) ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button 
                                    onclick="openTaxModal('<?= $tax['province_code'] ?>', '<?= htmlspecialchars($tax['province']) ?>', <?= $tax['gst_rate'] ?>, <?= $tax['pst_rate'] ?>, <?= $tax['hst_rate'] ?>)"
                                    class="text-brand hover:text-brand-dark font-semibold">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="tax-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeTaxModal()">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600" onclick="closeTaxModal()">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h2 class="text-2xl font-bold mb-2">Update Tax Rates</h2>
        <p class="text-sm text-gray-600 mb-6" id="modal-province-name"></p>
        <form method="POST" action="<?= BASE_URL ?>/admin/settings/tax" id="tax-form">
            <?= $csrfField ?? '' ?>
            <input type="hidden" name="province_code" id="tax-province-code">
            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label">GST Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="gst_rate" id="gst-input" class="form-input" required>
                    <p class="text-xs text-gray-500 mt-1">Goods and Services Tax (applies to all provinces)</p>
                </div>
                <div class="form-group">
                    <label class="form-label">PST Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="pst_rate" id="pst-input" class="form-input" required>
                    <p class="text-xs text-gray-500 mt-1">Provincial Sales Tax (applies to certain provinces)</p>
                </div>
                <div class="form-group">
                    <label class="form-label">HST Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="hst_rate" id="hst-input" class="form-input" required>
                    <p class="text-xs text-gray-500 mt-1">Harmonized Sales Tax (replaces GST + PST in some provinces)</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> If HST is used, set GST and PST to 0. If GST + PST is used, set HST to 0.
                    </p>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="button" class="btn btn-secondary mr-3" onclick="closeTaxModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i> Update Tax Rates
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openTaxModal(code, provinceName, gst, pst, hst) {
    const modal = document.getElementById('tax-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('tax-province-code').value = code;
    document.getElementById('modal-province-name').textContent = provinceName + ' (' + code + ')';
    document.getElementById('gst-input').value = (gst * 100).toFixed(2);
    document.getElementById('pst-input').value = (pst * 100).toFixed(2);
    document.getElementById('hst-input').value = (hst * 100).toFixed(2);
}

function closeTaxModal() {
    const modal = document.getElementById('tax-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTaxModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
?>

