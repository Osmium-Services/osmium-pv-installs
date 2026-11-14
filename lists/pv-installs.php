<?php

declare(strict_types=1);

use Osmium\Modules\Admin\Core\ListView;

/**
 * Solar PV installation requests, rendered by Osmium\Modules\Admin\Core\ListView.
 * People who asked for more information about a PV installation. A delete moves a request to the Deleted list below,
 * where an editor can restore it and only an admin can delete it for good.
 */
return [
    'leads' => [
        'id' => 'pv-installs',
        'title' => 'PV Installation Requests',
        'icon' => 'bx-sun',
        'noun' => ['request', 'requests'],
        'order' => null,
        'endpoint' => 'pv-installs/action/',
        'emptyText' => 'No PV installation requests',
        'columns' => [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'fill' => true],
            [
                'key' => 'email',
                'label' => 'Contact',
                'type' => 'html',
                'nowrap' => true,
                'render' => function (array $row, array $context): string {
                    $email = \htmlspecialchars((string) ($row['email'] ?? ''));
                    $phone = \htmlspecialchars((string) ($row['phone'] ?? ''));

                    return '<a href="mailto:' . $email . '">' . $email . '</a><br><small class="text-muted">' . $phone . '</small>';
                },
            ],
            [
                'key' => 'postcode',
                'label' => 'Location',
                'type' => 'html',
                'render' => function (array $row, array $context): string {
                    $address = \htmlspecialchars(\trim(\preg_replace('/\s+/', ' ', (string) ($row['address'] ?? ''))));
                    $postcode = \htmlspecialchars((string) ($row['postcode'] ?? ''));
                    $installPostcode = \trim((string) ($row['install_postcode'] ?? ''));
                    $installAt = $installPostcode !== '' ? '<br><small class="text-muted">Install at ' . \htmlspecialchars($installPostcode) . '</small>' : '';

                    return $address . '<br><small>' . $postcode . '</small>' . $installAt;
                },
            ],
            [
                'key' => 'install_type',
                'label' => 'Type',
                'type' => 'html',
                'nowrap' => true,
                'render' => function (array $row, array $context): string {
                    $type = \htmlspecialchars((string) ($row['install_type'] ?? ''));
                    $isCommercial = (int) ($row['is_commercial'] ?? 0) === 1 || \strtolower((string) ($row['install_type'] ?? '')) === 'commercial';
                    $badge = $isCommercial ? 'bg-label-info' : 'bg-label-success';

                    return '<span class="badge ' . $badge . '">' . ($type !== '' ? $type : 'Domestic') . '</span>';
                },
            ],
            [
                'key' => 'install_req',
                'label' => 'Requirements',
                'type' => 'html',
                'render' => function (array $row, array $context): string {
                    $requirement = \htmlspecialchars((string) ($row['install_req'] ?? ''));
                    $mount = \trim((string) ($row['mount_req'] ?? ''));
                    $mountLine = $mount !== '' ? '<br><small class="text-muted">' . \htmlspecialchars($mount) . '</small>' : '';

                    return ($requirement !== '' ? $requirement : '-') . $mountLine;
                },
            ],
            [
                'key' => 'lead_source',
                'label' => 'Source',
                'type' => 'html',
                'nowrap' => true,
                'render' => function (array $row, array $context): string {
                    $source = \htmlspecialchars((string) ($row['lead_source'] ?? '') !== '' ? (string) $row['lead_source'] : 'Website form');
                    $staff = \trim((string) ($row['staff_name'] ?? ''));
                    $staffLine = $staff !== '' ? '<br><small class="text-muted">' . \htmlspecialchars($staff) . '</small>' : '';

                    return '<small>' . $source . '</small>' . $staffLine;
                },
            ],
            [
                'key' => 'created_at',
                'label' => 'Submitted',
                'type' => 'text',
                'small' => true,
                'nowrap' => true,
                'sortKey' => 'created_at',
                'format' => fn(string $value): string => \date('j M Y, g:ia', \strtotime($value)),
            ],
        ],
        'actions' => [
            [
                'label' => 'Delete',
                'icon' => 'bx-trash',
                'action' => 'delete',
                'confirm' => 'Delete this request? It can be restored from Deleted Requests.',
                'danger' => true,
            ],
        ],
    ],

    'deleted' => ListView::deletedPreset(
        definition: [
            'id' => 'pv-installs-deleted',
            'title' => 'Deleted Requests',
            'noun' => ['request', 'requests'],
            'endpoint' => 'pv-installs/action/',
            'hideWhenEmpty' => false,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'fill' => true],
                ['key' => 'email', 'label' => 'Email', 'type' => 'text', 'nowrap' => true],
                [
                    'key' => 'created_at',
                    'label' => 'Submitted',
                    'type' => 'text',
                    'small' => true,
                    'nowrap' => true,
                    'sortKey' => 'created_at',
                    'format' => fn(string $value): string => \date('j M Y, g:ia', \strtotime($value)),
                ],
                [
                    'key' => 'deleted_at',
                    'label' => 'Deleted',
                    'type' => 'text',
                    'small' => true,
                    'nowrap' => true,
                    'sortKey' => 'deleted_at',
                    'format' => fn(string $value): string => \date('j M Y, g:ia', \strtotime($value)),
                ],
            ],
        ],
        permanentConfirm: 'Permanently delete this request? This cannot be undone!',
    ),
];
