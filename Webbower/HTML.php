<?php

namespace Webbower;

class HTML
{
    const HTML5 = 'html5';

    const HTML5_XML = 'html5-xml';

    const HTML4_STRICT = 'html4-strict';

    const HTML4_TRANS = 'html4-trans';

    const XHTML1_STRICT = 'xhtml1-strict';

    const XHTML1_TRANS = 'xhtml1-trans';

    const XHTML11 = 'xhtml11';

    // Configuration flags
    /**
     * @var $profile string
     */
    private static $profile = 'html5';

    /**
     * @var $empty_tags array HTML tags that are empty tags. Key should be tagname, value should be true
     */
    private static $empty_tags = array(
        'area'  => true,
        'base'  => true,
        'br'    => true,
        'col'   => true,
        'embed' => true,
        'hr'    => true,
        'img'   => true,
        'input' => true,
        'link'  => true,
        'meta'  => true,
        'param' => true,
    );

    /**
     * @var $supported_profiles array List of whitelisted values for {@link HTML::setProfile()}
     */
    private static $supported_profiles = array(
        'html5',
        'html5-xml',
        'xhtml11',
        'xhtml1-trans',
        'xhtml1-strict',
        'html4-strict',
        'html4-trans',
    );

    /**
     * @var $html5_tags array Map of HTML5 tags and their display type. Used to downgrade tags to non-HTML5
     */
    private static $html5_tags = array(
        // Block level tags
        'article'    => 'block',
        'aside'      => 'block',
        'details'    => 'block',
        'figcaption' => 'block',
        'figure'     => 'block',
        'header'     => 'block',
        'hgroup'     => 'block',
        'footer'     => 'block',
        'nav'        => 'block',
        'section'    => 'block',
        'summary'    => 'block',

        // Inline level tags
        'time'       => 'inline',
        'mark'       => 'inline',
        'meter'      => 'inline',
        'progress'   => 'inline',
        'data'       => 'inline',

        // Misc tags
    );

    private static $doctypes = array(
        'html5'         => '<!DOCTYPE html>',
        'html5-xml'     => '<!DOCTYPE html>',
        'xhtml11'       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        'xhtml1-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        'xhtml1-trans'  => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'html4-strict'  => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        'html4-trans'   => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
    );

    /**
     * Returns a templated message for an InvalidArgumentException
     *
     * @param $functionName string Name of the function. Typically __METHOD__
     * @param $argNumber int The argument position of the offending argument
     * @param $expectedType string A string fragment of the expected arg type
     * @param $givenValue mixed The value of the offending argument
     * @return string A formatted message for an InvalidArgumentException
     */
    private static function invalidArgumentExceptionMsg($functionName, $argNumber, $expectedType, $givenValue)
    {
        return "{$functionName} - Expected {$expectedType} for argument {$argNumber} but got " . gettype($givenValue) . " instead";
    }

    /**
     * Utility method to test which profile is active
     *
     * @param $profiles array An array of whitelisted profiles
     * @return boolean True/false depending if the current profile is in the whitelist
     */
    private static function profileMatch(array $profiles)
    {
        return in_array(self::getProfile(), $profiles, true);
    }

    /**
     * Converts a name/value pair to a valid HTML attribute
     *
     * The returned string can be configured by the class. You can control:
     * - Whether the attribute can be bare (unquoted)
     * - Whether boolean attributes need to be duplicated
     *
     * @param $name string The HTML attribute name
     * @param $value mixed The HTML attribute value. Can be a boolean, a string, or a list that will be joined
     * @return string A valid HTML attribute
     */
    private static function renderSingleAttr($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $name));
        }

        if (!is_string($value) && !is_array($value) && !is_bool($value)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 2, 'boolean, string or array', $value));
        }

        $actualValue = self::chooseAttrValue($value, $name);

        return ' ' . self::renderAttrName($name) . (is_null($actualValue) ? '' : '=' . self::renderAttrValue($actualValue));
    }

    /**
     * Helper function to determine the actual attribute value to render
     *
     * @param $value mixed A string for the attribute or a boolean for boolean attributes
     * @param $name string The attribute name
     * @return mixed The string to use as the attribute or null if no value is to be used
     */
    private static function chooseAttrValue($value, $name)
    {
        if ($value === true || $name === $value) {
            if (self::isXml()) {
                return $name;
            }
        } else {
            return $value;
        }

        return null;
    }

    /**
     * Returns a valid html attribute name
     *
     * Takes a string and returns it as a valid HTML attribute name based on configuration
     *
     * @param $name string The HTML attribute name
     * @return string A valid HTML attribute name
     */
    private static function renderAttrName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $name));
        }

        // TODO: Strip disallowed characters
        return self::isXml() ? strtolower($name) : $name;
    }

    /**
     * Utility function to return a valid tagname given the profile
     *
     * @param $tagname string The HTML tagname
     * @return string A validated version of the tagname
     */
    private static function tagname($tagname)
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        return self::isXml() ? strtolower($tagname) : $tagname;
    }

    /**
     * Returns a valid html attribute value
     *
     * Takes a boolean, string, or array and returns it as a valid HTML attribute value based on configuration
     *
     * @param $value mixed The HTML attribute value. Can be boolean, string, or array
     * @return string A valid HTML attribute value
     */
    private static function renderAttrValue($value)
    {
        if (!is_string($value) && !is_array($value)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string or array', $value));
        }

        // Supports passing an array as the value
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        $escaped_value = self::escape($value);

        // Wrap the value in quotes if they are required or if the value has whitespace
        $wrap_in_quotes = self::isXml() || preg_match('!\s!', $value) === 1;

        if ($wrap_in_quotes) {
            $escaped_value = ('"' . $escaped_value . '"');
        }

        return $escaped_value;
    }

    /**
     * Checks if the tagname passed in is an empty tag
     *
     * @param $tagname string An HTML tag name
     * @return boolean True if the tagname is an empty tag, false if it's not
     */
    private static function isEmptyTag($tagname)
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        return isset(self::$empty_tags[strtolower($tagname)]);
    }

    /**
     * Downgrades an HTML5 tag if HTML5 mode is not enabled
     *
     * If we're not in HTML5 mode, it will downgrade block elements to divs and
     * inline elements to spans and takes the original tagname and returns it
     * to be appended to the class.
     *
     * @param $tagname string An HTML tag name
     * @return array The index 0 is a tag name and index 1 is a class attr
     */
    private static function chooseTag($tagname)
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        $lowerTagname = strtolower($tagname);

        if (isset(self::$html5_tags[$lowerTagname])) {
            switch (self::$html5_tags[$lowerTagname]) {
                case 'inline':
                    return array('span', $tagname);
                    break;
                case 'block':
                    return array('div', $tagname);
                    break;
                default:
                    return array($tagname, '');
                    break;
            }
        }

        return array($tagname, '');
    }

    /**
     * Returns the actual content to be used
     *
     * This function determines what content will actually be used. If the tag
     * being used is an empty tag, then it returns an empty string. Otherwise
     * it returns the content, escaped
     *
     * @param $tagContent string The tagname that the content is for
     * @param $content string The content to be rendered
     * @return string The content to be used
     */
    private static function tagContent($tagname, $content = '')
    {
        return !self::isEmptyTag($tagname) ? self::escape($content) : '';
    }

    /**
     * Utility function to provide the XML self-closing string for a tag, if
     * appropriate
     *
     * @param $tagname string The tagname in question
     * @return string A string to self-close an opening tag
     */
    private static function closeSelf($tagname)
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        return self::isXml() && self::isEmptyTag($tagname) ? ' /' : '';
    }

    // PUBLIC METHODS
    /**
     * Returns an opening HTML tag or empty tag
     *
     * @param $tagname string The HTML tag name
     * @param $attrs array A map of HTML attributes
     * @return string A valid HTML opening/empty tag
     */
    public static function openTag($tagname, $attrs = array())
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        if (!is_array($attrs)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 2, 'array', $attrs));
        }

        return '<' . self::tagname($tagname) . self::attrs($attrs) . self::closeSelf($tagname) . '>';
    }

    /**
     * Returns a closing HTML tag nothing for an empty tag
     *
     * @param $tagname string The HTML tag name
     * @return string A valid HTML opening or empty string for an empty tag
     */
    public static function closeTag($tagname)
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        return !self::isEmptyTag($tagname) ? '</' . self::tagname($tagname) . '>' : '';
    }

    /**
     * Converts a map of HTML attributes to a valid string for use on an HTML tag
     *
     * @param $attrs array A map of HTML attributes
     * @return string A string of valid HTML attributes
     */
    public static function attrs($attrs = array())
    {
        if (!is_array($attrs)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'array', $attrs));
        }

        if (count($attrs) === 0) {
            return '';
        }

        $out = '';
        foreach ($attrs as $name => $value) {
            $out .= self::renderSingleAttr($name, $value);
        }

        return $out;
    }

    /**
     * Returns a complete HTML tag
     *
     * @param $tagname string The HTML tag name
     * @param $content string Content for the HTML tag
     * @param $attrs array A map of HTML attributes
     * @return string A valid HTML opening/empty tag
     */
    public static function tag($tagname, $content = '', $attrs = array())
    {
        if (!is_string($tagname)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $tagname));
        }

        if (!is_string($content)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 2, 'string', $content));
        }

        if (!is_array($attrs)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 3, 'array', $attrs));
        }

        // Check for downgrading HTML5 tag
        if (!self::isHtml5()) {
            list($tagname, $class) = self::chooseTag($tagname);

            if (!empty($class)) {
                if (isset($attrs['class'])) {
                    $attrs['class'] .= " {$class}";
                } else {
                    $attrs['class'] = $class;
                }
            }
        }

        return self::openTag($tagname, $attrs) . self::tagContent($tagname, $content) . self::closeTag($tagname);
    }

    /**
     * Escapes a string to be used for HTML content or HTML attribute values
     *
     * Is the inverse of {@link HTML::unescape}
     *
     * @param $str string The string to escape
     * @return string An escaped string that can be safely used in HTML
     */
    public function escape($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $str));
        }

        return htmlspecialchars($str, ENT_QUOTES | self::getEscapeMode(), 'UTF-8', false);
    }

    /**
     * Unescapes a string with HTML escaped characters
     *
     * Is the inverse of {@link HTML::escape}
     *
     * @param $str string The string to unescape
     * @return string A string with no HTML escaped characters
     */
    public function unescape($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $str));
        }

        return htmlspecialchars_decode($str, ENT_QUOTES | self::getEscapeMode());
    }

    /**
     * Returns a doctype
     *
     * Returns a valid doctype based on the configuration of this class
     *
     * @return string A valid HTML doctype
     */
    public static function doctype()
    {
        return isset(self::$doctypes[self::getProfile()]) ? self::$doctypes[self::getProfile()] : self::$doctypes['html5'];
    }

    /**
     * Configures this class for how to output HTML
     *
     * @param $profile string The configuration profile to use
     * @return void
     */
    public static function setProfile($profile = HTML::HTML5)
    {
        if (!is_string($profile)) {
            throw new \InvalidArgumentException(self::invalidArgumentExceptionMsg(__METHOD__, 1, 'string', $profile));
        }

        if (!in_array($profile, self::$supported_profiles)) {
            throw new \InvalidArgumentException(__METHOD__ . " - Invalid profile name {$profile} provided.");
        }

        self::$profile = $profile;
    }

    /**
     * Gets the current profile
     *
     * @return string The current profile
     */
    public static function getProfile()
    {
        return self::$profile;
    }

    /**
     * Is the current profile HTML5-compatible
     *
     * @return boolean True/false whether the current profile is HTML5-compatible
     */
    public static function isHtml5()
    {
        return self::profileMatch(array(HTML::HTML5, HTML::HTML5_XML));
    }

    /**
     * Is the current profile HTML4-compatible
     *
     * @return boolean True/false whether the current profile is HTML4-compatible
     */
    public static function isHtml4()
    {
        return self::profileMatch(array(HTML::HTML4_STRICT, HTML::HTML4_TRANS));
    }

    /**
     * Is the current profile XML-compatible
     *
     * @return boolean True/false whether the current profile is XML-compatible
     */
    public static function isXml()
    {
        return self::profileMatch(array(HTML::HTML5_XML, HTML::XHTML11, HTML::XHTML1_STRICT, HTML::XHTML1_TRANS));
    }

    /**
     * Gets the HTML escape mode flag for htmlspecialchars
     *
     * @return int The HTML escape mode flag
     */
    public static function getEscapeMode()
    {
        if (self::isHtml5()) {
            return ENT_HTML5;
        } elseif (self::isXml()) {
            return ENT_XHTML;
        } elseif (self::isHtml4()) {
            return ENT_HTML401;
        } else {
            return ENT_HTML401;
        }
    }
}
