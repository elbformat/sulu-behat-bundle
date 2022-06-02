<?php declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
abstract class DatabaseContext implements Context
{
    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(EntityManagerInterface $em) { $this->em = $em; }

    protected function exec(string $query)
    {
        $this->em->getConnection()->query($query)->execute();
    }
}
