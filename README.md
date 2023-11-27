
# Salla Coding Challenge

It's amazing task to show some examples of my experiance through the challenge, i tried different strategies to deliver this task 
such ansible-vault, docker, meillisearch "cache-products" layers, Redis "Queue Service"
## Overview

This challenge's stratigies of PHP development:

* PHP's OOP implementation (interfaces and design patterns)
  using repositories, observers, services.

* MySQL (Docker image DB)

* RESTful API integration (http service for getting products)

* Efficient workload processing (Supervisor processes, Patching Queues).

* Unit/feature testing (DB Test, Http Test Service)

## Project Description

Project Used laravel sail to initiate dependencies very fast and to be unfied across all devices which will need to test the code, and shared .env variables with ansible vault decryption and it will be very easy to encrypt it with given password "salla".

### 1. Setup the Poject!

* First you need to make download the repository to your local machine
* Install Docker, Dokcer-Compose, ansible.
* Install dependencies you need to run this command

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

* after installing ansible you need to run this command

```
    cp /ansible-vault/.env .env 
    ansible-vault decrypt .env and add password "salla"
```

* now you need to intiate project

```sh
./vendor/bin/sail up -d                         //initialize all services from docker compose file 
./vendor/bin/sail php artisan migrate --seed    //migrate DB tables and Seed User Factory Faker Data
```

### 2. Services
*Mysql Database
*Redis
*Meillisearch
*Adminer
*SailApp

### 3. Challange Product CSV

* The Problem Here we Have File Contains thousands of rows and we need to make process on them our key here is Queue/Patch
let us devide the problem into chunks

* CSV file and we need to read it first without exploading patterns.

```php
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
```

* Need to make command to update the data which inserted before.

* need to softdelete the products which has deleted state.

```php
class ConsumeProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Product repository pattern
            $repository = app(ProductInterface::class);
            $variantRepository = app(ProductVariationInterface::class);


            // Start transactions
            DB::beginTransaction();

                // Validate product uniqueness
                $product = $repository->getByIdOrSku($this->product->product_id, $this->product->sku);

                if ($product) {
                    $repository->update($product, $this->product);
                } else {
                    $product = $repository->store($this->product);

                    // insert variants 
                    if ($this->product->variations) {
                        foreach ($this->product->variations as $variation) {
                            $variantRepository->store($this->product->product_id, $variation);
                        }
                    }
                }

                // If status "deleted" softDelete
                if ($product->status == "deleted" && !$product->trashed()) {
                    $repository->delete($this->product->product_id);
                }

            DB::commit();       
        } catch (Throwable $e) {
            DB::rollBack();
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/error.log'),
            ])->error('System failed to create {id :' .$this->product->product_id. '}.', ['message' => $e->getMessage()]);
        }
    }
}
```

* The implementation was Create Command to Consume Data from CSV then send patched data by row to validate and santize data.
Redis Queue was the Key here to handel all processes in the background.

```php
php artisan queue:work 
php artisan product:import
php artisan product:import --count=500// you can use count for testing purposes
```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/2
* Files:  https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/10/files

### 4: Challange Product API

* Extend the service to update product data from a third-party supplier API we can use it by this way.

```php
php artisan service:sync-products
```

* the integration here come on three stages first creating a new service

```php

<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductService
{
    public function getProducts()
    {
        Log::info( 'System tries to hit product');
        try {
            $response =  Http::withHeaders([
                'accept' => 'application/json',
            ])->get(config('services.products.url'));
            if ($response->ok()) { 
                return $response->json();
            }
            Log::emergency(sprintf("service emerge", $response->getMessage()));
        } catch (Throwable $e) {
            Log::error( sprintf("service down", $e->getMessage()));
            return null;
        }
    }
}
```

* Second thing to make console command

```php
protected $signature = 'service:sync-products';

/**
 * The console command description.
 *
 * @var string
 */
protected $description = 'Third Party API to update a product';

/**
 * Execute the console command.
 */
public function handle()
{
    $service = new ProductService();
    $products = $service->getProducts();
    if (count($products)) {
        foreach ($products as $product) {
            UpdateProductJob::dispatch($product);
        }
    }
}
```

* Third thing to sceduale console command to run daily.

```php
/**
 * The Artisan commands provided by your application.
 *
 * @var array
 */
protected $commands = [
    Commands\UpdateProductService::class,
];

/**
 * Define the application's command schedule.
 */
protected function schedule(Schedule $schedule): void
{
    $schedule->command('service:sync-products')->daily();
}
```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/6
* Files:  https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/19/files

### 5: Challange Technical Concerns Variation Structure

the problem here: kindly read it carefully

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/6

* After Update Structure of DB as described in the issue product * Product Variants.

```php
$product->variants // $product has many variants
$variant->product  // $variant belings to the product
$variant->siblingVariants  // $variant has abillity to access tree of his siblings varinats with same product parent
```

* We Implement Repository Pattern as service method to reuse code and separate DB layer to be accessible from one place by registering a repository with eloquant repository in app service provider configuration.

```php
private $repositories = [
    ProductInterface::class => ProductEloquent::class,
    ProductVariationInterface::class => ProductVariationEloquent::class
];

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Product::observe(ProductObserver::class);
    
    foreach ($this->repositories as $interface => $eloquent) {
        $this->app->bind($interface, $eloquent);
    }
}
```

* We can use it by this way EX*

```php
try {
    // Product repository pattern
    $repository = app(ProductInterface::class);
    $variantRepository = app(ProductVariationInterface::class);


    // Start transactions
    DB::beginTransaction();

        // Validate product uniqueness
        $product = $repository->getByIdOrSku($this->product->product_id, $this->product->sku);

        if ($product) {
            $repository->update($product, $this->product);
        } else {
            $product = $repository->store($this->product);

            // insert variants 
            if ($this->product->variations) {
                foreach ($this->product->variations as $variation) {
                    $variantRepository->store($this->product->product_id, $variation);
                }
            }
        }

        // If status "deleted" softDelete
        if ($product->status == "deleted" && !$product->trashed()) {
            $repository->delete($this->product->product_id);
        }

    DB::commit();       
} catch (Throwable $e) {
    DB::rollBack();
    Log::build([
        'driver' => 'single',
        'path' => storage_path('logs/error.log'),
    ])->error('System failed to create {id :' .$this->product->product_id. '}.', ['message' => $e->getMessage()]);
}
```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/6 https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/14
* Files: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/23/files

### 6: Challange Improve Performance / Loggers

* Cusomize Supervisor to increase number of proccesses to consume the queue messages.

```sh
[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project/artisan queue:work --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=100
redirect_stderr=true
stdout_logfile=/var/www/project/storage/logs/queue.log
stopwaitsecs=3600
```

* Design For Logger files Created, Updated, Deleted, Error Logs depending on model changes Behavior and the linked with observers.

```php
Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/created.log'),
])->info('System create {id :' .$product->product_id. '}.', ['sku' => $product->sku]);

Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/updated.log'),
])->info($message);

Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/deleted.log'),
])->info('System delete {id :' .$product->product_id. '}.', ['sku' => $product->sku]);

```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/18
* Files:  https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/22/files

### 7: Challange Observers/Notifier on updates

* Design Product Observer to make actions on Create, Update, Delete product on system we can add logging.
* Notify Users, Even Send Email, Broadcast messages.

```php
// app service provider
public function boot(): void
{
    Product::observe(ProductObserver::class);
}

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function created(Product $product)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/created.log'),
        ])->info('System create {id :' .$product->product_id. '}.', ['sku' => $product->sku]);
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function updated(Product $product)
    {
        $message = match (true) {
            $product->wasChanged('price') && $product->wasChanged('quantity') => 
                __('System update :sku price & quantity', ['sku' => $product->sku]),
            $product->wasChanged('price') => __('System update :sku price', ['sku' => $product->sku]),
            $product->wasChanged('quantity') => __('System update : sku quantity', ['sku' => $product->sku]),
            default => __('System touch :sku', ['sku' => $product->sku])
        };
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/updated.log'),
        ])->info($message);
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function deleted(Product $product)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/deleted.log'),
        ])->info('System delete {id :' .$product->product_id. '}.', ['sku' => $product->sku]);
    }
}

```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/4
* Files:  https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/16/files

### 8: Unit Testing

```php
//// Test consoles
public function test_command_productImport_limitedRaws(): void
{
    $this->artisan('product:import --count=5')->assertSuccessful();
}

//// Test service
protected $productService;

public function setUp(): void
{
    $this->productService = new ProductService();
}

public function test_apiService_returnObjectOfProducts(): void
{
    $response = $this->productService;
    $this->assertIsObject($response);
}

///// Product Test 
public function test_createProduct_withMandatoryData()
{
    $product = Product::make([
        'product_id' => 1,
        'name' => 'Amazed Product',
    ]);

    $this->assertTrue(boolval($product));
}

public function test_factory_getProduct()
{
    Product::factory()->count(3)->make();
    $product = Product::first();
    $this->assertDatabaseHas('products', ['product_id' => $product->id]);
}

//// Test DB
public function test_createUser_withMandatoryData()
{
    $user = User::make([
        'name' => 'salla',
        'email' => 'a@b.c',
        'password' => bcrypt('123456')
    ]);

    $this->assertTrue(boolval($user));
}

public function test_factory_getUser()
{
    User::factory()->count(3)->make();
    $user = User::first();
    $this->assertDatabaseHas('users', ['email' => $user->email]);
}
```

* Issues: https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/issues/7
* Files:  https://github.com/SallaChallenges/Laravel-Challange-ilcapo3li-4329/pull/21/files


***All the best***
