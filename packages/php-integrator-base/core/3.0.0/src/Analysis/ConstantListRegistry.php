<?php

namespace PhpIntegrator\Analysis;

/**
 * Registry that maintains a list of (global) constants.
 */
final class ConstantListRegistry implements ConstantListProviderInterface
{
    /**
     * @var ConstantListProviderInterface
     */
    private $delegate;

    /**
     * @var array
     */
    private $registry;

    /**
     * @param ConstantListProviderInterface $delegate
     */
    public function __construct(ConstantListProviderInterface $delegate)
    {
        $this->delegate = $delegate;
    }

     /// @inherited
     public function getAll(): array
     {
         return $this->getRegistry();
     }

     /**
      * @param array $function
      */
     public function add(array $function): void
     {
         $this->initializeRegistryIfNecessary();

         $this->registry[$function['fqcn']] = $function;
     }

     /**
      * @param array $function
      */
     public function remove(array $function): void
     {
         $this->initializeRegistryIfNecessary();

         if (isset($this->registry[$function['fqcn']])) {
             unset($this->registry[$function['fqcn']]);
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
