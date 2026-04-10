<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Variant;
use App\Models\ImportTask;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements OnEachRow, WithHeadingRow, SkipsOnFailure, WithEvents
{
    protected $task;
    protected $totalRows = -1;
    protected $duplicate = [];
    protected $duplicateFileIsbn = [];
    protected $notFound = [];
    protected $processedIsbn = [];
    protected $updatedCount = 0;
    protected $notFoundCount = 0;
    protected $duplicateCount = 0;

    public function __construct(ImportTask $task)
    {
        $this->task = $task;
    }

    public function beforeImport(BeforeImport $event)
    {
        $sheet = $event->getReader()->getActiveSheet();
        $this->totalRows = $sheet->getHighestRow(); // Set total rows
        $this->task->update([
            'status' => 'processing',
            'progress' => 0,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => [$this, 'beforeImport'],  // Correct reference to the instance method
            AfterImport::class => [$this, 'afterImport'],  // Correct reference to the instance method
        ];
    }

    public function onRow($row)
    {
        $isbn = $row['isbn'];
        $quantity = $row['quantity']; // Excel quantity
        // Check for duplicates in the same file
        if (in_array($isbn, $this->processedIsbn)) {
            // If duplicate, store in duplicateFileIsbn and skip
            $this->duplicateFileIsbn[] = $isbn;
            $this->duplicateCount++;
            Log::channel('product_import')->info("Duplicate in file: {$isbn} - Skipped");
            return; // Skip processing this row
        }

        // Mark this ISBN as processed
        $this->processedIsbn[] = $isbn;


        $isProductUpdated = false;
        $updatedModel = ''; // Yeh variable tracking Product update hua ya Variant
        // Check if ISBN exists in the Product table
        $product = Product::where('isbn', $isbn)->first();
        $variant = Variant::where('isbn', $isbn)->first();
        if ($product && !$variant) {
            // Update the quantity in Product table
            $product->stock = $quantity;
            $product->save();
            $this->updatedCount++;
            $isProductUpdated = true;
            $updatedModel = 'Product';
        } elseif ($variant && !$product) {
            // Update the quantity in Variant table
            $variant->stock = $quantity;
            $variant->save();
            $this->updatedCount++;
            $isProductUpdated = true;
            $updatedModel = 'Variant';
        } elseif ($product && $variant) {
            // ISBN exists in both Product and Variant tables so update only in variant table
            $variant->stock = $quantity;
            $variant->save();
            $this->updatedCount++;
            $isProductUpdated = true;
            $updatedModel = 'Variant';
            $this->duplicate[] = $isbn;
            $this->duplicateCount++;
            Log::channel('product_import')->info("Duplicate ISBN found: {$isbn}");
        } else {
            // ISBN not found in both tables
            $this->notFound[] = $isbn;
            $this->notFoundCount++;
            Log::channel('product_import')->info("ISBN not found: {$isbn}");
        }
        // Update task progress
        $this->updateTaskProgress();
        // Log if updated
        if ($isProductUpdated) {
            Log::channel('product_import')->info("Updated ISBN: {$isbn} in {$updatedModel} with quantity: {$quantity}");
        }
    }

    // Method to update task progress
    private function updateTaskProgress()
    {
        $totalProcessed = $this->updatedCount  + $this->notFoundCount + $this->duplicateCount;
        // Calculate ratio or percentage
        $percentage = $this->totalRows > 0 ? ($totalProcessed / $this->totalRows) * 100 : 100;
        // Log the current progress and ratio
        Log::channel('product_import')->info("Progress updated: {$percentage}% ({$totalProcessed}/{$this->totalRows} rows processed)");
        // Update task progress
        $this->task->update([
            'progress' => $percentage,
        ]);
    }

    // Handle import failures
    public function onFailure(Failure ...$failures)
    {
        $this->task->update([
            'status' => 'failed',
            'result' => 'Import failed with errors.' . $failures,
        ]);
    }

    // Called after the import completes
    public function afterImport()
    {
        $this->task->update([
            'status' => 'completed',
            'result' => json_encode([
                'totalRows' => $this->totalRows,
                'duplicate' => $this->duplicate,
                'notFound' => $this->notFound,
                'updatedCount' => $this->updatedCount,
                'notFoundCount' => $this->notFoundCount,
                'duplicateCount' => $this->duplicateCount,
                'duplicateFileIsbn' => $this->duplicateFileIsbn,
                'processedIsbn' => $this->processedIsbn,
            ]),
        ]);
    }
}
