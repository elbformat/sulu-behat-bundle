<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Simulates the admin part of sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluMediaContext extends DatabaseContext
{
    protected MediaManagerInterface $mediaManager;

    public function __construct(EntityManagerInterface $em, MediaManagerInterface $mediaManager)
    {
        parent::__construct($em);
        $this->mediaManager = $mediaManager;
    }

    /**
     * Clear all media contents before each scenario
     *
     * @BeforeScenario @sulu
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM me_media');
        $this->exec('ALTER TABLE me_media AUTO_INCREMENT=1');
    }

    /**
     * @Given there is an image
     */
    public function thereIsAnImage(?TableNode $tableNode = null): void
    {
        $data = $tableNode ? $tableNode->getRowsHash() : [];
        $filename = (string) ($data['filename'] ?? '1px.jpg');
        $file = (string) ($data['file'] ?? '1px.jpg');
        $data['locale'] = 'de';
        $data['collection'] = 4;
        // @todo inject project folder
        $uploadedFile = new UploadedFile(__DIR__ . '/../fixtures/' . $file, $filename);
        $this->mediaManager->save($uploadedFile, $data, 1);
    }
}
