<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Elbformat\SuluBehatBundle\Sulu\DateTimeRequestProcessor;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class DateContext implements Context
{
    /**
     * @Given the current date is :date
     */
    public function theCurrentDateIs(string $date): void
    {
        DateTimeRequestProcessor::$currentDate = new \DateTime($date);
    }
}
