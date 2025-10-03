<?php

namespace App\DTO;

class LineResult
{
    private int $lineId;
    private bool $executed;
    private ?string $message;

    public function __construct(int $lineId, bool $executed, ?string $message = null)
    {
        $this->lineId   = $lineId;
        $this->executed = $executed;
        $this->message  = $message;
    }

    public function getLineId(): int
    {
        return $this->lineId;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
