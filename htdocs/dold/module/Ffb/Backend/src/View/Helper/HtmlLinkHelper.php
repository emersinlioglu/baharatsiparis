<?php

namespace Ffb\Backend\View\Helper;

/**
 * Helper for html link
 *
 * @todo Redundant to HtmlLinkHelper in Eventus, move to a common codebase
 */
class HtmlLinkHelper extends AbstractBackendHtmlElement
{

    /**
     * Generates a link html by invoke
     *
     * @param  string $content   Link content
     * @param  string $href      Link href
     * @param  string $title     Link title
     * @param  string $className Link CSS class
     * @param  array $attribs    Attributes for the a tag.
     * @return string The link XHTML.
     */
    public function __invoke($content, $href = null, $title = null, $className = null, $attribs = null, $dataAttribs = null)
    {

        return $this->getHtml($content, $href, $title, $className, $attribs, $dataAttribs);
    }

    /**
     * Generates a link html
     *
     * @param  string $content   Link content
     * @param  string $href      Link href
     * @param  string $title     Link title
     * @param  string $className Link CSS class
     * @param  array $attribs    Attributes for the a tag.
     * @return string The link XHTML.
     */
    public function getHtml($content, $href = null, $title = null, $className = null, $attribs = null, $dataAttribs = null) {

        if (!is_array($attribs)) $attribs = array();
        if ($href) $attribs['href'] = $href;
        if ($title) $attribs['title'] = $title;
        if ($className) $attribs['class'] = $className;

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        return '<a' . $attribs  . $dataAttribs . '>' . $content . '</a>';
    }
}
