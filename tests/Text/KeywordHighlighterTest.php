<?php

declare(strict_types=1);

namespace Symplify\RuleDocGenerator\Tests\Text;

use Iterator;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\RuleDocGenerator\Kernel\RuleDocGeneratorKernel;
use Symplify\RuleDocGenerator\Text\KeywordHighlighter;

final class KeywordHighlighterTest extends AbstractKernelTestCase
{
    private KeywordHighlighter $keywordHighlighter;

    protected function setUp(): void
    {
        $this->bootKernel(RuleDocGeneratorKernel::class);
        $this->keywordHighlighter = $this->getService(KeywordHighlighter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $inputText, string $expectedHighlightedText): void
    {
        $highlightedText = $this->keywordHighlighter->highlight($inputText);
        $this->assertSame($expectedHighlightedText, $highlightedText);
    }

    public function provideData(): Iterator
    {
        yield ['some @var text', 'some `@var` text'];
        yield ['@param @var text', '`@param` `@var` text'];
        yield ['some @var and @param text', 'some `@var` and `@param` text'];
        yield [
            'Split multiple inline assigns to each own lines default value, to prevent undefined array issues',
            'Split multiple inline assigns to each own lines default value, to prevent undefined array issues',
        ];
        yield [
            'autowire(), autoconfigure(), and public() are required in config service',
            '`autowire()`, `autoconfigure()`, and `public()` are required in config service',
        ];
    }
}
