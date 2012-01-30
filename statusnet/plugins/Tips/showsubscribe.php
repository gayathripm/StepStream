<?php
/**

 * Show a single Subscribe
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}


class ShowsubscribeAction extends ShownoticeAction
{
    protected $subscribe = null;
    protected $tip = null;

    function getNotice()
    {
        $this->id = $this->trimmed('id');

        $this->subscribe = Subscribe::staticGet('id', $this->id);

        if (empty($this->subscribe)) {
            // TRANS: Client exception thrown when referring to a non-existing Subscribe.
         
            throw new ClientException(_m('No such Subscribe.'), 404);
        }

        $this->tip = $this->subscribe->getEvent();

        if (empty($this->tip)) {
            // TRANS: Client exception thrown when referring to a non-existing tip
            throw new ClientException(_m('No such tip.'), 404);
        }

        $notice = $this->subscribe->getNotice();

        if (empty($notice)) {
            // Did we used to have it, and it got deleted?
            // TRANS: Client exception thrown when referring to a non-existing RSVP.
            // TRANS: RSVP stands for "Please reply".
            throw new ClientException(_m('No such Subscribe.'), 404);
        }

        return $notice;
    }

    /**
     * Title of the page
     *
     * Used by Action class for layout.
     *
     * @return string page tile
     */
    function title()
    {
        // TRANS: Title for tip.
	// TRANS: %1$s is a user nickname, %2$s is an tip title.
        return sprintf(_m('%1$s\'s Subscribe for "%2$s"'),
                       $this->user->nickname,
                       $this->tip->title);
    }
}
