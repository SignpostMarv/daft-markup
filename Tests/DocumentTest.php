<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Tests;

use BadMethodCallException;
use Closure;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftMarkup\AbstractHtmlElement;
use SignpostMarv\DaftMarkup\Html\Document;
use SignpostMarv\DaftMarkup\Markup;

class DocumentTest extends TestCase
{
    /**
    * @var bool
    */
    protected $backupGlobals = false;

    /**
    * @var bool
    */
    protected $backupStaticAttributes = false;

    /**
    * @var bool
    */
    protected $runTestInSeparateProcess = false;

    /**
    * @return array<int, array<int, string|array>>
    */
    public function dataProviderDocumentInstance() : array
    {
        return [
            [
                Document::class,
                [],
            ],
        ];
    }

    /**
    * @return array<int, array<string|mixed[]|null|Closure>>
    */
    public function dataProviderDocumentToString() : array
    {
        return [
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->asyncJs('./foo.js');
                    $doc->deferJs('./bar.js', './baz.js');
                    $doc->Preload('style', './foo.css', './bar.css');
                    $doc->Preload('module', './bar-module.js');
                    $doc->ConfigureIntegrity('./foo.js', 'example');
                    $doc->ConfigureIntegrity('./foo.css', 'example-2');
                    $doc->IncludeCss('./foo.css', './bar.css', './baz.css');
                    $doc->ExcludeCss('./baz.css');
                    $doc->CrossOrigin('anonymous', './bar.css', './baz.css', './foo.js');
                    $doc->AppendMeta('description', 'Example');
                    $doc->IncludeModules('./bar-module.js');
                    $doc->IncludeNoModules('./bar-not-module.js');
                    $doc->ExcludeJs('./baz.js');
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                            '<link rel="preload" href="./foo.css" as="style">' .
                            '<link rel="preload" href="./bar.css" as="style" crossorigin="anonymous">' .
                            '<link rel="modulepreload" href="./bar-module.js">' .
                            '<link rel="stylesheet" href="./foo.css" integrity="example-2">' .
                            '<link rel="stylesheet" href="./bar.css" crossorigin="anonymous">' .
                            '<meta name="description" content="Example">' .
                        '</head>' .
                        '<body>' .
                            '<script src="./foo.js" async crossorigin="anonymous" integrity="example"></script>' .
                            '<script src="./bar.js" defer></script>' .
                            '<script src="./bar-module.js" type="module"></script>' .
                            '<script src="./bar-not-module.js" nomodule></script>' .
                        '</body>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetMarkupConverter(new Markup());
                    $doc->SetTitle('Test');
                    $doc->SetTitleAttribute('Toast');
                    $doc->SetLang('en');
                    $doc->SetCharset('iso-8859-1');
                    $doc->AppendMeta('http:Refresh', '5');
                    $doc->Preload('script', './foo.js');
                    $doc->ConfigureIntegrity('./foo.js', 'example');
                    $doc->SetEnableIntegrityOnPreload(true);
                    $doc->SetItemScope(true);
                    $doc->SetHidden(true);
                    $doc->SetIs('html-future');
                    $doc->SetId('foo');
                    $doc->SetDropzone('bar');
                    $doc->SetItemId('baz');
                    $doc->SetSlot('nope');
                    $doc->SetItemType('http://schema.org/Thing');
                    $doc->SetDir('ltr');
                    $doc->SetContextMenu('bag');
                    $doc->SetContentEditable(true);
                    $doc->SetAutoCapitalize('off');
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html autocapitalize="off" contenteditable contextmenu="bag" dir="ltr" dropzone="bar" hidden id="foo" is="html-future" itemid="baz" itemscope itemtype="http://schema.org/Thing" lang="en" slot="nope" title="Toast">' .
                        '<head>' .
                            '<meta charset="iso-8859-1">' .
                            '<title>Test</title>' .
                            '<link rel="preload" href="./foo.js" as="script" integrity="example">' .
                            '<meta http-equiv="Refresh" content="5">' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc, TestCase $test) : void {
                    $doc->SetMarkupConverter(new Markup());
                    $doc->SetTitle('Test');
                    $doc->Preload('script', './foo.js');
                    $doc->ConfigureIntegrity('./foo.js', 'example');

                    $test::assertSame(
                        [
                            'Link: <./foo.js>; rel=preload; as=script',
                        ],
                        $doc->GetPossibleHeaders()
                    );
                    $doc->ClearPossibleHeaderSources();
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc, TestCase $test) : void {
                    $doc->SetMarkupConverter(new Markup());
                    $doc->SetTitle('Test');
                    $doc->Preload('script', './foo.js');
                    $doc->ConfigureIntegrity('./foo.js', 'example');
                    $doc->SetEnableIntegrityOnPreload(true);

                    $test::assertSame(
                        [
                            'Link: <./foo.js>; rel=preload; as=script; integrity=example',
                        ],
                        $doc->GetPossibleHeaders()
                    );

                    $doc->ClearPossibleHeaderSources();
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc, TestCase $test) : void {
                    $doc->SetMarkupConverter(new Markup());
                    $doc->SetTitle('Test');
                    $doc->Preload('script', './foo.js');
                    $doc->ConfigureIntegrity('./foo.js', 'example');
                    $doc->SetEnableIntegrityOnPreload(true);
                    $doc->ClearPossibleHeaderSources();

                    $test::assertSame([], $doc->GetPossibleHeaders());
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->ApplyValueForDataAttribute('foo', 'bar');
                    $doc->SetTabIndex(-1);
                    $doc->SetTranslate(false);
                    $doc->AppendClass('no-js');
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html class="no-js" data-foo="bar" tabindex="-1" translate="no">' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetDraggable(true);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html draggable="true">' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetDraggable(false);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html draggable="false">' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetDraggable(null);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetSpellcheck(true);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html spellcheck="true">' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetSpellcheck(false);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html spellcheck="false">' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
            [
                Document::class,
                [],
                null,
                function (Document $doc) : void {
                    $doc->SetTitle('Test');
                    $doc->SetSpellcheck(null);
                },
                (
                    '<!DOCTYPE html>' .
                    "\n" .
                    '<html>' .
                        '<head>' .
                            '<meta charset="utf-8">' .
                            '<title>Test</title>' .
                        '</head>' .
                    '</html>'
                ),
            ],
        ];
    }

    /**
    * @return array<int, array<string|mixed[]|null|Closure>>
    */
    public function dataProviderBadDocumentToString() : array
    {
        return [
            [
                Document::class,
                [],
                BadMethodCallException::class,
                'Document title must not be empty!',
            ],
        ];
    }

    /**
    * @return string[][]
    */
    public function dataProviderStringArrayMethodNames() : array
    {
        return [
            [
                'AccessKey',
            ],
            [
                'Class',
            ],
            [
                'ItemRefs',
            ],
            [
                'Style',
            ],
        ];
    }

    /**
    * @return string[][][]
    */
    public function dataProviderStringArrayMethodTestingValues() : array
    {
        return [
            [
                ['b', 'c', 'd'], // set
                ['b', 'c', 'd'], // assertSame $expected
                ['a', 'e'], // append
                ['b', 'c', 'd', 'a', 'e'], // assertSame $expected
                ['b', 'c', 'd', 'a', 'e'], // assertSame $expected post-sort
                ['a', 'b', 'c', 'd', 'e'], // assertSame $expected post-sort, post-set
            ],
        ];
    }

    public function dataProviderStringArrayMethods() : Generator
    {
        foreach ($this->dataProviderDocumentInstance() as $classArgs) {
            foreach ($this->dataProviderStringArrayMethodNames() as $methodNameArgs) {
                foreach ($this->dataProviderStringArrayMethodTestingValues() as $testingArgs) {
                    yield array_merge($classArgs, $methodNameArgs, $testingArgs);
                }
            }
        }
    }

    /**
    * @return array<int, array<int, scalar|array|null>>
    */
    public function dataProviderDefaultValues() : array
    {
        return [
            [
                'AccessKey',
                [],
            ],
            [
                'AutoCapitalize',
                null,
            ],
            [
                'Class',
                [],
            ],
            [
                'ContentEditable',
                false,
            ],
            [
                'ContextMenu',
                null,
            ],
            [
                'Dir',
                null,
            ],
            [
                'Draggable',
                false,
            ],
            [
                'Dropzone',
                null,
            ],
            [
                'Hidden',
                false,
            ],
            [
                'Id',
                null,
            ],
            [
                'Is',
                null,
            ],
            [
                'ItemId',
                null,
            ],
            [
                'ItemRefs',
                [],
            ],
            [
                'ItemScope',
                false,
            ],
            [
                'ItemType',
                null,
            ],
            [
                'Lang',
                null,
            ],
            [
                'Slot',
                null,
            ],
            [
                'Spellcheck',
                false,
            ],
            [
                'Style',
                [],
            ],
            [
                'TabIndex',
                null,
            ],
            [
                'Title',
                '',
            ],
            [
                'TitleAttribute',
                null,
            ],
            [
                'Translate',
                true,
            ],
        ];
    }

    public function dataProviderTestDefaults() : Generator
    {
        foreach ($this->dataProviderDocumentInstance() as $classArgs) {
            foreach ($this->dataProviderDefaultValues() as $valuesArgs) {
                yield array_merge([$classArgs[0]], $valuesArgs);
            }
        }
    }

    /**
    * @dataProvider dataProviderDocumentInstance
    */
    public function testIsAbstractHtmlElement(string $class) : void
    {
        static::assertTrue(is_a($class, AbstractHtmlElement::class, true));
    }

    /**
    * @dataProvider dataProviderDocumentInstance
    *
    * @depends testIsAbstractHtmlElement
    */
    public function testValidElementName(string $class) : void
    {
        if ( ! is_a($class, AbstractHtmlElement::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an instance of ' .
                AbstractHtmlElement::class .
                ', ' .
                $class .
                ' given!'
            );
        }

        static::assertRegExp(Markup::REGEX_ELEMENT_NAME, (string) ($class::MarkupElementName()));
    }

    /**
    * @param array<int|string, mixed>|null $content
    *
    * @dataProvider dataProviderDocumentToString
    *
    * @depends testIsAbstractHtmlElement
    */
    public function testDocumentToString(
        string $class,
        array $ctorargs,
        ? array $content,
        ? Closure $decorateDocument,
        string $expected
    ) : void {
        $doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

        if ($doc instanceof Document) {
            $doc->ApplyValueForDataAttribute('foo', 'bar');
            static::assertSame('bar', $doc->RetrieveDataAttribute('foo'));
            $doc->ApplyValueForDataAttribute('foo', null);
            static::assertNull($doc->RetrieveDataAttribute('foo'));

            $doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);
        }

        if ($decorateDocument instanceof Closure) {
            $decorateDocument($doc, $this);
        }

        static::assertSame(
            $expected,
            $doc->MarkupContentToDocumentString($content)
        );
    }

    /**
    * @param array<int|string, mixed>|null $content
    *
    * @dataProvider dataProviderBadDocumentToString
    *
    * @depends testIsAbstractHtmlElement
    */
    public function testBadDocumentToString(
        string $class,
        array $ctorargs,
        string $expectedExceptionClass,
        string $expectedExceptionMessage,
        array $content = null,
        Closure $decorateDocument = null
    ) : void {
        $doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

        if ($decorateDocument instanceof Closure) {
            $decorateDocument($doc);
        }

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $doc->MarkupContentToDocumentString($content);
    }

    /**
    * @param string[] $firstSet
    * @param string[] $assertSameFirstSetExpected
    * @param string[] $append
    * @param string[] $assertSameAppendExpected
    * @param string[] $assertSameSortExpected
    * @param string[] $assertSameSortSetExpected
    *
    * @dataProvider dataProviderStringArrayMethods
    *
    * @depends testIsAbstractHtmlElement
    */
    public function testStringArrayMethods(
        string $class,
        array $ctorargs,
        string $methodSuffix,
        array $firstSet,
        array $assertSameFirstSetExpected,
        array $append,
        array $assertSameAppendExpected,
        array $assertSameSortExpected,
        array $assertSameSortSetExpected
    ) : void {
        $doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

        $getMethod = 'Get' . $methodSuffix;
        $setMethod = 'Set' . $methodSuffix;
        $appendMethod = 'Append' . $methodSuffix;
        $clearMethod = 'Clear' . $methodSuffix;

        static::assertTrue(method_exists($doc, $getMethod));
        static::assertTrue(method_exists($doc, $setMethod));
        static::assertTrue(method_exists($doc, $appendMethod));
        static::assertTrue(method_exists($doc, $clearMethod));

        static::assertEmpty($doc->$getMethod());

        $doc->$setMethod(...$firstSet);
        static::assertSame($assertSameFirstSetExpected, $doc->$getMethod());

        $doc->$appendMethod(...$append);
        static::assertSame($assertSameAppendExpected, $doc->$getMethod());

        /**
        * @var string[]
        */
        $val = $doc->$getMethod();
        sort($val);
        static::assertSame($assertSameSortExpected, $doc->$getMethod());
        $doc->$setMethod(...$val);
        static::assertSame($assertSameSortSetExpected, $doc->$getMethod());

        $doc->$clearMethod();
        static::assertEmpty($doc->$getMethod());
    }

    /**
    * @param scalar|null $expected
    *
    * @dataProvider dataProviderTestDefaults
    *
    * @depends testIsAbstractHtmlElement
    */
    public function testTranslateDefault(string $class, string $methodSuffix, $expected) : void
    {
        $doc = $this->AbstractHtmlElementFromCtorArgs($class);

        $method = 'Get' . $methodSuffix;

        if ( ! method_exists($doc, $method)) {
            static::markTestSkipped(sprintf('%s does not implement method "%s"', $class, $method));
        } else {
            static::assertSame($expected, $doc->$method());
        }
    }

    protected function AbstractHtmlElementFromCtorArgs(
        string $class,
        array $ctorargs = []
    ) : AbstractHtmlElement {
        if ( ! is_a($class, AbstractHtmlElement::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                AbstractHtmlElement::class
            ));
        }

        /**
        * @var AbstractHtmlElement
        */
        $doc = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);

        return $doc;
    }
}
