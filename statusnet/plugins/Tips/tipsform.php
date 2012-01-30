<?php

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Form for adding a tip
 *
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 */
class TipsForm extends Form
{
    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'form_new_tips';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings ajax-notice';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('newtips');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $this->out->elementStart('fieldset', array('id' => 'new_tips_data'));


        $this->out->elementStart('ul', 'form_data');

        $this->li();
        $this->out->input('tips-description',
                          // TRANS: Field label on tip form.
                          _m('LABEL','Description'),
                          null,
                          // TRANS: Field title on tip form.
                          _m('Description of the tip.'),
                          'description');
        $this->unli();

        $this->out->elementEnd('ul');

        $toWidget = new ToSelector($this->out,
                                   common_current_user(),
                                   null);
        $toWidget->show();

        $this->out->elementEnd('fieldset');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text to save an tip..
        $this->out->submit('tips-submit', _m('BUTTON', 'Save'), 'submit', 'submit');
    }
}
