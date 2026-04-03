<?php
namespace Modules\ManagedPremises\Support\Enums;

class RoomTypes
{
    public static function common(): array
    {
        return [
            'bedroom','bathroom','kitchen','laundry','living','garage','balcony','outdoor','roof','plant',
            'office','store','hall','stairs'
        ];
    }
}
