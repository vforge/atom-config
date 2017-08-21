<?php

namespace PhpIntegrator\Analysis;

use ArrayObject;
use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\StorageInterface;

/**
 * Adapts and resolves data from the index as needed to receive an appropriate output data format.
 */
class ClasslikeInfoBuilder
{
    /**
     * @var Conversion\ConstantConverter
     */
    private $constantConverter;

    /**
     * @var Conversion\ClasslikeConstantConverter
     */
    private $classlikeConstantConverter;

    /**
     * @var Conversion\PropertyConverter
     */
    private $propertyConverter;

    /**
     * @var Conversion\FunctionConverter
     */
    private $functionConverter;

    /**
     * @var Conversion\MethodConverter
     */
    private $methodConverter;

    /**
     * @var Conversion\ClasslikeConverter
     */
    private $classlikeConverter;

    /**
     * @var Relations\InheritanceResolver
     */
    private $inheritanceResolver;

    /**
     * @var Relations\InterfaceImplementationResolver
     */
    private $interfaceImplementationResolver;

    /**
     * @var Relations\TraitUsageResolver
     */
    private $traitUsageResolver;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var Typing\TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var string[]
     */
    private $resolutionStack = [];

    /**
     * @param Conversion\ConstantConverter              $constantConverter
     * @param Conversion\ClasslikeConstantConverter     $classlikeConstantConverter
     * @param Conversion\PropertyConverter              $propertyConverter
     * @param Conversion\FunctionConverter              $functionConverter
     * @param Conversion\MethodConverter                $methodConverter
     * @param Conversion\ClasslikeConverter             $classlikeConverter
     * @param Relations\InheritanceResolver             $inheritanceResolver
     * @param Relations\InterfaceImplementationResolver $interfaceImplementationResolver
     * @param Relations\TraitUsageResolver              $traitUsageResolver
     * @param StorageInterface                          $storage
     * @param Typing\TypeAnalyzer                       $typeAnalyzer
     */
    public function __construct(
        Conversion\ConstantConverter $constantConverter,
        Conversion\ClasslikeConstantConverter $classlikeConstantConverter,
        Conversion\PropertyConverter $propertyConverter,
        Conversion\FunctionConverter $functionConverter,
        Conversion\MethodConverter $methodConverter,
        Conversion\ClasslikeConverter $classlikeConverter,
        Relations\InheritanceResolver $inheritanceResolver,
        Relations\InterfaceImplementationResolver $interfaceImplementationResolver,
        Relations\TraitUsageResolver $traitUsageResolver,
        StorageInterface $storage,
        Typing\TypeAnalyzer $typeAnalyzer
    ) {
        $this->constantConverter = $constantConverter;
        $this->classlikeConstantConverter = $classlikeConstantConverter;
        $this->propertyConverter = $propertyConverter;
        $this->functionConverter = $functionConverter;
        $this->methodConverter = $methodConverter;
        $this->classlikeConverter = $classlikeConverter;

        $this->inheritanceResolver = $inheritanceResolver;
        $this->interfaceImplementationResolver = $interfaceImplementationResolver;
        $this->traitUsageResolver = $traitUsageResolver;

        $this->storage = $storage;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * Retrieves information about the specified structural element.
     *
     * @param string $fqcn
     *
     * @throws UnexpectedValueException
     * @throws CircularDependencyException
     *
     * @return array
     */
    public function getClasslikeInfo(string $fqcn): array
    {
        $this->resolutionStack = [];

        return $this->getCheckedClasslikeInfo($fqcn, '')->getArrayCopy();
    }

    /**
     * @param string $fqcn
     * @param string $originFqcn
     *
     * @throws CircularDependencyException
     *
     * @return ArrayObject
     */
    protected function getCheckedClasslikeInfo(string $fqcn, string $originFqcn): ArrayObject
    {
        if (in_array($fqcn, $this->resolutionStack)) {
            throw new CircularDependencyException("Circular dependency detected from {$originFqcn} to {$fqcn}!");
        }

        $this->resolutionStack[] = $fqcn;

        $data = $this->getUncheckedClasslikeInfo($fqcn);

        array_pop($this->resolutionStack);

        return $data;
    }

    /**
     * @param string $fqcn
     *
     * @throws UnexpectedValueException
     *
     * @return ArrayObject
     */
    protected function getUncheckedClasslikeInfo(string $fqcn): ArrayObject
    {
        $structure = $this->storage->findStructureByFqcn($fqcn);

        if (!$structure) {
            throw new UnexpectedValueException('The structural element "' . $fqcn . '" was not found!');
        }

        return $this->fetchFlatClasslikeInfo($structure);
    }

    /**
     * Builds information about a classlike in a flat structure, meaning it doesn't resolve any inheritance or interface
     * implementations. Instead, it will only list members and data directly relevant to the classlike.
     *
     * @param Structures\Structure $structure
     *
     * @return ArrayObject
     */
    protected function fetchFlatClasslikeInfo(Structures\Structure $structure): ArrayObject
    {
        $classlike = new ArrayObject($this->classlikeConverter->convert($structure) + [
            'parents'            => [],
            'interfaces'         => [],
            'traits'             => [],

            'directParents'      => [],
            'directInterfaces'   => [],
            'directTraits'       => [],
            'directChildren'     => [],
            'directImplementors' => [],
            'directTraitUsers'   => [],

            'constants'          => [],
            'properties'         => [],
            'methods'            => []
        ]);

        $this->buildDirectChildrenInfo($classlike, $structure);
        $this->buildDirectImplementorsInfo($classlike, $structure);
        $this->buildTraitUsersInfo($classlike, $structure);
        $this->buildConstantsInfo($classlike, $structure);
        $this->buildPropertiesInfo($classlike, $structure);
        $this->buildMethodsInfo($classlike, $structure);
        $this->buildTraitsInfo($classlike, $structure);

        $this->resolveNormalTypes($classlike);
        $this->resolveSelfTypesTo($classlike, $classlike['fqcn']);

        $this->buildParentsInfo($classlike, $structure);
        $this->buildInterfacesInfo($classlike, $structure);

        $this->resolveStaticTypesTo($classlike, $classlike['fqcn']);

        return $classlike;
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildDirectChildrenInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        if (!$structure instanceof Structures\Class_ && !$structure instanceof Structures\Interface_) {
            return;
        }

        foreach ($structure->getChildFqcns() as $childFqcn) {
            $classlike['directChildren'][] = $childFqcn;
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildDirectImplementorsInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        if (!$structure instanceof Structures\Interface_) {
            return;
        }

        foreach ($structure->getImplementorFqcns() as $implementorFqcn) {
            $classlike['directImplementors'][] = $implementorFqcn;
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildTraitUsersInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        if (!$structure instanceof Structures\Trait_) {
            return;
        }

        foreach ($structure->getTraitUserFqcns() as $traitUserFqcn) {
            $classlike['directTraitUsers'][] = $traitUserFqcn;
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildConstantsInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        foreach ($structure->getConstants() as $constant) {
            $classlike['constants'][$constant->getName()] = $this->classlikeConstantConverter->convertForClass(
                $constant,
                $classlike
            );
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildPropertiesInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        foreach ($structure->getProperties() as $property) {
            $classlike['properties'][$property->getName()] = $this->propertyConverter->convertForClass(
                $property,
                $classlike
            );
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildMethodsInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        foreach ($structure->getMethods() as $method) {
            $classlike['methods'][$method->getName()] = $this->methodConverter->convertForClass($method, $classlike);
        }
    }

    /**
     * @param ArrayObject         $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildTraitsInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        if (!$structure instanceof Structures\Class_ && !$structure instanceof Structures\Trait_) {
            return;
        }

        foreach ($structure->getTraitFqcns() as $traitFqcn) {
            $classlike['traits'][] = $traitFqcn;
            $classlike['directTraits'][] = $traitFqcn;

            try {
                $traitInfo = $this->getCheckedClasslikeInfo($traitFqcn, $classlike['fqcn']);
            } catch (UnexpectedValueException|CircularDependencyException $e) {
                continue;
            }

            $this->traitUsageResolver->resolveUseOf(
                $traitInfo,
                $classlike,
                $structure->getTraitAliases(),
                $structure->getTraitPrecedences()
            );
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildParentsInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        $parentFqcns = [];

        if (!$structure instanceof Structures\Class_ && !$structure instanceof Structures\Interface_) {
            return;
        } elseif ($structure instanceof Structures\Class_) {
            $parentFqcns = array_filter([$structure->getParentFqcn()]);
        } else {
            $parentFqcns = $structure->getParentFqcns();
        }

        foreach ($parentFqcns as $parentFqcn) {
            $classlike['parents'][] = $parentFqcn;
            $classlike['directParents'][] = $parentFqcn;

            try {
                $parentInfo = $this->getCheckedClasslikeInfo($parentFqcn, $classlike['fqcn']);
            } catch (UnexpectedValueException|CircularDependencyException $e) {
                continue;
            }

            $this->inheritanceResolver->resolveInheritanceOf($parentInfo, $classlike);
        }
    }

    /**
     * @param ArrayObject          $classlike
     * @param Structures\Structure $structure
     *
     * @return void
     */
    protected function buildInterfacesInfo(ArrayObject $classlike, Structures\Structure $structure): void
    {
        if (!$structure instanceof Structures\Class_) {
            return;
        }

        foreach ($structure->getInterfaceFqcns() as $interfaceFqcn) {
            $classlike['interfaces'][] = $interfaceFqcn;
            $classlike['directInterfaces'][] = $interfaceFqcn;

            try {
                $interfaceInfo = $this->getCheckedClasslikeInfo($interfaceFqcn, $classlike['fqcn']);
            } catch (UnexpectedValueException|CircularDependencyException $e) {
                continue;
            }

            $this->interfaceImplementationResolver->resolveImplementationOf($interfaceInfo, $classlike);
        }
    }

    /**
     * @param ArrayObject $result
     * @param string      $elementFqcn
     *
     * @return void
     */
    protected function resolveSelfTypesTo(ArrayObject $result, $elementFqcn): void
    {
        $typeAnalyzer = $this->typeAnalyzer;

        $this->walkTypes($result, function (array &$type) use ($elementFqcn, $typeAnalyzer) {
            if ($type['resolvedType'] !== null) {
                $type['resolvedType'] = $typeAnalyzer->interchangeSelfWithActualType($type['resolvedType'], $elementFqcn);
            }
        });
    }

    /**
     * @param ArrayObject $result
     * @param string      $elementFqcn
     *
     * @return void
     */
    protected function resolveStaticTypesTo(ArrayObject $result, $elementFqcn): void
    {
        $typeAnalyzer = $this->typeAnalyzer;

        $this->walkTypes($result, function (array &$type) use ($elementFqcn, $typeAnalyzer) {
            $replacedThingy = $typeAnalyzer->interchangeStaticWithActualType($type['type'], $elementFqcn);
            $replacedThingy = $typeAnalyzer->interchangeThisWithActualType($replacedThingy, $elementFqcn);

            if ($type['type'] !== $replacedThingy) {
                $type['resolvedType'] = $replacedThingy;
            }
        });
    }

    /**
     * @param ArrayObject $result
     *
     * @return void
     */
    protected function resolveNormalTypes(ArrayObject $result): void
    {
        $typeAnalyzer = $this->typeAnalyzer;

        $this->walkTypes($result, function (array &$type) use ($typeAnalyzer) {
            if ($type['fqcn'] !== null && $typeAnalyzer->isClassType($type['fqcn'])) {
                $type['resolvedType'] = $typeAnalyzer->getNormalizedFqcn($type['fqcn']);
            } else {
                $type['resolvedType'] = $type['fqcn'];
            }
        });
    }

    /**
     * @param ArrayObject $result
     * @param callable    $callable
     *
     * @return void
     */
    protected function walkTypes(ArrayObject $result, callable $callable): void
    {
        foreach ($result['methods'] as $name => &$method) {
            foreach ($method['parameters'] as &$parameter) {
                foreach ($parameter['types'] as &$type) {
                    $callable($type);
                }
            }

            foreach ($method['returnTypes'] as &$returnType) {
                $callable($returnType);
            }
        }

        foreach ($result['properties'] as $name => &$property) {
            foreach ($property['types'] as &$type) {
                $callable($type);
            }
        }

        foreach ($result['constants'] as $name => &$constants) {
            foreach ($constants['types'] as &$type) {
                $callable($type);
            }
        }
    }
}
