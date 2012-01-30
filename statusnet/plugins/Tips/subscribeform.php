<?php
/**
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
 * A form to Subscribe for a tip
 *
 * @category  General
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class SubscribeForm extends Form
{
    protected $tip = null;

    function __construct($tip, $out=null)
    {
        parent::__construct($out);
        $this->tip = $tip;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'form_tip_subscribe';
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
        return common_local_url('newsubscribe');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $this->out->elementStart('fieldset', array('id' => 'new_subscribe_data'));

        // TRANS: Field label on form to Subscribe for a tip.
        $this->out->text(_m('Subscribejflgjfdkjg:'));

             $this->out->hidden('tips-id', $this->tips->id, 'tips');

        $this->out->hidden('submitvalue', '');

        $this->out->elementEnd('fieldset');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {

        $this->submitButton('yes', _m('BUTTON', 'Usedjckjckcj'));

        $this->submitButton('no', _m('BUTTON', 'Dislike'));

        $this->submitButton('maybe', _m('BUTTON', 'May Use'));
    }

    function submitButton($id, $label)
    {
        $this->out->element(
            'input',
                array(
                    'type'    => 'submit',
                    'id'      => 'subscribe-submit',
                    'name'    => $id,
                    'class'   => 'submit',
                    'value'   => $label,
                    'title'   => $label,
                    'onClick' => 'this.form.submitvalue.value = this.name; return true;'
            )
        );
    }
}
