<?php

namespace Tests\Unit;

use App\Models\Depot;
use Tests\TestCase;

class DepotTest extends TestCase
{
    public function test_deposit_due_today_is_not_late(): void
    {
        $this->travelTo('2026-11-05 16:00:00');
        $depot = new Depot([
            'date_retrait_prevue' => '2026-11-05',
            'statut' => 'depose',
        ]);

        $this->assertSame(false, $depot->estEnRetard());
    }

    public function test_active_deposit_due_before_today_is_late(): void
    {
        $this->travelTo('2026-11-05 16:00:00');
        $depot = new Depot([
            'date_retrait_prevue' => '2026-11-04',
            'statut' => 'en_traitement',
        ]);

        $this->assertSame(true, $depot->estEnRetard());
    }

    public function test_ready_deposit_due_before_today_is_not_late(): void
    {
        $this->travelTo('2026-11-05 16:00:00');
        $depot = new Depot([
            'date_retrait_prevue' => '2026-11-04',
            'statut' => 'pret',
        ]);

        $this->assertSame(false, $depot->estEnRetard());
    }

    public function test_deposit_without_a_due_date_is_not_late(): void
    {
        $depot = new Depot([
            'statut' => 'depose',
        ]);

        $this->assertSame(false, $depot->estEnRetard());
    }
}
