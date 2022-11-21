<?php

namespace Hyvor\BlogBundle\Service\Response\Provider;

use Hyvor\BlogBundle\DTO\DeliveryAPIResponseObject;

interface DeliveryAPIResponseObjectProviderInterface
{
    public function getResponseObject(string $subdomain, string $path): DeliveryAPIResponseObject;
}
