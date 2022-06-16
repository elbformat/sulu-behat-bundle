<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Collection\Manager\CollectionManagerInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Simulates the admin part of sulu.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluMediaContext extends AbstractSuluContext
{
    protected MediaManagerInterface $mediaManager;
    protected CollectionManagerInterface $collectionManager;
    protected string $projectDir;

    public function __construct(EntityManagerInterface $em, WebspaceManagerInterface $webspaceManager, MediaManagerInterface $mediaManager, CollectionManagerInterface $collectionManager, string $projectDir)
    {
        parent::__construct($em, $webspaceManager);
        $this->mediaManager = $mediaManager;
        $this->collectionManager = $collectionManager;
        $this->projectDir = $projectDir;
    }

    /**
     * Clear all media contents before each scenario
     *
     * @BeforeScenario
     */
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM me_media WHERE id >= 1000');
        $this->exec('ALTER TABLE me_media AUTO_INCREMENT=1000');
    }

    /**
     * @Given there is an image in collection :collection
     * @Given there is an image
     */
    public function thereIsAnImage(?TableNode $tableNode = null, string $collection = 'sulu_media'): void
    {
        $data = $tableNode ? $tableNode->getRowsHash() : [];
        $file = (string)($data['file'] ?? 'tests/fixtures/1px.jpg');
        $filename = (string)($data['filename'] ?? basename($file));
        $data['locale'] = $this->getLocale();
        $data['collection'] = $this->findCollectionIdByKey($collection);

        if (!file_exists($this->projectDir.'/'.$file)) {
            throw new \DomainException(sprintf('Fixture file not found at %s', $this->projectDir.'/'.$file));
        }
        $uploadedFile = new UploadedFile($this->projectDir.'/'.$file, $filename);
        $this->mediaManager->save($uploadedFile, $data, 1);
    }

    protected function findCollectionIdByKey(string $collectionKey): int
    {
        return $this->collectionManager->getByKey($collectionKey,$this->getLocale())->getId();
    }
}
