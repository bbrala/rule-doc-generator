<?php

declare(strict_types=1);

namespace Symplify\RuleDocGenerator\RuleCodeSamplePrinter;

use Symplify\PhpConfigPrinter\Printer\SmartPhpConfigPrinter;
use Symplify\RuleDocGenerator\Contract\Printer\ConfiguredRuleCustomPrinterInterface;
use Symplify\RuleDocGenerator\Printer\CodeSamplePrinter\DiffCodeSamplePrinter;
use Symplify\RuleDocGenerator\Printer\Markdown\MarkdownCodeWrapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConfiguredCodeSamplerPrinter
{
    public function __construct(
        private readonly SmartPhpConfigPrinter $smartPhpConfigPrinter,
        private readonly MarkdownCodeWrapper $markdownCodeWrapper,
        private readonly DiffCodeSamplePrinter $diffCodeSamplePrinter
    ) {
    }

    /**
     * @return string[]
     */
    public function printConfiguredCodeSample(
        RuleDefinition $ruleDefinition,
        ConfiguredCodeSample $configuredCodeSample,
        ConfiguredRuleCustomPrinterInterface|null $configuredRuleCustomPrinter = null
    ): array {
        $lines = [];

        // if use it
        if ($configuredRuleCustomPrinter instanceof ConfiguredRuleCustomPrinterInterface) {
            $configPhpCode = $configuredRuleCustomPrinter->printConfigureService(
                $ruleDefinition,
                $configuredCodeSample
            );
        } else {
            $configPhpCode = $this->smartPhpConfigPrinter->printConfiguredServices(
                [
                    $ruleDefinition->getRuleClass() => $configuredCodeSample->getConfiguration(),
                ],
            );
        }

        $lines[] = $this->markdownCodeWrapper->printPhpCode($configPhpCode);

        $lines[] = '↓';

        $newLines = $this->diffCodeSamplePrinter->print($configuredCodeSample);

        return array_merge($lines, $newLines);
    }
}
