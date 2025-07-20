<?php

namespace App\RequestInformation\Domain\ValueObject;

enum RequestStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case RECONTACT = 'RECONTACT';
    case WON = 'WON';
    case LOST = 'LOST';
}
