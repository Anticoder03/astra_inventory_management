<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$name = $sku = $price = $stock = "";
$name_err = $sku_err = $price_err = $stock_err = "";
$success_message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate name
    $name = sanitizeInput($_POST["name"]);
    $name_err = validateRequired($name, "Product Name");
    
    // Validate SKU
    $sku = sanitizeInput($_POST["sku"]);
    $sku_err = validateRequired($sku, "SKU");
    if(empty($sku_err)) {
        $sku_err = validateSKU($sku);
        if(empty($sku_err) && recordExists($conn, "products", "sku", $sku)) {
            $sku_err = "This SKU already exists.";
        }
    }
    
    // Validate price
    $price = sanitizeInput($_POST["price"]);
    $price_err = validateRequired($price, "Price");
    if(empty($price_err)) {
        $price_err = validateNumeric($price, "Price");
    }
    
    // Validate stock
    $stock = sanitizeInput($_POST["stock"]);
    $stock_err = validateRequired($stock, "Stock");
    if(empty($stock_err)) {
        $stock_err = validateNumeric($stock, "Stock");
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($sku_err) && empty($price_err) && empty($stock_err)){
        $sql = "INSERT INTO products (name, sku, price, stock) VALUES (?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssdi", $name, $sku, $price, $stock);
            
            if(mysqli_stmt_execute($stmt)){
                $success_message = "Product added successfully!";
                // Clear form fields
                $name = $sku = $price = $stock = "";
            } else{
                $error_message = handleDatabaseError(mysqli_error($conn));
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Add New Product</h1>
        <a href="/quick_site/IMS/products/index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>

    <?php 
    if(!empty($success_message)) {
        echo showSuccess($success_message);
    }
    if(!empty($error_message)) {
        echo showError($error_message);
    }
    ?>

    <form action="/quick_site/IMS/products/create.php" method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" name="name" value="<?php echo $name; ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($name_err)) ? 'border-red-500' : ''; ?>">
            <?php if(!empty($name_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $name_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">SKU</label>
            <input type="text" name="sku" value="<?php echo $sku; ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($sku_err)) ? 'border-red-500' : ''; ?>">
            <?php if(!empty($sku_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $sku_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Price</label>
            <input type="number" step="0.01" name="price" value="<?php echo $price; ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($price_err)) ? 'border-red-500' : ''; ?>">
            <?php if(!empty($price_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $price_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Stock</label>
            <input type="number" name="stock" value="<?php echo $stock; ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php echo (!empty($stock_err)) ? 'border-red-500' : ''; ?>">
            <?php if(!empty($stock_err)): ?>
                <span class="text-red-500 text-sm"><?php echo $stock_err; ?></span>
            <?php endif; ?>
        </div>

        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Product</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 