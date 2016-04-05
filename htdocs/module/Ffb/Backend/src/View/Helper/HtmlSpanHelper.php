<?php

namespace Ffb\Backend\View\Helper;

/**
 * Helper for html link
 *
 * @todo Redundant to HtmlSpanHelper in Eventus, move to a common codebase
 */
class HtmlSpanHelper extends AbstractBackendHtmlElement {

    /**
     * Generates a link html by invoke
     *
     * @param string $content Link content
     * @param string $className Link CSS class
     * @param array $attribs Attributes for the a tag.
     * @return string The link XHTML.
     */
    public function __invoke($content, $className = null, $attribs = null) {

        return $this->getHtml($content, $className, $attribs);
    }

    /**
     * Generates a link html
     *
     * @param string $content Link content
     * @param string $className Link CSS class
     * @param array $attribs Attributes for the a tag.
     * @return string The link XHTML.
     */
    public function getHtml($content, $className = null, $attribs = null) {

        if (!is_array($attribs)) {
            $attribs = array();
        }

        if ($className) {
            $attribs['class'] = $className;
        }

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        return '<span' . $attribs . '>' . $content . '</span>';
    }
}
