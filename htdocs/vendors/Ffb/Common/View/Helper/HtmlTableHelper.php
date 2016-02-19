<?php

namespace Ffb\Common\View\Helper;

use Ffb\Common\I18n\Translator\Translator;

/**
 * Class HtmlTableHelper
 * @package Ffb\Common\View\Helper
 */
class HtmlTableHelper extends AbstractHtmlElement {

    /**
     * Default amount of item per page.
     *
     * @var int
     */
    const PER_PAGE = 10;

    /**
     * @var
     */
    protected $_id = null;

    /**
     * @var array
     */
    protected $_attribs = array();

    /**
     * @var
     */
    protected $_headers;

    /**
     * @var array
     */
    protected $_rows = array();

    /**
     * @var bool
     */
    protected $_sort;

    /**
     * If sorting should be enabled.
     *
     * @var bool
     */
    protected $_enableSort;

    /**
     * @var
     */
    protected $_pagination;

    /**
     * @var
     */
    protected $_search;

    /**
     * Array with columns names to filter
     *
     * @var
     */
    protected $_filters;

    /**
     * @var
     */
    protected $_itemsCount;

    /**
     * @var
     */
    protected $_totalCount;

    /**
     * @var
     */
    protected $_sortCondition;

    /**
     * @var
     */
    protected $_sortDirection;

    /**
     * @var
     */
    protected $_class;

    /**
     * @var string
     */
    protected $_dataUrl;

    /**
     * @var int
     */
    protected $_currentPage = 0;

    /**
     * @var int
     */
    protected $_perPage = self::PER_PAGE;

    /**
     * @var
     */
    protected $_position;

    /**
     * @var
     */
    protected $_scrollbar = null;

    /**
     * @var
     */
    protected $_footer = null;

    /**
     * @param bool $enableSort [optional]
     *         if sorting should be enabled
     */
    public function __construct($enableSort = true) {
        $this->_enableSort = $enableSort;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getHtml();
    }

    /**
     * Create table html with paginator and counter
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $rows       Rows array
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @param array $attribs    Attributes for the a tag.
     * @return string Table html
     */
    public function __invoke(
        array $headers = null,
        array $rows = null,
        array $sort = null,
        array $pagination = null,
        array $attribs = null
    ) {
        return $this->getHtml($headers, $rows, $sort, $pagination, $attribs);
    }

    /**
     * Create table html with paginator and counter
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $rows       Rows array
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @param array $attribs    Attributes for the a tag.
     * @param bool  $escape     Whether to escape html special chars in return value
     * @return string Table html
     */
    public function getHtml(
        array $headers = null,
        array $rows = null,
        array $sort = null,
        array $pagination = null,
        array $attribs = null,
        $escape = false
    ) {

        $headers = (is_array($this->_headers) && count($this->_headers) === 0 ) ? null : $this->_headers;

        $rows = $this->_rows;

        $sort = array(
            'condition' => $this->_sortCondition,
            'direction' => $this->_sortDirection
        );

        $pagination = array(
            'currentPage' => $this->_currentPage,
            'perPage'     => $this->_perPage > 0 ? $this->_perPage : 10,
            'itemsCount'  => $this->_itemsCount
        );

        $attribs = array(
            'class' => 'table-default ' . $this->_class
        );
        // _dataUrl is optional
        if ($this->_dataUrl) {
            $attribs['data-url'] = $this->_dataUrl;
        }

        if (isset($this->_id)) {
            $attribs['id'] = $this->_id;
        }

        $html = $this->getPaginationHtml($pagination, 'top');
        $html .= $this->getTableHtml($headers, $rows, $sort, $attribs, $escape);
        $html .= $this->getPaginationHtml($pagination, 'bottom');

        return $html;
    }

    /**
     * Create pagination html
     *
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @param string $position Paging position ['top', 'bottom']
     * @return string Pagination html
     */
    public function getPaginationHtml($pagination = null, $position = null) {

        $html = '';

        if (!$pagination) {
            return $html;
        }

        if ($pagination['perPage'] >= $pagination['itemsCount']) {
            return '<div class="table-paging' . (($position) ? ' ' . $position : '') . '"></div>';
        }

        $curr  = $pagination['currentPage'];
        $pages = $pagination['itemsCount'] / $pagination['perPage'];
        $total = $pagination['itemsCount'];

        if ($pagination['itemsCount'] % $pagination['perPage'] !== 0) {
            $pages++;
        }
        $pages = (int)$pages - 1;

        if ($pages <= 0) {
            return $html;
        }

        $html .= '<div class="table-paging' . (($position) ? ' ' . $position : '') . '">';
        $html .= Translator::translate('STR_PAGE') . ': ';
        //Add preious
        if ($curr > 0) {
            $html .= '<span class="page prev" data-page="' . ($curr - 1) . '">&lt;</span>';
        }

        //Add first page
        if ($curr > 2) {
            $html .= '<span class="page first" data-page="0">1</span>';
            $html .= '&nbsp;...&nbsp;';
        }

        //Add pages
        $startI = $curr - 1;
        $finishI = $curr + 1;
        if ($curr <= 2) {
            $startI = 0;
            $finishI = 2;
        }
        if ($curr > 2 && $curr >= $pages - 2) {
            $startI = $pages - 2;
            $finishI = $pages;
        }
        if ($startI < 0) {
            $startI = 0;
        }
        if ($finishI > $pages) {
            $finishI = $pages;
        }
        for ($i = $startI; $i <= $finishI; $i++) {
            $active = '';
            if ($curr === $i) {
                $active = ' active';
            }
            $html .= '<span class="page' . $active . '" data-page="' . $i . '">' . ($i + 1) . '</span>';
        }

        //Add last page
        if ($curr < $pages - 2) {
            $html .= '&nbsp;...&nbsp;';
            $html .= '<span class="page last" data-page="' . $pages . '">' . ($pages + 1) . '</span>';
        }

        //Add next
        if ($curr < $pages) {
            $html .= '<span class="page next" data-page="' . ($curr + 1) . '">&gt;</span>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Create table body html
     *
     * @param array $rows       Rows array
     * @param bool  $escape     Whether to escape html special chars in return value
     * @return string Table body html
     */
    public function getTableBodyHtml($rows = null, $escape = false) {

        $html = '';
        if (!is_array($rows) || count($rows) === 0) {
            return $html;
        }

        if ($escape) {
            $escaper = $this->getEscaper();
        }

        foreach ($rows as $i => $row) {

            if (array_key_exists('attribs', $row) && is_array($row['attribs'])) {
                $attribs = $this->htmlAttribs($row['attribs']);
            } else {
                $attribs = '';
            }

            $html .= '<tr' . $attribs . '>';
            foreach ($row['columns'] as $k => $column) {
                $attribs = '';
                if (is_array($column)) {
                    list($column, $attribs) = $column;
                    $attribs = $this->htmlAttribs($attribs);
                }
                if ($escape) {
                    $html .= '<td' . $attribs . '>' . $escaper->escapeHtml($column) . '</td>' . self::EOL;
                } else {
                    $html .= '<td' . $attribs . '>' . $column . '</td>' . self::EOL;
                }
            }
            $html .= '</tr>' . self::EOL;
        }

        if (strlen($html) > 0) {
            $html = '<tbody>' . $html . '</tbody>' . self::EOL;
        }

        return $html;
    }

    /**
     * Create table headers html
     *
     * @param array $headers    Headers array ['title', 'sortcon']
     * @param array $sort       Sort data ['condition', 'direction' => {'asc'|'desc'}]
     * @param bool  $escape     Whether to escape html special chars in return value
     * @return string Table header html
     */
    public function getTableHeaderHtml($headers = null, $sort = null, $escape = false) {

        $html = '';
        if (!is_array($headers) || count($headers) === 0) {
            return $html;
        }

        $html .= '<tr>';
        foreach ($headers as $i => $header) {

            $htmlAttributes = '';
            if (isset($header['attributes'])) {
                $htmlAttributes = $this->getHtmlAttributes($header['attributes']);
            }

            // add extra class for agencies
            $agencyClass = '';

            if (   array_key_exists('sortcon', $header)
                && is_array($sort)
                && array_key_exists('condition', $sort)
                && $this->_enableSort === true
            ) {
                if (!isset($sort['direction'])) {
                    $sort['direction'] = 'asc';
                }
                if ($header['sortcon'] == 'agency') {
                    $agencyClass = ' agency';
                }

                $isAscActive = '';
                $isDescActive = '';
                if ($sort['condition'] === $header['sortcon']) {
                    if ($sort['direction'] === 'asc') {
                        $isAscActive = ' active';
                    }
                    if ($sort['direction'] === 'desc') {
                        $isDescActive = ' active';
                    }
                }

                if ($escape) {
                    $escaper = $this->getEscaper();
                    $html .= '<th ' . $htmlAttributes . '><span class="title sortable' . $agencyClass . '">' . $escaper->escapeHtml($header['title']);
                    $html .= '<span data-sortcon="' . $header['sortcon'] . '" class="sort asc' . $isAscActive . '"></span>';
                    $html .= '<span data-sortcon="' . $header['sortcon'] . '" class="sort desc' . $isDescActive . '"></span>';
                } else {
                    $html .= '<th ' . $htmlAttributes . '><span class="title sortable' . $agencyClass . '">' . $header['title'];
                    $html .= '<span data-sortcon="' . $header['sortcon'] . '" class="sort asc' . $isAscActive . '"></span>';
                    $html .= '<span data-sortcon="' . $header['sortcon'] . '" class="sort desc' . $isDescActive . '"></span>';
                }
            } else {
                if ($escape) {
                    $escaper = $this->getEscaper();
                    $html .= '<th ' . $htmlAttributes . '><span class="title' . $agencyClass . '">' . $escaper->escapeHtml($header['title']);
                } else {
                    $html .= '<th ' . $htmlAttributes . '><span class="title' . $agencyClass . '">' . $header['title'];
                }
            }
            $html .= '</span></th>' . self::EOL;
        }
        $html .= '</tr>' . self::EOL;

        if (strlen($html) > 0) {
            $html = '<thead>' . $html . '</thead>' . self::EOL;
        }

        return $html;
    }

    /**
     * Generates html attribute string from array
     *
     * @param array $attributes
     * @return string
     */
    public function getHtmlAttributes($attributes) {

        $string = join(' ', array_map(function($key) use ($attributes)
        {
           if(is_bool($attributes[$key]))
           {
              return $attributes[$key]?$key:'';
           }
           return $key.'="'.$attributes[$key].'"';
        }, array_keys($attributes)));

        return $string;
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

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        if (isset($this->_scrollbar)) {
            $html = '<div class="scrollbar">';
            $html .= '<table' . $attribs . '>';
        } else {
            $html = '<table' . $attribs . '>';
        }

        $html .= $this->getTableHeaderHtml($headers, $sort, $escape);
        $html .= $this->getTableBodyHtml($rows, $escape);

        if (isset($this->_scrollbar)) {
            $html .= '</table>' . '</div>' . self::EOL;
        } else {
            $html .= '</table>' . self::EOL;
        }

        if (isset($this->_footer)) {
            $html .= '<div class="table-default-footer">';
            $html .= $this->_footer;
            $html .= '</div>' . self::EOL;
        }

        return $html;
    }

    /**
     * Create total counter html
     *
     * @param array $pagination Pagination data ['currentPage', 'perPage', 'itemsCount']
     * @return string Counter html
     */
    public function getTotalHtml($pagination = null) {

        $html = '';
        if (!$pagination) {
            return $html;
        }

        $showed = ($pagination['currentPage'] * $pagination['perPage'] + $pagination['perPage']);
        if ($showed > $pagination['itemsCount']) {
            $showed = $pagination['itemsCount'];
        }

        $html .= '<div class="table-entries">';
        $html .= Translator::translate('STR_ENTRIES') . ': ';
        $html .= $showed . ' / ' . $pagination['itemsCount'];
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array $attribs
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setAttribs(array $attribs) {
        $this->_attribs = $attribs;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttribs() {
        return $this->_attribs;
    }

    /**
     * @param mixed $filters
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setFilters($filters) {
        $this->_filters = $filters;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilters() {
        return $this->_filters;
    }

    /**
     * @param mixed $headers
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setHeaders($headers) {
        $this->_headers = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaders() {
        return $this->_headers;
    }

    /**
     * @param mixed $itemCount
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setItemsCount($itemCount) {
        $this->_itemsCount = (int)$itemCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getItemsCount() {
        return $this->_itemsCount;
    }

    /**
     * @param mixed $pagination
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setPagination($pagination) {
        $this->_pagination = $pagination;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPagination() {
        return $this->_pagination;
    }

    /**
     * @param mixed $rows
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setRows($rows) {
        $this->_rows = $rows;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRows() {
        return $this->_rows;
    }

    /**
     * @param mixed $search
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setSearch($search) {
        $this->_search = $search;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearch() {
        return $this->_search;
    }

    /**
     * @param mixed $sort
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setSort(array $sort) {
        $this->_sort = $sort;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSort() {
        return $this->_sort;
    }

    /**
     * @param mixed $class
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setClass($class) {
        $this->_class = $class;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass() {
        return $this->_class;
    }

    /**
     * @param mixed $currentPage
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setCurrentPage($currentPage) {
        $this->_currentPage = $currentPage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage() {
        return $this->_currentPage;
    }

    /**
     * @param $dataUrl
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setDataUrl($dataUrl) {
        $this->_dataUrl = $dataUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataUrl() {
        return $this->_dataUrl;
    }

    /**
     * @param mixed $perPage
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setPerPage($perPage) {
        $this->_perPage = $perPage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPerPage() {
        return $this->_perPage;
    }

    /**
     * @param $sortDirection
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setSortDirection($sortDirection) {
        $this->_sortDirection = $sortDirection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortDirection() {
        return $this->_sortDirection;
    }

    /**
     * @param mixed $totalCount
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setTotalCount($totalCount) {
        $this->_totalCount = $totalCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalCount() {
        return $this->_totalCount;
    }

    /**
     * @param mixed $position
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setPosition($position) {
        $this->_position = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition() {
        return $this->_position;
    }

    /**
     * @param mixed $sortCondition
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setSortCondition($sortCondition) {
        $this->_sortCondition = $sortCondition;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortCondition() {
        return $this->_sortCondition;
    }

    /**
     * @param $id
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->_id;
    }

    /**
     *
     */
    public function disableSort() {
        $this->_sort = false;
    }

    /**
     * @param mixed $scrollbar
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setScrollbar($scrollbar) {
        $this->_scrollbar = $scrollbar;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScrollbar() {
        return $this->_scrollbar;
    }

    /**
     * @param mixed $footer
     * @return \Ffb\Common\View\Helper\HtmlTableHelper
     */
    public function setFooter($footer) {
        $this->_footer = $footer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFooter() {
        return $this->_footer;
    }

}
