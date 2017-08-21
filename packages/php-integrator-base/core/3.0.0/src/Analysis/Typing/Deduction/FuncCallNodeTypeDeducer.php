<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Conversion\FunctionConverter;

use PhpIntegrator\Analysis\Node\FunctionNameNodeFqsenDeterminer;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\FuncCall} node.
 */
class FuncCallNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FunctionConverter
     */
    private $functionConverter;

    /**
     * @var FunctionNameNodeFqsenDeterminer
     */
    private $functionNameNodeFqsenDeterminer;

    /**
     * @param ManagerRegistry                   $managerRegistry
     * @param FunctionConverter               $functionConverter
     * @param FunctionNameNodeFqsenDeterminer $functionNameNodeFqsenDeterminer
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        FunctionConverter $functionConverter,
        FunctionNameNodeFqsenDeterminer $functionNameNodeFqsenDeterminer
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->functionConverter = $functionConverter;
        $this->functionNameNodeFqsenDeterminer = $functionNameNodeFqsenDeterminer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromFuncCallNode($node);
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @return string[]
     */
    protected function deduceTypesFromFuncCallNode(Node\Expr\FuncCall $node): array
    {
        if ($node->name instanceof Node\Expr) {
            return []; // Can't currently deduce type of an expression such as "{$foo}()";
        }

        $fqsen = $this->functionNameNodeFqsenDeterminer->determine($node->name);

        $globalFunction = $this->managerRegistry->getRepository(Structures\Function_::class)->findOneBy([
            'fqcn' => $fqsen
        ]);

        if (!$globalFunction) {
            return [];
        }

        $convertedGlobalFunction = $this->functionConverter->convert($globalFunction);

        return $this->fetchResolvedTypesFromTypeArrays($convertedGlobalFunction['returnTypes']);
    }
}
