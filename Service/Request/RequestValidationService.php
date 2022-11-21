<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestValidationService
{
    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * @var string
     */
    private $hashHeader;

    public function __construct(string $hashAlgorithm, string $hashHeader)
    {
        $this->hashAlgorithm = $hashAlgorithm;
        $this->hashHeader = $hashHeader;
    }

    public function validate(Request $request, string $secret) : bool
    {
        $content = (string) $request->getContent();
        $knownHash = hash_hmac($this->hashAlgorithm, $content, $secret);
        $signature = $request->headers->get($this->hashHeader);
        if ($signature === null) {
            return false;
        }

        return hash_equals($knownHash, $signature);
    }
}
