<?php

namespace Enjin\Platform\Clients\Implementations;

use Enjin\Platform\Clients\Abstracts\WebsocketAbstract;

class SubstrateWebsocket extends WebsocketAbstract
{
    /**
     * Create a new websocket client instance.
     */
    public function __construct(?string $url = null)
    {
        $host = $url ?? config(sprintf('enjin-platform.chains.supported.substrate.%s.node', config('enjin-platform.chains.network')));

        parent::__construct($host);
    }
}
