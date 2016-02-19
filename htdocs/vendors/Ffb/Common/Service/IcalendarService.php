<?php

namespace Ffb\Common\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service to generate icalendar contents.
 *
 * @see DERTRA-573
 * @author erdal.mersinlioglu
 */
class IcalendarService extends AbstractService {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param $user (optional) entity of user
     */
    public function __construct(ServiceLocatorInterface $sl, $user = null) {
        parent::__construct($sl, $user);

        $this->_translator = \Ffb\Common\I18n\Translator\Translator::getTranslator();
    }

    /**
     * Generate content.
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function generateIcalendar(array $data = array()) {

        $content[] = "BEGIN:VCALENDAR";
        $content[] = "VERSION:2.0";
        $content[] = "PRODID:-//hacksw/handcal//NONSGML v1.0//EN";
        $content[] = "BEGIN:VEVENT";

        // check minimum requirements
        if (!array_key_exists('SUMMARY', $data) ||
            !array_key_exists('DTSTART', $data) ||
            !array_key_exists('DTEND', $data)) {

            throw new \Exception($this->_translator->translate('MSG_SUMMARY_DTSTART_AND_DTEND_NEEDED'));
        }

        // check if from and until values are date
        if (false === ($data['DTSTART'] instanceof \DateTime) ||
            false === ($data['DTEND'] instanceof \DateTime)) {

            throw new \Exception($this->_translator->translate('MSG_DTSTART_AND_DTEND_MUST_BE_DATE'));
        }

        foreach($data as $key => $value) {

            if ($value instanceof \DateTime) {
                $content[] = strtoupper($key) . ":" . $value->format('Ymd\Tgis\Z');
            } else {
                $value     = str_replace("\n", "\\n", $value);
                $content[] = strtoupper($key) . ":" . $value;
            }
//            switch ($key) {
//                case 'UID':
//                    $content[] = "UID:" . $value;
//                    break;
//                case 'DTSTAMP':
//                    $content[] = "DTSTAMP:" . date('Ymd\Tgis\Z', time());
//                    break;
//                case 'DTSTART':
//                    if ($value instanceof \DateTime) {
//                        $content[] = "DTSTART:" . $value->format('Ymd\Tgis\Z');
//                    } else {
//                        throw new \Exception('MSG_NO_START_DATE');
//                    }
//                    break;
//                case 'DTEND':
//                    if ($value instanceof \DateTime) {
//                        $content[] = "DTEND:" . $value->format('Ymd\Tgis\Z');
//                    } else {
//                        throw new \Exception('MSG_NO_END_DATE');
//                    }
//                    break;
//                case 'SUMMARY':
//                    $content[] = "SUMMARY:" . $value;
//                    break;
//                case 'DESCRIPTION':
//                    $content[] = "DESCRIPTION:" . $value;
//                    break;
//                case 'LOCATION':
//                    $content[] = "LOCATION:" . $value;
//                    break;
//                default:
//                    break;
//            }
        }

        $content[] = "END:VEVENT";
        $content[] = "END:VCALENDAR";
        $content = implode(PHP_EOL, $content);

        return $content;
    }

}
