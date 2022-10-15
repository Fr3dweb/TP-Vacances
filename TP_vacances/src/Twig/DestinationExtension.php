<?php

namespace App\Twig;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DestinationExtension extends AbstractExtension
{
    private DestinationRepository $destinationRepository;

    public function __construct(DestinationRepository $destinationRepository)
    {
        $this->destinationRepository = $destinationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('moyenne_review', [$this, 'moyenneReview']),
        ];
    }

    public function moyenneReview(Destination $destination)
    {
        return $this->destinationRepository->moyenneReview($destination)[1];
    }
}