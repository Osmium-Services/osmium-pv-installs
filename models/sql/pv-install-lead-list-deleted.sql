SELECT id, name, phone, email, postcode, install_type, created_at, deleted_at
FROM {TABLE}
WHERE deleted_at IS NOT NULL
ORDER BY deleted_at DESC, id DESC
