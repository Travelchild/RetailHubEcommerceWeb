-- Run once on existing databases (schema.sql includes these columns for new installs)
USE retail_system;

ALTER TABLE orders
    ADD COLUMN payment_gateway VARCHAR(50) NOT NULL DEFAULT 'cod' AFTER payment_method,
    ADD COLUMN payment_status VARCHAR(40) NOT NULL DEFAULT 'Pending' AFTER payment_gateway,
    ADD COLUMN payment_transaction_id VARCHAR(120) NULL DEFAULT NULL AFTER payment_status;
