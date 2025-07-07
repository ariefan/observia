<?php

namespace App\Enum;

enum StatusLivestock: int
{
    case ACTIVE = 1;
    case SOLD = 2;
    case DEAD = 3;
    case SLAUGHTERED = 4;
}
