-- Migration: Add 'cancelled' and 'error' statuses to alsendo_bulk_send_batch table
-- Execute this SQL on your PrestaShop database
-- Replace 'ps_' with your actual database prefix if different

ALTER TABLE `ps_alsendo_bulk_send_batch`
MODIFY `status` ENUM('pending', 'processing', 'completed', 'partial_error', 'cancelled', 'error')
DEFAULT 'pending';

-- Verify the change
SELECT COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'ps_alsendo_bulk_send_batch'
  AND COLUMN_NAME = 'status';
