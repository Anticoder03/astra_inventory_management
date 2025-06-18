<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$product_id = $quantity = $notes = "";
$product_id_err = $quantity_err = "";
$success_message = "";

// Fetch all products for dropdown
$products_sql = "SELECT id, name, sku FROM products ORDER BY name ASC";
$products_result = mysqli_query($conn, $products_sql);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate product
    if(empty(trim($_POST["product_id"]))){
        $product_id_err = "Please select a product.";
    } else{
        $product_id = trim($_POST["product_id"]);
    }
    
    // Validate quantity
    $quantity = sanitizeInput($_POST["quantity"]);
    $quantity_err = validateRequired($quantity, "Quantity");
    if(empty($quantity_err)) {
        $quantity_err = validateNumeric($quantity, "Quantity");
    }
    
    // Get notes
    $notes = sanitizeInput($_POST["notes"]);
    
    // Check input errors before inserting in database
    if(empty($product_id_err) && empty($quantity_err)){
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert transaction record
            $sql = "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, notes) VALUES (?, 'in', ?, ?)";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "iis", $product_id, $quantity, $notes);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            
            // Update product stock
            $sql = "UPDATE products SET stock = stock + ? WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "ii", $quantity, $product_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            
            // Commit transaction
            mysqli_commit($conn);
            $success_message = "Stock added successfully!";
            
            // Clear form fields
            $product_id = $quantity = $notes = "";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error_message = handleDatabaseError($e->getMessage());
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Stock In</h1>
        <a href="/quick_site/IMS/inventory/index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>

    <?php 
    if(!empty($success_message)) {
        echo showSuccess($success_message);
    }
    if(!empty($error_message)) {
        echo showError($error_message);
    }
    ?>

    <form action="/quick_site/IMS/inventory/stock_in.php" method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Product</label>
            <select name="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($product_id_err)) ? 'border-red-500' : ''; ?>">
                <option value="">Select a product</option>
                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                    <option value="<?php echo $product['id']; ?>" <?php echo ($product_id == $product['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if(!empty($product_id_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $product_id_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Quantity</label>
            <input type="number" name="quantity" value="<?php echo $quantity; ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($quantity_err)) ? 'border-red-500' : ''; ?>">
            <?php if(!empty($quantity_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $quantity_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="3" 
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo $notes; ?></textarea>
        </div>

        <div>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add Stock</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 