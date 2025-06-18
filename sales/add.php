<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// Fetch products for dropdown
$products = $conn->query("SELECT id, name, sku, price, stock_quantity FROM products ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    $items_data = $_POST['items'] ?? [];

    // Validate customer name
    if (empty($customer_name)) {
        $error = "Customer name is required";
    }
    // Validate date
    elseif (empty($sale_date)) {
        $error = "Sale date is required";
    }
    // Validate payment method
    elseif (empty($payment_method)) {
        $error = "Payment method is required";
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

                // Insert sale
                $stmt = $conn->prepare("
                    INSERT INTO sales (
                        sale_date, customer_name, customer_email, customer_phone,
                        total_amount, payment_method, status, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->bind_param(
                    "ssssdss",
                    $sale_date,
                    $customer_name,
                    $customer_email,
                    $customer_phone,
                    $total_amount,
                    $payment_method,
                    $notes
                );
                $stmt->execute();
                $sale_id = $conn->insert_id;

                // Insert sale items
                $stmt = $conn->prepare("
                    INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?)
                ");

                foreach ($items_data as $item) {
                    $product_id = (int)$item['product_id'];
                    $quantity = (int)$item['quantity'];
                    $unit_price = (float)$item['unit_price'];
                    $total_price = $quantity * $unit_price;

                    $stmt->bind_param("iiids", $sale_id, $product_id, $quantity, $unit_price, $total_price);
                    $stmt->execute();

                    // Update product stock
                    $update_stock = $conn->prepare("
                        UPDATE products 
                        SET stock_quantity = stock_quantity - ? 
                        WHERE id = ?
                    ");
                    $update_stock->bind_param("ii", $quantity, $product_id);
                    $update_stock->execute();

                    // Log inventory transaction
                    $log_transaction = $conn->prepare("
                        INSERT INTO inventory_transactions (product_id, transaction_type, quantity, notes)
                        VALUES (?, 'out', ?, ?)
                    ");
                    $transaction_note = "Stock out from sale #" . str_pad($sale_id, 6, '0', STR_PAD_LEFT);
                    $log_transaction->bind_param("iis", $product_id, $quantity, $transaction_note);
                    $log_transaction->execute();
                }

                $conn->commit();
                $_SESSION['success'] = "Sale added successfully";
                header("Location: view.php?id=" . $sale_id);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error adding sale: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Add New Sale</h1>
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                <input type="text" name="customer_name" id="customer_name" required
                    value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="customer_email" class="block text-sm font-medium text-gray-700">Customer Email</label>
                <input type="email" name="customer_email" id="customer_email"
                    value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                <input type="tel" name="customer_phone" id="customer_phone"
                    value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : ''; ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="sale_date" class="block text-sm font-medium text-gray-700">Sale Date</label>
                <input type="date" name="sale_date" id="sale_date" required
                    value="<?php echo isset($_POST['sale_date']) ? htmlspecialchars($_POST['sale_date']) : date('Y-m-d'); ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select name="payment_method" id="payment_method" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Payment Method</option>
                    <option value="cash" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="credit_card" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                    <option value="bank_transfer" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                </select>
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" id="notes" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Sale Items</h2>
            <div id="items-container">
                <div class="item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product</label>
                        <select name="items[0][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Product</option>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <option value="<?php echo $product['id']; ?>" 
                                        data-price="<?php echo $product['price']; ?>"
                                        data-stock="<?php echo $product['stock_quantity']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="items[0][quantity]" required min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="number" name="items[0][unit_price]" required min="0.01" step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-item" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add Item</button>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Create Sale</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('items-container');
    const addItemButton = document.getElementById('add-item');
    let itemCount = 1;

    // Function to update unit price when product is selected
    function updateUnitPrice(select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const stock = option.getAttribute('data-stock');
        const row = select.closest('.item-row');
        const quantityInput = row.querySelector('input[name$="[quantity]"]');
        const priceInput = row.querySelector('input[name$="[unit_price]"]');
        
        if (price) {
            priceInput.value = price;
        }
        
        if (stock) {
            quantityInput.max = stock;
        }
    }

    // Add event listeners to existing product selects
    document.querySelectorAll('select[name$="[product_id]"]').forEach(select => {
        select.addEventListener('change', function() {
            updateUnitPrice(this);
        });
    });

    addItemButton.addEventListener('click', function() {
        const newItem = document.createElement('div');
        newItem.className = 'item-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4';
        newItem.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Product</label>
                <select name="items[${itemCount}][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Product</option>
                    <?php 
                    $products->data_seek(0);
                    while ($product = $products->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $product['id']; ?>" 
                                data-price="<?php echo $product['price']; ?>"
                                data-stock="<?php echo $product['stock_quantity']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="items[${itemCount}][quantity]" required min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                <input type="number" name="items[${itemCount}][unit_price]" required min="0.01" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
            </div>
        `;
        itemsContainer.appendChild(newItem);
        
        // Add event listener to new product select
        const newSelect = newItem.querySelector('select[name$="[product_id]"]');
        newSelect.addEventListener('change', function() {
            updateUnitPrice(this);
        });
        
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