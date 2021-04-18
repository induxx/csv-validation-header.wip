<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiClient;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Writer\ItemWriterInterface;

class ApiWriter implements ItemWriterInterface
{
    private $client;
    private $endpoint;

    public function __construct(ApiClient $client, ApiEndpointInterface $endpoint)
    {
        $this->client = $client;
        $this->endpoint = $endpoint;
    }

    private function post(array $data): void
    {
        $this->client
            ->post($this->client->generateUrl($this->endpoint->getSingleEndPoint()), $data)
            ->getResponse()
        ;
    }

    public function write(array $data): void
    {
        $this->post($data);
    }
}
