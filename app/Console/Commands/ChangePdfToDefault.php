<?php

namespace App\Console\Commands;

use App\Models\System\Client;
use App\Models\Tenant\Establishment;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;

class ChangePdfToDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando fallback para cambiar los template de pdf a default';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clients = Client::all();


        foreach ($clients as $client) {
            $website_id = $client->hostname->website_id;

            $website = Website::find($website_id);
            $tenancy = app(Environment::class);
            $tenancy->tenant($website);

            Establishment::whereNotIn('template_pdf', ['default', 'marca_de_agua'])->update(['template_pdf' => 'default', 'template_ticket_pdf' => 'default']);
        }
    }
}
