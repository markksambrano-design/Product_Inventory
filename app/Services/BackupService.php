<?php

namespace App\Services;

use App\Models\BackupHistory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public const TABLES = ['users','categories','products','suppliers','stock_ins','stock_outs','stock_adjustments','product_batches','stock_out_batches','expired_product_disposals','inventory_counts','activity_logs','purchase_orders','purchase_order_items','purchase_order_receipts','sales','sale_items','sale_returns','sales_closings','inventory_notifications','login_histories','locations','product_location_stocks','inventory_movements','stock_transfers','stock_transfer_items','stock_returns'];

    public static function disk(): string
    {
        return env('BACKUP_DISK', 'local');
    }

    public static function create(?int $userId = null, string $type = 'manual'): BackupHistory
    {
        $backup = ['generated_at' => now()->toIso8601String(), 'application' => config('app.name'), 'tables' => []];
        foreach (self::TABLES as $table) if (Schema::hasTable($table)) $backup['tables'][$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $plain = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $payload = env('BACKUP_ENCRYPT', true) ? json_encode(['encrypted' => true, 'data' => Crypt::encryptString($plain)], JSON_THROW_ON_ERROR) : $plain;
        $filename = 'backups/inventory-'.$type.'-'.now()->format('Y-m-d-His').'.json';
        $disk = self::disk();
        Storage::disk($disk)->put($filename, $payload);
        return BackupHistory::create(['user_id' => $userId, 'filename' => $filename, 'size' => strlen($payload), 'checksum' => hash('sha256', $payload), 'disk' => $disk, 'type' => $type]);
    }

    public static function decode(string $payload): array
    {
        $decoded = json_decode($payload, true);
        if (is_array($decoded) && ($decoded['encrypted'] ?? false) === true) $decoded = json_decode(Crypt::decryptString($decoded['data']), true);
        if (! is_array($decoded) || ! isset($decoded['tables'], $decoded['generated_at'])) throw new \InvalidArgumentException('Invalid inventory backup payload.');
        return $decoded;
    }

    public static function prune(int $days = 30): int
    {
        $deleted = 0;
        BackupHistory::where('created_at', '<', now()->subDays($days))->each(function (BackupHistory $backup) use (&$deleted) { Storage::disk($backup->disk ?: 'local')->delete($backup->filename); $backup->delete(); $deleted++; });
        return $deleted;
    }
}
