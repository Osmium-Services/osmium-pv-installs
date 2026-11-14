# PV Installs

Adds a **PV Installs** item to the top of the admin sidebar, listing the solar PV installation requests a site's enquiry form has saved in its `pvinstall_leads` table.

This service only lists and manages requests. It does not create the table, the public form or the notification email: those stay in the site. Without the table the page shows a notice and nothing else.

## What it does

The page lists each request, newest first, with its name, contact (email link and phone), location (address, postcode and a different install postcode), property type (a Commercial badge when flagged), requirements and mounting, source (and the staff member who entered it), and when it was submitted. The list searches, sorts and pages like the other admin lists.

- **Delete** moves a request to *Deleted Requests*, a collapsible list below. Anyone who can see the page can delete.
- **Restore**, in the Deleted list, puts a request back. Anyone who can see the page can restore.
- **Permanently Delete**, in the Deleted list, removes a request for good. Admins only: the menu item is hidden from everyone else and the server refuses them too.

Deleting, restoring and permanently deleting each add a Changelog entry.

## What the site must have

A `pvinstall_leads` table (with its table prefix) holding at least `name`, `phone`, `email`, `address`, `postcode`, `install_address`, `install_postcode`, `is_commercial`, `install_type`, `install_req`, `mount_req`, `lead_source`, `staff_name` and `created_at`. The service's own migration adds one column to it, `deleted_at`, so a delete can be undone. On a site without the table the migration does nothing and the page says there is nothing to list.

## Install

Install it from the Store (or `install-service.php osmium-pv-installs`). Needs Core 1.0.0 or later and no other services. Uninstalling removes the menu item and leaves the table, including `deleted_at`, as it is.

## Tests

`php vendor/phpunit.phar app/services/osmium-pv-installs/tests` inside the site's container.
