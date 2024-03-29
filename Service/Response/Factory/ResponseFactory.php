<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Response\Factory;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    public function create(DeliveryAPIResponseObject $responseObject): Response
    {
        if ($responseObject->type === DeliveryAPIResponseObject::TYPE_FILE) {
            return new Response(
                base64_decode((string) $responseObject->content),
                $responseObject->status,
                [
                    'Content-Type' => $responseObject->mime_type,
                    'Cache-Control' => $responseObject->cache_control,
                ]
            );
        }

        return new RedirectResponse((string) $responseObject->to, $responseObject->status);
    }
}
