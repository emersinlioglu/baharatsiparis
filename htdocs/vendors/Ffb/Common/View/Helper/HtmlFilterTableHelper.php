<?php

namespace Ffb\Common\View\Helper;

use Zend\Form\Element;
use Zend\Form\View\Helper;
use Zend\Json\Json;

/**
 * Class HtmlFilterTableHelper
 *
 * @package Ffb\Common\View\Helper
 */
class HtmlFilterTableHelper extends HtmlTableHelper {

    /**
     * Array of action URLs that will be displayed as selectbox in the table
     * footer.
     *
     * @var array
     */
    protected $_actions = array();

    /**
     * Array of download URLs and names that will be displayed as links in the
     * table footer.
     *
     * @var array
     */
    protected $_downloads = array();

    /**
     * If searching is enabled.
     *
     * @var boolean
     */
    protected $_isSearchable = true;

    /**
     * Is self init.
     *
     * @var boolean
     */
    protected $_isSelfInit = false;

    /**
     * label for table.
     *
     * @var string
     */
    protected $_label;

    /**
     * If an expert view is available.
     * Disabled by default.
     *
     * @var boolean
     */
    protected $_isExpertView = false;

    /**
     * Array with extra attributes for parent table only
     *
     * @var array
     */
    protected $_extraAttribs = array();

    /**
     * Array with options for expert show/hide columns button.
     *
     * @var array
     */
    protected $_expertViewColumns = array();

    /**
     * Array with existing expert views and links to CRUD 'em.
     * [
     *     'addviewurl' =>
     *     'updateviewurl' =>
     *     'deleteviewurl' =>
     *     'views' => [
     *         [
     *             'id'   =>
     *             'name' =>
     *             'json' =>
     *             'isdeletable' =>
     *         ],
     *         ...
     *     ]
     * ]
     *
     * @var array
     */
    protected $_expertViews = array();

    /**
     * Array with expert view filters.
     *
     * @var array
     */
    protected $_expertFilters = array();

    /**
     * Create table html with paginator and counter
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $rows       Rows array
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @param array $attribs    Attributes for the a tag.
     * @param array $filters    Array with elements for filters
     * @param array $actions    Array with actions options
     * @return string Table html
     */
    public function __invoke(
        array $headers = null,
        array $rows = null,
        array $sort = null,
        array $pagination = null,
        array $attribs = null,
        array $filters = null,
        array $actions = null
    ) {
        return $this->getHtml($headers, $rows, $sort, $pagination, $attribs, false, $filters, $actions);
    }

    /**
     * Create table html with paginator and counter
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $rows       Rows array
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @param array $attribs    Attributes for the a tag.
     * @param array $filters    Array with elements for filters
     * @param array $actions    Array with actions options
     * @param bool  $escape     Whether to escape html special chars in return value
     * @return string Table html
     */
    public function getHtml(
        array $headers = null,
        array $rows = null,
        array $sort = null,
        array $pagination = null,
        array $attribs = null,
        $escape = false,
        array $filters = null,
        array $actions = null
    ) {

        if ($headers) {
            $this->setHeaders($headers);
        }

        if ($rows) {
            $this->setRows($rows);
        }

        if ($sort) {
            $this->setSort($sort);
        }

        if ($pagination) {
            $this->setPagination($pagination);
        }

        if ($filters) {
            $this->setFilters($filters);
        }

        if ($actions) {
            $this->setActions($actions);
        }

        // prepare attributes
        $defAttribs = array(
            'class' => 'table-default table-filtered ' . $this->_class
        );

        $this->_extraAttribs['data-url'] = $this->_dataUrl;

        // get expert view settings
        if ($this->getIsExpertView() && count($this->getExpertViewColumns() > 0)) {
            $this->_extraAttribs['data-expertcolumns'] = Json::encode($this->getExpertViewColumns());
        }

        if ($this->getExpertFilters() && count($this->getExpertFilters() > 0)) {
            $this->_extraAttribs['data-expertfilters'] = Json::encode($this->getExpertFilters());
        }

        if ($this->getIsExpertView() && $this->getExpertViews() && count($this->getExpertViews() > 0)) {
            $this->_extraAttribs['data-expertviews'] = Json::encode($this->getExpertViews());
        }

        if (!$attribs) {
            $attribs = array();
        }
        $this->setAttribs(array_merge($defAttribs, $this->getAttribs(), $attribs));

        // get table html
        $html = isset($this->_label) ? $this->_label : '';
        $html .= $this->getTableHtml(null, null, null, null, $escape);

        return $html;
    }

    /**
     * Create table html
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $rows       Rows array
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param array $attribs    Attributes for the a tag.
     * @param bool  $escape     Whether to escape html special chars in return value
     * @return string Table html
     */
    public function getTableHtml(
        $headers = null,
        $rows = null,
        $sort = null,
        $attribs = null,
        $escape = false
    ) {

        // get class only if exist
        if (isset($this->_attribs['class'])) {
            $attribs = $this->htmlAttribs(array('class' => $this->_attribs['class']));
        } else {
            $attribs = '';
        }

        $sort = array(
            'condition' => $this->_sortCondition,
            'direction' => $this->_sortDirection
        );

        // get headers and body html
        $headers = $this->getTableHeaderHtml($this->_headers, $sort, $escape);
        $body    = $this->getTableBodyHtml($this->_rows, $escape);

        // create content table
        $table = '<div class="table-filtered-dynamic">' . self::EOL;

        // create dynamic headers
        $table .= '<div class="table-filtered-headers">' . self::EOL;
        $table .= '<table' . $attribs . '>';
        $table .= $headers;
        $table .= '</table>' . self::EOL;
        $table .= '</div>' . self::EOL;

        // create dynamic rows
        $table .= '<div class="table-filtered-list">' . self::EOL;
        $table .= '<table' . $attribs . '>';
        $table .= $headers;
        $table .= $body;
        $table .= '</table>' . self::EOL;
        $table .= '</div>' . self::EOL;

        $table .= '</div>' . self::EOL;

        // get filters
        $filters = $this->getTableFilterHtml();

        // get footer
        $footer  = $this->getTableFooterHtml();

        // create table html
        $html = '<div' . $this->htmlAttribs(array_merge($this->_attribs, $this->_extraAttribs)) . '>';
        $html .= $filters . self::EOL;
        $html .= $table . self::EOL;
        $html .= $footer . self::EOL;
        $html .= '</div>' . self::EOL;

        return $html;
    }

    /**
     * Create HTML for filters and search.
     *
     * @return string
     */
    public function getTableFilterHtml() {

        $html   = '';
        if (count($this->_filters) === 0 && !$this->_isSearchable) {
            return $html;
        }

        // prepare buttons container
        $buttons = array();

        $formInput  = new Helper\FormInput();
        $formSelect = new Helper\FormSelect();
        $html .= '<div class="table-filtered-filters">';

        // create filters
        if (count($this->_filters) > 0) {

            $columns = new Element\Select('column');
            if ($this->_isSelfInit) {
                $columns->setAttribute('class', 'self-init');
            }
            $columns->setValueOptions($this->_getFilterValueOptions());

            $html .= '<div class="filters">';
            $html .= '<label>' . $this->getTranslation('LBL_FILTER') . '</label>';
            $html .= '<div class="column-select">' . $formSelect->render($columns) . '</div>';
            $html .= '<div class="column-value-select"></div>';
            $html .= '</div>';
        }

        // create search
        if (count($this->_isSearchable) > 0) {

            $input = new Element\Text('search');
            $input->setAttribute('class', 'search');
            $input->setAttribute('placeholder', $this->getTranslation('PLH_SEARCH'));

            $html .= '<div class="search">';
            $html .= $formInput->render($input);
            $html .= '<span class="search-icon"></span>';
            $html .= '</div>';
        }

        // create expert view button
        if ($this->_isExpertView) {

            $buttons[] = '<div class="button gray view expert">' . $this->getTranslation('BTN_EXPERT_VIEW') . '</div>';
        }

        // create buttons area
        if (count($buttons) > 0) {
            $html .= '<div class="links-and-buttons">';
            foreach ($buttons as $button) {
                $html .= $button;
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Prepares the valueoptions for filter-select element
     *
     * @return array
     */
    private function _getFilterValueOptions() {

        $valueOptions = array();

        // empty option
        $valueOptions[] = array(
            'label' => $this->getTranslation('OPT_ALL'),
            'value' => ''
        );

        // other options
        foreach ($this->_filters as $filter) {

            $valueOptions[] = array(
                'label' => $filter['title'],
                'value' => $filter['value'],
                'attributes' => array(
                    'data-options' => Json::encode($filter['options'])
                )
            );
        }

        return $valueOptions;
    }

    /**
     * Create table footer html
     *
     * @return string
     */
    public function getTableFooterHtml() {


        $html = '';

        // show action selectbox including a checkbox to select all items
        if (count($this->_actions)) {
            $formSelect = new Helper\FormSelect();
            $actions = new Element\Select('actions');
            if ($this->_isSelfInit) {
                $actions->setAttribute('class', 'self-init');
            }
            $actions->setValueOptions($this->_actions);
            $html .= '    <div class="actions">';
            $html .= '        <div class="select-all"></div>';
            $html .= '        <label>' . $this->getTranslation('LBL_ACTIONS') . '</label>';
            $html .= '        <div class="action-select">' . $formSelect->render($actions) . '</div>';
            $html .= '    </div>';
        }

        // show downloads
        if (count($this->_downloads)) {
            $html .= '    <div class="downloads">';
            foreach ($this->_downloads as $download) {
                $html .= '    <a href="' . $download['url'] . '">' . $download['name'] . '</a>';
            }
            $html .= '    </div>';
        }

        // show statistics for expert view
        if ($this->getIsExpertView()) {
            $html .= '    <div class="rows-selected">' . $this->getTranslation('LBL_SELECTED_ROWS') . '<span></span></div>';
            $html .= '    <div class="total-rows-selected">' . $this->getTranslation('LBL_TOTAL_ROWS') . '<span></span></div>';
        }

        if (strlen($html)) {
            $html = '<div class="table-filtered-actions">' . $html . '</div>';
        }

        return $html;
    }

    /**
     * Get array of action URLs that will be displayed as selectbox in the table
     * footer.
     *
     * @return array
     */
    public function getActions() {
        return $this->_actions;
    }

    /**
     * Set array of action URLs that will be displayed as selectbox in the table
     * footer.
     *
     * @param array $actions
     */
    public function setActions(array $actions) {
        $this->_actions = $actions;
        return $this;
    }

    /**
     * Get array of download URLs and names that will be displayed as links in
     * the table footer.
     *
     * @return array
     */
    public function getDownloads() {
        return $this->_downloads;
    }

    /**
     * Set array of download URLs and names that will be displayed as links in
     * the table footer.
     *
     * @param array
     */
    public function setDownloads(array $downloads) {
        $this->_downloads = $downloads;
        return $this;
    }

    /**
     * @return the $_isSearchable
     */
    public function getIsSearchable() {
        return $this->_isSearchable;
    }

    /**
     * @param boolean $isSearchable
     */
    public function setIsSearchable($isSearchable) {
        $this->_isSearchable = $isSearchable;
        return $this;
    }

    /**
     * @param boolean $isSelfInit
     */
    public function setIsSelfInit($isSelfInit) {
        $this->_isSelfInit = $isSelfInit;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->_label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label) {
        $this->_label = $label;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getIsExpertView() {
        return $this->_isExpertView;
    }

    /**
     *
     * @param boolean $isExpertView
     * @return \Ffb\Common\View\Helper\HtmlFilterTableHelper
     */
    public function setIsExpertView($isExpertView) {
        $this->_isExpertView = $isExpertView;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getExpertViewColumns() {
        return $this->_expertViewColumns;
    }

    /**
     *
     * @param array $expertViewColumns
     * @return \Ffb\Common\View\Helper\HtmlFilterTableHelper
     */
    public function setExpertViewColumns($expertViewColumns) {
        $this->_expertViewColumns = $expertViewColumns;
        return $this;
    }

    /**
     * @return array
     */
    public function getExpertViews() {
        return $this->_expertViews;
    }

    /**
     *
     * @param array $expertViews
     * @return \Ffb\Common\View\Helper\HtmlFilterTableHelper
     */
    public function setExpertViews($expertViews) {
        $this->_expertViews = $expertViews;
        return $this;
    }

    /**
     * @return array
     */
    public function getExpertFilters() {
        return $this->_expertFilters;
    }

    /**
     * @param array $expertFilters
     * @return \Ffb\Common\View\Helper\HtmlFilterTableHelper
     */
    public function setExpertFilters($expertFilters) {
        $this->_expertFilters = $expertFilters;
        return $this;
    }

}