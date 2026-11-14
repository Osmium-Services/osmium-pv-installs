<?php

declare(strict_types=1);

namespace Tests\Services\PvInstalls;

use PHPUnit\Framework\TestCase;

require_once 'app/core/admin/core/ListView.php';

/**
 * How the PV installs list shows a saved request: contact and location cells escape what visitors typed,
 * and the type badge follows the commercial flag as well as the type text.
 */
final class PvInstallsListTest extends TestCase
{
    /** @return array<string, array<string, mixed>> column definitions by key */
    private function columns(): array
    {
        $list = require __DIR__ . '/../lists/pv-installs.php';
        $byKey = [];
        foreach ($list['leads']['columns'] as $column) {
            $byKey[$column['key']] = $column;
        }

        return $byKey;
    }

    public function test_a_delete_moves_to_the_deleted_list_where_only_an_admin_can_delete_for_good(): void
    {
        $list = require __DIR__ . '/../lists/pv-installs.php';

        $delete = $list['leads']['actions'][0];
        $this->assertSame('delete', $delete['action']);
        $this->assertStringContainsString('restored', $delete['confirm']);

        $byAction = [];
        foreach ($list['deleted']['actions'] as $action) {
            $byAction[$action['action']] = $action;
        }
        $this->assertArrayNotHasKey('visible', $byAction['restore']); // Editors can restore
        $this->assertFalse($byAction['permanently_delete']['visible']([], ['isAdmin' => false]));
        $this->assertTrue($byAction['permanently_delete']['visible']([], ['isAdmin' => true]));
    }

    public function test_contact_cell_links_the_email_and_escapes_everything(): void
    {
        $cell = $this->columns()['email']['render'](['email' => 'a"b@example.com', 'phone' => '<b>01234</b>'], []);

        $this->assertStringContainsString('href="mailto:a&quot;b@example.com"', $cell);
        $this->assertStringContainsString('&lt;b&gt;01234&lt;/b&gt;', $cell);
        $this->assertStringNotContainsString('<b>', $cell);
    }

    public function test_location_cell_collapses_the_address_and_mentions_a_different_install_postcode(): void
    {
        $render = $this->columns()['postcode']['render'];

        $cell = $render(['address' => "1 High St\r\nTown", 'postcode' => 'GL1 1AA', 'install_postcode' => 'GL2 2BB'], []);
        $this->assertStringContainsString('1 High St Town', $cell);
        $this->assertStringContainsString('Install at GL2 2BB', $cell);

        $same = $render(['address' => '1 High St', 'postcode' => 'GL1 1AA', 'install_postcode' => ''], []);
        $this->assertStringNotContainsString('Install at', $same);
    }

    public function test_type_badge_is_commercial_by_flag_or_by_text_and_domestic_when_empty(): void
    {
        $render = $this->columns()['install_type']['render'];

        $this->assertStringContainsString('bg-label-info', $render(['install_type' => 'Commercial', 'is_commercial' => 0], []));
        $this->assertStringContainsString('bg-label-info', $render(['install_type' => 'Farm', 'is_commercial' => 1], []));
        $this->assertStringContainsString('bg-label-success', $render(['install_type' => 'Domestic', 'is_commercial' => 0], []));
        $this->assertStringContainsString('Domestic', $render(['install_type' => '', 'is_commercial' => 0], []));
    }
}
