<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Response\Factory;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    public function create(DeliveryAPIResponseObject $responseObject) : Response
    {
        if ($responseObject->type === DeliveryAPIResponseObject::TYPE_FILE) {
            return new Response(
                base64_decode($responseObject->content),
                $responseObject->status,
                [
                    'Content-Type' => $responseObject->mime_type,
                ]
            );
        }

        return new RedirectResponse($responseObject->to, $responseObject->status);
    }
}
