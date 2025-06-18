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

// Fetch products for dropdown
$products = $conn->query("SELECT id, name, sku, price, stock_quantity FROM products ORDER BY name");

// Fetch sale items
$stmt = $conn->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
$stmt->bind_param('i', $sale_id);
$stmt->execute();
$sale_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    $items_data = $_POST['items'] ?? [];
    $status = $sale['status'];
    if ($sale['status'] === 'pending' && isset($_POST['status'])) {
        $status = $_POST['status'];
    }

    if (empty($customer_name)) {
        $error = "Customer name is required";
    } elseif (empty($sale_date)) {
        $error = "Sale date is required";
    } elseif (empty($payment_method)) {
        $error = "Payment method is required";
    } elseif (empty($items_data)) {
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
                // Update sale
                $stmt = $conn->prepare("UPDATE sales SET sale_date=?, customer_name=?, customer_email=?, customer_phone=?, total_amount=?, payment_method=?, notes=?, status=? WHERE id=?");
                $stmt->bind_param("sssssdssi", $sale_date, $customer_name, $customer_email, $customer_phone, $total_amount, $payment_method, $notes, $status, $sale_id);
                $stmt->execute();

                // Restore stock for old items
                $stmt = $conn->prepare("SELECT product_id, quantity FROM sale_items WHERE sale_id = ?");
                $stmt->bind_param('i', $sale_id);
                $stmt->execute();
                $old_items = $stmt->get_result();
                while ($old = $old_items->fetch_assoc()) {
                    $conn->query("UPDATE products SET stock_quantity = stock_quantity + {$old['quantity']} WHERE id = {$old['product_id']}");
                }

                // Delete old sale items
                $conn->query("DELETE FROM sale_items WHERE sale_id = $sale_id");

                // Insert new sale items
                $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                foreach ($items_data as $item) {
                    $product_id = (int)$item['product_id'];
                    $quantity = (int)$item['quantity'];
                    $unit_price = (float)$item['unit_price'];
                    $total_price = $quantity * $unit_price;
                    $stmt->bind_param("iiids", $sale_id, $product_id, $quantity, $unit_price, $total_price);
                    $stmt->execute();
                    // Update product stock
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $update_stock->bind_param("ii", $quantity, $product_id);
                    $update_stock->execute();
                }
                $conn->commit();
                $_SESSION['success'] = "Sale updated successfully";
                header("Location: view.php?id=$sale_id");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error updating sale: " . $e->getMessage();
            }
        }
    }
}
?>
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Sale #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                <input type="text" name="customer_name" id="customer_name" required value="<?php echo htmlspecialchars($_POST['customer_name'] ?? $sale['customer_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="customer_email" class="block text-sm font-medium text-gray-700">Customer Email</label>
                <input type="email" name="customer_email" id="customer_email" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? $sale['customer_email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                <input type="tel" name="customer_phone" id="customer_phone" value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? $sale['customer_phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="sale_date" class="block text-sm font-medium text-gray-700">Sale Date</label>
                <input type="date" name="sale_date" id="sale_date" required value="<?php echo htmlspecialchars($_POST['sale_date'] ?? $sale['sale_date']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select name="payment_method" id="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Payment Method</option>
                    <option value="cash" <?php echo (($_POST['payment_method'] ?? $sale['payment_method']) === 'cash') ? 'selected' : ''; ?>>Cash</option>
                    <option value="credit_card" <?php echo (($_POST['payment_method'] ?? $sale['payment_method']) === 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                    <option value="bank_transfer" <?php echo (($_POST['payment_method'] ?? $sale['payment_method']) === 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                </select>
            </div>
            <?php if ($sale['status'] === 'pending'): ?>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="pending" <?php echo (($status ?? $sale['status']) === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo (($status ?? $sale['status']) === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo (($status ?? $sale['status']) === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <?php else: ?>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1 px-3 py-2 rounded bg-gray-100 text-gray-700"><?php echo ucfirst($sale['status']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['notes'] ?? $sale['notes']); ?></textarea>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Sale Items</h2>
            <div id="items-container">
                <?php $i = 0; foreach ($_POST['items'] ?? $sale_items as $item): ?>
                <div class="item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product</label>
                        <select name="items[<?php echo $i; ?>][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Product</option>
                            <?php $products->data_seek(0); while ($product = $products->fetch_assoc()): ?>
                                <option value="<?php echo $product['id']; ?>" <?php echo ($item['product_id'] == $product['id']) ? 'selected' : ''; ?> data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['stock_quantity']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="items[<?php echo $i; ?>][quantity]" required min="1" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="number" name="items[<?php echo $i; ?>][unit_price]" required min="0.01" step="0.01" value="<?php echo htmlspecialchars($item['unit_price']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
            <button type="button" id="add-item" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add Item</button>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Update Sale</button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('items-container');
    const addItemButton = document.getElementById('add-item');
    let itemCount = <?php echo $i ?? 0; ?>;
    function updateUnitPrice(select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const stock = option.getAttribute('data-stock');
        const row = select.closest('.item-row');
        const quantityInput = row.querySelector('input[name$="[quantity]"]');
        const priceInput = row.querySelector('input[name$="[unit_price]"]');
        if (price) priceInput.value = price;
        if (stock) quantityInput.max = stock;
    }
    itemsContainer.querySelectorAll('select[name$="[product_id]"]').forEach(select => {
        select.addEventListener('change', function() { updateUnitPrice(this); });
    });
    addItemButton.addEventListener('click', function() {
        const newItem = document.createElement('div');
        newItem.className = 'item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4';
        newItem.innerHTML = `
            <div>
                <label class=\"block text-sm font-medium text-gray-700\">Product</label>
                <select name=\"items[${itemCount}][product_id]\" required class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                    <option value=\"\">Select Product</option>
                    <?php $products->data_seek(0); while ($product = $products->fetch_assoc()): ?>
                        <option value=\"<?php echo $product['id']; ?>\" data-price=\"<?php echo $product['price']; ?>\" data-stock=\"<?php echo $product['stock_quantity']; ?>\"><?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class=\"block text-sm font-medium text-gray-700\">Quantity</label>
                <input type=\"number\" name=\"items[${itemCount}][quantity]\" required min=\"1\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
            </div>
            <div>
                <label class=\"block text-sm font-medium text-gray-700\">Unit Price</label>
                <input type=\"number\" name=\"items[${itemCount}][unit_price]\" required min=\"0.01\" step=\"0.01\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
            </div>
            <div class=\"flex items-end\">
                <button type=\"button\" class=\"remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600\">Remove</button>
            </div>
        `;
        itemsContainer.appendChild(newItem);
        const newSelect = newItem.querySelector('select[name$="[product_id]"]');
        newSelect.addEventListener('change', function() { updateUnitPrice(this); });
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