<?php

declare(strict_types=1);

namespace Symplify\RuleDocGenerator\RuleCodeSamplePrinter;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;
use Symplify\RuleDocGenerator\Contract\RuleCodeSamplePrinterInterface;
use Symplify\RuleDocGenerator\Printer\CodeSamplePrinter\DiffCodeSamplePrinter;
use Symplify\RuleDocGenerator\Printer\Markdown\MarkdownCodeWrapper;
use Symplify\RuleDocGenerator\RuleCodeSamplePrinter\ConfiguredRuleCustomPrinter\RectorConfigConfiguredRuleCustomPrinter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ComposerJsonAwareCodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ExtraFileCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RectorRuleCodeSamplePrinter implements RuleCodeSamplePrinterInterface
{
    public function __construct(
        private readonly DiffCodeSamplePrinter $diffCodeSamplePrinter,
        private readonly MarkdownCodeWrapper $markdownCodeWrapper,
        private readonly ConfiguredCodeSamplerPrinter $configuredCodeSamplerPrinter,
        private readonly RectorConfigConfiguredRuleCustomPrinter $rectorConfigConfiguredRuleCustomPrinter
    ) {
    }

    public function isMatch(string $class): bool
    {
        return is_a($class, RectorInterface::class, true);
    }

    /**
     * @return string[]
     */
    public function print(CodeSampleInterface $codeSample, RuleDefinition $ruleDefinition): array
    {
        if ($codeSample instanceof ExtraFileCodeSample) {
            return $this->printExtraFileCodeSample($codeSample);
        }

        if ($codeSample instanceof ComposerJsonAwareCodeSample) {
            return $this->printComposerJsonAwareCodeSample($codeSample);
        }

        if ($codeSample instanceof ConfiguredCodeSample) {
            return $this->configuredCodeSamplerPrinter->printConfiguredCodeSample(
                $ruleDefinition,
                $codeSample,
                $this->rectorConfigConfiguredRuleCustomPrinter
            );
        }

        return $this->diffCodeSamplePrinter->print($codeSample);
    }

    /**
     * @return string[]
     */
    private function printComposerJsonAwareCodeSample(ComposerJsonAwareCodeSample $composerJsonAwareCodeSample): array
    {
        $lines = [];

        $lines[] = '- with `composer.json`:';
        $lines[] = $this->markdownCodeWrapper->printJsonCode($composerJsonAwareCodeSample->getComposerJson());
        $lines[] = '↓';

        $newLines = $this->diffCodeSamplePrinter->print($composerJsonAwareCodeSample);
        return array_merge($lines, $newLines);
    }

    /**
     * @return string[]
     */
    private function printExtraFileCodeSample(ExtraFileCodeSample $extraFileCodeSample): array
    {
        $lines = $this->diffCodeSamplePrinter->print($extraFileCodeSample);

        $lines[] = 'Extra file:';
        $lines[] = $this->markdownCodeWrapper->printPhpCode($extraFileCodeSample->getExtraFile());

        return $lines;
    }
}
