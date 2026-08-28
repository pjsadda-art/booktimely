<?php

namespace App\Services\Inventory;

use App\Models\Inventory\OpenUnit;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\StockTransaction;
use Illuminate\Support\Facades\DB;

/**
 * The only thing in the module allowed to move stock.
 *
 * Two invariants it exists to hold:
 *
 *  1. **The ledger and the balance are written together, inside one database
 *     transaction, always.** A balance updated without its ledger row is a
 *     number nobody can explain; a ledger row without its balance update is a
 *     number nobody can trust.
 *  2. **Quantity never goes below zero.** Availability is checked against a
 *     locked balance row, so two concurrent transfers cannot both pass the check
 *     and leave stock negative.
 */
class StockService
{
    /**
     * The movement vocabulary.
     *
     * Application-level rather than a database enum on purpose: the source
     * system had to ALTER its enum twice to add new movement types, paying for
     * a schema migration every time the business invented a word. Adding one
     * here is a one-line change.
     */
    public const TRANSACTION_TYPES = [
        'opening_balance' => 'Opening balance',
        'adjustment' => 'Adjustment',
        'transfer' => 'Transfer',
        'purchase_receive' => 'Purchase receive',
        'internal_use' => 'Internal use',
        'sample' => 'Sample',
    ];

    /* --------------------------------------------------------------------- */
    /* Operations                                                            */
    /* --------------------------------------------------------------------- */

    /**
     * Set the starting quantity of a product at one place.
     */
    public function openingBalance(Product $product, string $entityType, $entityId, float $quantity, ?string $remarks = null): StockTransaction
    {
        $this->assertPositive($quantity);

        return $this->move($product, [
            'transaction_type' => 'opening_balance',
            'destination_entity_type' => $entityType,
            'destination_entity_id' => $entityId,
            'quantity' => $quantity,
            'unit_cost' => $product->cost_price,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Correct a balance up or down.
     *
     * `$delta` is signed: negative takes stock out. The reason is mandatory —
     * an unexplained adjustment is indistinguishable from a mistake six months
     * later.
     */
    public function adjust(Product $product, string $entityType, $entityId, float $delta, string $reason): StockTransaction
    {
        if ($delta == 0.0) {
            throw new \RuntimeException(__('An adjustment of zero changes nothing.'));
        }

        if (trim($reason) === '') {
            throw new \RuntimeException(__('An adjustment needs a reason.'));
        }

        $movement = [
            'transaction_type' => 'adjustment',
            'quantity' => abs($delta),
            'unit_cost' => $product->cost_price,
            'remarks' => $reason,
        ];

        if ($delta > 0) {
            $movement['destination_entity_type'] = $entityType;
            $movement['destination_entity_id'] = $entityId;
        } else {
            $movement['source_entity_type'] = $entityType;
            $movement['source_entity_id'] = $entityId;
        }

        return $this->move($product, $movement);
    }

    /**
     * Move quantity between two places.
     *
     * One ledger row, not a matched out/in pair, so the movement cannot be half
     * recorded. Availability at the source is validated first, against a locked
     * row.
     */
    public function transfer(
        Product $product,
        string $sourceType,
        $sourceId,
        string $destinationType,
        $destinationId,
        float $quantity,
        ?string $remarks = null
    ): StockTransaction {
        $this->assertPositive($quantity);

        if ($sourceType === $destinationType && (int) $sourceId === (int) $destinationId) {
            throw new \RuntimeException(__('The source and destination are the same place.'));
        }

        return $this->move($product, [
            'transaction_type' => 'transfer',
            'source_entity_type' => $sourceType,
            'source_entity_id' => $sourceId,
            'destination_entity_type' => $destinationType,
            'destination_entity_id' => $destinationId,
            'quantity' => $quantity,
            'unit_cost' => $product->cost_price,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Receive part or all of a purchase line.
     *
     * Only this step touches stock; confirming a purchase does not. Receiving
     * may be partial and may happen several times against the same line.
     */
    public function receivePurchaseLine(Purchase $purchase, $item, float $quantity): StockTransaction
    {
        $this->assertPositive($quantity);

        if ($quantity > $item->outstanding() + 0.0001) {
            throw new \RuntimeException(__('That is more than is still outstanding on this line.'));
        }

        $product = Product::find($item->product_id);

        if (empty($product)) {
            throw new \RuntimeException(__('That product no longer exists.'));
        }

        return DB::transaction(function () use ($purchase, $item, $quantity, $product) {
            $transaction = $this->move($product, [
                'transaction_type' => 'purchase_receive',
                'destination_entity_type' => $purchase->entity_type,
                'destination_entity_id' => $purchase->entity_id,
                'quantity' => $quantity,
                'unit_cost' => $item->unit_cost,
                'reference_id' => $purchase->id,
                'reference_type' => 'purchase',
                'remarks' => __('Received against :number', ['number' => $purchase->purchase_number]),
            ], false);

            $item->received_quantity = round((float) $item->received_quantity + $quantity, 4);
            $item->save();

            // The order is only "received" once every line is complete; until
            // then it stays confirmed and open for the rest of the delivery.
            if (!$purchase->fresh('items')->hasOutstanding()) {
                $purchase->status = Purchase::RECEIVED;
                $purchase->save();
            }

            return $transaction;
        });
    }

    /**
     * Open a container for salon use, or hand one out as a sample.
     *
     * Stock leaves *now*, at the moment it is opened — that is the whole point:
     * an opened bottle is no longer sellable even though it is not yet empty.
     * Closing it later records that it ran out, and moves no stock.
     */
    public function open(Product $product, string $entityType, $entityId, string $kind, float $quantity = 1, ?string $remarks = null): OpenUnit
    {
        $this->assertPositive($quantity);

        if (!in_array($kind, [OpenUnit::OPEN_UNIT, OpenUnit::SAMPLE], true)) {
            throw new \RuntimeException(__('Unknown internal-use type.'));
        }

        return DB::transaction(function () use ($product, $entityType, $entityId, $kind, $quantity, $remarks) {
            $transaction = $this->move($product, [
                'transaction_type' => $kind === OpenUnit::SAMPLE ? 'sample' : 'internal_use',
                'source_entity_type' => $entityType,
                'source_entity_id' => $entityId,
                'quantity' => $quantity,
                'unit_cost' => $product->cost_price,
                'remarks' => $remarks,
            ], false);

            $unit = new OpenUnit([
                'product_id' => $product->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'kind' => $kind,
                // A sample is gone the moment it is handed over, so it has no
                // second step; only an opened container stays open.
                'status' => $kind === OpenUnit::SAMPLE ? OpenUnit::STATUS_CLOSED : OpenUnit::STATUS_OPEN,
                'quantity' => $quantity,
                'opened_at' => now(),
                'closed_at' => $kind === OpenUnit::SAMPLE ? now() : null,
                'opened_by' => \Auth::check() ? \Auth::user()->id : null,
                'stock_transaction_id' => $transaction->id,
                'remarks' => $remarks,
            ]);

            $unit->applyTenant($product->business_id, $product->created_by)->save();

            return $unit;
        });
    }

    /**
     * Mark an opened container finished.
     *
     * Moves no stock: the unit left the balance when it was opened. This only
     * records that it is now empty.
     */
    public function close(OpenUnit $unit): void
    {
        if (!$unit->isOpen()) {
            throw new \RuntimeException(__('That container is already closed.'));
        }

        $unit->status = OpenUnit::STATUS_CLOSED;
        $unit->closed_at = now();
        $unit->closed_by = \Auth::check() ? \Auth::user()->id : null;
        $unit->save();
    }

    /* --------------------------------------------------------------------- */
    /* The single write path                                                 */
    /* --------------------------------------------------------------------- */

    /**
     * Write one ledger row and apply it to the balances.
     *
     * Everything above funnels through here, which is why the transactional
     * guarantee only has to be got right once.
     *
     * @param  array<string,mixed>  $movement
     * @param  bool  $wrap  false when the caller already opened a transaction
     */
    protected function move(Product $product, array $movement, bool $wrap = true): StockTransaction
    {
        $write = function () use ($product, $movement) {
            $type = $movement['transaction_type'];

            if (!isset(self::TRANSACTION_TYPES[$type])) {
                // The validation the database enum used to do, now where it can
                // be extended without a migration.
                throw new \RuntimeException(__('Unknown stock movement type: :type', ['type' => $type]));
            }

            $quantity = round((float) $movement['quantity'], 4);

            $sourceType = $movement['source_entity_type'] ?? null;
            $sourceId = $movement['source_entity_id'] ?? null;
            $destinationType = $movement['destination_entity_type'] ?? null;
            $destinationId = $movement['destination_entity_id'] ?? null;

            if (empty($sourceType) && empty($destinationType)) {
                throw new \RuntimeException(__('A movement needs a source, a destination, or both.'));
            }

            // Stock out first: it is the leg that can fail, and failing before
            // anything has been credited keeps the rollback trivial.
            if (!empty($sourceType)) {
                $this->applyDelta($product, $sourceType, $sourceId, -$quantity);
            }

            if (!empty($destinationType)) {
                $this->applyDelta($product, $destinationType, $destinationId, $quantity);
            }

            $transaction = new StockTransaction([
                'product_id' => $product->id,
                'transaction_type' => $type,
                'source_entity_type' => $sourceType,
                'source_entity_id' => $sourceId,
                'destination_entity_type' => $destinationType,
                'destination_entity_id' => $destinationId,
                'quantity' => $quantity,
                'unit_cost' => $movement['unit_cost'] ?? null,
                'reference_id' => $movement['reference_id'] ?? null,
                'reference_type' => $movement['reference_type'] ?? null,
                'remarks' => $movement['remarks'] ?? null,
                'performed_by' => \Auth::check() ? \Auth::user()->id : null,
            ]);

            $transaction->applyTenant($product->business_id, $product->created_by)->save();

            return $transaction;
        };

        return $wrap ? DB::transaction($write) : $write();
    }

    /**
     * Apply a signed change to one balance row, refusing to go negative.
     *
     * The row is locked for the rest of the transaction, so two concurrent
     * movements out of the same place are serialised rather than both reading
     * the same "enough stock" answer.
     */
    protected function applyDelta(Product $product, string $entityType, $entityId, float $delta): void
    {
        if (!in_array($entityType, [ProductStock::LOCATION, ProductStock::WAREHOUSE], true)) {
            throw new \RuntimeException(__('Unknown stock location type: :type', ['type' => $entityType]));
        }

        $stock = ProductStock::where('product_id', $product->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('business_id', $product->business_id)
            ->lockForUpdate()
            ->first();

        if (empty($stock)) {
            $stock = new ProductStock([
                'product_id' => $product->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'quantity' => 0,
            ]);
            $stock->applyTenant($product->business_id, $product->created_by);
        }

        $updated = round((float) $stock->quantity + $delta, 4);

        if ($updated < 0) {
            throw new \RuntimeException(__(
                'Not enough :product at :place — :available available, :needed needed.',
                [
                    'product' => $product->name,
                    'place' => ProductStock::placeLabel($entityType, $entityId),
                    'available' => rtrim(rtrim(number_format((float) $stock->quantity, 4, '.', ''), '0'), '.') ?: '0',
                    'needed' => rtrim(rtrim(number_format(abs($delta), 4, '.', ''), '0'), '.'),
                ]
            ));
        }

        $stock->quantity = $updated;
        $stock->save();
    }

    protected function assertPositive(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \RuntimeException(__('Enter a quantity greater than zero.'));
        }
    }

    /* --------------------------------------------------------------------- */
    /* Reconciliation                                                        */
    /* --------------------------------------------------------------------- */

    /**
     * Rebuild every balance for a product from the ledger.
     *
     * The balance table is a cache, so this is always available as the answer to
     * "the number looks wrong". Nothing calls it automatically — it is a
     * deliberate operator action.
     *
     * @return int number of balance rows rewritten
     */
    public function rebuildBalances(Product $product): int
    {
        return DB::transaction(function () use ($product) {
            $totals = [];

            $transactions = StockTransaction::where('business_id', $product->business_id)
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->get();

            foreach ($transactions as $transaction) {
                if (!empty($transaction->destination_entity_type)) {
                    $key = $transaction->destination_entity_type . ':' . $transaction->destination_entity_id;
                    $totals[$key] = ($totals[$key] ?? 0) + (float) $transaction->quantity;
                }

                if (!empty($transaction->source_entity_type)) {
                    $key = $transaction->source_entity_type . ':' . $transaction->source_entity_id;
                    $totals[$key] = ($totals[$key] ?? 0) - (float) $transaction->quantity;
                }
            }

            ProductStock::where('business_id', $product->business_id)
                ->where('product_id', $product->id)
                ->update(['quantity' => 0]);

            foreach ($totals as $place => $quantity) {
                [$entityType, $entityId] = explode(':', $place, 2);

                $stock = ProductStock::firstOrNew([
                    'product_id' => $product->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'business_id' => $product->business_id,
                ]);

                $stock->quantity = round($quantity, 4);
                $stock->created_by = $stock->created_by ?: $product->created_by;
                $stock->save();
            }

            return count($totals);
        });
    }
}
