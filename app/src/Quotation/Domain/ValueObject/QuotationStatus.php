<?php

namespace App\Quotation\Domain\ValueObject;

enum QuotationStatus: string
{
    case CREATING = 'creating';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CLOSED = 'closed';
}
