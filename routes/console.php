<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockAdjustment;
use App\Models\ExpiredProductDisposal;
use App\Models\InventoryCount;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Services\BackupService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:backup', function () {
    $backup = BackupService::create(null, 'scheduled');
    $this->info('Backup created: '.$backup->filename);
})->purpose('Create a JSON backup of inventory data');

Schedule::command('inventory:backup')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('inventory:backup:prune')->dailyAt('03:00')->withoutOverlapping();

Artisan::command('inventory:backup:prune', function () {
    $this->info('Removed '.BackupService::prune((int) env('BACKUP_RETENTION_DAYS', 30)).' expired backup(s).');
})->purpose('Remove backups beyond the retention period');

Artisan::command('inventory:send-alerts', function () {
    $low = Product::where('status', true)->whereColumn('quantity', '<=', 'minimum_stock')->get();
    $expiring = ProductBatch::with('product')->where('quantity', '>', 0)->whereNotNull('expiration_date')
        ->whereDate('expiration_date', '<=', today()->addDays(30))->orderBy('expiration_date')->get();

    if ($low->isEmpty() && $expiring->isEmpty()) {
        return $this->info('No inventory alerts to send.');
    }

    $message = "Inventory alert summary\n\nLow/out-of-stock products: {$low->count()}\nExpiring/expired batches: {$expiring->count()}\n\nOpen the dashboard for details.";
    User::where('role', 'admin')->pluck('email')->each(fn ($email) => Mail::raw($message, fn ($mail) => $mail->to($email)->subject('Product Inventory Alerts')));
    $this->info('Inventory alerts sent to administrators.');
})->purpose('Email low-stock and expiration alerts to administrators');

Artisan::command('inventory:reconcile', function () {
    $mismatches = Product::query()->withSum('batches', 'quantity')->get()->filter(function ($product) {
        return abs((float) $product->quantity - (float) $product->batches_sum_quantity) > 0.001;
    });

    if ($mismatches->isEmpty()) {
        return $this->info('Inventory quantities match their batch totals.');
    }

    $mismatches->each(fn ($product) => $this->line(sprintf(
        '%s (%s): product=%s, batches=%s',
        $product->product_name,
        $product->product_code,
        $product->quantity,
        $product->batches_sum_quantity ?? 0
    )));
    $this->error($mismatches->count().' product(s) need reconciliation.');
})->purpose('Report product and batch quantity mismatches');

Artisan::command('inventory:backfill-ledger', function () {
    $created = 0;

    StockIn::query()->orderBy('id')->each(function (StockIn $stockIn) use (&$created) {
        if ((float) $stockIn->quantity == 0.0) {
            return;
        }

        $movement = InventoryMovement::firstOrCreate(
            ['idempotency_key' => 'legacy-stock-in-'.$stockIn->id],
            [
                'product_id' => $stockIn->product_id,
                'product_batch_id' => $stockIn->batch?->id,
                'movement_type' => 'stock_in',
                'quantity' => $stockIn->quantity,
                'source_type' => StockIn::class,
                'source_id' => $stockIn->id,
                'movement_date' => $stockIn->stock_in_date,
                'remarks' => 'Backfilled from existing stock-in record.',
            ]
        );
        $created += $movement->wasRecentlyCreated ? 1 : 0;
    });

    StockOut::query()->orderBy('id')->each(function (StockOut $stockOut) use (&$created) {
        if ((float) $stockOut->quantity == 0.0) {
            return;
        }

        $movement = InventoryMovement::firstOrCreate(
            ['idempotency_key' => 'legacy-stock-out-'.$stockOut->id],
            [
                'product_id' => $stockOut->product_id,
                'movement_type' => 'stock_out',
                'quantity' => -$stockOut->quantity,
                'source_type' => StockOut::class,
                'source_id' => $stockOut->id,
                'movement_date' => $stockOut->stock_out_date,
                'remarks' => 'Backfilled from existing stock-out record.',
            ]
        );
        $created += $movement->wasRecentlyCreated ? 1 : 0;
    });

    StockAdjustment::query()->orderBy('id')->each(function (StockAdjustment $adjustment) use (&$created) {
        if ((float) $adjustment->quantity == 0.0) {
            return;
        }

        $movement = InventoryMovement::firstOrCreate(
            ['idempotency_key' => 'legacy-adjustment-'.$adjustment->id],
            [
                'product_id' => $adjustment->product_id,
                'product_batch_id' => $adjustment->product_batch_id,
                'movement_type' => 'stock_adjustment',
                'quantity' => $adjustment->type === 'increase' ? $adjustment->quantity : -$adjustment->quantity,
                'source_type' => StockAdjustment::class,
                'source_id' => $adjustment->id,
                'movement_date' => $adjustment->adjustment_date,
                'remarks' => 'Backfilled from existing stock adjustment.',
            ]
        );
        $created += $movement->wasRecentlyCreated ? 1 : 0;
    });

    ExpiredProductDisposal::with('productBatch')->orderBy('id')->each(function (ExpiredProductDisposal $disposal) use (&$created) {
        if ((float) $disposal->quantity == 0.0 || ! $disposal->productBatch) {
            return;
        }

        $movement = InventoryMovement::firstOrCreate(
            ['idempotency_key' => 'legacy-disposal-'.$disposal->id],
            [
                'product_id' => $disposal->productBatch->product_id,
                'product_batch_id' => $disposal->product_batch_id,
                'movement_type' => 'expired_disposal',
                'quantity' => -$disposal->quantity,
                'source_type' => ExpiredProductDisposal::class,
                'source_id' => $disposal->id,
                'movement_date' => $disposal->disposal_date,
                'remarks' => 'Backfilled from existing expired disposal.',
            ]
        );
        $created += $movement->wasRecentlyCreated ? 1 : 0;
    });

    InventoryCount::with('productBatch')->where('variance', '<>', 0)->orderBy('id')->each(function (InventoryCount $count) use (&$created) {
        $movement = InventoryMovement::firstOrCreate(
            ['idempotency_key' => 'legacy-count-'.$count->id],
            [
                'product_id' => $count->productBatch->product_id,
                'product_batch_id' => $count->product_batch_id,
                'movement_type' => 'physical_count',
                'quantity' => $count->variance,
                'source_type' => InventoryCount::class,
                'source_id' => $count->id,
                'movement_date' => $count->count_date,
                'remarks' => 'Backfilled from existing physical count.',
            ]
        );
        $created += $movement->wasRecentlyCreated ? 1 : 0;
    });

    $this->info($created.' existing inventory movement(s) added to the ledger. Existing records were not changed.');
})->purpose('Backfill the inventory ledger from existing stock records');

Schedule::command('inventory:send-alerts')->dailyAt('08:00')->withoutOverlapping();
