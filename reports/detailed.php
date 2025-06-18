<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch sales by category
$sales_by_category = $conn->query("SELECT c.name, COUNT(s.id) as sale_count, SUM(s.total_amount) as total_revenue FROM sales s JOIN sale_items si ON s.id = si.sale_id JOIN products p ON si.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE s.status = 'completed' GROUP BY c.id ORDER BY total_revenue DESC");

// Fetch sales by payment method
$sales_by_payment = $conn->query("SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total FROM sales WHERE status = 'completed' GROUP BY payment_method");

// Fetch detailed sales list with optional filters
$where_clause = "WHERE 1=1";
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $where_clause .= " AND s.sale_date >= '" . $conn->real_escape_string($_GET['start_date']) . "'";
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $where_clause .= " AND s.sale_date <= '" . $conn->real_escape_string($_GET['end_date']) . "'";
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clause .= " AND s.status = '" . $conn->real_escape_string($_GET['status']) . "'";
}

$detailed_sales = $conn->query("SELECT s.*, COUNT(si.id) as item_count FROM sales s LEFT JOIN sale_items si ON s.id = si.sale_id $where_clause GROUP BY s.id ORDER BY s.sale_date DESC");

?>
<div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Detailed Sales Report</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <h2 class="text-lg font-semibold mb-4">Sales by Category</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border-b">Category</th>
                            <th class="px-4 py-2 border-b">Sales Count</th>
                            <th class="px-4 py-2 border-b">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $sales_by_category->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="px-4 py-2 border-b text-center"><?php echo $category['sale_count']; ?></td>
                            <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($category['total_revenue'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Sales by Payment Method</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border-b">Payment Method</th>
                            <th class="px-4 py-2 border-b">Count</th>
                            <th class="px-4 py-2 border-b">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = $sales_by_payment->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                            <td class="px-4 py-2 border-b text-center"><?php echo $payment['count']; ?></td>
                            <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($payment['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-semibold mb-4">Detailed Sales List</h2>
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $_GET['end_date'] ?? ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">Sale #</th>
                        <th class="px-4 py-2 border-b">Date</th>
                        <th class="px-4 py-2 border-b">Customer</th>
                        <th class="px-4 py-2 border-b">Items</th>
                        <th class="px-4 py-2 border-b">Total</th>
                        <th class="px-4 py-2 border-b">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sale = $detailed_sales->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2 border-b"><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td class="px-4 py-2 border-b text-center"><?php echo $sale['item_count']; ?></td>
                        <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($sale['total_amount'], 2); ?></td>
                        <td class="px-4 py-2 border-b">
                            <span class="px-2 py-1 rounded text-white <?php
                                if ($sale['status'] === 'completed') echo 'bg-green-500';
                                elseif ($sale['status'] === 'cancelled') echo 'bg-red-500';
                                else echo 'bg-yellow-500';
                            ?>"><?php echo ucfirst($sale['status']); ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?> 