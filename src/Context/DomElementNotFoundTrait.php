<?php

namespace Elbformat\SuluBehatBundle\Context;

use Symfony\Component\DomCrawler\Crawler;

trait DomElementNotFoundTrait
{
    protected function createNotFoundException(string $what, ?Crawler $fallbacks = null, ?string $attrib = null): \DomainException
    {
        $errMsg = sprintf('%s not found.', $what);
        if (null !== $fallbacks) {
            $names = [];
            foreach ($fallbacks as $fallback) {
                $names[] = (null !== $attrib) ? $fallback->getAttribute($attrib) : $fallback->ownerDocument->saveXML($fallback);
            }
            switch (\count($names)) {
                case 0:
                    break;
                case 1:
                    $errMsg .= sprintf(' Did you mean "%s"?', $names[0]);
                    break;
                default:
                    $errMsg .= sprintf(" Did you mean one of the following?\n%s", implode("\n", $names));
                    break;
            }
        }

        return new \DomainException($errMsg);
    }
}