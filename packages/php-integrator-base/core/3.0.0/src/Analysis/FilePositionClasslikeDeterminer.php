<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Analysis\Typing\FileStructureListProviderInterface;

use PhpIntegrator\Common\Position;

use PhpIntegrator\Indexing\Structures;

/**
 * Determines in which class a position (offset) in a file is located.
 */
class FilePositionClasslikeDeterminer
{
    /**
     * @var FileStructureListProviderInterface
     */
    private $fileStructureListProvider;

    /**
     * @param FileStructureListProviderInterface $fileStructureListProvider
     */
    public function __construct(FileStructureListProviderInterface $fileStructureListProvider)
    {
        $this->fileStructureListProvider = $fileStructureListProvider;
    }

    /**
     * @param Position        $position
     * @param Structures\File $file
     *
     * @return string|null
     */
     public function determine(Position $position, Structures\File $file): ?string
     {
         $classesInFile = $this->fileStructureListProvider->getAllForFile($file);

         foreach ($classesInFile as $fqcn => $classInfo) {
             if ($position->getLine() >= $classInfo['startLine'] && $position->getLine() <= $classInfo['endLine']) {
                 return $fqcn;
             }
         }

         return null;
     }
}
