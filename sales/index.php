<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle sale deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $conn->begin_transaction();
        
        // Delete sale items first (due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM sale_items WHERE sale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Delete the sale
        $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Sale deleted successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting sale: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Fetch all sales with item counts
$sales = $conn->query("
    SELECT s.*, 
           COUNT(si.id) as item_count
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    GROUP BY s.id
    ORDER BY s.sale_date DESC
");
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Sales Management</h1>
        <a href="add.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add New Sale</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($sale = $sales->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="view.php?id=<?php echo $sale['id']; ?>" class="text-blue-500 hover:text-blue-600">
                                #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $sale['item_count']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($sale['total_amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo ucfirst(str_replace('_', ' ', $sale['payment_method'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                echo match($sale['status']) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                                ?>">
                                <?php echo ucfirst($sale['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="view.php?id=<?php echo $sale['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <?php if ($sale['status'] === 'pending'): ?>
                                <a href="edit.php?id=<?php echo $sale['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                                <a href="index.php?delete=<?php echo $sale['id']; ?>" class="text-red-600 hover:text-red-900" 
                                   onclick="return confirm('Are you sure you want to delete this sale?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 