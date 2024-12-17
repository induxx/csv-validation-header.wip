<?php

namespace Misery\Component\Common\Client;

use App\Component\Akeneo\Api\Client\AkeneoApiClientAccount;
use App\Component\Common\Client\ApiClient;
use App\Component\Common\Client\ApiCurlClient;
use App\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiClientInterface as BaseApiClientInterface;
use Misery\Component\Connections\BusinessCentral\Client\MicrosoftDynamicsOauthAccount;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Connections\E5Dal\Client\E5DalAPIAccount;

/**
 * This Factory looks like a specific multi-tool implementation
 */
class ApiClientFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $account): ApiClientInterface|BaseApiClientInterface
    {
        $type = $account['type'] ?? null;
        if ($type === 'basic_auth') {
            try {
                // no need to authorize a basic auth

                return new BasicAuthApiClient(
                    $account['domain'],
                    $account['username'],
                    $account['password']
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }
        if ($type === 'microsoft_oauth') {
            try {
                // no need to authorize a basic auth
                $client = new ApiCurlClient($account['domain']);

                $account = new MicrosoftDynamicsOauthAccount(
                    $account['client_id'],
                    $account['client_secret'],
                    $account['tenant_id'],
                    $account['scope'],
                    $account['environment']
                );
                $client->authorize($account);

                return $client;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if ($type === 'e5_dal_token') {
            try {
                // no need to authorize token is fixed
                $client = new ApiCurlClient($account['domain']);

                $account = new E5DalAPIAccount($account['token']);
                $client->authorize($account);

                return $client;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        try {
            $account = new AkeneoApiClientAccount(
                rtrim($account['domain'], '/'),
                $account['username'],
                $account['password'],
                $account['client_id'],
                $account['secret'] ?? $account['client_secret']
            );

            $client = new ApiClient($account);
            $client->authorize();

            return $client;
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to create API client :: ' .$e->getMessage(), 0, $e);
        }
    }

    public function getName(): string
    {
        return 'api_client';
    }
}