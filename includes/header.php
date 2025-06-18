<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto">
            <div class="flex justify-between items-center">
                <a href="/quick_site/IMS/index.php" class="text-2xl font-bold">IMS</a>
                <ul class="flex space-x-4">
                    <li><a href="/quick_site/IMS/index.php" class="hover:text-gray-200">Dashboard</a></li>
                    <li><a href="/quick_site/IMS/products/index.php" class="hover:text-gray-200">Products</a></li>
                    <li class="relative group">
                        <a href="/quick_site/IMS/inventory/index.php" class="hover:text-gray-200">Stock Management</a>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/quick_site/IMS/inventory/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Stock List</a>
                            <a href="/quick_site/IMS/inventory/stock_in.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Stock In</a>
                            <a href="/quick_site/IMS/inventory/stock_out.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Stock Out</a>
                        </div>
                    </li>
                    <li><a href="/quick_site/IMS/suppliers/index.php" class="hover:text-gray-200">Suppliers</a></li>
                    <li><a href="/quick_site/IMS/reports/index.php" class="hover:text-gray-200">Reports</a></li>
                    <li><a href="/quick_site/IMS/purchases/index.php" class="hover:text-gray-200">Purchases</a></li>
                    <li><a href="/quick_site/IMS/sales/index.php" class="hover:text-gray-200">Sales</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mx-auto p-4"> 