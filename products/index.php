<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$success_message = "";
$error_message = "";

// Delete product
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if product exists
    if(recordExists($conn, "products", "id", $id)) {
        $sql = "DELETE FROM products WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $success_message = "Product deleted successfully!";
            } else {
                $error_message = handleDatabaseError(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Product not found.";
    }
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);

if(!$result) {
    $error_message = handleDatabaseError(mysqli_error($conn));
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Products Management</h1>
        <a href="/quick_site/IMS/products/create.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add New Product</a>
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
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">SKU</th>
                        <th class="px-6 py-3 text-left">Price</th>
                        <th class="px-6 py-3 text-left">Stock</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4"><?php echo $i++ ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td class="px-6 py-4">₹<?php echo number_format($row['price'], 2); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['stock_quantity'] < 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo $row['stock_quantity']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/quick_site/IMS/products/edit.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3">Edit</a>
                            <a href="/quick_site/IMS/products/index.php?delete=<?php echo $row['id']; ?>" 
                               class="text-red-500 hover:text-red-700" 
                               onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-4">
            <p class="text-gray-500">No products found.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 