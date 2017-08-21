<?php

namespace PhpIntegrator\Analysis;

/**
 * Registry that maintains a list of structures.
 */
final class StructureListRegistry implements StructureListProviderInterface
{
    /**
     * @var StructureListProviderInterface
     */
    private $delegate;

    /**
     * @var array
     */
    private $registry;

    /**
     * @param StructureListProviderInterface $delegate
     */
    public function __construct(StructureListProviderInterface $delegate)
    {
        $this->delegate = $delegate;
    }

     /// @inherited
     public function getAll(): array
     {
         return $this->getRegistry();
     }

     /**
      * @param array $structure
      */
     public function add(array $structure): void
     {
         $this->initializeRegistryIfNecessary();

         $this->registry[$structure['fqcn']] = $structure;
     }

     /**
      * @param array $structure
      */
     public function remove(array $structure): void
     {
         $this->initializeRegistryIfNecessary();

         if (isset($this->registry[$structure['fqcn']])) {
             unset($this->registry[$structure['fqcn']]);
         }
     }

     /**
      * @return void
      */
     public function reset(): void
     {
         $this->registry = null;
     }

     /**
      * @return array
      */
     protected function getRegistry(): array
     {
         $this->initializeRegistryIfNecessary();

         return $this->registry;
     }

     /**
      * @return void
      */
     protected function initializeRegistryIfNecessary(): void
     {
         if ($this->registry === null) {
             $this->initializeRegistry();
         }
     }

     /**
      * @return void
      */
     protected function initializeRegistry(): void
     {
         $this->registry = $this->delegate->getAll();
     }
}
