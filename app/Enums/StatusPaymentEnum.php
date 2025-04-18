<?php

namespace App\Enums;

enum StatusPaymentEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::COMPLETED => 'Selesai',
            self::FAILED => 'Gagal'
        };
    }
}
