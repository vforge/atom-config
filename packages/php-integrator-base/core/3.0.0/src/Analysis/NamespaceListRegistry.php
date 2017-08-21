<?php

namespace PhpIntegrator\Analysis;

/**
 * Registry that maintains a list of namespaces.
 */
final class NamespaceListRegistry implements NamespaceListProviderInterface
{
    /**
     * @var NamespaceListProviderInterface
     */
    private $delegate;

    /**
     * @var array
     */
    private $registry;

    /**
     * @param NamespaceListProviderInterface $delegate
     */
    public function __construct(NamespaceListProviderInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /// @inherited
    public function getAll(): array
    {
        return $this->getRegistry();
    }

    /**
     * @param array $namespace
     */
    public function add(array $namespace): void
    {
        $this->initializeRegistryIfNecessary();

        $index = array_search($namespace, $this->registry, true);

        if ($index !== false) {
            $this->registry[$index] = $namespace;
        } else {
            $this->registry[] = $namespace;
        }
    }

    /**
     * @param array $namespace
     */
    public function remove(array $namespace): void
    {
        $this->initializeRegistryIfNecessary();

        $index = array_search($namespace, $this->registry, true);

        if ($index !== false) {
            unset($this->registry[$index]);

            // Ensure integral indices remain sequential.
            $this->registry = array_values($this->registry);
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
