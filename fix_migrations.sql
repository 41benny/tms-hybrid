-- Mark duplicate create migrations as already ran
INSERT INTO migrations (migration, batch) VALUES
('2025_11_16_100001_create_invoices_table', 22),
('2025_11_16_100002_create_invoice_items_table', 22),
('2025_11_16_100003_create_payment_receipts_table', 22),
('2025_11_16_100004_create_invoice_payments_table', 22);
