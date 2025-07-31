<?php

namespace App\RequestInformation\Domain\ValueObject;

enum RequestStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case RECONTACT = 'recontact';
    case WON = 'won';
    case LOST = 'lost';

    case CLOSE = 'close';
}
