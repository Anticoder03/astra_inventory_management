<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// Fetch active suppliers
$suppliers = $conn->query("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name");

// Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = (int)$_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $notes = trim($_POST['notes']);
    $items = $_POST['items'];
    
    // Validate input
    if (empty($supplier_id)) {
        $error = "Please select a supplier";
    } elseif (empty($purchase_date)) {
        $error = "Please select a purchase date";
    } elseif (empty($items)) {
        $error = "Please add at least one item";
    } else {
        // Calculate total amount
        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += $item['quantity'] * $item['unit_price'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert purchase
            $stmt = $conn->prepare("INSERT INTO purchases (supplier_id, purchase_date, total_amount, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $supplier_id, $purchase_date, $total_amount, $notes);
            $stmt->execute();
            $purchase_id = $conn->insert_id;
            
            // Insert purchase items
            $stmt = $conn->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $product_id = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];
                $unit_price = (float)$item['unit_price'];
                $total_price = $quantity * $unit_price;
                
                $stmt->bind_param("iiidd", $purchase_id, $product_id, $quantity, $unit_price, $total_price);
                $stmt->execute();
                
                // Update product stock
                $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
                
                // Add inventory transaction
                $add_transaction = $conn->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, notes) VALUES (?, 'in', ?, ?)");
                $transaction_note = "Purchase Order #" . str_pad($purchase_id, 6, '0', STR_PAD_LEFT);
                $add_transaction->bind_param("iis", $product_id, $quantity, $transaction_note);
                $add_transaction->execute();
            }
            
            $conn->commit();
            $_SESSION['success'] = "Purchase order created successfully";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating purchase order: " . $e->getMessage();
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Purchase Order</h1>
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="purchaseForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                <select name="supplier_id" id="supplier_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Supplier</option>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date *</label>
                <input type="date" name="purchase_date" id="purchase_date" required
                       value="<?php echo date('Y-m-d'); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Purchase Items</h2>
            <div id="itemsContainer" class="space-y-4">
                <!-- Items will be added here dynamically -->
            </div>
            <button type="button" id="addItem" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Add Item
            </button>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Create Purchase Order</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemButton = document.getElementById('addItem');
    let itemCount = 0;

    function createItemRow() {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border rounded';
        itemDiv.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Product *</label>
                <select name="items[${itemCount}][product_id]" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Product</option>
                    <?php 
                    $products->data_seek(0);
                    while ($product = $products->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                <input type="number" name="items[${itemCount}][quantity]" required min="1"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
                <input type="number" name="items[${itemCount}][unit_price]" required min="0.01" step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                        onclick="this.parentElement.parentElement.remove()">Remove</button>
            </div>
        `;
        itemsContainer.appendChild(itemDiv);
        itemCount++;
    }

    addItemButton.addEventListener('click', createItemRow);

    // Add first item row by default
    createItemRow();
});
</script>

<?php require_once '../includes/footer.php'; ?> 