<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Response\Provider;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogsBundle\Exception\BadResponseException;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeliveryAPIResponseObjectProvider implements DeliveryAPIResponseObjectProviderInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var RequestUrlProvider
     */
    private $requestUrlProvider;

    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    public function __construct(
        HttpClientInterface $httpClient,
        RequestUrlProvider $requestUrlProvider,
        ConfigurationRegistry $configurationRegistry
    ) {
        $this->httpClient = $httpClient;
        $this->requestUrlProvider = $requestUrlProvider;
        $this->configurationRegistry = $configurationRegistry;
    }

    public function getResponseObject(string $subdomain, string $path): DeliveryAPIResponseObject
    {
        $blogConfiguration = $this->configurationRegistry->getConfiguration($subdomain);
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->requestUrlProvider->getRequestUrl($blogConfiguration->getSubdomain()),
                [
                    'query' => [
                        'path' => $path,
                        'api_key' => $blogConfiguration->getDeliveryApiKey()
                    ]
                ]
            );

            return DeliveryAPIResponseObject::create(json_decode($response->getContent(), true));
        } catch (TransportException $exception) {
            throw new BadResponseException('Could not get response from Delivery API', 0, $exception);
        }
    }
}
