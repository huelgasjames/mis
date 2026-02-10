-- Sample INSERTs for departments
INSERT INTO departments (name, office_location, created_at, updated_at) VALUES
('IT', 'Building A', NOW(), NOW()),
('HR', 'Building B', NOW(), NOW()),
('Finance', 'Building C', NOW(), NOW()),
('Administration', 'Main Office', NOW(), NOW());

-- Sample INSERTs for users (passwords should be hashed; prefer using seeders to hash correctly)
-- Example: set password to 'password' after hashing via Laravel seeders
-- INSERT INTO users (name, email, password, created_at, updated_at) VALUES
-- ('Admin User', 'admin@example.com', '$2y$10$REPLACE_WITH_BCRYPT_HASH', NOW(), NOW());

-- Sample INSERTs for computers referencing department ids by name
INSERT INTO computers (asset_tag, computer_name, processor, ram, storage, serial_number, status, department_id, created_at, updated_at)
VALUES
('ASSET-1001', 'IT-Workstation-01', 'Intel Core i5-10400', '16GB', '512GB SSD', 'SN1001', 'Working', (SELECT id FROM departments WHERE name='IT' LIMIT 1), NOW(), NOW()),
('ASSET-1002', 'HR-Laptop-01', 'Intel Core i3-10110U', '8GB', '256GB SSD', 'SN1002', 'Working', (SELECT id FROM departments WHERE name='HR' LIMIT 1), NOW(), NOW()),
('ASSET-1003', 'FIN-Desktop-01', 'AMD Ryzen 5 3600', '16GB', '1TB HDD', 'SN1003', 'Defective', (SELECT id FROM departments WHERE name='Finance' LIMIT 1), NOW(), NOW());

-- Note: For users, use the Laravel seeders to ensure password hashing and model events run correctly.
