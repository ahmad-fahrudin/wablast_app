<?php

namespace App\Enums;

enum GroupTypeEnum: string
{
    case CONTACT = 'contact';
    case WHATSAPP = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::CONTACT => 'Group Contact',
            self::WHATSAPP => 'WhatsApp Group'
        };
    }
}
