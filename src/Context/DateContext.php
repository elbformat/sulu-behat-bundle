<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Elbformat\SuluBehatBundle\Sulu\DateTimeRequestProcessor;

class DateContext extends \Elbformat\SymfonyBehatBundle\Context\DateContext
{
    public function theCurrentDateIs(string $date): void
    {
        parent::theCurrentDateIs($date);
        DateTimeRequestProcessor::$currentDate = new \DateTime($date);
    }
}
