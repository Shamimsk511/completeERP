## Laravel 12 With AdminLTE3
php artisan admin:create
to create super admin


CREATE INDEX idx_challans_invoice_id_status ON challans(invoice_id, status);
CREATE INDEX idx_challan_items_invoice_item_id ON challan_items(invoice_item_id);