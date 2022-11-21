<?php

namespace Hyvor\BlogsBundle\Service\Response\Provider;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;

interface DeliveryAPIResponseObjectProviderInterface
{
    public function getResponseObject(string $subdomain, string $path): DeliveryAPIResponseObject;
}
