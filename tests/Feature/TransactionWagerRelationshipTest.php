<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionWagerRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_wager_relationship_eager_loads_correctly(): void
    {
        // Arrange
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $wager = Wager::factory()->create(['group_id' => $group->id, 'creator_id' => $user->id]);
        $entry = WagerEntry::factory()->create([
            'wager_id' => $wager->id,
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => 'wager_placed',
            'amount' => -100,
            'balance_before' => 1000,
            'balance_after' => 900,
            'transactionable_type' => WagerEntry::class,
            'transactionable_id' => $entry->id,
        ]);

        // Act - Test eager loading through transactionable relationship
        $loadedTransaction = Transaction::with(['transactionable.wager:id,title'])
            ->where('id', $transaction->id)
            ->first();

        // Assert
        $this->assertNotNull($loadedTransaction);
        $this->assertNotNull($loadedTransaction->wager);
        $this->assertEquals($wager->id, $loadedTransaction->wager->id);
        $this->assertEquals($wager->title, $loadedTransaction->wager->title);
    }

    public function test_transaction_wager_relationship_returns_null_for_non_wager_transactions(): void
    {
        // Arrange
        $group = Group::factory()->create();
        $user = User::factory()->create();

        // Create a transaction without a wager (e.g., a drop or other type)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => 'drop',
            'amount' => 100,
            'balance_before' => 900,
            'balance_after' => 1000,
            'transactionable_type' => null,
            'transactionable_id' => null,
        ]);

        // Act
        $loadedTransaction = Transaction::find($transaction->id);

        // Assert
        $this->assertNotNull($loadedTransaction);
        $this->assertNull($loadedTransaction->wager);
    }

    public function test_transaction_wager_direct_access_works(): void
    {
        // Arrange
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $wager = Wager::factory()->create(['group_id' => $group->id, 'creator_id' => $user->id]);
        $entry = WagerEntry::factory()->create([
            'wager_id' => $wager->id,
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => 'wager_placed',
            'amount' => -100,
            'balance_before' => 1000,
            'balance_after' => 900,
            'transactionable_type' => WagerEntry::class,
            'transactionable_id' => $entry->id,
        ]);

        // Act - Direct access without eager loading
        $freshTransaction = Transaction::find($transaction->id);
        $wagerResult = $freshTransaction->wager;

        // Assert
        $this->assertNotNull($wagerResult);
        $this->assertEquals($wager->id, $wagerResult->id);
    }
}
