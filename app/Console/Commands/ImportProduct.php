<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeProductJob;
use Illuminate\Console\Command;

class ImportProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:import {--count=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import product from csv file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contents = fopen(base_path('data/products.csv'),'r');
        $i = 0;
        while ( ($fields = fgetcsv($contents) ) !== FALSE ) {
            if ($this->option('count') && $this->option('count') < $i) {
                break;
            }
            if ($i != 0) {
                $data = (object) [
                    'product_id' => $fields[0], 
                    'name' => $fields[1],
                    'sku' => $fields[2],
                    'price' => $fields[3],
                    'currency' => $fields[4],
                    'variations' => json_decode($fields[5]),
                    'quantity' => $fields[6],
                    'status' => $fields[7],
                ];
                ConsumeProductJob::dispatch($data);
            }
            $i++;
        }
    }
}
