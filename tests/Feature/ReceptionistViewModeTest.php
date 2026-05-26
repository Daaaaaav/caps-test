<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Pages\Receptionist\RoomApproval;
use App\Livewire\Pages\Receptionist\BookingsApproval;
use App\Livewire\Pages\Receptionist\DocPackStatus;
use App\Livewire\Pages\Receptionist\Vehiclestatus;
use App\Livewire\Pages\Receptionist\BookingHistory;
use App\Livewire\Pages\Receptionist\DocPackHistory;
use App\Livewire\Pages\Receptionist\GuestbookHistory;
use App\Livewire\Pages\Receptionist\Vehicleshistory;
use App\Models\User;

class ReceptionistViewModeTest extends TestCase
{
    /**
     * Test that all receptionist pages possess the HasViewMode trait and default to 'card'.
     */
    public function test_components_default_to_card_view(): void
    {
        $components = [
            RoomApproval::class,
            BookingsApproval::class,
            DocPackStatus::class,
            Vehiclestatus::class,
            BookingHistory::class,
            DocPackHistory::class,
            GuestbookHistory::class,
            Vehicleshistory::class,
        ];

        foreach ($components as $component) {
            Livewire::test($component)
                ->assertSet('viewMode', 'card');
        }
    }

    /**
     * Test that calling setViewMode updates the view mode property and persists it in the session.
     */
    public function test_setting_view_mode_persists_in_session(): void
    {
        // Set mode to 'table' on one component
        Livewire::test(RoomApproval::class)
            ->call('setViewMode', 'table')
            ->assertSet('viewMode', 'table');

        $this->assertEquals('table', session('viewMode'));

        // Check if other components now pick it up on mount
        $components = [
            BookingsApproval::class,
            DocPackStatus::class,
            Vehiclestatus::class,
            BookingHistory::class,
            DocPackHistory::class,
            GuestbookHistory::class,
            Vehicleshistory::class,
        ];

        foreach ($components as $component) {
            Livewire::test($component)
                ->assertSet('viewMode', 'table');
        }

        // Toggle back to 'card'
        Livewire::test(GuestbookHistory::class)
            ->call('setViewMode', 'card')
            ->assertSet('viewMode', 'card');

        $this->assertEquals('card', session('viewMode'));
    }

    /**
     * Test that dynamic pagination properties adjust based on view mode (6 for card, 10 for table).
     */
    public function test_dynamic_pagination_per_page_limits(): void
    {
        // 1. Check card view limits
        Livewire::test(Vehiclestatus::class)
            ->call('setViewMode', 'card')
            ->assertSet('perPage', 6);

        Livewire::test(BookingHistory::class)
            ->call('setViewMode', 'card')
            ->assertSet('perDone', 6)
            ->assertSet('perRejected', 6);

        Livewire::test(RoomApproval::class)
            ->call('setViewMode', 'card')
            ->assertSet('perPending', 6)
            ->assertSet('perOngoing', 6);

        // 2. Check table view limits
        Livewire::test(Vehiclestatus::class)
            ->call('setViewMode', 'table')
            ->assertSet('perPage', 10);

        Livewire::test(BookingHistory::class)
            ->call('setViewMode', 'table')
            ->assertSet('perDone', 10)
            ->assertSet('perRejected', 10);

        Livewire::test(RoomApproval::class)
            ->call('setViewMode', 'table')
            ->assertSet('perPending', 10)
            ->assertSet('perOngoing', 10);
    }
}
