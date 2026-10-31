<?php

namespace App\Enums;

enum CatalogSort: string
{
    case Featured = 'featured';
    case PriceAsc = 'price_asc';
    case PriceDesc = 'price_desc';
    case Rating = 'rating';
    case Newest = 'newest';

    public function label(): string
    {
        return match ($this) {
            self::Featured => 'Destaques',
            self::PriceAsc => 'Menor preço',
            self::PriceDesc => 'Maior preço',
            self::Rating => 'Mais bem avaliados',
            self::Newest => 'Novidades',
        };
    }
}
