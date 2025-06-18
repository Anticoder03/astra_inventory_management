<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$success_message = "";
$error_message = "";

// Fetch all inventory transactions with product details
$sql = "SELECT t.*, p.name as product_name, p.sku 
        FROM inventory_transactions t 
        JOIN products p ON t.product_id = p.id 
        ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $sql);

if(!$result) {
    $error_message = handleDatabaseError(mysqli_error($conn));
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Stock Management</h1>
        <div class="space-x-4">
            <a href="/quick_site/IMS/inventory/stock_in.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Stock In</a>
            <a href="/quick_site/IMS/inventory/stock_out.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Stock Out</a>
        </div>
    </div>

    <?php 
    if(!empty($success_message)) {
        echo showSuccess($success_message);
    }
    if(!empty($error_message)) {
        echo showError($error_message);
    }
    ?>

    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Product</th>
                        <th class="px-6 py-3 text-left">SKU</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-left">Quantity</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4"><?php echo $i++; ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['transaction_type'] == 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo strtoupper($row['transaction_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?php echo $row['quantity']; ?></td>
                        <td class="px-6 py-4"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['notes']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-4">
            <p class="text-gray-500">No stock transactions found.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 