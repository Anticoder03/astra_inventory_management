<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$name = $sku = $price = $stock = "";
$name_err = $sku_err = $price_err = $stock_err = "";

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $id = trim($_GET["id"]);
    
    $sql = "SELECT * FROM products WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result);
                
                $name = $row["name"];
                $sku = $row["sku"];
                $price = $row["price"];
                $stock = $row["stock_quantity"];
            } else{
                header("location: index.php");
                exit();
            }
            
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id = $_POST["id"];
    
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a product name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate SKU
    if(empty(trim($_POST["sku"]))){
        $sku_err = "Please enter a SKU.";
    } else{
        $sku = trim($_POST["sku"]);
    }
    
    // Validate price
    if(empty(trim($_POST["price"]))){
        $price_err = "Please enter a price.";
    } else{
        $price = trim($_POST["price"]);
    }
    
    // Validate stock
    if(empty(trim($_POST["stock"]))){
        $stock_err = "Please enter stock quantity.";
    } else{
        $stock = trim($_POST["stock"]);
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($sku_err) && empty($price_err) && empty($stock_err)){
        $sql = "UPDATE products SET name=?, sku=?, price=?, stock_quantity=? WHERE id=?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssdii", $name, $sku, $price, $stock, $id);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Product</h1>
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to List</a>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" name="name" value="<?php echo $name; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="text-red-500 text-sm"><?php echo $name_err; ?></span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">SKU</label>
            <input type="text" name="sku" value="<?php echo $sku; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="text-red-500 text-sm"><?php echo $sku_err; ?></span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Price</label>
            <input type="number" step="0.01" name="price" value="<?php echo $price; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="text-red-500 text-sm"><?php echo $price_err; ?></span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Stock</label>
            <input type="number" name="stock" value="<?php echo $stock; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="text-red-500 text-sm"><?php echo $stock_err; ?></span>
        </div>

        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Product</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 