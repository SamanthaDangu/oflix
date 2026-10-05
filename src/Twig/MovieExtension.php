<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MovieExtension extends AbstractExtension
{
    private const TYPE_LABELS = [
        'Movie' => 'Film',
        'Series' => 'Série',
    ];

    public function getFilters(): array
    {
        return [
            new TwigFilter('movieTypeLabel', [$this, 'movieTypeLabel']),
        ];
    }

    public function movieTypeLabel(?string $type): string
    {
        return self::TYPE_LABELS[$type] ?? (string) $type;
    }
}
