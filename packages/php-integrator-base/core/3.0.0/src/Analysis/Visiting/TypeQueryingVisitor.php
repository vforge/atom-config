<?php

namespace PhpIntegrator\Analysis\Visiting;

use DomainException;

use PhpIntegrator\Parsing\DocblockParser;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinterAbstract;

/**
 * Visitor that walks to a specific position, building a list of information about variables and their possible and
 * guaranteed types.
 */
class TypeQueryingVisitor extends NodeVisitorAbstract
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var ExpressionTypeInfoMap
     */
    private $expressionTypeInfoMap;

    /**
     * @var PrettyPrinterAbstract
     */
    private $prettyPrinter;

    /**
     * Constructor.
     *
     * @param DocblockParser        $docblockParser
     * @param PrettyPrinterAbstract $prettyPrinter
     * @param int                   $position
     */
    public function __construct(DocblockParser $docblockParser, PrettyPrinterAbstract $prettyPrinter, int $position)
    {
        $this->docblockParser = $docblockParser;
        $this->prettyPrinter = $prettyPrinter;
        $this->position = $position;

        $this->expressionTypeInfoMap = new ExpressionTypeInfoMap();
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $startFilePos = $node->getAttribute('startFilePos');

        if ($startFilePos >= $this->position) {
            if ($startFilePos == $this->position) {
                // We won't analyze this node anymore (it falls outside the position and can cause infinite recursion
                // otherwise), but php-parser matches each docblock with the next node. That docblock might still
                // contain a type override annotation we need to parse.
                $this->parseNodeDocblock($node);
            }

            // We've gone beyond the requested position, there is nothing here that can still be relevant anymore.
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        $this->parseNodeDocblock($node);

        if ($node instanceof Node\Stmt\Catch_) {
            $this->parseCatch($node);
        } elseif ($node instanceof Node\Stmt\If_ ||
            $node instanceof Node\Stmt\ElseIf_ ||
            $node instanceof Node\Expr\Ternary
        ) {
            $this->parseConditional($node);
        } elseif ($node instanceof Node\Expr\Assign) {
            $this->parseAssignment($node);
        } elseif ($node instanceof Node\Stmt\Foreach_) {
            $this->parseForeach($node);
        }

        $this->checkForScopeChange($node);
    }

    /**
     * @param Node\Stmt\Catch_ $node
     *
     * @return void
     */
    protected function parseCatch(Node\Stmt\Catch_ $node): void
    {
        $this->expressionTypeInfoMap->setBestMatch('$' . $node->var->name, $node);
    }

    /**
     * @param Node\Stmt\If_|Node\Stmt\ElseIf_|Node\Expr\Ternary $node
     *
     * @return void
     */
    protected function parseConditional(NodeAbstract $node): void
    {
        // There can be conditional expressions inside the current scope (think variables assigned to a ternary
        // expression). In that case we don't want to actually look at the condition for type deduction unless
        // we're inside the scope of that conditional.
        if ($this->position < $node->getAttribute('startFilePos') ||
            $this->position > $node->getAttribute('endFilePos')
        ) {
            return;
        }

        $typeData = $this->parseCondition($node->cond);

        foreach ($typeData as $variable => $typePossibilityMap) {
            $existingTypePossibilityMap = $this->expressionTypeInfoMap->get($variable)->getTypePossibilityMap();
            $existingGuaranteedTypes = $existingTypePossibilityMap->getAllGuaranteed();

            $newGuaranteedTypes = $typePossibilityMap->determineApplicableTypes($existingGuaranteedTypes);

            foreach ($existingGuaranteedTypes as $existingGuaranteedType) {
                $existingTypePossibilityMap->remove($existingGuaranteedType);
            }

            foreach ($newGuaranteedTypes as $newGuaranteedType) {
                $existingTypePossibilityMap->set($newGuaranteedType, TypePossibility::TYPE_GUARANTEED);
            }

            foreach ($typePossibilityMap->getAllImpossible() as $impossibleType) {
                $existingTypePossibilityMap->set($impossibleType, TypePossibility::TYPE_IMPOSSIBLE);
            }
        }
    }

    /**
     * @param Node\Expr\Assign $node
     *
     * @return void
     */
    protected function parseAssignment(Node\Expr\Assign $node): void
    {
        if ($node->getAttribute('endFilePos') > $this->position) {
            return;
        } elseif (!$this->isExpressionSubjectToTypePossibilities($node->var)) {
            return;
        }

        $name = $this->getExpressionString($node->var);

        $this->expressionTypeInfoMap->setBestMatch($name, $node);
    }

    /**
     * @param Node\Stmt\Foreach_ $node
     *
     * @return void
     */
    protected function parseForeach(Node\Stmt\Foreach_ $node): void
    {
        if (!$node->valueVar instanceof Node\Expr\List_) {
            if (!$this->isExpressionSubjectToTypePossibilities($node->valueVar)) {
                return;
            }

            $key = $this->getExpressionString($node->valueVar);

            $this->expressionTypeInfoMap->setBestMatch($key, $node);
        }
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    protected function checkForScopeChange(Node $node): void
    {
        if ($node->getAttribute('startFilePos') > $this->position ||
            $node->getAttribute('endFilePos') < $this->position
        ) {
            return;
        }

        if ($node instanceof Node\Stmt\ClassLike) {
            $this->expressionTypeInfoMap->clear();
            $this->expressionTypeInfoMap->setBestMatch('$this', $node);
        } elseif ($node instanceof Node\FunctionLike) {
            $variablesOutsideCurrentScope = ['$this'];

            // If a variable is in a use() statement of a closure, we can't reset the state as we still need to
            // examine the parent scope of the closure where the variable is defined.
            if ($node instanceof Node\Expr\Closure) {
                foreach ($node->uses as $closureUse) {
                    $variablesOutsideCurrentScope[] = '$' . $closureUse->var->name;
                }
            }

            $this->expressionTypeInfoMap->removeAllExcept($variablesOutsideCurrentScope);

            foreach ($node->getParams() as $param) {
                $this->expressionTypeInfoMap->setBestMatch('$' . $param->var->name, $node);
            }
        }
    }

    /**
     * @param Node\Expr $node
     *
     * @return TypePossibilityMap[] with expression names as keys.
     */
    protected function parseCondition(Node\Expr $node): array
    {
        $types = [];

        if ($node instanceof Node\Expr\BinaryOp\BitwiseAnd ||
            $node instanceof Node\Expr\BinaryOp\BitwiseOr ||
            $node instanceof Node\Expr\BinaryOp\BitwiseXor ||
            $node instanceof Node\Expr\BinaryOp\BooleanAnd ||
            $node instanceof Node\Expr\BinaryOp\BooleanOr ||
            $node instanceof Node\Expr\BinaryOp\LogicalAnd ||
            $node instanceof Node\Expr\BinaryOp\LogicalOr ||
            $node instanceof Node\Expr\BinaryOp\LogicalXor
        ) {
            $this->processConditionLogicalOperators($node, $types);
        } elseif (
            $node instanceof Node\Expr\BinaryOp\Equal ||
            $node instanceof Node\Expr\BinaryOp\Identical
        ) {
            $this->processConditionEqualityOperators($node, $types);
        } elseif (
            $node instanceof Node\Expr\BinaryOp\NotEqual ||
            $node instanceof Node\Expr\BinaryOp\NotIdentical
        ) {
            $this->processConditionInequalityOperators($node, $types);
        } elseif ($this->isExpressionSubjectToTypePossibilities($node)) {
            $this->processConditionBoolean($node, $types);
        } elseif ($node instanceof Node\Expr\BooleanNot) {
            $this->processConditionBooleanNot($node, $types);
        } elseif ($node instanceof Node\Expr\Instanceof_) {
            $this->processConditionInstanceof($node, $types);
        } elseif ($node instanceof Node\Expr\FuncCall) {
            $this->processConditionFuncCall($node, $types);
        }

        return $types;
    }

    /**
     * @param Node\Expr\BinaryOp   $node
     * @param TypePossibilityMap[] &$types
     *
     * @return void
     */
    protected function processConditionLogicalOperators(Node\Expr\BinaryOp $node, array &$types): void
    {
        $leftTypes = $this->parseCondition($node->left);
        $rightTypes = $this->parseCondition($node->right);

        foreach ($leftTypes as $variable => $typePossibilityMap) {
            foreach ($typePossibilityMap->getAll() as $conditionalType => $possibility) {
                $this->setTypePossibilityForExpression($types, $variable, $conditionalType, $possibility);
            }
        }

        foreach ($rightTypes as $variable => $typePossibilityMap) {
            foreach ($typePossibilityMap->getAll() as $conditionalType => $possibility) {
                $this->setTypePossibilityForExpression($types, $variable, $conditionalType, $possibility);
            }
        }
    }

    /**
     * @param Node\Expr\BinaryOp\Equal|Node\Expr\BinaryOp\Identical $node
     * @param TypePossibilityMap[]                                  &$types
     *
     * @return void
     */
    protected function processConditionEqualityOperators(Node\Expr\BinaryOp $node, array &$types): void
    {
        if ($this->isExpressionSubjectToTypePossibilities($node->left)) {
            if ($node->right instanceof Node\Expr\ConstFetch && $node->right->name->toString() === 'null') {
                $key = $this->getExpressionString($node->left);

                $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_GUARANTEED);
            }
        } elseif ($this->isExpressionSubjectToTypePossibilities($node->right)) {
            if ($node->left instanceof Node\Expr\ConstFetch && $node->left->name->toString() === 'null') {
                $key = $this->getExpressionString($node->right);

                $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_GUARANTEED);
            }
        }
    }

    /**
     * @param Node\Expr\BinaryOp\NotEqual|Node\Expr\BinaryOp\NotIdentical $node
     * @param TypePossibilityMap[]                                        &$types
     *
     * @return void
     */
    protected function processConditionInequalityOperators(Node\Expr\BinaryOp $node, array &$types): void
    {
        if ($this->isExpressionSubjectToTypePossibilities($node->left)) {
            if ($node->right instanceof Node\Expr\ConstFetch && $node->right->name->toString() === 'null') {
                $key = $this->getExpressionString($node->left);

                $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_IMPOSSIBLE);
            }
        } elseif ($this->isExpressionSubjectToTypePossibilities($node->right)) {
            if ($node->left instanceof Node\Expr\ConstFetch && $node->left->name->toString() === 'null') {
                $key = $this->getExpressionString($node->right);

                $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_IMPOSSIBLE);
            }
        }
    }

    /**
     * @param Node\Expr            $node
     * @param TypePossibilityMap[] &$types
     *
     * @return void
     */
    protected function processConditionBoolean(Node\Expr $node, array &$types): void
    {
        $key = $this->getExpressionString($node);

        $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_IMPOSSIBLE);
    }

    /**
     * @param Node\Expr\BooleanNot $node
     * @param TypePossibilityMap[] &$types
     *
     * @return void
     */
    protected function processConditionBooleanNot(Node\Expr\BooleanNot $node, array &$types): void
    {
        if ($this->isExpressionSubjectToTypePossibilities($node->expr)) {
            $key = $this->getExpressionString($node->expr);

            $this->setTypePossibilityForExpression($types, $key, 'int', TypePossibility::TYPE_GUARANTEED);    // 0
            $this->setTypePossibilityForExpression($types, $key, 'string', TypePossibility::TYPE_GUARANTEED); // ''
            $this->setTypePossibilityForExpression($types, $key, 'float', TypePossibility::TYPE_GUARANTEED);  // 0.0
            $this->setTypePossibilityForExpression($types, $key, 'array', TypePossibility::TYPE_GUARANTEED);  // []
            $this->setTypePossibilityForExpression($types, $key, 'null', TypePossibility::TYPE_GUARANTEED);   // null
        } else {
            $subTypes = $this->parseCondition($node->expr);

            foreach ($subTypes as $variable => $typePossibilityMap) {
                foreach ($typePossibilityMap->getAll() as $subType => $possibility) {
                    $this->setTypePossibilityForExpression($types, $variable, $subType, TypePossibility::getReverse($possibility));
                }
            }
        }
    }

    /**
     * @param Node\Expr\Instanceof_ $node
     * @param TypePossibilityMap[]  &$types
     *
     * @return void
     */
    protected function processConditionInstanceof(Node\Expr\Instanceof_ $node, array &$types): void
    {
        if ($this->isExpressionSubjectToTypePossibilities($node->expr)) {
            if ($node->class instanceof Node\Name) {
                $key = $this->getExpressionString($node->expr);

                $this->setTypePossibilityForExpression($types, $key, NodeHelpers::fetchClassName($node->class), TypePossibility::TYPE_GUARANTEED);
            } else {
                // This is an expression, we could fetch its return type, but that still won't tell us what
                // the actual class is, so it's useless at the moment.
            }
        }
    }

    /**
     * @param Node\Expr\FuncCall   $node
     * @param TypePossibilityMap[] &$types
     *
     * @return void
     */
    protected function processConditionFuncCall(Node\Expr\FuncCall $node, array &$types): void
    {
        if (!$node->name instanceof Node\Name) {
            return;
        }

        $variableHandlingFunctionTypeMap = [
            'is_array'    => ['array'],
            'is_bool'     => ['bool'],
            'is_callable' => ['callable'],
            'is_double'   => ['float'],
            'is_float'    => ['float'],
            'is_int'      => ['int'],
            'is_integer'  => ['int'],
            'is_long'     => ['int'],
            'is_null'     => ['null'],
            'is_numeric'  => ['int', 'float', 'string'],
            'is_object'   => ['object'],
            'is_real'     => ['float'],
            'is_resource' => ['resource'],
            'is_scalar'   => ['int', 'float', 'string', 'bool'],
            'is_string'   => ['string']
        ];

        if (!isset($variableHandlingFunctionTypeMap[$node->name->toString()])) {
            return;
        } elseif (
            empty($node->args) ||
            $node->args[0]->unpack ||
            !$this->isExpressionSubjectToTypePossibilities($node->args[0]->value)
        ) {
            return;
        }

        $key = $this->getExpressionString($node->args[0]->value);

        $guaranteedTypes = $variableHandlingFunctionTypeMap[$node->name->toString()];

        foreach ($guaranteedTypes as $guaranteedType) {
            $this->setTypePossibilityForExpression($types, $key, $guaranteedType, TypePossibility::TYPE_GUARANTEED);
        }
    }

    /**
     * @param TypePossibilityMap[] &$types
     * @param string               $expression
     * @param string               $type
     * @param int                  $possibility
     *
     * @return void
     */
    protected function setTypePossibilityForExpression(
        array &$types,
        string $expression,
        string $type,
        int $possibility
    ): void {
        if (!isset($types[$expression])) {
            $types[$expression] = new TypePossibilityMap();
        }

        $types[$expression]->set($type, $possibility);
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    protected function parseNodeDocblock(Node $node): void
    {
        $docblock = $node->getDocComment();

        if (!$docblock) {
            return;
        }

        // Check for a reverse type annotation /** @var $someVar FooType */. These aren't correct in the sense that
        // they aren't consistent with the standard syntax "@var <type> <name>", but they are still used by some IDE's.
        // For this reason we support them, but only their most elementary form.
        $classRegexPart = "?:\\\\?[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*";
        $reverseRegexTypeAnnotation = "/\/\*\*\s*@var\s+(\\\$[A-Za-z0-9_])\s+(({$classRegexPart}(?:\[\])?))\s*(\s.*)?\*\//";

        if (preg_match($reverseRegexTypeAnnotation, $docblock, $matches) === 1) {
            $this->expressionTypeInfoMap->setBestTypeOverrideMatch(
                $matches[1],
                $matches[2],
                $node->getLine()
            );
        } else {
            $docblockData = $this->docblockParser->parse((string) $docblock, [
                DocblockParser::VAR_TYPE
            ], '');

            foreach ($docblockData['var'] as $variableName => $data) {
                if ($data['type']) {
                    $this->expressionTypeInfoMap->setBestTypeOverrideMatch(
                        $variableName,
                        $data['type'],
                        $node->getLine()
                    );
                }
            }
        }
    }

    /**
     * @param Node\Expr $expression
     *
     * @return bool
     */
    protected function isExpressionSubjectToTypePossibilities(Node\Expr $expression): bool
    {
        if ($expression instanceof Node\Expr\Variable && is_string($expression->name)) {
            return true;
        } elseif ($expression instanceof Node\Expr\PropertyFetch && $expression->name instanceof Node\Identifier) {
            return true;
        } elseif ($expression instanceof Node\Expr\StaticPropertyFetch && $expression->name instanceof Node\Identifier) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves a string representing an expression.
     *
     * @param Node\Expr $expression
     *
     * @throws DomainException
     *
     * @return string
     */
    protected function getExpressionString(Node\Expr $expression): string
    {
        if ($expression instanceof Node\Expr\Variable) {
            return '$' . ((string) $expression->name);
        } elseif ($expression instanceof Node\Expr\PropertyFetch ||
                  $expression instanceof Node\Expr\StaticPropertyFetch
        ) {
            return $this->prettyPrinter->prettyPrintExpr($expression);
        }

        throw new DomainException(
            "Don't know how to retrieve a string from expression of type " . get_class($expression)
        );
    }

    /**
     * @return ExpressionTypeInfoMap
     */
    public function getExpressionTypeInfoMap(): ExpressionTypeInfoMap
    {
        return $this->expressionTypeInfoMap;
    }
}
