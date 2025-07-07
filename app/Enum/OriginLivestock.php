<?php

namespace App\Enum;

enum OriginLivestock: int
{
    case BIRTH = 1;
    case PURCHASE = 2;
    case BARTER = 3;
    case GIFT = 4;
    case RENT = 5;
}
