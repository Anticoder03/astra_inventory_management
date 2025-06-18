-- Insert dummy categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and components'),
('Office Supplies', 'Stationery and office materials'),
('Furniture', 'Office furniture and fixtures'),
('IT Equipment', 'Computers, servers, and networking equipment'),
('Cleaning Supplies', 'Cleaning and maintenance products');

-- Insert dummy suppliers
INSERT INTO suppliers (name, contact_person, email, phone, address, status) VALUES
('Tech Solutions Inc.', 'John Smith', 'john@techsolutions.com', '555-0101', '123 Tech Street, Silicon Valley, CA', 'active'),
('Office Depot', 'Sarah Johnson', 'sarah@officedepot.com', '555-0102', '456 Office Ave, New York, NY', 'active'),
('Furniture World', 'Mike Brown', 'mike@furnitureworld.com', '555-0103', '789 Design Blvd, Chicago, IL', 'active'),
('IT Supplies Co.', 'Lisa Chen', 'lisa@itsupplies.com', '555-0104', '321 Hardware Lane, Seattle, WA', 'active'),
('Clean Pro', 'David Wilson', 'david@cleanpro.com', '555-0105', '654 Hygiene Road, Miami, FL', 'active'),
('Global Electronics', 'Emma Davis', 'emma@globalelectronics.com', '555-0106', '987 Circuit Court, Austin, TX', 'inactive');

-- Insert dummy products
INSERT INTO products (name, description, category_id, supplier_id, sku, price, stock_quantity, reorder_level) VALUES
-- Electronics
('Laptop Pro X1', 'High-performance business laptop', 1, 1, 'ELEC-LAP-001', 1299.99, 15, 5),
('Wireless Mouse', 'Ergonomic wireless mouse', 1, 1, 'ELEC-MOU-001', 29.99, 50, 10),
('4K Monitor', '27-inch 4K display', 1, 1, 'ELEC-MON-001', 399.99, 20, 5),

-- Office Supplies
('Premium Paper', 'A4 size, 500 sheets', 2, 2, 'OFF-PAP-001', 9.99, 100, 20),
('Ballpoint Pens', 'Pack of 12 blue pens', 2, 2, 'OFF-PEN-001', 4.99, 200, 50),
('Sticky Notes', 'Pack of 100 notes', 2, 2, 'OFF-NOT-001', 2.99, 150, 30),

-- Furniture
('Office Chair', 'Ergonomic mesh chair', 3, 3, 'FUR-CHA-001', 199.99, 25, 5),
('Desk', 'Standing desk with storage', 3, 3, 'FUR-DES-001', 299.99, 15, 3),
('Bookshelf', '5-shelf storage unit', 3, 3, 'FUR-SHE-001', 149.99, 20, 5),

-- IT Equipment
('Network Switch', '24-port gigabit switch', 4, 4, 'IT-SWI-001', 199.99, 10, 3),
('Server Rack', '42U server rack cabinet', 4, 4, 'IT-RAC-001', 899.99, 5, 2),
('UPS', '1500VA uninterruptible power supply', 4, 4, 'IT-UPS-001', 299.99, 15, 5),

-- Cleaning Supplies
('All-Purpose Cleaner', '1-gallon cleaner', 5, 5, 'CLE-CLE-001', 12.99, 30, 10),
('Paper Towels', 'Pack of 12 rolls', 5, 5, 'CLE-TOW-001', 8.99, 40, 15),
('Trash Bags', 'Pack of 100 bags', 5, 5, 'CLE-BAG-001', 14.99, 25, 8);

-- Insert dummy inventory transactions
INSERT INTO inventory_transactions (product_id, transaction_type, quantity, notes) VALUES
-- Stock In transactions
(1, 'in', 20, 'Initial stock'),
(2, 'in', 100, 'Bulk order'),
(3, 'in', 30, 'New shipment'),
(4, 'in', 200, 'Regular restock'),
(5, 'in', 300, 'Back to school order'),
(6, 'in', 200, 'Office supplies restock'),
(7, 'in', 40, 'Furniture delivery'),
(8, 'in', 25, 'New office setup'),
(9, 'in', 30, 'Storage solution order'),
(10, 'in', 15, 'Network upgrade'),
(11, 'in', 8, 'Data center expansion'),
(12, 'in', 20, 'Power backup systems'),
(13, 'in', 50, 'Cleaning supplies restock'),
(14, 'in', 60, 'Paper products order'),
(15, 'in', 40, 'Waste management supplies'),

-- Stock Out transactions
(1, 'out', 5, 'Regular usage'),
(2, 'out', 50, 'Department distribution'),
(3, 'out', 10, 'Office setup'),
(4, 'out', 100, 'Regular usage'),
(5, 'out', 100, 'Department distribution'),
(6, 'out', 50, 'Office usage'),
(7, 'out', 15, 'New employee setup'),
(8, 'out', 10, 'Office renovation'),
(9, 'out', 10, 'Storage reorganization'),
(10, 'out', 5, 'Network maintenance'),
(11, 'out', 3, 'Data center upgrade'),
(12, 'out', 5, 'UPS replacement'),
(13, 'out', 20, 'Regular cleaning'),
(14, 'out', 20, 'Office usage'),
(15, 'out', 15, 'Regular waste management');

-- Insert additional users
INSERT INTO users (username, password, email, role) VALUES
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@example.com', 'admin'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com', 'user'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@example.com', 'user'); 

-- Insert dummy purchases
INSERT INTO purchases (supplier_id, purchase_date, total_amount, status, notes) VALUES
(1, '2024-03-01', 3999.97, 'completed', 'Regular IT equipment order'),
(2, '2024-03-05', 299.97, 'completed', 'Office supplies restock'),
(3, '2024-03-10', 899.97, 'pending', 'New office furniture order'),
(4, '2024-03-12', 1399.95, 'completed', 'Network equipment upgrade'),
(5, '2024-03-14', 179.85, 'pending', 'Cleaning supplies order'),
(1, '2024-03-15', 2599.98, 'cancelled', 'Cancelled due to budget constraints');

-- Insert purchase items
INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, total_price) VALUES
-- Purchase 1 (Tech Solutions Inc.)
(1, 1, 2, 1299.99, 2599.98), -- 2 Laptops
(1, 2, 10, 29.99, 299.90),   -- 10 Wireless Mice
(1, 3, 2, 399.99, 799.99),   -- 2 4K Monitors

-- Purchase 2 (Office Depot)
(2, 4, 20, 9.99, 199.80),    -- 20 Premium Paper
(2, 5, 10, 4.99, 49.90),     -- 10 Ballpoint Pen packs
(2, 6, 15, 2.99, 44.85),     -- 15 Sticky Note packs

-- Purchase 3 (Furniture World)
(3, 7, 3, 199.99, 599.97),   -- 3 Office Chairs
(3, 8, 1, 299.99, 299.99),   -- 1 Desk

-- Purchase 4 (IT Supplies Co.)
(4, 10, 5, 199.99, 999.95),  -- 5 Network Switches
(4, 12, 1, 299.99, 299.99),  -- 1 UPS
(4, 11, 1, 899.99, 899.99),  -- 1 Server Rack

-- Purchase 5 (Clean Pro)
(5, 13, 5, 12.99, 64.95),    -- 5 All-Purpose Cleaners
(5, 14, 8, 8.99, 71.92),     -- 8 Paper Towel packs
(5, 15, 3, 14.99, 44.97),    -- 3 Trash Bag packs

-- Purchase 6 (Tech Solutions Inc. - Cancelled)
(6, 1, 2, 1299.99, 2599.98); -- 2 Laptops (Cancelled order)

-- Insert dummy sales
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status, notes) VALUES
('2024-03-01', 'ABC Corporation', 'contact@abccorp.com', '555-0201', 1799.97, 'bank_transfer', 'completed', 'Regular office equipment order'),
('2024-03-05', 'XYZ Ltd', 'sales@xyzltd.com', '555-0202', 449.97, 'credit_card', 'completed', 'IT supplies for new office'),
('2024-03-10', 'Tech Solutions', 'info@techsolutions.com', '555-0203', 899.97, 'bank_transfer', 'pending', 'Pending approval'),
('2024-03-12', 'Office Plus', 'orders@officeplus.com', '555-0204', 299.97, 'credit_card', 'completed', 'Office supplies order'),
('2024-03-14', 'Global Services', 'contact@globalservices.com', '555-0205', 1599.98, 'cash', 'completed', 'Bulk order for multiple departments'),
('2024-03-15', 'Startup Inc', 'hello@startupinc.com', '555-0206', 799.98, 'credit_card', 'cancelled', 'Cancelled due to budget issues');

-- Insert sale items
INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES
-- Sale 1 (ABC Corporation)
(1, 1, 1, 1299.99, 1299.99), -- 1 Laptop
(1, 2, 5, 29.99, 149.95),    -- 5 Wireless Mice
(1, 3, 1, 399.99, 399.99),   -- 1 4K Monitor

-- Sale 2 (XYZ Ltd)
(2, 10, 2, 199.99, 399.98),  -- 2 Network Switches
(2, 12, 1, 299.99, 299.99),  -- 1 UPS

-- Sale 3 (Tech Solutions)
(3, 7, 3, 199.99, 599.97),   -- 3 Office Chairs
(3, 8, 1, 299.99, 299.99),   -- 1 Desk

-- Sale 4 (Office Plus)
(4, 4, 20, 9.99, 199.80),    -- 20 Premium Paper
(4, 5, 10, 4.99, 49.90),     -- 10 Ballpoint Pen packs
(4, 6, 15, 2.99, 44.85),     -- 15 Sticky Note packs

-- Sale 5 (Global Services)
(5, 1, 1, 1299.99, 1299.99), -- 1 Laptop
(5, 3, 1, 399.99, 399.99),   -- 1 4K Monitor

-- Sale 6 (Startup Inc - Cancelled)
(6, 1, 1, 1299.99, 1299.99); -- 1 Laptop (Cancelled order)

-- add pueches records here