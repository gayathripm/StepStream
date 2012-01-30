<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Form for posting a notice
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Form
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/form.php';

/**
 * Form for posting a notice
 *
 * Frequently-used form for posting a notice
 *
 * @category Form
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Sarven Capadisli <csarven@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 *
 * @see      HTMLOutputter
 */
class ProfileNoticeForm extends Form
{
    /**
     * Current action, used for returning to this page.
     */
    var $actionName = null;

    /**
     * Pre-filled content of the form
     */
    var $content = null;

    /**
     * The current user
     */
    var $user = null;

    /**
     * The notice being replied to
     */
    var $inreplyto = null;

    /**
     * Pre-filled location content of the form
     */

    var $lat;
    var $lon;
    var $location_id;
    var $location_ns;

    /** select this group from the drop-down by default. */
    var $to_group;

    /** select this user from the drop-down by default. */
    var $to_profile;

    /** Pre-click the private checkbox. */
    var $private;

    /**
     * Constructor
     *
     * @param Action $action  Action we're being embedded into
     * @param array  $options Array of optional parameters
     *                        'user' a user instead of current
     *                        'content' notice content
     *                        'inreplyto' ID of notice to reply to
     *                        'lat' Latitude
     *                        'lon' Longitude
     *                        'location_id' ID of location
     *                        'location_ns' Namespace of location
     */
    function __construct($action, $options=null)
    {
        // XXX: ??? Is this to keep notice forms distinct?
        // Do we have to worry about sub-second race conditions?
        // XXX: Needs to be above the parent::__construct() call...?

        $this->id_suffix = time();

        parent::__construct($action);

        if (is_null($options)) {
            $options = array();
        }

        $this->actionName  = $action->trimmed('action');

        $prefill = array('content', 'inreplyto', 'lat', 
                         'lon', 'location_id', 'location_ns',
                         'to_group', 'to_profile', 'private');

        foreach ($prefill as $fieldName) {
            if (array_key_exists($fieldName, $options)) {
                $this->$fieldName = $options[$fieldName];
            }
        }

        // Prefill the profile if we're replying

        if (empty($this->to_profile) &&
            !empty($this->inreplyto)) {
            $notice = Notice::staticGet('id', $this->inreplyto);
            if (!empty($notice)) {
                $this->to_profile = $notice->getProfile();
            }
        }

        if (array_key_exists('user', $options)) {
            $this->user = $options['user'];
        } else {
            $this->user = common_current_user();
        }

        $this->profile = $this->user->getProfile();

        if (common_config('attachments', 'uploads')) {
            $this->enctype = 'multipart/form-data';
        }
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */

    function id()
    {
        return 'form_notice_' . $this->id_suffix;
    }

   /**
     * Class of the form
     *
     * @return string class of the form
     */

    function formClass()
    {
        return 'form_notice ajax-notice';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */

    function action()
    {
        return common_local_url('newnotice');
    }

    /**
     * Legend of the Form
     *
     * @return void
     */
    function formLegend()
    {
        // TRANS: Form legend for notice form.
        $this->out->element('legend', null, _('Send a notice'));
    }

    /**
     * Data elements
     *
     * @return void
     */
    function formData()
    {
        if (Event::handle('StartShowNoticeFormData', array($this))) {
            $this->out->element('label', array('for' => 'notice_data-text',
                                               'id' => 'notice_data-text-label'),
                                // TRANS: Title for notice label. %s is the user's nickname.
                                sprintf(_('What\'s up, %s?'), $this->user->nickname));
            // XXX: vary by defined max size
            $this->out->element('textarea', array('class' => 'notice_data-text',
                                                 
                                                  'name' => 'status_textarea'),
                                ($this->content) ? $this->content : 'Update your status');

            $contentLimit = Notice::maxContent();

            if ($contentLimit > 0) {
                $this->out->element('span',
                                    array('class' => 'count'),
                                    $contentLimit);
            }

     
            if (!empty($this->actionName)) {
                $this->out->hidden('notice_return-to', $this->actionName, 'returnto');
            }
            $this->out->hidden('notice_in-reply-to', $this->inreplyto, 'inreplyto');

            $this->out->elementStart('div', 'to-selector');
            $toWidget = new ToSelector($this->out,
                                       $this->user,
                                       (!empty($this->to_group) ? $this->to_group : $this->to_profile));

            $toWidget->show();
            $this->out->elementEnd('div');

  

            Event::handle('EndShowNoticeFormData', array($this));
        }

 
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
$this->out->element('br');
        $this->out->element('input', array('id' => 'notice_action-submit',
                                           'class' => 'submit',
                                           'name' => 'status_submit',
                                           'type' => 'submit',
                                           // TRANS: Button text for sending notice.
                                           'value' => _m('BUTTON', 'Send')));



 $this->out->elementStart('div',array('id' =>'awesome-graphgpm'));
 $this->out->elementEnd('div');
  /* $this->out->element('input',array('type' => 'button', 'class' => 'button','name' => 'showgraph','id' => 'showgraph','value' => 'Show my progress','onclick' => 'loadGraph()'));   */
    }
}
