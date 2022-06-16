<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Context;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\PageBundle\Form\Type\PageDocumentType;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Handles internal phpcr parts. Used by Sulu Content and Articles.
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
abstract class AbstractPhpCrContext extends AbstractSuluContext
{
    protected FormFactoryInterface $formFactory;
    protected DocumentManagerInterface $docManager;

    public function __construct(EntityManagerInterface $em, WebspaceManagerInterface $webspaceManager, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory)
    {
        parent::__construct($em, $webspaceManager);
        $this->docManager = $docManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @BeforeScenario
     */
    public function resetDatabase(): void
    {
        // Clear database from all new created entries and reset auto_increment to 1000
        $this->exec('DELETE FROM phpcr_nodes WHERE id >= 1000');
        $this->exec('ALTER TABLE phpcr_nodes AUTO_INCREMENT=1000');
        $this->exec('DELETE FROM phpcr_nodes_references WHERE source_id >= 1000 OR target_id >= 1000');

        // Remove references from initial entries (like home)
        $this->exec("UPDATE phpcr_nodes set props = REGEXP_REPLACE(props, '<sv.+settings:snippets-.+<\/sv\:property>', '')");
    }

    /** @param mixed $document */
    protected function saveDocument($document, array $data, string $formType = PageDocumentType::class, ?bool $hasWebSpaceKey = null, ?int $parentId = null): void
    {
        // Bind data to form
        $initialData = [
            // disable csrf protection, since we can't produce a token, because the form is cached on the client
            'csrf_protection' => false,
        ];
        if ($hasWebSpaceKey ?? PageDocumentType::class === $formType) {
            $initialData['webspace_key'] = $this->getWebspaceKey();
        }

        if (null !== $parentId) {
            $parent = $this->docManager->find($this->getIdentifierFromId($parentId));
            $document->setParent($parent);
        }

        $form = $this->formFactory->create($formType, $document, $initialData);
        $form->submit($data, false);

        // Save updated document
        $persistOptions = [
            'clear_missing_content' => false,
        ];
        $this->docManager->persist($document, $this->getLocale(), $persistOptions);
        $this->docManager->publish($document, $this->getLocale());
        $this->docManager->flush();
    }

    protected function addModule(string $moduleName, string $blockName, array $moduleData): void
    {
        $data[$blockName][0] = $moduleData;
        $data[$blockName][0]['type'] = $moduleName;

        $this->saveDocument($this->getLastDocument(), $data);
    }

    /** Convert data with dot notation into nested array structure */
    protected function expandData(array $data): array
    {
        $newData = [];
        foreach ($data as $k => $v) {
            // Plain key
            if (false === strpos((string)$k, '.')) {
                $newData[$k] = $this->replacePlaceholders((string)$v);
                continue;
            }
            $parts = explode('.', (string)$k, 2);
            $deepStructure = $this->expandData([$parts[1] => $v]);
            $newData[$parts[0]] = array_merge_recursive($newData[$parts[0]] ?? [], $deepStructure);
        }

        return $newData;
    }

    protected function replacePlaceholders(string $value): string
    {
        if (preg_match('/IDENTIFIER\[(\d+)]/', $value, $match)) {
            $value = preg_replace('/IDENTIFIER\[(\d+)]/', $this->getIdentifierFromId((int) $match[1]), $value);
        }

        return $value;
    }

    protected function getIdentifierFromId(int $id): string
    {
        return $this->em->getConnection()->fetchOne('SELECT identifier FROM phpcr_nodes WHERE id=:id',['id' => $id]);
    }

    abstract protected function getLastDocument(): object;
}
