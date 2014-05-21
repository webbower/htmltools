# HTML Tools

Utilities for programmatically generating consistent HTML

## API

All code samples assume `use \Webbower\HTML` at the top to allow for brevity in sample code. Otherwise, you'll need to prepend `\Webbower\` to all of your calls.

### `setProfile($profile = HTML::HTML5)`

Sets the profile for how you'd like your HTML to be generated

```php
// Set HTML5 (non-XML) profile
HTML::setProfile(HTML::HTML5);
```

The `HTML` class defaults to `HTML::HTML5` if `setProfile()` is never called.

Possible profiles are:

* `HTML::HTML5` - HTML 5 without XML restrictions
* `HTML::HTML5_XML` - HTML 5 with XML restrictions
* `HTML::XHTML11` - XHTML 1.1 rules
* `HTML::XHTML1_STRICT` - XHTML 1.0 Strict rules
* `HTML::XHTML1_TRANS` - XHTML 1.0 Transitional rules
* `HTML::HTML4_STRICT` - HTML 4.01 Strict rules
* `HTML::HTML4_TRANS` - HTML 4.01 Transitional rules

The profiles have the following effects:

* `HTML5` and `HTML5_XML` allow for use of the HTML5 tags. If you are using a non-HTML5 profile, HTML5 tags will be downgraded to `div` and `span` for block and inline tags respectively, and append the original tag name to the class attribute
* `HTML5_XML`, `XHTML11`, `XHTML1_STRICT`, and `XHTML1_TRANS` all follow XML rules:
  * All tag names and attribute names are forced to lowercase
  * All boolean attributes (like `disabled`, `required`, etc) must have a value that duplicates the attribute name
  * All attribute values are quoted with double quotes
  * All empty tags are self-closed with ` /` (a space and a forward-slash)
* Conversely, `HTML5`, `HTML4_STRICT`, `HTML4_TRANS` are much looser on the requirements and will create the shortest possible tags:
  * Tag names will not have their case modified
  * Boolean attributes can omit their attribute value
  * Attribute values with no spaces can be unquoted
  * Empty tags do not render with the self-closing string

### `getProfile()`

Returns a string representing the current profile

### `doctype()`

Returns the appropriate DOCTYPE based on the currently set profile

```php
HTML::setProfile(HTML::XHTML11);

echo HTML::doctype();
// Outputs <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
```

### `tag($tagname, $content = '', $attrs = array())`

Constructs a valid HTML tag based on the profile

```php
HTML::setProfile(HTML::HTML5);

echo HTML::tag('article', 'Lorem ipsum', array('id' => 'entry1', 'class' => 'entry'));
// Outputs <article id=entry1 class=entry>Lorem ipsum</article>

HTML::setProfile(HTML::HTML5_XML);

echo HTML::tag('article', 'Lorem ipsum', array('id' => 'entry1', 'class' => 'entry'));
// Outputs <article id="entry1" class="entry">Lorem ipsum</article>

HTML::setProfile(HTML::XHTML11);

echo HTML::tag('article', 'Lorem ipsum', array('id' => 'entry1', 'class' => 'entry'));
// Outputs <div id="entry1" class="entry article">Lorem ipsum</div>
```
* `$tagname` - (string) The tag name
* `$content` - (string) The content for the tag. Ignored if the tag name is an empty tag
* `$attrs` - (array) An array of attributes. See documentation for `HTML::attrs()` for how to structure this array

### `attrs($attrs = array())`

Takes an associative array and converts it into a valid string to be used as the attributes for an HTML tag, based on the current profile.

```php
HTML::setProfile(HTML::HTML5);

echo HTML::attrs(array('id' => 'entry1', 'class' => 'entry', 'disabled' => true, 'autofocus' => 'autofocus'));
// Outputs: id="entry1" class="entry" disabled autofocus

// Compared to an XML-based profile
HTML::setProfile(HTML::XHTML11);

echo HTML::attrs(array('id' => 'entry1', 'class' => 'entry', 'disabled' => true, 'autofocus' => 'autofocus'));
// Outputs: id="entry1" class="entry" disabled="disabled" autofocus="autofocus"
```

* `$attrs` - (array) An array of attributes. The key is the attribute name and the value is the attribute value. For boolean attributes, you can either have the key and value be the same, or set the value to a literal `true`

### `openTag($tagname, $attrs = array())`

* `$tagname` - (string) The tag name
* `$attrs` - (array) An array of attributes. See documentation for `HTML::attrs()` for how to structure this array

Returns a complete and valid opening tag with attributes and is self-closed for empty tags in an XML-based profile

### `closeTag($tagname)`

* `$tagname` - (string) The tag name

Returns a complete and valid closing tag, or an empty string if `$tagname` is the name of an empty tag

### `escape($str)`

Escapes all special HTML characters (<, >, ", ', and &)

This is basically a pass-through to the native PHP `htmlspecialchars` function with a predefined set of configuration arguments based on current configuration. `HTML::unescape()` is the inverse of this.

* `$str` - (string) The string to escape

### `unescape($str)`

Unescapes all special HTML characters (&lt;, &gt;, &quot;, &#039;, and &amp;)

This is basically a pass-through to the native PHP `htmlspecialchars_decode` function with a predefined set of configuration arguments based on current configuration. `HTML::escape()` is the inverse of this.

* `$str` - (string) The string to unescape

### `isHtml5()`

Determines if the current profile is an HTML5-compatible one.

Returns `true` if the current profile is HTML5-compatible, and `false` if it isn't

### `isHtml4()`

Determines if the current profile is an HTML4-compatible one.

Returns `true` if the current profile is HTML4-compatible, and `false` if it isn't

### `isXml()`

Determines if the current profile is an XML-strict one.

Returns `true` if the current profile is XML-strict, and `false` if it isn't