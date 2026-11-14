-- Adds deleted_at to the site's pvinstall_leads table so a delete can be undone. The table belongs to the site, not
-- this service, so on a site without it (or already carrying the column) this does nothing.
SET @pv_sql = IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '{PREFIX}pvinstall_leads') > 0
    AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{PREFIX}pvinstall_leads' AND column_name = 'deleted_at') = 0,
    'ALTER TABLE {PREFIX}pvinstall_leads ADD COLUMN deleted_at DATETIME DEFAULT NULL, ADD KEY idx_pvinstall_leads_deleted (deleted_at)',
    'SELECT 1'
);
PREPARE pv_stmt FROM @pv_sql;
EXECUTE pv_stmt;
DEALLOCATE PREPARE pv_stmt;
