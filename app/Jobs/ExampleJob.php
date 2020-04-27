<?php

namespace App\Jobs;

use App\User;

class ExampleJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        file_put_contents("H:/filename" . time() . ".txt","Love.text");
    }
}
