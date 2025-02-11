<?php

namespace Misery\Component\Akeneo\Client\Errors;

class AkeneoErrorFactory
{
    public static function createErrors($payload): AkeneoErrors
    {
        $errors = [];

        // convert to multi-payload
        if (!isset($payload[0]) && isset($payload['code'])) {
            $singlePayLoad = $payload;
            unset($payload);
            $payload[0] = $singlePayLoad;
        }

        // Handle multi-payload response
        if (is_array($payload) && isset($payload[0])) {
            foreach ($payload as $msg) {
                $statusCode = $msg['status_code'] ?? $msg['code'] ?? null;
                $id = $msg['identifier'] ?? $msg['code'] ?? 'unknown_id';
                $errors[] = new AkeneoError(
                    $id,
                    implode(': ', array_filter([$statusCode, $msg['message']]))
                );

                // Handle Errors
                if (isset($msg['errors']) && is_array($msg['errors'])) {
                    foreach ($msg['errors'] as $error) {
                        if (isset($error['message'])) {
                            $errors[] = new AkeneoError(
                                $error['property'] ?? 'unknown',
                                $error['message'],
                                $error['attribute'] ?? null,
                                $error['locale'] ?? null,
                                $error['scope'] ?? null,
                            );
                        }
                    }
                }
            }
        }

        return new AkeneoErrors('Validation failed', $errors);
    }
}