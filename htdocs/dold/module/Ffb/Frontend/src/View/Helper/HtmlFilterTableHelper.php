<?php

namespace Ffb\Frontend\View\Helper;

use Ffb\Common\I18n\Translator\Translator;

/**
 * Class HtmlTableHelper
 * @package Ffb\Tms\View\Helper
 */
class HtmlFilterTableHelper extends \Ffb\Common\View\Helper\HtmlFilterTableHelper {

    /**
     * @param bool $sort
     */
    public function __construct($sort = true) {
        parent::__construct($sort);

        $this->setTranslations(array(
            'LBL_FILTER'  => Translator::translate('LBL_FILTER'),
            'PLH_SEARCH'  => Translator::translate('PLH_SEARCH'),
            'LBL_ACTIONS' => Translator::translate('LBL_ACTIONS'),
            'OPT_ALL'     => Translator::translate('OPT_ALL'),
        ));
    }

}