<?php

namespace PhpIntegrator\Analysis\Visiting;

/**
 * Holds possibilities for a list of types.
 */
class TypePossibilityMap
{
    /**
     * A map of conditional types that the item may have.
     *
     * Whenever the item is encountered inside a conditional (if statement, ternary expression, ...), there are certain
     * assumptions that can be made about the type. For example, a check such as "if ($a === null)" clearly states that
     * if the condition passes, the type of the expression must be null. At that point it doesn't matter if the type
     * could previously be "Foo|null", as the conditional has now effectively limited the possible types.
     *
     * @see TypePossibility
     *
     * @var array
     */
    private $map = [];

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->map = [];
    }

    /**
     * @param string $type
     * @param int    $possibility
     *
     * @return static
     */
    public function set(string $type, int $possibility)
    {
        $this->map[$type] = $possibility;
        return $this;
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public function remove(string $type)
    {
        unset($this->map[$type]);
        return $this;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->map;
    }

    /**
     * Retrieves a list of applicable types based on type information.
     *
     * This takes a list of types (that e.g. a variable is supposed to have) and filters out any types that do not apply
     * based on the information from this object. Note that in some cases, the types returned do not necessarily contain
     * any of the types specified in the parameter.
     *
     * @param string[] $typeList
     *
     * @return string[]
     */
    public function determineApplicableTypes(array $typeList): array
    {
        $guaranteedTypes = $this->getAllWithPossibility(TypePossibility::TYPE_GUARANTEED);

        // Types guaranteed by conditionals take precendece over the best match types as if they did not apply, we
        // could never have ended up in the conditional in the first place. However, sometimes conditionals don't
        // know the exact type, but only know that the type must be one in a list of possible types (e.g. in an if
        // statement such as "if (!$a)" $a could still be an int, a float, a string, ...). In this case, the list
        // of conditionals is effectively narrowed down further by the type specified by a best match (i.e. the
        // best match types act as a whitelist for the conditional types).
        $types = array_intersect($guaranteedTypes, $typeList);

        if (empty($types)) {
            if (empty($guaranteedTypes)) {
                $types = $typeList;
            } else {
                // We got inside the if statement, so the type MUST be of one of the guaranteed types. However, if
                // an assignment said that $a is a string and the if statement checks if $a is a bool, in theory we
                // can never end up in the if statement at all as the condition will never pass. Still, for the
                // sake of deducing the type, we choose to return the types guaranteed by the if statement rather
                // than no types at all (as that isn't useful to anyone).
                $types = $guaranteedTypes;
            }
        }

        $impossibleTypes = $this->getAllWithPossibility(TypePossibility::TYPE_IMPOSSIBLE);

        return array_diff($types, $impossibleTypes);
    }

    /**
     * @return string[]
     */
    public function getAllGuaranteed(): array
    {
        return $this->getAllWithPossibility(TypePossibility::TYPE_GUARANTEED);
    }

    /**
     * @return string[]
     */
    public function getAllImpossible(): array
    {
        return $this->getAllWithPossibility(TypePossibility::TYPE_IMPOSSIBLE);
    }

    /**
     * @param int $possibility
     *
     * @return string[]
     */
    protected function getAllWithPossibility(int $possibility): array
    {
        $guaranteedTypes = [];

        foreach ($this->getAll() as $type => $typePossibility) {
            if ($typePossibility === $possibility) {
                $guaranteedTypes[] = $type;
            }
        }

        return $guaranteedTypes;
    }
}
