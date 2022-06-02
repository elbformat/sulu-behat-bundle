<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Sulu;

use Sulu\Component\Webspace\Analyzer\Attributes\DateTimeRequestProcessor as SuluDateTimeRequestProcessor;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test-Double to manipulate the request's date used by sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class DateTimeRequestProcessor extends SuluDateTimeRequestProcessor
{
    public static ?\DateTime $currentDate;

    public function process(Request $request, RequestAttributes $requestAttributes)
    {
        return new RequestAttributes(['dateTime' => self::$currentDate ?? new \DateTime()]);
    }
}
