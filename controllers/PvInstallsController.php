<?php

declare(strict_types=1);

namespace Osmium\Services\PvInstalls\Controllers;

use Osmium\Modules\Admin\Core\AdminController;
use Osmium\Services\PvInstalls\Models\PvInstallLead;

/**
 * PV installs admin: the installation requests the site's enquiry form has saved. A delete moves a request to the Deleted list, where it can be restored and where only an admin can delete it for good.
 *
 * Routes:
 *   - index() → /admin/pv-installs/
 *   - action() → /admin/pv-installs/action/ (JSON: delete, restore, permanently_delete)
 */
class PvInstallsController extends AdminController
{
    public function index(): void
    {
        $leads = new PvInstallLead($this->osmium->dataSource);
        $isReady = $leads->isReady();

        $this->data['admin']['lists'] = require __DIR__ . '/../lists/pv-installs.php';
        $this->data['admin']['isReady'] = $isReady;
        $this->data['admin']['pvInstalls'] = $isReady ? $leads->listAll() : [];
        $this->data['admin']['deletedPvInstalls'] = $isReady ? $leads->listDeleted() : [];
        $this->data['admin']['isAdmin'] = $this->isAdmin();

        $this->setView('pv-installs/index.phtml');
    }

    public function action(): void
    {
        \header('Content-Type: application/json');

        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        if (!$isPost) $this->admin->jsonError('Method not allowed');

        $input = $this->admin->auth->getJsonInput();
        $csrfValid = $this->admin->auth->validateCsrfJson($input);
        if (!$csrfValid) $this->admin->jsonError('Invalid request token. Please refresh and try again.');

        $action = $input['action'] ?? '';
        $id = (int) ($input['id'] ?? 0);

        try {
            $result = match ($action) {
                'delete' => $this->deleteLead($id),
                'restore' => $this->restoreLead($id),
                'permanently_delete' => $this->permanentlyDeleteLead($id),
                default => throw new \InvalidArgumentException('Unknown action'),
            };

            $this->admin->jsonSuccess($result);
        } catch (\Exception $e) {
            $this->admin->jsonError($e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    private function deleteLead(int $id): array
    {
        $leads = new PvInstallLead($this->osmium->dataSource);
        $lead = $leads->find($id);
        $isLive = $lead !== null && $lead['deleted_at'] === null;
        if (!$isLive) throw new \InvalidArgumentException('Request not found');

        $leads->softDelete($id);
        $this->logChange(action: 'Deleted', id: $id, name: (string) $lead['name']);

        return ['success' => true];
    }

    /** @return array<string, mixed> */
    private function restoreLead(int $id): array
    {
        $leads = new PvInstallLead($this->osmium->dataSource);
        $lead = $leads->find($id);
        $isDeleted = $lead !== null && $lead['deleted_at'] !== null;
        if (!$isDeleted) throw new \InvalidArgumentException('Deleted request not found');

        $leads->restore($id);
        $this->logChange(action: 'Restored', id: $id, name: (string) $lead['name']);

        return ['success' => true];
    }

    /** Admins only, and only from the Deleted list
     *
     * @return array<string, mixed>
     */
    private function permanentlyDeleteLead(int $id): array
    {
        $notAdmin = !$this->isAdmin();
        if ($notAdmin) throw new \RuntimeException('Permission denied');

        $leads = new PvInstallLead($this->osmium->dataSource);
        $lead = $leads->find($id);
        $isDeleted = $lead !== null && $lead['deleted_at'] !== null;
        if (!$isDeleted) throw new \InvalidArgumentException('Deleted request not found');

        $leads->delete($id);
        $this->logChange(action: 'Permanently deleted', id: $id, name: (string) $lead['name']);

        return ['success' => true];
    }

    private function logChange(string $action, int $id, string $name): void
    {
        $this->admin->model->changelog->log(
            description: $action . ': PV install request from ' . $name,
            recordType: 'pv-installs',
            recordId: $id,
        );
    }
}
