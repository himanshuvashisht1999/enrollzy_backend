<?php

namespace App\Http\Controllers\Backend\General;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class ExportCsvController extends Controller
{
    public function ExportProduct()
    {
        $data = Product::get();
        $csvFileName = 'product_export.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Add CSV header
            fputcsv($handle, ['ID', 'Name', 'IsVariation']);

            // Add data to CSV
            foreach ($data as $row) {
                fputcsv($handle, [$row->id, $row->name, $row->is_variation]);
            }

            fclose($handle);
        };
        sendNotify(null, 'csv_export', 'info', 'CSV Data exported', 'medium', 'admin', null);
        // function name ('userId = null for all users ', 'for', 'type', 'message', 'priority', 'visible for', 'target id');
        return Response::streamDownload($callback, $csvFileName, $headers);
    }
}
