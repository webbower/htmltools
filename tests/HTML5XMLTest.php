<?php

use \Webbower\HTML;

class HTML5XMLTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        HTML::setProfile(HTML::HTML5_XML);
    }

    protected function tearDown() {
    }

    public function testProfile()
    {
        $this->assertEquals(HTML::HTML5_XML, HTML::getProfile());
    }

    public function testDoctype()
    {
        $this->assertEquals('<!DOCTYPE html>', HTML::doctype());
    }
    
    public function testIsHtml5()
    {
        $this->assertTrue(HTML::isHtml5());
    }

    public function testIsNotHtml4()
    {
        $this->assertFalse(HTML::isHtml4());
    }

    public function testIsXml()
    {
        $this->assertTrue(HTML::isXml());
    }

    public function testOpenTag()
    {
        $this->assertEquals('<p>', HTML::openTag('p'));
        $this->assertEquals('<img />', HTML::openTag('img'));
    }

    public function testCloseTag()
    {
        $this->assertEquals('</p>', HTML::closeTag('p'));
        $this->assertEquals('', HTML::closeTag('img'));
    }

    public function testAttributes()
    {
        $this->assertEquals(
            ' id="foo" title="Hello World" autofocus="autofocus" novalidate="novalidate"',
            HTML::attrs(array(
                'id' => 'foo',
                'title' => 'Hello World',
                'autofocus' => true,
                'novalidate' => 'novalidate',
            ))
        );
    }

    public function testEscapeMode()
    {
        $this->assertEquals(ENT_HTML5, HTML::getEscapeMode());
    }

    public function testSimpleTags()
    {
        $this->assertEquals('<div></div>', HTML::tag('div'));
        $this->assertEquals('<br />', HTML::tag('br'));
        $this->assertEquals('<span></span>', HTML::tag('span'));
    }

    public function testSimpleTagsWithAttributes()
    {
        $this->assertEquals(
            '<div class="foo" title="bar" baz="baz"></div>',
            HTML::tag(
                'div',
                '',
                array(
                    'class' => 'foo',
                    'title' => 'bar',
                    'baz' => true,
                )
            )
        );
    }

    public function testSimpleTagWithBooleanAttributes()
    {
        $this->assertEquals('<input autofocus="autofocus" />', HTML::tag('input', '', array('autofocus' => true)));
        $this->assertEquals('<input autofocus="autofocus" />', HTML::tag('input', '', array('autofocus' => 'autofocus')));
    }

    public function testSimpleTagWithMixedAttributes()
    {
        $this->assertEquals(
            '<input type="text" class="foo bar" autofocus="autofocus" required="required" />',
            HTML::tag('input', '', array(
                'type' => 'text',
                'class' => 'foo bar',
                'autofocus' => true,
                'required' => 'required',
            ))
        );
    }

    public function testTagWithDataAttributes()
    {
        $this->assertEquals(
            '<div data-foo-bar="something else"></div>',
            HTML::tag( 'div', '', array(
                'data-foo-bar' => 'something else',
            ))
        );
    }

    public function testEmptyTagHasNoContent()
    {
        $this->assertEquals('<img />', HTML::tag('img', 'Lorem ipsum', array()));
    }

    public function testTagHasContent()
    {
        $this->assertEquals('<p>Lorem ipsum</p>', HTML::tag('p', 'Lorem ipsum', array()));
    }
    
    public function testTagWithTheWorks()
    {
        $this->assertEquals(
            '<div class="hello-world hi" data-something="bar" data-another="data-another" data-blarg="bar sneh">Lorem ipsum</div>',
            HTML::tag(
                'div',
                'Lorem ipsum',
                array(
                    'class' => array('hello-world', 'hi'),
                    'data-something' => 'bar',
                    'data-another' => true,
                    'data-blarg' => 'bar sneh',
                )
            )
        );
    }
    
    public function testRendersHtml5Tag()
    {
        $this->assertEquals('<article></article>', HTML::tag('article'));
        $this->assertEquals('<time></time>', HTML::tag('time'));
    }

    public function testTagCasing()
    {
        $this->assertEquals('<p class="foo">Lorem ipsum</p>', HTML::tag('P', 'Lorem ipsum', array('CLASS' => 'foo')));
    }
}
