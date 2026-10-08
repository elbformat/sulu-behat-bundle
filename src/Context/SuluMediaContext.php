<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Collection\Manager\CollectionManagerInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Webmozart\Assert\Assert;

/**
 * Simulates the admin part of sulu.
 *
 * @phpstan-import-type InputData from AbstractSuluContext
 */
class SuluMediaContext extends AbstractSuluContext
{
    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        protected MediaManagerInterface $mediaManager,
        protected CollectionManagerInterface $collectionManager,
        protected string $projectDir,
    ) {
        parent::__construct($entityManager, $webspaceManager);
    }

    #[BeforeScenario]
    public function resetDatabase(): void
    {
        $this->exec('DELETE FROM me_media WHERE id >= 1000');
        $this->exec('ALTER TABLE me_media AUTO_INCREMENT=1000');
    }

    #[Given('there is an image in collection :collection')]
    #[Given('there is an image')]
    public function thereIsAnImage(?TableNode $tableNode = null, string $collection = 'sulu_media'): void
    {
        $data = $this->getData($tableNode);
        $data = $this->applyDefaults($data);
        Assert::string($data['file']);
        Assert::string($data['filename']);
        $data['collection'] = $this->findCollectionIdByKey($collection);

        if (!file_exists($this->projectDir.'/'.$data['file'])) {
            throw new \DomainException(\sprintf('Fixture file not found at %s', $this->projectDir.'/'.$data['file']));
        }
        $uploadedFile = new UploadedFile($this->projectDir.'/'.$data['file'], $data['filename']);
        $this->mediaManager->save($uploadedFile, $data, 1);
    }

    protected function findCollectionIdByKey(string $collectionKey): int
    {
        return $this->collectionManager->getByKey($collectionKey, $this->getDefaultLocale())->getId();
    }

    /**
     * @param InputData $data
     *
     * @return InputData
     */
    protected function applyDefaults(array $data): array
    {
        $data['locale'] ??= $this->getDefaultLocale();
        $data['file'] ??= 'tests/fixtures/1px.jpg';
        Assert::string($data['file']);
        $data['filename'] ??= basename($data['file']);

        return $data;
    }
}
