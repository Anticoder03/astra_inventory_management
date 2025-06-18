<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch purchase data with supplier information
$stmt = $conn->prepare("
    SELECT p.*, s.name as supplier_name, s.contact_person, s.email, s.phone
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();

if (!$purchase) {
    $_SESSION['error'] = "Purchase not found";
    header("Location: index.php");
    exit();
}

// Fetch purchase items
$stmt = $conn->prepare("
    SELECT pi.*, pr.name as product_name, pr.sku
    FROM purchase_items pi
    LEFT JOIN products pr ON pi.product_id = pr.id
    WHERE pi.purchase_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Purchase Order #<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <div class="space-x-2">
            <?php if ($purchase['status'] === 'pending'): ?>
                <a href="edit.php?id=<?php echo $purchase['id']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Edit</a>
            <?php endif; ?>
            <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <h2 class="text-lg font-semibold mb-4">Purchase Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Purchase Date</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo date('F d, Y', strtotime($purchase['purchase_date'])); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php 
                            echo match($purchase['status']) {
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800'
                            };
                            ?>">
                            <?php echo ucfirst($purchase['status']); ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900">₹<?php echo number_format($purchase['total_amount'], 2); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($purchase['notes'])); ?></dd>
                </div>
            </dl>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($purchase['supplier_name']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($purchase['contact_person']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($purchase['email']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($purchase['phone']); ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-4">Purchase Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['sku']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['quantity']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-6 py-4 text-right font-medium">Total Amount:</td>
                        <td class="px-6 py-4 font-medium">₹<?php echo number_format($purchase['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 