-- Insert sample sales records for the last 12 months
-- First, ensure we have some products
INSERT INTO products (name, description, price, stock_quantity, category_id) 
VALUES 
('Premium Laptop', 'High-performance laptop with latest specs', 1299.99, 50, 1),
('Wireless Mouse', 'Ergonomic wireless mouse', 49.99, 100, 1),
('Mechanical Keyboard', 'RGB mechanical gaming keyboard', 129.99, 75, 1),
('4K Monitor', '27-inch 4K UHD display', 399.99, 30, 1),
('Gaming Headset', '7.1 surround sound gaming headset', 89.99, 60, 1)
ON DUPLICATE KEY UPDATE id=id;

-- Insert sales records for the last 12 months
-- January 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-01-15', 'John Smith', 'john@example.com', '555-0101', 1299.99, 'credit_card', 'completed'),
('2024-01-20', 'Sarah Johnson', 'sarah@example.com', '555-0102', 89.99, 'cash', 'completed'),
('2024-01-25', 'Mike Brown', 'mike@example.com', '555-0103', 399.99, 'credit_card', 'completed');

-- February 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-02-05', 'Lisa Davis', 'lisa@example.com', '555-0104', 129.99, 'cash', 'completed'),
('2024-02-15', 'Tom Wilson', 'tom@example.com', '555-0105', 1299.99, 'credit_card', 'completed'),
('2024-02-28', 'John Smith', 'john@example.com', '555-0101', 49.99, 'cash', 'completed');

-- March 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-03-10', 'Sarah Johnson', 'sarah@example.com', '555-0102', 399.99, 'credit_card', 'completed'),
('2024-03-20', 'Mike Brown', 'mike@example.com', '555-0103', 89.99, 'cash', 'completed'),
('2024-03-25', 'Lisa Davis', 'lisa@example.com', '555-0104', 1299.99, 'credit_card', 'completed');

-- April 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-04-05', 'Tom Wilson', 'tom@example.com', '555-0105', 49.99, 'cash', 'completed'),
('2024-04-15', 'John Smith', 'john@example.com', '555-0101', 399.99, 'credit_card', 'completed'),
('2024-04-30', 'Sarah Johnson', 'sarah@example.com', '555-0102', 129.99, 'cash', 'completed');

-- May 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-05-10', 'Mike Brown', 'mike@example.com', '555-0103', 1299.99, 'credit_card', 'completed'),
('2024-05-20', 'Lisa Davis', 'lisa@example.com', '555-0104', 89.99, 'cash', 'completed'),
('2024-05-25', 'Tom Wilson', 'tom@example.com', '555-0105', 399.99, 'credit_card', 'completed');

-- June 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-06-05', 'John Smith', 'john@example.com', '555-0101', 129.99, 'cash', 'completed'),
('2024-06-15', 'Sarah Johnson', 'sarah@example.com', '555-0102', 1299.99, 'credit_card', 'completed'),
('2024-06-30', 'Mike Brown', 'mike@example.com', '555-0103', 49.99, 'cash', 'completed');

-- July 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-07-10', 'Lisa Davis', 'lisa@example.com', '555-0104', 399.99, 'credit_card', 'completed'),
('2024-07-20', 'Tom Wilson', 'tom@example.com', '555-0105', 89.99, 'cash', 'completed'),
('2024-07-25', 'John Smith', 'john@example.com', '555-0101', 1299.99, 'credit_card', 'completed');

-- August 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-08-05', 'Sarah Johnson', 'sarah@example.com', '555-0102', 49.99, 'cash', 'completed'),
('2024-08-15', 'Mike Brown', 'mike@example.com', '555-0103', 399.99, 'credit_card', 'completed'),
('2024-08-30', 'Lisa Davis', 'lisa@example.com', '555-0104', 129.99, 'cash', 'completed');

-- September 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-09-10', 'Tom Wilson', 'tom@example.com', '555-0105', 1299.99, 'credit_card', 'completed'),
('2024-09-20', 'John Smith', 'john@example.com', '555-0101', 89.99, 'cash', 'completed'),
('2024-09-25', 'Sarah Johnson', 'sarah@example.com', '555-0102', 399.99, 'credit_card', 'completed');

-- October 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-10-05', 'Mike Brown', 'mike@example.com', '555-0103', 129.99, 'cash', 'completed'),
('2024-10-15', 'Lisa Davis', 'lisa@example.com', '555-0104', 1299.99, 'credit_card', 'completed'),
('2024-10-30', 'Tom Wilson', 'tom@example.com', '555-0105', 49.99, 'cash', 'completed');

-- November 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-11-10', 'John Smith', 'john@example.com', '555-0101', 399.99, 'credit_card', 'completed'),
('2024-11-20', 'Sarah Johnson', 'sarah@example.com', '555-0102', 89.99, 'cash', 'completed'),
('2024-11-25', 'Mike Brown', 'mike@example.com', '555-0103', 1299.99, 'credit_card', 'completed');

-- December 2024
INSERT INTO sales (sale_date, customer_name, customer_email, customer_phone, total_amount, payment_method, status) VALUES
('2024-12-05', 'Lisa Davis', 'lisa@example.com', '555-0104', 49.99, 'cash', 'completed'),
('2024-12-15', 'Tom Wilson', 'tom@example.com', '555-0105', 399.99, 'credit_card', 'completed'),
('2024-12-30', 'John Smith', 'john@example.com', '555-0101', 129.99, 'cash', 'completed');

-- Insert corresponding sale items for each sale
-- This will create a relationship between sales and products
INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price)
SELECT 
    s.id,
    p.id,
    1,
    p.price,
    p.price
FROM sales s
CROSS JOIN products p
WHERE s.id > (SELECT COALESCE(MAX(id), 0) FROM sale_items)
LIMIT 100; 