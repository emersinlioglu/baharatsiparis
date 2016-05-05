<?php

namespace Ffb\Frontend\View\Helper;

/**
 * Helper for html linked list
 */
class HtmlLinkedListHelper extends AbstractBackendHtmlElement {

    /**
     * Generates a linked list html by invoke
     *
     * @param array $data
     * @param string $className
     * @param string $attribs
     * @return string
     */
    public function __invoke(array $data, $className = null, $attribs = null) {

        return $this->getHtml($data, $className, $attribs);
    }

    /**
     * Generates a linked list html
     *
     * @param  array $data   array of li element contents
     * @param  string $className Link CSS class
     * @param  array $attribs    Attributes for the a tag.
     * @return string The link XHTML.
     */
    public function getHtml(array $data, $className = null, $attribs = null, $showEntrySpan = true) {

        if (!is_array($attribs)) $attribs = array();
        if ($className) $attribs['class'] = $className;

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        $out = '<ul ' . $attribs . '>';
        foreach($data as $content) {
            $out .= '<li>';
            if ($showEntrySpan) {
                $out .= '<span class="entry-action hidden"></span><span class="entry-name">';
            }
            
            $out .= $content;

            if ($showEntrySpan) {
                $out .= '</span>';
            }
            $out .= '</li>';
        }
        $out .= '</ul>';

        return $out;
    }
}
