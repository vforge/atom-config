<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;

/**
 * Keeps track of {@see ExpressionTypeInfo} objects for a set of expressions represented by strings.
 */
class ExpressionTypeInfoMap
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * @param string $expression
     *
     * @return ExpressionTypeInfo
     */
    public function get(string $expression): ExpressionTypeInfo
    {
        $this->createIfNecessary($expression);

        return $this->map[$expression];
    }

    /**
     * @param string $expression
     *
     * @return bool
     */
    public function has(string $expression): bool
    {
        return isset($this->map[$expression]);
    }

    /**
     * @param string    $expression
     * @param Node|null $bestMatch
     *
     * @return void
     */
    public function setBestMatch(string $expression, ?Node $bestMatch): void
    {
        $this->createIfNecessary($expression);

        $this->get($expression)->setBestMatch($bestMatch);
        $this->get($expression)->getTypePossibilityMap()->clear();
    }

    /**
     * @param string $expression
     * @param string $type
     * @param int    $line
     *
     * @return void
     */
    public function setBestTypeOverrideMatch(string $expression, string $type, int $line): void
    {
        $this->createIfNecessary($expression);

        $this->get($expression)->setBestTypeOverrideMatch($type);
        $this->get($expression)->setBestTypeOverrideMatchLine($line);
    }

    /**
     * @param string[] $exclusionList
     *
     * @return void
     */
    public function removeAllExcept(array $exclusionList): void
    {
        $newMap = [];

        foreach ($this->map as $expression => $data) {
            if (in_array($expression, $exclusionList)) {
                $newMap[$expression] = $data;
            }
        }

        $this->map = $newMap;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->map = [];
    }

    /**
     * @param string $expression
     *
     * @return void
     */
    protected function createIfNecessary(string $expression): void
    {
        if ($this->has($expression)) {
            return;
        }

        $this->create($expression);
    }

    /**
     * @param string $expression
     *
     * @return void
     */
    protected function create(string $expression): void
    {
        $this->map[$expression] = new ExpressionTypeInfo();
    }
}
