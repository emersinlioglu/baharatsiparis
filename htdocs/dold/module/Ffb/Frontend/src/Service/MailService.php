<?php

namespace Ffb\Frontend\Service;

use Ffb\Frontend\Model;

/**
 * Basic functionality for mailing requirements is implemented
 * in \Ffb\Common\Service\MailService.
 *
 * In this class Ffb\Frontend\Service\MailService
 * implement only project specifically requests.
 * 
 * @author erdal.mersinlioglu
 */
class MailService extends \Ffb\Common\Service\MailService {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Ffb\Tms\Entity\UserEntity $user (optional)
     *         user who uses this service as entity
     */
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $sl, \Ffb\Tms\Entity\UserEntity $user = null) {
        parent::__construct($sl, $user);
        $conf = $sl->get('Config');
        $this->_conf = $conf['service']['mail'];

    }

    /**
     * Send request invoice email
     *
     * @param \Ffb\Backend\Entity\EventEntity $event
     * @param array $data
     * @return boolean
     */
    public function sendRequestInvoice(\Ffb\Backend\Entity\EventEntity $event, array $data) {

        // get sender
        $me = $this->getUser();

        // get models
        $eventModel    = new Model\EventModel($this->getServiceLocator(), $me);
        $eventlogModel = new Model\EventlogModel($this->getServiceLocator(), $this->_logger);

        // senderName
        $senderName = $me->getName();
        // subject
        $subject    = $this->_translator->translate('SBJ_REQUEST_INVOICE');
        // recipients
        $recipients = array();
        // to
        $recipients[] = array (
            'email' => $data['email']
        );

        // cc
        if ((int)$data['sendCopy'] === 1) {

            // send copy to self
            $recipients[] = array (
                'name'  => $me->getName(),
                'email' => $me->getEmail(),
                'type'  => 'cc'
            );
        }

        // TODO: generate url

        // TODO: select subject depanding on

        // TODO: set status

        //// build mail text view
        //$mailTextView = new ViewModel();
        //$mailTextView->setTemplate('Ffb\Frontend/mail/requestinvoice');
        //$this->getServiceLocator()->get('SmartyRenderer')->render($mailTextView)

        // body
        $body = array(
            'text' => $data['message']
            //'html' => '',
        );

        // send mail
        $succ = $this->send(
            $senderName,
            $recipients,
            $subject,
            $body
        );

        // add log entry
        $event->addLog(
            $eventlogModel->build(
                $event,
                $me,
                Model\EventlogModel::ITEM_EVENT_SEND_REQUEST,
                'MSG_LOG_REQUEST_MAIL_SENT'
            )
        );

        // update
        $eventModel->update($event);

        return $succ;
    }

    /**
     * Send invoice email
     *
     * @param \Ffb\Backend\Entity\EventEntity $event
     * @param array $data
     * @return boolean
     */
    public function sendInvoice(\Ffb\Backend\Entity\EventEntity $event, array $data) {

        // get config
        $config = $this->_sl->get('Application\Config');

        // get sender
        $me = $this->getUser();

        // get models
        $eventModel    = new Model\EventModel($this->getServiceLocator(), $me);
        $eventlogModel = new Model\EventlogModel($this->getServiceLocator(), $this->_logger);

        // senderName
        $senderName = $me->getName();
        // subject
        $subject = $this->_translator->translate('SBJ_SEND_INVOICE');
        // attachements
        $attachments = array();

        // TODO: add real pdf with invoice        
        $attachments[] = $config['uploads']['public'] . '/images/backend/logo_tagungshotel.png';

        // recipients
        $recipients = array();
        // to
        $recipients[] = array (
            'email' => $data['email']
        );

        // cc
        if ((int)$data['sendCopy'] === 1) {

            // send copy to self
            $recipients[] = array (
                'name'  => $me->getName(),
                'email' => $me->getEmail(),
                'type'  => 'cc'
            );
        }

        //// build mail text view
        //$mailTextView = new ViewModel();
        //$mailTextView->setTemplate('Ffb\Frontend/mail/requestinvoice');
        //$this->getServiceLocator()->get('SmartyRenderer')->render($mailTextView)

        // body
        $body = array(
            'text' => $data['message']
            //'html' => '',
        );

        // send mail
        $succ = $this->send(
            $senderName,
            $recipients,
            $subject,
            $body,
            $attachments
        );

        // add log entry
        $event->addLog(
            $eventlogModel->build(
                $event,
                $me,
                Model\EventlogModel::ITEM_EVENT_SEND_INVOICE,
                'MSG_LOG_REQUEST_MAIL_SENT'
            )
        );

        // update
        $eventModel->update($event);

        return $succ;
    }

}