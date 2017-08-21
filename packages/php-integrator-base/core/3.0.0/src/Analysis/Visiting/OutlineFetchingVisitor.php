<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpIntegrator\Analysis\Typing\TypeNormalizerInterface;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Node visitor that fetches the outline of a file, creating a list of structural elements (classes, interfaces, ...)
 * with their direct methods, properties, constants, and so on.
 */
class OutlineFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $structures = [];

    /**
     * @var array
     */
    private $globalFunctions = [];

    /**
     * @var array
     */
    private $globalConstants = [];

    /**
     * @var array
     */
    private $globalDefines = [];

    /**
     * @var Node\Stmt\Class_|null
     */
    private $currentStructure;

    /**
     * @var TypeNormalizerInterface
     */
    private $typeNormalizer;

    /**
     * @var string
     */
    private $code;

    /**
     * @param TypeNormalizerInterface $typeNormalizer
     * @param string                  $code
     */
    public function __construct(TypeNormalizerInterface $typeNormalizer, string $code)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if ($node instanceof Node\Stmt\Property) {
            $this->parseClassPropertyNode($node);
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            $this->parseClassMethodNode($node);
        } elseif ($node instanceof Node\Stmt\ClassConst) {
            $this->parseClassConstantNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->parseFunctionNode($node);
        } elseif ($node instanceof Node\Stmt\Const_) {
            $this->parseConstantNode($node);
        } elseif ($node instanceof Node\Stmt\Class_) {
            if ($node->isAnonymous()) {
                // Ticket #45 - Skip PHP 7 anonymous classes.
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            $this->parseClassNode($node);
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $this->parseInterfaceNode($node);
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $this->parseTraitNode($node);
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->parseTraitUseNode($node);
        } elseif (
            $node instanceof Node\Expr\FuncCall &&
            $node->name instanceof Node\Name &&
            $node->name->toString() === 'define'
        ) {
            $this->parseDefineNode($node);
        }
    }

    /**
     * @param Node\Stmt\Class_ $node
     *
     * @return void
     */
    protected function parseClassNode(Node\Stmt\Class_ $node): void
    {
        $this->currentStructure = $node;

        $interfaces = [];

        foreach ($node->implements as $implementedName) {
            $interfaces[] = NodeHelpers::fetchClassName($implementedName->getAttribute('resolvedName'));
        }

        $fqcn = $this->typeNormalizer->getNormalizedFqcn($node->namespacedName->toString());

        $this->structures[$fqcn] = [
            'name'           => $node->name->name,
            'fqcn'           => $fqcn,
            'type'           => 'class',
            'startLine'      => $node->getLine(),
            'endLine'        => $node->getAttribute('endLine'),
            'startPosName'   => $node->name->getAttribute('startFilePos') ? $node->name->getAttribute('startFilePos') : null,
            'endPosName'     => $node->name->getAttribute('endFilePos') ? ($node->name->getAttribute('endFilePos') + 1) : null,
            'isAbstract'     => $node->isAbstract(),
            'isFinal'        => $node->isFinal(),
            'docComment'     => $node->getDocComment() ? $node->getDocComment()->getText() : null,
            'parents'        => $node->extends ? [NodeHelpers::fetchClassName($node->extends->getAttribute('resolvedName'))] : [],
            'interfaces'     => $interfaces,
            'traits'         => [],
            'methods'        => [],
            'properties'     => [],
            'constants'      => []
        ];
    }

    /**
     * @param Node\Stmt\Interface_ $node
     *
     * @return void
     */
    protected function parseInterfaceNode(Node\Stmt\Interface_ $node): void
    {
        if (!isset($node->namespacedName)) {
            return;
        }

        $this->currentStructure = $node;

        $extendedInterfaces = [];

        foreach ($node->extends as $extends) {
            $extendedInterfaces[] = NodeHelpers::fetchClassName($extends->getAttribute('resolvedName'));
        }

        $fqcn = $this->typeNormalizer->getNormalizedFqcn($node->namespacedName->toString());

        $this->structures[$fqcn] = [
            'name'           => $node->name->name,
            'fqcn'           => $fqcn,
            'type'           => 'interface',
            'startLine'      => $node->getLine(),
            'endLine'        => $node->getAttribute('endLine'),
            'startPosName'   => $node->name->getAttribute('startFilePos') ? $node->name->getAttribute('startFilePos') : null,
            'endPosName'     => $node->name->getAttribute('endFilePos') ? ($node->name->getAttribute('endFilePos') + 1) : null,
            'parents'        => $extendedInterfaces,
            'docComment'     => $node->getDocComment() ? $node->getDocComment()->getText() : null,
            'traits'         => [],
            'methods'        => [],
            'properties'     => [],
            'constants'      => []
        ];
    }

    /**
     * @param Node\Stmt\Trait_ $node
     *
     * @return void
     */
    protected function parseTraitNode(Node\Stmt\Trait_ $node): void
    {
        if (!isset($node->namespacedName)) {
            return;
        }

        $this->currentStructure = $node;

        $fqcn = $this->typeNormalizer->getNormalizedFqcn($node->namespacedName->toString());

        $this->structures[$fqcn] = [
            'name'           => $node->name->name,
            'fqcn'           => $fqcn,
            'type'           => 'trait',
            'startLine'      => $node->getLine(),
            'endLine'        => $node->getAttribute('endLine'),
            'startPosName'   => $node->name->getAttribute('startFilePos') ? $node->name->getAttribute('startFilePos') : null,
            'endPosName'     => $node->name->getAttribute('endFilePos') ? ($node->name->getAttribute('endFilePos') + 1) : null,
            'docComment'     => $node->getDocComment() ? $node->getDocComment()->getText() : null,
            'methods'        => [],
            'properties'     => [],
            'constants'      => []
        ];
    }

    /**
     * @param Node\Stmt\TraitUse $node
     *
     * @return void
     */
    protected function parseTraitUseNode(Node\Stmt\TraitUse $node): void
    {
        $fqcn = $this->typeNormalizer->getNormalizedFqcn($this->currentStructure->namespacedName->toString());

        foreach ($node->traits as $traitName) {
            $this->structures[$fqcn]['traits'][] =
                NodeHelpers::fetchClassName($traitName->getAttribute('resolvedName'));
        }

        foreach ($node->adaptations as $adaptation) {
            if ($adaptation instanceof Node\Stmt\TraitUseAdaptation\Alias) {
                $this->structures[$fqcn]['traitAliases'][] = [
                    'name'                       => $adaptation->method,
                    'alias'                      => $adaptation->newName,
                    'trait'                      => $adaptation->trait ? NodeHelpers::fetchClassName($adaptation->trait->getAttribute('resolvedName')) : null,
                    'isPublic'                   => ($adaptation->newModifier === 1),
                    'isPrivate'                  => ($adaptation->newModifier === 4),
                    'isProtected'                => ($adaptation->newModifier === 2),
                    'isInheritingAccessModifier' => ($adaptation->newModifier === null)
                ];
            } elseif ($adaptation instanceof Node\Stmt\TraitUseAdaptation\Precedence) {
                $this->structures[$fqcn]['traitPrecedences'][] = [
                    'name'  => $adaptation->method,
                    'trait' => NodeHelpers::fetchClassName($adaptation->trait->getAttribute('resolvedName'))
                ];
            }
        }
    }

    /**
     * @param Node\Stmt\Property $node
     *
     * @return void
     */
    protected function parseClassPropertyNode(Node\Stmt\Property $node): void
    {
        $fqcn = $this->typeNormalizer->getNormalizedFqcn($this->currentStructure->namespacedName->toString());

        foreach ($node->props as $property) {
            $this->structures[$fqcn]['properties'][$property->name->name] = [
                'name'             => $property->name->name,
                'startLine'        => $property->getLine(),
                'endLine'          => $property->getAttribute('endLine'),
                'startPosName'     => $property->name->getAttribute('startFilePos') ? $property->name->getAttribute('startFilePos') : null,
                'endPosName'       => $property->name->getAttribute('endFilePos') ? ($property->name->getAttribute('endFilePos') + 1) : null,
                'isPublic'         => $node->isPublic(),
                'isPrivate'        => $node->isPrivate(),
                'isStatic'         => $node->isStatic(),
                'isProtected'      => $node->isProtected(),
                'docComment'       => $node->getDocComment() ? $node->getDocComment()->getText() : null,
                'defaultValueNode' => $property->default,

                'defaultValue' => $property->default ?
                    substr(
                        $this->code,
                        $property->default->getAttribute('startFilePos'),
                        $property->default->getAttribute('endFilePos') - $property->default->getAttribute('startFilePos') + 1
                    ) :
                    null
            ];
        }
    }

    /**
     * @param Node\Stmt\Function_ $node
     *
     * @return void
     */
    protected function parseFunctionNode(Node\Stmt\Function_ $node): void
    {
        $data = $this->extractFunctionLikeNodeData($node);

        $fqcn = $this->typeNormalizer->getNormalizedFqcn(
            isset($node->namespacedName) ? $node->namespacedName->toString() : $node->name->name
        );

        $this->globalFunctions[$fqcn] = $data + [
            'fqcn' => $fqcn
        ];
    }

    /**
     * @param Node\Stmt\ClassMethod $node
     *
     * @return void
     */
    protected function parseClassMethodNode(Node\Stmt\ClassMethod $node): void
    {
        if (!isset($this->currentStructure->namespacedName)) {
            return;
        }

        $fqcn = $this->typeNormalizer->getNormalizedFqcn($this->currentStructure->namespacedName->toString());

        $this->structures[$fqcn]['methods'][$node->name->name] = $this->extractFunctionLikeNodeData($node) + [
            'isPublic'       => $node->isPublic(),
            'isPrivate'      => $node->isPrivate(),
            'isProtected'    => $node->isProtected(),
            'isAbstract'     => $node->isAbstract(),
            'isFinal'        => $node->isFinal(),
            'isStatic'       => $node->isStatic()
        ];
    }

    /**
     * @param Node\FunctionLike $node
     *
     * @return array
     */
    protected function extractFunctionLikeNodeData(Node\FunctionLike $node): array
    {
        $parameters = [];

        foreach ($node->getParams() as $i => $param) {
            $localType = null;
            $resolvedType = null;

            $typeNode = $param->type;

            if ($typeNode instanceof Node\NullableType) {
                $typeNode = $typeNode->type;
            }

            if ($typeNode instanceof Node\Name) {
                $localType = NodeHelpers::fetchClassName($typeNode);
                $resolvedType = NodeHelpers::fetchClassName($typeNode->getAttribute('resolvedName'));
            } elseif ($typeNode instanceof Node\Identifier) {
                $localType = $typeNode->name;
                $resolvedType = $typeNode->name;
            }

            $parameters[$i] = [
                'name'             => $param->var->name,
                'type'             => $localType,
                'fullType'         => $resolvedType,
                'isReference'      => $param->byRef,
                'isVariadic'       => $param->variadic,
                'isOptional'       => $param->default ? true : false,
                'defaultValueNode' => $param->default,

                'isNullable'   => (
                    ($param->type instanceof Node\NullableType) ||
                    ($param->default instanceof Node\Expr\ConstFetch && $param->default->name->toString() === 'null')
                ),

                'defaultValue' => $param->default ?
                    substr(
                        $this->code,
                        $param->default->getAttribute('startFilePos'),
                        $param->default->getAttribute('endFilePos') - $param->default->getAttribute('startFilePos') + 1
                    ) :
                    null
            ];
        }

        $localType = null;
        $resolvedType = null;
        $nodeType = $node->getReturnType();

        if ($nodeType instanceof Node\NullableType) {
            $nodeType = $nodeType->type;
        }

        if ($nodeType instanceof Node\Name) {
            $localType = NodeHelpers::fetchClassName($nodeType);
            $resolvedType = NodeHelpers::fetchClassName($nodeType->getAttribute('resolvedName'));
        } elseif ($nodeType instanceof Node\Identifier) {
            $localType = $nodeType->name;
            $resolvedType = $nodeType->name;
        }

        return [
            'name'                 => $node->name->name,
            'startLine'            => $node->getLine(),
            'endLine'              => $node->getAttribute('endLine'),
            'startPosName'         => $node->name->getAttribute('startFilePos') ? $node->name->getAttribute('startFilePos') : null,
            'endPosName'           => $node->name->getAttribute('endFilePos') ? ($node->name->getAttribute('endFilePos') + 1) : null,
            'returnType'           => $localType,
            'fullReturnType'       => $resolvedType,
            'isReturnTypeNullable' => ($node->getReturnType() instanceof Node\NullableType),
            'parameters'           => $parameters,
            'docComment'           => $node->getDocComment() ? $node->getDocComment()->getText() : null
        ];
    }

    /**
     * @param Node\Stmt\ClassConst $node
     *
     * @return void
     */
    protected function parseClassConstantNode(Node\Stmt\ClassConst $node): void
    {
        $fqcn = $this->typeNormalizer->getNormalizedFqcn($this->currentStructure->namespacedName->toString());

        foreach ($node->consts as $const) {
            $this->structures[$fqcn]['constants'][$const->name->name] = [
                'name'             => $const->name->name,
                'startLine'        => $const->getLine(),
                'endLine'          => $const->getAttribute('endLine'),
                'startPosName'     => $const->name->getAttribute('startFilePos') ? $const->name->getAttribute('startFilePos') : null,
                'endPosName'       => $const->name->getAttribute('endFilePos') ? ($const->name->getAttribute('endFilePos') + 1) : null,
                'docComment'       => $node->getDocComment() ? $node->getDocComment()->getText() : null,
                'isPublic'         => $node->isPublic(),
                'isPrivate'        => $node->isPrivate(),
                'isProtected'      => $node->isProtected(),
                'defaultValueNode' => $const->value,

                'defaultValue' => substr(
                    $this->code,
                    $const->value->getAttribute('startFilePos'),
                    $const->value->getAttribute('endFilePos') - $const->value->getAttribute('startFilePos') + 1
                )
            ];
        }
    }

    /**
     * @param Node\Stmt\Const_ $node
     *
     * @return void
     */
    protected function parseConstantNode(Node\Stmt\Const_ $node): void
    {
        foreach ($node->consts as $const) {
            $fqcn = $this->typeNormalizer->getNormalizedFqcn(
                isset($const->namespacedName) ? $const->namespacedName->toString() : $const->name
            );

            $this->globalConstants[$fqcn] = [
                'name'             => $const->name,
                'fqcn'             => $fqcn,
                'startLine'        => $const->getLine(),
                'endLine'          => $const->getAttribute('endLine'),
                'startPosName'     => $const->name->getAttribute('startFilePos') ? $const->name->getAttribute('startFilePos') : null,
                'endPosName'       => $const->name->getAttribute('endFilePos') ? $const->name->getAttribute('endFilePos') : null,
                'docComment'       => $node->getDocComment() ? $node->getDocComment()->getText() : null,
                'defaultValueNode' => $const->value,

                'defaultValue' => substr(
                    $this->code,
                    $const->value->getAttribute('startFilePos'),
                    $const->value->getAttribute('endFilePos') - $const->value->getAttribute('startFilePos') + 1
                )
            ];
        }
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @return void
     */
    protected function parseDefineNode(Node\Expr\FuncCall $node): void
    {
        if (count($node->args) < 2) {
            return;
        }

        $nameValue = $node->args[0]->value;

        if (!$nameValue instanceof Node\Scalar\String_) {
            return;
        }

        // Defines can be namespaced if their name contains slashes, see also
        // https://php.net/manual/en/function.define.php#90282
        $name = new Node\Name((string) $nameValue->value);

        $fqcn = $this->typeNormalizer->getNormalizedFqcn($name->toString());

        $this->globalDefines[$fqcn] = [
            'name'             => $name->getLast(),
            'fqcn'             => $fqcn,
            'startLine'        => $node->getLine(),
            'endLine'          => $node->getAttribute('endLine'),
            'startPosName'     => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos') : null,
            'endPosName'       => $node->getAttribute('endFilePos') ? $node->getAttribute('endFilePos') : null,
            'docComment'       => $node->getDocComment() ? $node->getDocComment()->getText() : null,
            'defaultValueNode' => $node->args[1],

            'defaultValue' => substr(
                $this->code,
                $node->args[1]->getAttribute('startFilePos'),
                $node->args[1]->getAttribute('endFilePos') - $node->args[1]->getAttribute('startFilePos') + 1
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        if ($this->currentStructure === $node) {
            $this->currentStructure = null;
        }
    }

    /**
     * Retrieves the list of structural elements.
     *
     * @return array
     */
    public function getStructures(): array
    {
        return $this->structures;
    }

    /**
     * Retrieves the list of (global) functions.
     *
     * @return array
     */
    public function getGlobalFunctions(): array
    {
        return $this->globalFunctions;
    }

    /**
     * Retrieves the list of (global) constants.
     *
     * @return array
     */
    public function getGlobalConstants(): array
    {
        return $this->globalConstants;
    }

    /**
     * Retrieves the list of (global) defines.
     *
     * @return array
     */
    public function getGlobalDefines(): array
    {
        return $this->globalDefines;
    }
}
