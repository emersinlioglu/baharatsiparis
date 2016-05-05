<?php

namespace Ffb\Common\Service;

use Zend\Log;
use Zend\Mail;
use Zend\Mime;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service to send mails.
 *
 * $address = $config['mail']['account']['address'];
 * $user = $config['mail']['account']['user'];
 * $passwort = $config['mail']['account']['passwort'];
 *
 * $server = $config['mail']['in']['server'];
 * $port = $config['mail']['in']['port'];
 * $encryption = $config['mail']['in']['encryption'];
 *
 * $server = $config['mail']['out']['server'];
 * $port = $config['mail']['out']['port'];
 *
 * Steht in einem Programm "STARTTLS" nicht zur Verfügung, nutzen Sie
 * bitte das Protokoll "TLS". Existiert auch hierfür keine Option,
 * genügt es, die Option "Verschlüsselung" zu aktivieren.)
 * $encryption = $config['mail']['out']['encryption'];
 *
 * @see DERTMS-526
 * @see http://framework.zend.com/manual/2.1/en/modules/zend.mail.introduction.html
 * @see http://www.zendframeworkmagazin.de/zf/blog/zend-mail-und-zend-mime-mailen-mit-dem-zend-framework-2
 * @author
 */
class MailService extends AbstractService {

    /**
     * Configuration
     *
     * @var array
     */
    protected $_conf;

    /**
     *
     * @var \Zend\Log\Logger
     */
    protected $_logger;

    /**
     * Transport to use for sending mails. Either sendmail, smtp or file.
     * File is not yet implemented!
     *
     * @var \Zend\Mail\Transport\TransportInterface
     */
    private $_transport;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param $user (optional) entity of user
     */
    public function __construct(ServiceLocatorInterface $sl, $user = null) {

        parent::__construct($sl, $user);

        // build config
        $this->_conf = array(
            'account' => array(
                'address' => ''
            ),
            'encoding' => '',
            'log' => array(
                'enable' => 'true',
                // name of logfile the logger is bound to
                'filename' => 'mails.log',
                'anonymize' => 'true'
            ),
            'transport' => 'sendmail'
        );
        $conf = $sl->get('Config');
        if (isset($conf['service'], $conf['service']['mail'])) {
            $this->_conf = array_merge($this->_conf, $conf['service']['mail']);
        }

        // build logger (if config says so)
        if ('true' === $this->_conf['log']['enable']) {
            $this->_logger = $this->_getLogger();
        }

    }

    /**
     * Builds a new logger for a logfile of the given filename.
     * If no filename is given "mails.log" will be assumed.
     * If logging is deactiveted by configuration null will be returned.
     *
     * @return \Zend\Log\Logger
     */
    protected function _getLogger() {

        // get basepath of log files
        // TODO what about using basepath?
        $logpath = ini_get('error_log');
        $logpath = explode('/', $logpath);
        array_pop($logpath);
        $logpath  = implode('/', $logpath);

        $filename = $this->_conf['log']['filename'];

        // build logger
        $logger = new Log\Logger();

        // add writer
        $writer = new Log\Writer\Stream($logpath . '/' . $filename);
        $logger->addWriter($writer);

        return $logger;

    }

    /**
     * Sends a mail immediately.
     *
     * ######## Example data structure ########
     * $sender = '';
     * $sender = array(
     *     'name'  => 'Max',
     *     'email' => 'xxx@xxx.xx'
     * );
     * $recipients = array(
     *     array(
     *         'email' => '',
     *         'name' => ''
     *         'type' => {'to','bcc','cc','reply-to'} // default: 'to'
     *     )*
     * );
     * $subject = '';
     * $body = array(
     *     'text' => 'text_content',
     *     'html' => 'html_content'
     * );
     * $attachments = array(
     *      'abs_path1',
     *      'abs_path2'
     * );
     * ########################################
     *
     * @param string $mailtype
     *         name of mailtype to be used for logging
     * @param string|array $sender
     * @param array $recipients
     * @param string $subject
     * @param array $body
     * @param array $attachments
     * @return boolean
     * @throws \Exception
     */
    public function send($mailtype, $sender, array $recipients, $subject, array $body, $attachments = array()) {

        // build mime
        $mime = $this->_getMime($body, $attachments);

        // parse sender
        $confSenderEmail = $this->_conf['account']['address'];
        if (is_array($sender)) {
            $senderName  = array_key_exists('name', $sender) ? $sender['name'] : null;
            $senderEmail = array_key_exists('email', $sender) && strlen($sender['email']) > 0 ? $sender['email'] : $confSenderEmail;
        } else {
            $senderName  = $sender;
            $senderEmail = $confSenderEmail;
        }

        // build mail
        $mail = $this->_getMail($senderEmail, $senderName, $recipients, $subject, $mime);

        // transport mail
        if ($mail->isValid()) {
            $succ = $this->_transport($mail);
        } else {
            $succ = false;
        }

        // log sended mail
        if ($this->_logger) {
            $adresses = array();
            foreach ($recipients as $recipient) {
                $adress = $recipient['email'];
                // anonymize email addresses of recipients
                if ('true' === $this->_conf['log']['anonymize']) {
                    $adress = substr($adress, 0, 3);
                }
                $adresses[] = $adress;
            }
            $this->_logger->info($mailtype . ' from [' . $senderEmail . '] to [' . implode(', ', $adresses) . ']: ' . $subject);
        }

        return $succ;
    }

    /**
     *
     * @param array $body
     * @param array $attachments
     * @return \Zend\Mime\Message
     */
    protected function _getMime(array $body, array $attachments) {

        $mimeparts = array();
        foreach ($body as $type => $content) {

            switch ($type) {
                case 'text':
                    $type = Mime\Mime::TYPE_TEXT;
                    break;
                case 'html':
                    $type = Mime\Mime::TYPE_HTML;
                    break;
                default:
                    continue;
            }

            $mimepart = new Mime\Part($content);
            $mimepart->charset = $this->_conf['encoding'];
            $mimepart->type = $type;
            $mimeparts[] = $mimepart;
        }

        // add attachments (given as absolute path to file)
        // TODO better use relative path for security reasons!
        if (!empty($attachments)) {
            foreach ($attachments as $filename) {

                if (!is_file($filename)) {
                    continue;
                }

                // get attachment content
                $content = fopen($filename, 'r');

                // get attachment mime type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $type  = finfo_file($finfo, $filename);
                finfo_close($finfo);

                // get attachment base name
                $basename = pathinfo($filename, PATHINFO_BASENAME);

                // build mime part for attachment
                $mimepart       = new Mime\Part($content);
                $mimepart->type = $type;

                // set id
                $mimepart->id = $basename;

                // set disposition
                //$mimepart->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

                // obligatory for attachments
                $mimepart->encoding = Mime\Mime::ENCODING_BASE64;

                // optional, filename
                $mimepart->filename = $basename;

                $mimeparts[] = $mimepart;
            }
        }

        // build mime message
        $mime = new Mime\Message();
        $mime->setParts($mimeparts);

        return $mime;
    }

    /**
     * @param string $senderEmail
     * @param string $senderName
     * @param array $recipients
     * @param string $subject
     * @param \Zend\Mime\Message $mime
     * @return \Zend\Mail\Message
     */
    protected function _getMail($senderEmail, $senderName, array $recipients, $subject, \Zend\Mime\Message $mime) {

        // sender
        $senderName  = strlen($senderName) === 0 ? null : $senderName;

        // build mail message
        $mail = new Mail\Message();
        $mail->setEncoding($this->_conf['encoding']);
        $mail->setSubject($subject);
        $mail->setBody($mime);
        $mail->setFrom($senderEmail, $senderName);

        // TEST: log sending of mail
        if (isset($this->_conf['test'], $this->_conf['test']['log']) && 1 < $this->_conf['test']['log']) {
            error_log('###############   MAIL   ###############');
            error_log(print_r($this->_conf,1));
            error_log("\$subject: $subject");
            //error_log("\$mime: $mime");
            error_log("\$senderEmail, \$senderName: $senderEmail, $senderName");
        }

        // TEST: add configured test address as BCC for testing
        if (   isset(
                    $this->_conf['test'], $this->_conf['test']['address'],
                    $this->_conf['test']['address']['bcc']
               )
            && 0 < strlen($this->_conf['test']['address']['bcc'])
        ) {
            $mail->addBcc($this->_conf['test']['address']['bcc']);
        }

        foreach ($recipients as $recipient) {

            $type   = isset($recipient['type']) ? $recipient['type'] : '';
            $email  = $recipient['email'];
            $name   = isset($recipient['name']) ? $recipient['name'] : null;

            switch ($type) {
                case 'bcc':
                    $mail->addBcc($email, $name);
                    break;
                case 'cc':
                    $mail->addCc($email, $name);
                    break;
                case 'reply-to':
                    $mail->addReplyTo($email, $name);
                    break;
                default:
                    $mail->addTo($email, $name);
                    break;
            }

            // TEST: log sending of mail
            if (isset($this->_conf['test'], $this->_conf['test']['log']) && 0 < $this->_conf['test']['log']) {
                error_log("\$type: $type, \$email: $email, \$name: $name");
            }
        }

        // overwrite content-type 'mutlipart/mixed'
        // w/ 'multipart/alternative' after body has been set
        /* @var $contentType \Zend\Mail\Header\ContentType */
        $contentType = $mail->getHeaders()->get('content-type');
        if (false !== $contentType && 'mutlipart/mixed' === $contentType->getType()) {
            $contentType->setType('multipart/alternative');
        }

        return $mail;
    }

    /**
     * == Choosing your transport wisely ==
     * Although the sendmail transport is the transport that requires only
     * minimal configuration, it may not be suitable for your production
     * environment. This is because emails sent using the sendmail transport
     * will be more often delivered to SPAM-boxes. This can partly be
     * remedied by using the SMTP Transport combined with an SMTP server
     * that has an overall good reputation. Additionally, techniques such
     * as SPF and DKIM may be employed to ensure even more email messages
     * are delivered as should.
     *
     * @see http://framework.zend.com/manual/2.1/en/modules/zend.mail.transport.html
     * @throws \Exception if file transport is configured which is not implemented
     * @return \Zend\Mail\Transport\TransportInterface
     */
    protected function _getTransport() {

        switch (isset($this->_conf['transport']) ? $this->_conf['transport'] : 'sendmail') {
            case 'file':
                $transport = new Mail\Transport\File();
                throw new \Exception('file transport is configured which is not implemented');
            case 'smtp':
                $transport = new Mail\Transport\Smtp();
                if (isset($this->_conf['smtp']['options'])) {
                    $confOpts = $this->_conf['smtp']['options'];
                    // determine IP of SMTP server on the fly
                    // This might be usefull if the server IP
                    // changes on a regular basis.
                    if (!isset($confOpts['host'])) {
                        $confOpts['host'] = gethostbyname($confOpts['name']);
                    }

                    // cause of inherited configs it is necessary to
                    // undefine settings
                    if (isset($confOpts['connection_class']) && '' === $confOpts['connection_class']) {
                        unset($confOpts['connection_class']);
                    }
                    if (isset($confOpts['connection_config']) && !is_array($confOpts['connection_config'])) {
                        unset($confOpts['connection_config']);
                    }

                    $options = new Mail\Transport\SmtpOptions($confOpts);
                    $transport->setOptions($options);
                    break;
                }
            case 'sendmail':
            default:
                // Sendmail is a wrapper to the PHP mail() function
                $transport = new Mail\Transport\Sendmail();
                break;
        }

        return $transport;
    }

    /**
     * @param \Zend\Mail\Message $mail
     * @return boolean
     */
    protected function _transport(\Zend\Mail\Message $mail) {

        // get transport (build once and aggregate for later use)
        if (is_null($this->_transport)) {
            try {
                $this->_transport = $this->_getTransport();
            } catch (\Exception $e) {
                error_log($e->getMessage());
                return false;
            }
        }

        // Transport\File and Transport\Smtp throw an RuntimeException
        // when something goes wrong. The File transporter does this,
        // for example, when it can not write the file.
        try {
            $this->_transport->send($mail);
            return true;
        } catch (\RuntimeException $e) {
            error_log('RuntimeException for mail');
            error_log($e->getMessage());
            // error_log('#froms: ' . $mail->getFrom()->count());
            // /* @var $from \Zend\Mail\Address */
            // foreach ($mail->getFrom() as $from) {
            //     error_log('email: ' . $from->getEmail());
            //     error_log('name: ' . $from->getName());
            // }
            return false;
        }
    }

    /**
     * @return \Zend\View\Helper\Url
     */
    protected function _getUrl() {
        if (!$this->_url) {
            $this->_url = $this->_sl->get('ViewHelperManager')->get('url');
        }
        return $this->_url;
    }

}
