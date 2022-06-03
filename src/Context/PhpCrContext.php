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
abstract class PhpCrContext extends DatabaseContext
{
    protected const LOCALE = 'TODO:de';

    protected FormFactoryInterface $formFactory;
    protected DocumentManagerInterface $docManager;
    protected WebspaceManagerInterface $webspaceManager;
    /** @var array<int,string> */
    private static array $documentIdStack = [];
    /** @var array<string,string> */
    private static $documentRouteStack = [];

    public function __construct(EntityManagerInterface $em, DocumentManagerInterface $docManager, FormFactoryInterface $formFactory, WebspaceManagerInterface $webspaceManager)
    {
        parent::__construct($em);
        $this->docManager = $docManager;
        $this->formFactory = $formFactory;
        $this->webspaceManager = $webspaceManager;
    }

    /**
     * @BeforeScenario @sulu
     */
    public function resetDocumentIdStack(): void
    {
        self::$documentIdStack = [];
        self::$documentRouteStack = [];
    }

    /** @param mixed $document */
    protected function saveDocument($document, array $data, string $formType = PageDocumentType::class, ?string $webSpaceKey = null, ?string $parentPath = null): void
    {
        // Bind data to form
        $initialData = [
            // disable csrf protection, since we can't produce a token, because the form is cached on the client
            'csrf_protection' => false,
        ];
        $initialData['webspace_key'] = $webSpaceKey ?? $this->getWebspaceKey();

        if (null !== $parentPath) {
            $parent = $this->getByPath($parentPath);
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
        self::$documentIdStack[] = $document->getUuid();
    }

    protected function addModule(string $moduleName, string $blockName, array $moduleData): void
    {
        $data = [
            $blockName => [
                [
                    'type' => $moduleName,
                ],
            ],
        ];
        foreach ($moduleData as $key => $val) {
            $data[$blockName][0][$key] = $this->isJson($moduleName, $key) ? \json_decode($val, true, 512, JSON_THROW_ON_ERROR) : $val;
        }

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
        if (preg_match('/\[DOCUMENT_ID\[(\d+)]]/', $value, $match)) {
            $value = preg_replace('/\[DOCUMENT_ID\[(\d+)]]/', self::$documentIdStack[(int)$match[1]], $value);
        }

        return $value;
    }

    protected function isJson(string $module, string $field): bool
    {
        // @todo use heuristic instead of convention
        switch ($module.'/'.$field) {
            case 'stage/block_link':
                return true;
            default:
                return false;
        }
    }

    protected function addToRouteStack(string $path, string $uuid): void
    {
        self::$documentRouteStack[$path] = $uuid;
    }

    /** Get a created page by it's path */
    protected function getByPath(string $path): object
    {
        // @rfe find by route via phpcr
        return $this->docManager->find(self::$documentRouteStack[$path]);
    }

    protected function getWebspaceKey(): string
    {
        $webspaces = $this->webspaceManager->getWebspaceCollection()
            ->getWebspaces();
        if (!$webspaces) {
            throw new \DomainException('No webspaces found!');
        }

        return (string) array_keys($webspaces)[0];
    }

    protected function getLocale(): string
    {
        $webspaceKey = $this->getWebspaceKey();
        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);
        if (null === $webspace) {
            throw new \DomainException(sprintf('Webspace %s not found!', $webspaceKey));
        }
        return $webspace->getDefaultLocalization()->getLanguage();
    }

    abstract protected function getLastDocument(): object;
}
