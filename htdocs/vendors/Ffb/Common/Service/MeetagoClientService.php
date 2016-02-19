<?php

namespace Ffb\Common\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

/**

 * @see DERTMS-887
 * @see http://framework.zend.com/manual/2.1/en/modules/zend.mail.introduction.html
 * @see http://www.zendframeworkmagazin.de/zf/blog/zend-mail-und-zend-mime-mailen-mit-dem-zend-framework-2
 * @author ilja.schwarz
 */
class MeetagoClientService extends AbstractService {

    /**
     * List of the states for insert
     *
     * @var type
     */
    private $_visibleStatusCategories = array(
        'BOOKING'
    );

    /**
     * Configuration
     *
     * @var array
     */
    protected $_conf;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param $user (optional) entity of user
     */
    public function __construct(ServiceLocatorInterface $sl, $user = null) {
        parent::__construct($sl, $user);

        // prepare config
        $this->_conf = array(
            'login'    => null,
            'password' => null,
            'url'      => null,
            'wsdl'     => null,
            'language' => null,
            'proxy'    => array(
                'proxy_host'     => null,
                'proxy_port'     => null,
                'proxy_login'    => null,
                'proxy_password' => null
            )
        );

        // set values from sl
        $this->_parseConfig();
    }

    /**
     * Search hotel records by recordId
     *
     * @param integer $recordId
     * @return array $records
     */
    public function search($recordId) {

        $records  = array();

//        RecordsResponseType Records(RecordsRequestType $RecordsRequest)
//        RecordDetailsResponseType RecordDetails(RecordDetailsRequestType $RecordDetailsRequest)
//        HotelsResponseType Hotels(HotelsRequestType $HotelRequest)
//        HotelDetailsResponseType HotelDetails(HotelDetailsRequestType $HotelDetailsRequest)
//        PingResponseType Ping(PingRequestType $PingRequest)

        // prepare request object
        $recordsRequest = new \stdClass();
        $recordsRequest->recordId = (int)trim($recordId);
        $recordsRequest->language = $this->_conf['language'];
        $recordsRequest->login = $this->_conf['login'];
        $recordsRequest->password = $this->_conf['password'];

        // get client
        $client   = $this->_newClient();

        // call
        $response = $client->recordDetails($recordsRequest);

        // check response
        $this->_checkSoapResponse($response);

        // if empty
        if (!isset($response->RequestHotels)) {
            return $records;
        }

        // create array of objects couse return may array or 1 object been
        if (!is_array($response->RequestHotels)) {
            $response->RequestHotels = array($response->RequestHotels);
        }

        // parse response
        foreach ($response->RequestHotels as $hotelObject) {

            // check status
            if (!in_array($hotelObject->statusCategory, $this->_visibleStatusCategories)) continue;

            // parse data
            $records[] = $this->_parseHotelData($hotelObject);
        }

        return $records;
    }

    /**
     * Return new SoapClient
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param array $config
     *         Config values
     * @throws Exception
     *         if soap wsdl to use could not determined
     * @return \Zend\Soap\Client
     */
    protected function _newClient(
        \Zend\ServiceManager\ServiceLocatorInterface $sl = null,
        array $config = null
    ) {

        if (!$sl) {
            $sl = $this->_sl;
        }

        if (!$config) {
            $config = $this->_conf;
        }

        if (!$config['wsdl']) {
            throw new \Exception('soap wsdl to use could not determined');
        }

        $options = array(

            // The soap_version option should be one of either SOAP_1_1 or
            // SOAP_1_2 to select SOAP 1.1 or 1.2, respectively.
            // If omitted, 1.1 is used.
            'soap_version' => SOAP_1_2,

            // The encoding option defines internal character encoding.
            // This option does not change the encoding of SOAP requests
            // (it is always utf-8), but converts strings into it.
            'encoding' => 'UTF-8',

            // The trace option enables tracing of request so faults can be
            // backtraced. This defaults to FALSE
            // http://php.net/manual/de/soapclient.soapclient.php#111682
            'trace' => 1,

            // The exceptions option is a boolean value defining whether soap
            // errors throw exceptions of type SoapFault.
            'exceptions' => 0,

            // The connection_timeout option defines a timeout in seconds for
            // the connection to the SOAP service. This option does not define
            // a timeout for services with slow responses. To limit the time to
            // wait for calls to finish the default_socket_timeout setting is
            // available.
            'connection_timeout' => 60
        );

        // set proxy options
        foreach ($config['proxy'] as $key => $value) {

            if (!$value) continue;

            $options[$key] = $value;
        }

        // init SOAP client in plain vanilla style
        $client = new \SoapClient($config['wsdl'], $options);

        return $client;
    }

    /**
     * Performs standard checks for validity of SOAP response.
     * In case of errors an Exception will be thrown.
     *
     * The response will be interpreted as an error:
     * - if response is an instance of SoapFault
     * - if response is not an array
     * - if response array has no state
     * - if response array has a state which is not 'ok'
     *
     * @param mixed $response
     * @throws \Exception if response is or indicates an error
     */
    protected function _checkSoapResponse($response) {

        if (is_object($response) && $response instanceof \SoapFault) {
            error_log($response->getMessage());
            error_log($response->getTraceAsString());
            throw new \Exception($response->getMessage());
        }
    }

    /**
     * Parse config from input
     *
     * @param array $config
     */
    protected function _parseConfig(array $config = null) {

        if (!$config) {
            $config = $this->_sl->get('Config');
        }

        // get service config
        if (isset($config['service'], $config['service']['meetago_webservice'])) {
            $this->_conf = array_merge($this->_conf, $config['service']['meetago_webservice']);
        }
    }

    /**
     * Parse hotel object
     *
     * @param \stdClass $hotel
     * @return $array
     */
    protected function _parseHotelData(\stdClass $hotel) {

        // parse hotel
        $data = array(
            'hotelId'     => $hotel->HotelInfo->hotelId,
//            'type'   => null,
            'rating'      => (int)$hotel->HotelInfo->rating, //!
//            'comission' => null,
//            'rank'   => null,
//            'hotelGroupId' => null,
            'street'      => $hotel->HotelInfo->Address->street,  //!
            'postcode'    => $hotel->HotelInfo->Address->postalCode,  //!
//            'city' => null,  //!
            'country'     => $hotel->HotelInfo->Address->countryName,  //!
//            'phone'       => null,  //!
//            'website'     => null,  //!
//            'latitude' => null,
//            'longitude' => null,
//            'cancelCostsBefore' => null,
//            'cancelCostsAfter' => null,
//            'distanceEvent' => null,
//            'distanceTrainStation' => null,
//            'distanceAirport' => null,
//            'distancePublicTransport' => null,

            'name'        => $hotel->HotelInfo->hotelName,  //!
//            'description' => null,  //!
//            'cancelInfo' => null,
//            'bookingTerms' => null,
//            'info_1' => null,
//            'info_2' => null,
//            'info_3' => null,
//            'hotelFacilityDescription' => null,
//            'roomFacilityDescription' => null,

            // one contingent pro hotel
            'contingents' => array(
                array(
//                    'cancelDateClient' => null, //!
//                    'cancelDateInternal' => null,
//                    'cancelEmail' => null,
//                    'discountDirectBooking' => null,
//                    'discountEarlyBid' => null,
//                    'limitEarlyBid' => null,
//                    'offlineDate' => null,
//                    'discountEarlyBid' => null,
//                    'discountEarlyBid' => null,

//                    'name'             => null, //!
//                    'description'      => null, //!
//                    'cancelInfo' => null,

                    'contingentparts'  => array()
                )
            )
        );

        // initital
        $contingents = array();

        // parse contingents (contingentparts by us)
        if (isset($hotel->Contingents) && is_array($hotel->Contingents)) {
            $contingents = $hotel->Contingents;
        } else if (isset($hotel->Contingents)) {
            $contingents = array($hotel->Contingents);
        }
        foreach ($contingents as $contingent) {

            // prepare continentpart
            $contingentpart = array(
//                'isOverbookingAllowed' => null,
//                'isRecommendedPrice' => null,
//                'isBruttoPrice' => null,
//                'hasMultipleRooms' => null,
                'availableFrom'            => $contingent->arrival, //!
                'availableUntil'           => $contingent->departure, //!
                'hasBreakfast'             => $contingent->breakfast, //!
//                'breakfastCost'            => null, //!
//                'hasAccomodation'          => null, //!
//                'accomodationMinimum'      => null, //!
//                'accomodationMinimumFrom'  => null, //!
//                'accomodationMinimumUntil' => null, //!

                'roomtypes' => array(
                    $contingent->typeOfRoom
                ),
                'roomstocks' => array(
                    'amount'   => $contingent->quantityCurrent,
                    'roomtype' => $contingent->typeOfRoom,
                    'price'    => $contingent->pricePerNight
                )
            );

            // add contingent
            $data['contingents'][0]['contingentparts'][] = $contingentpart;
        }

//        <option value="2">Einzelzimmer</option>
//        <option value="3">Doppelzimmer</option>
//        <option value="4">Doppelzimmer als Einzelzimmer</option>
//        $roomtype = array(
//            'abbreviation' => null,
//            'bedCount' => null,
//        );

//        $roomstock = array(
//            'day' => null, //!
//            'amount' => null, //!
//            'isOverbooking' => null,
//            'roomprices' => array()
//        );

//        $roomprices = array(
//            'price' => null  //!
//        );

        return $data;
    }
}
