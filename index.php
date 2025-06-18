<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get total products
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];

// Get total categories
$total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

// Get total suppliers
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'];

// Get low stock products (below reorder level)
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= reorder_level")->fetch_assoc()['count'];

// Get recent stock transactions
$recent_transactions = $conn->query("
    SELECT t.*, p.name as product_name 
    FROM inventory_transactions t 
    JOIN products p ON t.product_id = p.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
");

// Get supplier statistics
$supplier_stats = $conn->query("
    SELECT 
        s.name,
        COUNT(p.id) as product_count,
        SUM(p.stock_quantity) as total_stock
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id
    ORDER BY product_count DESC
    LIMIT 5
");

// Get stock value by supplier
$stock_value = $conn->query("
    SELECT 
        s.name,
        SUM(p.stock_quantity * p.price) as total_value
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id
    ORDER BY total_value DESC
    LIMIT 5
");

// Get purchase statistics
$purchase_stats = $conn->query("
    SELECT 
        COUNT(*) as total_purchases,
        SUM(total_amount) as total_amount,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_purchases,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_purchases
    FROM purchases
")->fetch_assoc();

// Get recent purchases
$recent_purchases = $conn->query("
    SELECT p.*, s.name as supplier_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    ORDER BY p.purchase_date DESC
    LIMIT 5
");

// Get sales statistics
$sales_stats = $conn->query("
    SELECT 
        COUNT(*) as total_sales,
        SUM(total_amount) as total_revenue,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_sales,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sales,
        COUNT(CASE WHEN payment_method = 'cash' THEN 1 END) as cash_sales,
        COUNT(CASE WHEN payment_method = 'credit_card' THEN 1 END) as credit_card_sales,
        COUNT(CASE WHEN payment_method = 'bank_transfer' THEN 1 END) as bank_transfer_sales
    FROM sales
")->fetch_assoc();

// Get recent sales
$recent_sales = $conn->query("
    SELECT s.*, 
           COUNT(si.id) as item_count
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    GROUP BY s.id
    ORDER BY s.sale_date DESC
    LIMIT 5
");

// Get monthly sales data for chart
$monthly_sales = $conn->query("
    SELECT 
        DATE_FORMAT(sale_date, '%Y-%m') as month,
        SUM(total_amount) as total_amount,
        COUNT(*) as sale_count
    FROM sales
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");

// Get top selling products
$top_products = $conn->query("
    SELECT 
        p.name,
        p.sku,
        SUM(si.quantity) as total_quantity,
        SUM(si.total_price) as total_revenue
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.status = 'completed'
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Products</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $total_products; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Categories</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $total_categories; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Suppliers</h3>
        <p class="text-3xl font-bold text-purple-600"><?php echo $total_suppliers; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Low Stock Items</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $low_stock; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Transactions</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Product</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left">Quantity</th>
                        <th class="px-4 py-2 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs <?php echo $transaction['transaction_type'] === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($transaction['transaction_type']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?php echo $transaction['quantity']; ?></td>
                            <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Supplier Statistics -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Top Suppliers</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Supplier</th>
                        <th class="px-4 py-2 text-left">Products</th>
                        <th class="px-4 py-2 text-left">Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stat = $supplier_stats->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($stat['name']); ?></td>
                            <td class="px-4 py-2"><?php echo $stat['product_count']; ?></td>
                            <td class="px-4 py-2"><?php echo $stat['total_stock']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Stock Value by Supplier</h2>
        <canvas id="stockValueChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Purchase Statistics -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold mb-4">Purchase Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800">Total Purchases</h3>
            <p class="text-2xl font-bold text-blue-900"><?php echo $purchase_stats['total_purchases']; ?></p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800">Total Amount</h3>
            <p class="text-2xl font-bold text-green-900">₹<?php echo number_format($purchase_stats['total_amount'], 2); ?></p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800">Pending Purchases</h3>
            <p class="text-2xl font-bold text-yellow-900"><?php echo $purchase_stats['pending_purchases']; ?></p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-purple-800">Completed Purchases</h3>
            <p class="text-2xl font-bold text-purple-900"><?php echo $purchase_stats['completed_purchases']; ?></p>
        </div>
    </div>
</div>

<!-- Recent Purchases -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Recent Purchases</h2>
        <a href="purchases/" class="text-blue-500 hover:text-blue-600">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($purchase = $recent_purchases->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="purchases/view.php?id=<?php echo $purchase['id']; ?>" class="text-blue-500 hover:text-blue-600">
                                #<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($purchase['total_amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                echo match($purchase['status']) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                                ?>">
                                <?php echo ucfirst($purchase['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Sales Statistics -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold mb-4">Sales Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800">Total Sales</h3>
            <p class="text-2xl font-bold text-blue-900"><?php echo $sales_stats['total_sales']; ?></p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800">Total Revenue</h3>
            <p class="text-2xl font-bold text-green-900">₹<?php echo number_format($sales_stats['total_revenue'], 2); ?></p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800">Pending Sales</h3>
            <p class="text-2xl font-bold text-yellow-900"><?php echo $sales_stats['pending_sales']; ?></p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-purple-800">Completed Sales</h3>
            <p class="text-2xl font-bold text-purple-900"><?php echo $sales_stats['completed_sales']; ?></p>
        </div>
    </div>
</div>

<!-- Sales Charts -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Monthly Sales Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Monthly Sales</h2>
        <canvas id="monthlySalesChart"></canvas>
    </div>

    <!-- Payment Methods Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Payment Methods</h2>
        <canvas id="paymentMethodsChart"></canvas>
    </div>
</div>

<!-- Recent Sales -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Recent Sales</h2>
        <a href="sales/" class="text-blue-500 hover:text-blue-600">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="sales/view.php?id=<?php echo $sale['id']; ?>" class="text-blue-500 hover:text-blue-600">
                                #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($sale['total_amount'], 2); ?></td>
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
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Selling Products -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold mb-4">Top Selling Products</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($product = $top_products->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['sku']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['total_quantity']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($product['total_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Stock Value Chart
const stockValueCtx = document.getElementById('stockValueChart').getContext('2d');
const stockValueData = {
    labels: [
        <?php 
        $stock_value->data_seek(0);
        while ($value = $stock_value->fetch_assoc()) {
            echo "'" . addslashes($value['name']) . "',";
        }
        ?>
    ],
    datasets: [{
        label: 'Stock Value ($)',
        data: [
            <?php 
            $stock_value->data_seek(0);
            while ($value = $stock_value->fetch_assoc()) {
                echo $value['total_value'] . ",";
            }
            ?>
        ],
        backgroundColor: 'rgba(59, 130, 246, 0.5)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1
    }]
};

new Chart(stockValueCtx, {
    type: 'bar',
    data: stockValueData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Monthly Sales Chart
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesData = {
    labels: <?php 
        $months = [];
        $amounts = [];
        $monthly_sales->data_seek(0);
        while ($row = $monthly_sales->fetch_assoc()) {
            $months[] = date('M Y', strtotime($row['month'] . '-01'));
            $amounts[] = $row['total_amount'];
        }
        echo json_encode(array_reverse($months));
    ?>,
    datasets: [{
        label: 'Monthly Sales',
        data: <?php echo json_encode(array_reverse($amounts)); ?>,
        backgroundColor: 'rgba(59, 130, 246, 0.5)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1
    }]
};

new Chart(monthlySalesCtx, {
    type: 'line',
    data: monthlySalesData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Payment Methods Chart
const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
const paymentMethodsData = {
    labels: ['Cash', 'Credit Card', 'Bank Transfer'],
    datasets: [{
        data: [
            <?php echo $sales_stats['cash_sales']; ?>,
            <?php echo $sales_stats['credit_card_sales']; ?>,
            <?php echo $sales_stats['bank_transfer_sales']; ?>
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.5)',
            'rgba(59, 130, 246, 0.5)',
            'rgba(168, 85, 247, 0.5)'
        ],
        borderColor: [
            'rgb(34, 197, 94)',
            'rgb(59, 130, 246)',
            'rgb(168, 85, 247)'
        ],
        borderWidth: 1
    }]
};

new Chart(paymentMethodsCtx, {
    type: 'pie',
    data: paymentMethodsData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 