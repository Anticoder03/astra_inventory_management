<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch sales statistics
$sales_stats = $conn->query("SELECT COUNT(*) as total_sales, SUM(total_amount) as total_revenue, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_sales, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sales FROM sales")->fetch_assoc();

// Fetch top-selling products
$top_products = $conn->query("SELECT p.name, p.sku, SUM(si.quantity) as total_quantity, SUM(si.total_price) as total_revenue FROM sale_items si JOIN products p ON si.product_id = p.id JOIN sales s ON si.sale_id = s.id WHERE s.status = 'completed' GROUP BY p.id ORDER BY total_quantity DESC LIMIT 5");

// Fetch monthly sales data for the last 12 months
$monthly_sales = $conn->query("SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE status = 'completed' AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");

?>
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
        <a href="detailed.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            View Detailed Report
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-blue-800">Total Sales</h2>
            <p class="text-2xl font-bold text-blue-600"><?php echo $sales_stats['total_sales']; ?></p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-green-800">Total Revenue</h2>
            <p class="text-2xl font-bold text-green-600">₹<?php echo number_format($sales_stats['total_revenue'], 2); ?></p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-yellow-800">Pending Sales</h2>
            <p class="text-2xl font-bold text-yellow-600"><?php echo $sales_stats['pending_sales']; ?></p>
        </div>
        <div class="bg-purple-100 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-purple-800">Completed Sales</h2>
            <p class="text-2xl font-bold text-purple-600"><?php echo $sales_stats['completed_sales']; ?></p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h2 class="text-lg font-semibold mb-4">Monthly Sales Trend</h2>
            <canvas id="monthlySalesChart"></canvas>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Top Selling Products</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border-b">Product</th>
                            <th class="px-4 py-2 border-b">SKU</th>
                            <th class="px-4 py-2 border-b">Quantity Sold</th>
                            <th class="px-4 py-2 border-b">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($product['sku']); ?></td>
                            <td class="px-4 py-2 border-b text-center"><?php echo $product['total_quantity']; ?></td>
                            <td class="px-4 py-2 border-b text-right">₹<?php echo number_format($product['total_revenue'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = <?php echo json_encode($monthly_sales->fetch_all(MYSQLI_ASSOC)); ?>;
    const labels = monthlyData.map(item => item.month);
    const data = monthlyData.map(item => item.total);
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Sales Revenue',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php require_once '../includes/footer.php'; ?> 