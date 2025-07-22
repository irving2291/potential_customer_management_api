<?php

namespace App\RequestInformation\Application\Query;

class GetRequestSummaryQuery
{
    public ?\DateTimeImmutable $from;
    public ?\DateTimeImmutable $to;

    public function __construct(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
