<?php

namespace Railken\Amethyst\Events;

use Illuminate\Queue\SerializesModels;
use Railken\Amethyst\Models\Importer;
use Railken\Lem\Contracts\AgentContract;

class ImportSucceeded
{
    use SerializesModels;

    public $importer;
    public $agent;

    /**
     * Create a new event instance.
     *
     * @param \Railken\Amethyst\Models\Importer    $importer
     * @param \Railken\Lem\Contracts\AgentContract $agent
     */
    public function __construct(Importer $importer, AgentContract $agent = null)
    {
        $this->importer = $importer;
        $this->agent = $agent;
    }
}
