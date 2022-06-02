<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
abstract class DatabaseContext implements Context
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function exec(string $query): void
    {
        $this->em->getConnection()->executeQuery($query);
    }
}
