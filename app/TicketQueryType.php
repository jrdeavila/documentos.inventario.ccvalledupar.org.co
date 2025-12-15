<?php

namespace App;

enum TicketQueryType: string
{
    case COMMERCIAL = 'commercial';
    case NONPROFIT = 'non-profit';
    case PROPOSER = 'proposer';
}
