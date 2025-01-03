<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Ensure the name ends with "Service"
        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $path = app_path("Services/{$name}.php");

        // Create Services directory if it doesn't exist
        if (!File::isDirectory(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        // Service class template
        $template = <<<CLASS
<?php

namespace App\Services;

class {$name}
{
    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Example method
     */
    public function execute()
    {
        //
    }
}
CLASS;

        // Check if file already exists
        if (File::exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        // Create the service class file
        File::put($path, $template);

        $this->info("Service class {$name} created successfully!");
    }
}