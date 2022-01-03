<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use App\Tests\Sulu\DateTimeRequestProcessor;
use Behat\Behat\Context\Context;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class DateContext implements Context
{
    /**
     * @Given the current date is :date
     */
    public function theCurrentDateIs($date)
    {
        DateTimeRequestProcessor::$currentDate = new \DateTime($date);
    }

}
