<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Location;
use App\Models\ProductLocationStock;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    public function assertAvailable(?string $idempotencyKey): void
    {
        if ($idempotencyKey && InventoryMovement::where('idempotency_key', $idempotencyKey)->exists()) {
            throw ValidationException::withMessages(['idempotency_key' => 'This inventory request was already processed.']);
        }
    }

    public function record(array $attributes): InventoryMovement
    {
        $movement = InventoryMovement::create($attributes + [
            'user_id' => auth()->id(),
            'movement_date' => today(),
        ]);

        if (empty($attributes['location_id'])) {
            $mainLocation = Location::where('code', 'MAIN')->first();
            if ($mainLocation) {
                $balance = ProductLocationStock::firstOrCreate([
                    'product_id' => $movement->product_id,
                    'location_id' => $mainLocation->id,
                ], ['quantity' => 0]);
                $balance->increment('quantity', $movement->quantity);
            }
        }

        return $movement;
    }
}