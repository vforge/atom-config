<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

use Doctrine\DBAL\Exception\DriverException;

use PhpIntegrator\Analysis\Conversion\ClasslikeConverter;

use PhpIntegrator\Analysis\Typing\FileStructureListProviderInterface;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

/**
 * Retrieves a list of available structures via Doctrine.
 */
class DoctrineStructureListProvider implements FileStructureListProviderInterface, StructureListProviderInterface
{
    /**
     * @var ClasslikeConverter
     */
    private $classlikeConverter;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ClasslikeConverter $classlikeConverter
     * @param ManagerRegistry    $managerRegistry
     */
    public function __construct(ClasslikeConverter $classlikeConverter, ManagerRegistry $managerRegistry)
    {
        $this->classlikeConverter = $classlikeConverter;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $items = [];
        $result = [];

        try {
            $items = $this->managerRegistry->getRepository(Structures\Structure::class)->findAll();
        } catch (DriverException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        foreach ($items as $element) {
            $result[$element->getFqcn()] = $this->classlikeConverter->convert($element);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getAllForFile(Structures\File $file): array
    {
        try {
            $items = $this->managerRegistry->getRepository(Structures\Structure::class)->findBy([
                'file' => $file
            ]);
        } catch (DriverException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        $result = [];

        foreach ($items as $element) {
            $result[$element->getFqcn()] = $this->classlikeConverter->convert($element);
        }

        return $result;
    }
}
