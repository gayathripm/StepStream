<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * A form to subscribe for a tip

 */
class CancelSubscribeForm extends Form
{
    protected $subscribe = null;

    function __construct($subscribe, $out=null)
    {
        parent::__construct($out);
        $this->subscribe = $subscribe;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'form_tips_subscribe';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'ajax';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('cancelsubscribe');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $this->out->elementStart('fieldset', array('id' => 'new_subscribe_data'));

        $this->out->hidden('subscribe-id', $this->subscribe->id, 'subscribe');

        switch (Subscribe::verbFor($this->subscribe->response)) {
        case Subscribe::POSITIVE:
           
            $this->out->text(_m('You have used this tip.'));
            break;
        case Subscribe::NEGATIVE:
           
            $this->out->text(_m('You donot like this tip.'));
            break;
        case Subscribe::POSSIBLE:
            
            $this->out->text(_m('You might use this tip.'));
            break;
        }

        $this->out->elementEnd('fieldset');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {

        $this->out->submit('subscribe-cancel', _m('BUTTON', 'Cancel'));
    }
}
