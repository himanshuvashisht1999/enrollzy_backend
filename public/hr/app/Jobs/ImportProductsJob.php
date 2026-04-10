<?php

namespace App\Jobs;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Models\ImportTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $taskId;
    public $filePath;

    public function __construct($taskId, $filePath)
    {
        $this->taskId = $taskId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $task = ImportTask::find($this->taskId);
        $task->status = 'processing';
        $task->save();
        Excel::import(new ProductsImport($task), $this->filePath);
    }
}
