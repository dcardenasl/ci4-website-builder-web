<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\EditorBlockAnnotator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EditorBlockAnnotatorTest extends CIUnitTestCase
{
    public function testWrapAddsEscapedReferenceAndFallbackFlag(): void
    {
        $html = (new EditorBlockAnnotator())->wrap(
            ['editor_ref' => 'id_7', 'is_fallback' => true],
            '<section>Hola</section>',
        );

        self::assertSame(
            '<div data-block-instance="id_7" data-block-fallback="1"><section>Hola</section></div>',
            $html,
        );
    }

    public function testMissingReferenceLeavesMarkupUntouched(): void
    {
        $annotator = new EditorBlockAnnotator();

        self::assertSame('<p>Hola</p>', $annotator->wrap([], '<p>Hola</p>'));
        self::assertSame('<p>Hola</p>', $annotator->wrap(['editor_ref' => ''], '<p>Hola</p>'));
    }

    public function testReferenceIsEscapedAndFragmentStylesAreRemoved(): void
    {
        $annotator = new EditorBlockAnnotator();
        $annotator->wrap(
            ['editor_ref' => 'id_1" onload="x'],
            '<style nonce="abc">.hero { color: red; }</style><section>Hola</section>',
        );

        $fragment = $annotator->fragment('id_1" onload="x');

        self::assertIsString($fragment);
        self::assertStringContainsString('&quot;', $fragment);
        self::assertStringNotContainsString('onload="x"', $fragment);
        self::assertStringNotContainsString('<style', $fragment);
        self::assertNull($annotator->fragment('missing'));
    }
}
