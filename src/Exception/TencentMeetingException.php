<?php

namespace Tourze\TencentMeetingBundle\Exception;

abstract class TencentMeetingException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $apiResponse
     */
    public function __construct(
        string $message,
        private readonly array $apiResponse = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getApiResponse(): array
    {
        return $this->apiResponse;
    }
}
