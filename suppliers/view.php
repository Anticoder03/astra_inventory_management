<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch supplier data
$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

if (!$supplier) {
    $_SESSION['error'] = "Supplier not found";
    header("Location: index.php");
    exit();
}

// Fetch products from this supplier
$stmt = $conn->prepare("SELECT * FROM products WHERE supplier_id = ? ORDER BY name");
$stmt->bind_param("i", $id);
$stmt->execute();
$products = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Supplier Details</h1>
        <div class="space-x-2">
            <a href="edit.php?id=<?php echo $supplier['id']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Edit</a>
            <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($supplier['name']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($supplier['contact_person']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($supplier['email']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($supplier['phone']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $supplier['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($supplier['status']); ?>
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Address</h2>
            <p class="text-sm text-gray-900 whitespace-pre-line"><?php echo htmlspecialchars($supplier['address']); ?></p>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-4">Products from this Supplier</h2>
        <?php if ($products->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($product['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['stock_quantity']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="../products/view.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No products found for this supplier.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 