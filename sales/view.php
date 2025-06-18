<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Invalid sale ID.</div>';
    require_once '../includes/footer.php';
    exit;
}
$sale_id = (int)$_GET['id'];

// Fetch sale
$stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->bind_param('i', $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
if (!$sale) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Sale not found.</div>';
    require_once '../includes/footer.php';
    exit;
}

// Fetch sale items
$stmt = $conn->prepare("SELECT si.*, p.name, p.sku FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$stmt->bind_param('i', $sale_id);
$stmt->execute();
$sale_items = $stmt->get_result();
?>
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Sale #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h2 class="font-semibold text-lg mb-2">Customer Info</h2>
            <div><strong>Name:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($sale['customer_email']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($sale['customer_phone']); ?></div>
        </div>
        <div>
            <h2 class="font-semibold text-lg mb-2">Sale Details</h2>
            <div><strong>Date:</strong> <?php echo htmlspecialchars($sale['sale_date']); ?></div>
            <div><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $sale['payment_method'])); ?></div>
            <div><strong>Status:</strong> <span class="px-2 py-1 rounded text-white <?php
                if ($sale['status'] === 'completed') echo 'bg-green-500';
                elseif ($sale['status'] === 'cancelled') echo 'bg-red-500';
                else echo 'bg-yellow-500';
            ?>"><?php echo ucfirst($sale['status']); ?></span></div>
        </div>
    </div>
    <div class="mb-4">
        <h2 class="font-semibold text-lg mb-2">Notes</h2>
        <div class="bg-gray-100 rounded p-2 text-gray-700"><?php echo nl2br(htmlspecialchars($sale['notes'])); ?></div>
    </div>
    <div>
        <h2 class="font-semibold text-lg mb-2">Sale Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">Product</th>
                        <th class="px-4 py-2 border-b">SKU</th>
                        <th class="px-4 py-2 border-b">Quantity</th>
                        <th class="px-4 py-2 border-b">Unit Price</th>
                        <th class="px-4 py-2 border-b">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $grand_total = 0; while ($item = $sale_items->fetch_assoc()): $grand_total += $item['total_price']; ?>
                    <tr>
                        <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($item['sku']); ?></td>
                        <td class="px-4 py-2 border-b text-center"><?php echo $item['quantity']; ?></td>
                        <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="px-4 py-2 font-bold text-right">Grand Total</td>
                        <td class="px-4 py-2 font-bold text-right">₹<?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?> 