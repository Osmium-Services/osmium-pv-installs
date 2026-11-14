SELECT id, name, phone, email, address, postcode, install_address, install_postcode, is_commercial,
       install_type, install_req, mount_req, lead_source, staff_name, created_at
FROM {TABLE}
WHERE deleted_at IS NULL
ORDER BY created_at DESC, id DESC
