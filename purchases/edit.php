<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch purchase data
$stmt = $conn->prepare("
    SELECT p.*, s.name as supplier_name
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

if ($purchase['status'] !== 'pending') {
    $_SESSION['error'] = "Only pending purchases can be edited";
    header("Location: view.php?id=" . $id);
    exit();
}

// Fetch purchase items
$stmt = $conn->prepare("
    SELECT pi.*, pr.name as product_name, pr.sku, pr.stock_quantity
    FROM purchase_items pi
    LEFT JOIN products pr ON pi.product_id = pr.id
    WHERE pi.purchase_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();

// Fetch active suppliers
$suppliers = $conn->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");

// Fetch products
$products = $conn->query("SELECT id, name, sku, price FROM products ORDER BY name");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = (int)$_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $notes = trim($_POST['notes']);
    $items_data = $_POST['items'] ?? [];

    // Validate supplier
    if ($supplier_id <= 0) {
        $error = "Please select a supplier";
    }
    // Validate date
    elseif (empty($purchase_date)) {
        $error = "Please select a purchase date";
    }
    // Validate items
    elseif (empty($items_data)) {
        $error = "Please add at least one item";
    } else {
        $total_amount = 0;
        $valid_items = true;

        foreach ($items_data as $item) {
            if (empty($item['product_id']) || empty($item['quantity']) || empty($item['unit_price'])) {
                $valid_items = false;
                break;
            }
            $total_amount += $item['quantity'] * $item['unit_price'];
        }

        if (!$valid_items) {
            $error = "Please fill in all item details";
        } else {
            try {
                $conn->begin_transaction();

                // Update purchase
                $stmt = $conn->prepare("
                    UPDATE purchases 
                    SET supplier_id = ?, purchase_date = ?, total_amount = ?, notes = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("isdsi", $supplier_id, $purchase_date, $total_amount, $notes, $id);
                $stmt->execute();

                // Delete existing items
                $stmt = $conn->prepare("DELETE FROM purchase_items WHERE purchase_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                // Insert new items
                $stmt = $conn->prepare("
                    INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?)
                ");

                foreach ($items_data as $item) {
                    $product_id = (int)$item['product_id'];
                    $quantity = (int)$item['quantity'];
                    $unit_price = (float)$item['unit_price'];
                    $total_price = $quantity * $unit_price;

                    $stmt->bind_param("iiids", $id, $product_id, $quantity, $unit_price, $total_price);
                    $stmt->execute();

                    // Update product stock
                    $update_stock = $conn->prepare("
                        UPDATE products 
                        SET stock_quantity = stock_quantity + ? 
                        WHERE id = ?
                    ");
                    $update_stock->bind_param("ii", $quantity, $product_id);
                    $update_stock->execute();

                    // Log inventory transaction
                    $log_transaction = $conn->prepare("
                        INSERT INTO inventory_transactions (product_id, transaction_type, quantity, notes)
                        VALUES (?, 'stock_in', ?, ?)
                    ");
                    $transaction_note = "Stock in from purchase #" . str_pad($id, 6, '0', STR_PAD_LEFT);
                    $log_transaction->bind_param("iis", $product_id, $quantity, $transaction_note);
                    $log_transaction->execute();
                }

                $conn->commit();
                $_SESSION['success'] = "Purchase updated successfully";
                header("Location: view.php?id=" . $id);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error updating purchase: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Purchase Order #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></h1>
        <a href="view.php?id=<?php echo $id; ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to View</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                <select name="supplier_id" id="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Supplier</option>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier['id'] == $purchase['supplier_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                <input type="date" name="purchase_date" id="purchase_date" required
                    value="<?php echo htmlspecialchars($purchase['purchase_date']); ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($purchase['notes']); ?></textarea>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Purchase Items</h2>
            <div id="items-container">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product</label>
                            <select name="items[<?php echo $item['id']; ?>][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Product</option>
                                <?php 
                                $products->data_seek(0);
                                while ($product = $products->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $product['id']; ?>" <?php echo $product['id'] == $item['product_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="items[<?php echo $item['id']; ?>][quantity]" required min="1"
                                value="<?php echo $item['quantity']; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                            <input type="number" name="items[<?php echo $item['id']; ?>][unit_price]" required min="0.01" step="0.01"
                                value="<?php echo $item['unit_price']; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <button type="button" id="add-item" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add Item</button>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Update Purchase</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('items-container');
    const addItemButton = document.getElementById('add-item');
    let itemCount = <?php echo $items->num_rows; ?>;

    addItemButton.addEventListener('click', function() {
        const newItem = document.createElement('div');
        newItem.className = 'item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4';
        newItem.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Product</label>
                <select name="items[new_${itemCount}][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Product</option>
                    <?php 
                    $products->data_seek(0);
                    while ($product = $products->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="items[new_${itemCount}][quantity]" required min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                <input type="number" name="items[new_${itemCount}][unit_price]" required min="0.01" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
            </div>
        `;
        itemsContainer.appendChild(newItem);
        itemCount++;
    });

    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 