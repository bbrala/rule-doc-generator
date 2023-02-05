<?php

declare(strict_types=1);

namespace Symplify\RuleDocGenerator\CaseConverter;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Symplify\PhpConfigPrinter\Contract\CaseConverterInterface;
use Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory;
use Symplify\PhpConfigPrinter\Printer\ArrayDecorator\ServiceConfigurationDecorator;

final class RectorRuleCaseConverter implements CaseConverterInterface
{
    /**
     * @var string
     */
    public const NAME = 'rectorConfig';

    public function __construct(
        private readonly ArgsNodeFactory $argsNodeFactory,
        private readonly ServiceConfigurationDecorator $serviceConfigurationDecorator
    ) {
    }

    public function match(string $rootKey, mixed $key, mixed $values): bool
    {
        return $rootKey === self::NAME;
    }

    public function convertToMethodCallStmt(mixed $key, mixed $values): Stmt
    {
        $rectorClass = $values['class'];
        $configuration = $values['configuration'] ?? null;

        $classConstFetch = new ClassConstFetch(new FullyQualified($rectorClass), 'class');
        $args = [new Arg($classConstFetch)];

        $methodName = $configuration ? 'ruleWithConfiguration' : 'rule';

        if ($configuration) {
            $configuration = $this->serviceConfigurationDecorator->decorate($configuration, $rectorClass);
            $array = $this->argsNodeFactory->createFromValues($configuration, false, false, true);
            $args[] = new Arg(new Array_($array));
        }

        $ruleMethodCall = new MethodCall(new Variable(self::NAME), $methodName, $args);

        return new Expression($ruleMethodCall);
    }
}
