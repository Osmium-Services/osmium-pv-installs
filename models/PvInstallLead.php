<?php

declare(strict_types=1);

namespace Osmium\Services\PvInstalls\Models;

use Osmium\Core\Library\OsmiumPDO;
use Osmium\Core\Models\Model;

/**
 * Solar PV installation requests saved by a site's enquiry form (the pvinstall_leads table).
 */
class PvInstallLead extends Model
{
    protected ?string $sqlDir = __DIR__ . '/sql';

    public function __construct(OsmiumPDO $database)
    {
        parent::__construct(database: $database, tableName: 'pvinstall_leads');
    }

    /**
     * The table belongs to the site, not this service, so a site without it (or before this service's migration has
     * added deleted_at) gets a notice and not an error.
     */
    public function isReady(): bool
    {
        $found = $this->database->prepare(
            sql: 'SELECT COUNT(*) AS n FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :name AND column_name = \'deleted_at\'',
            bindings: [':name' => $this->database->tablePrefix() . 'pvinstall_leads'],
        )->single();

        return (int) ($found['n'] ?? 0) > 0;
    }

    /** @return array<int, array<string, mixed>> Every live request, newest first */
    public function listAll(): array
    {
        return $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-list.sql'), bindings: [])->resultset();
    }

    /** @return array<int, array<string, mixed>> Deleted requests, most recently deleted first */
    public function listDeleted(): array
    {
        return $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-list-deleted.sql'), bindings: [])->resultset();
    }

    /** A request whether or not it is deleted (check deleted_at), or null when there is no such request */
    public function find(int $id): ?array
    {
        $row = $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-find.sql'), bindings: [':id' => $id])->single();

        return $row ?: null;
    }

    public function softDelete(int $id): void
    {
        $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-soft-delete.sql'), bindings: [':id' => $id])->execute();
    }

    public function restore(int $id): void
    {
        $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-restore.sql'), bindings: [':id' => $id])->execute();
    }

    /** Permanent: only a request already in the bin can be removed */
    public function delete(int $id): void
    {
        $this->database->prepare(sql: $this->loadSqlFile('pv-install-lead-delete.sql'), bindings: [':id' => $id])->execute();
    }
}
