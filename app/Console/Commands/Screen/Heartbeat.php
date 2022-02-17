<?php

namespace App\Console\Commands\Screen;

use App\Helpers\Screen;
use Illuminate\Console\Command;

class Heartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'screen:heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        for ($i=0; $i < 5; $i++) {
            Screen::get()->heartbeat();
            sleep(1);
        }
        return 0;
    }
}
