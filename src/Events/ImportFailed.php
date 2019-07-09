<?php

namespace Amethyst\Events;

use Exception;
use Illuminate\Queue\SerializesModels;
use Amethyst\Models\Importer;
use Railken\Lem\Contracts\AgentContract;

class ImportFailed
{
    use SerializesModels;

    public $importer;
    public $error;
    public $agent;

    /**
     * Create a new event instance.
     *
     * @param \Amethyst\Models\Importer    $importer
     * @param \Exception                           $exception
     * @param \Railken\Lem\Contracts\AgentContract $agent
     */
    public function __construct(Importer $importer, Exception $exception, AgentContract $agent = null)
    {
        $this->importer = $importer;
        $this->error = (object) [
            'class'   => get_class($exception),
            'message' => $exception->getMessage(),
        ];
        $this->agent = $agent;
    }
}
