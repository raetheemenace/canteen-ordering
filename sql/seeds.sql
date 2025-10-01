-- sql/seeds.sql
-- SEED DATA FOR CANTEEN ORDERING SYSTEM

USE canteen_db;

-- 1) Admin User
-- Password: AdminPass123!
-- Generate hash via PHP: php -r "echo password_hash('AdminPass123!', PASSWORD_DEFAULT) . PHP_EOL;"
INSERT INTO users (role, username, email, password_hash, full_name, status)
VALUES (
  'admin',
  'admin',
  'admin@tip.edu.ph',
  '$2y$10$0MZwYfYvToLQmfNZTh82eugtaItuXpmPbhxx9PRaOZVYVnpLxxRre', -- hash for AdminPass123!
  'Canteen Administrator',
  'active'
);

-- 2) Categories
INSERT INTO categories (name, description) VALUES
('Meals', 'Main meal options'),
('Drinks', 'Beverages and refreshments'),
('Desserts', 'Sweet treats and pastries');

-- 3) Menu Items
INSERT INTO menu_items (category_id, name, description, price, image_path, is_active)
VALUES
(1, 'Burger Meal', 'Classic beef burger with fries', 85.00, 'assets/images/burger.jpg', 1),
(1, 'Chicken Meal', 'Fried chicken with rice', 95.00, 'assets/images/chicken.jpg', 1),
(2, 'Iced Tea', 'Refreshing iced tea', 25.00, 'assets/images/icedtea.jpg', 1),
(2, 'Bottled Water', 'Mineral water 500ml', 20.00, 'assets/images/water.jpg', 1),
(3, 'Brownie', 'Chocolate brownie square', 35.00, 'assets/images/brownie.jpg', 1),
(3, 'Cupcake', 'Vanilla cupcake with icing', 40.00, 'assets/images/cupcake.jpg', 1);

-- 4) Inventory (sample stock)
INSERT INTO inventory (menu_item_id, stock, unit, threshold)
VALUES
(1, 50, 'pcs', 10),
(2, 40, 'pcs', 10),
(3, 100, 'cups', 20),
(4, 200, 'bottles', 30),
(5, 60, 'pcs', 10),
(6, 70, 'pcs', 10);
