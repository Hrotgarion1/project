<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TermsAcceptance;

class CleanTermsAcceptances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-terms-acceptances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina registros de aceptación de términos más antiguos que 30 minutos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TermsAcceptance::where('accepted_at', '<', now()->subMinutes(30))->delete();
        $this->info('Registros de aceptación de términos antiguos eliminados.');
    }
}
