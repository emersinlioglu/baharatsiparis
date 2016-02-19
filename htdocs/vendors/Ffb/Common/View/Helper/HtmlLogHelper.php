<?php

namespace Ffb\Common\View\Helper;

/**
 * Class HtmlLogHelper
 * @package Ffb\Common\View\Helper
 */
class HtmlLogHelper extends AbstractHtmlElement {

    /**
     * Create log table html
     *
     * @param array $data       Log data
     * 'date'    = \DateTime
     * 'user'    = String,
     * 'changes' = String,
     * 'item'    = String     
     * 
     * @return string Table html
     */
    public function __invoke(array $data = array()) {
        return $this->getHtml($data);
    }

    /**
     * Create log table html
     *
     * @param array $data       Log data
     * 'date'    = \DateTime
     * 'user'    = String,
     * 'changes' = String,
     * 'item'    = String     
     * 
     * @return string Table html
     */
    public function getHtml(array $data = array()) {   
                                              
        if (count($data) === 0) {
            return '';
        }                
        
        $translator = \Ffb\Common\I18n\Translator\Translator::Instance();        
        $datetime   = \Ffb\Common\DateFormatter\DateFormatter::getInstance();        
        
        $headers = array(
            '<th>' . $translator->translate('TTL_DATE') . '</th>',
            '<th>' . $translator->translate('TTL_MODIFIER') . '</th>',
            '<th>' . $translator->translate('TTL_LOG_ACTION') . '</th>',
            '<th>' . $translator->translate('TTL_LOG_ITEM') . '</th>'
        );
        
        $rows = array();
        foreach ($data as $row) {            
            
            $rows[] = '<tr><td>' . implode('</td><td>', array(
                $datetime->format($row['date'], 'long'), 
                $row['user'], 
                $translator->translate($row['changes']), 
                $translator->translate($row['item']))
                ) . '</td></tr>';
        }              
        
        $html  = '<table class="table-default table-log">';
        $html .= '<thead><tr>' . implode('', $headers) . '</tr></thead>';
        $html .= '<tbody>' . implode('', $rows) . '</tbody>';
        $html .= '</table>';       

        return $html;
    }   
}