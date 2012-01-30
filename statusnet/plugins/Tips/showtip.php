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
 * Show a single tip, with associated information
 */
class ShowtipAction extends ShownoticeAction
{
    protected $id    = null;
    protected $tip = null;

    function getNotice()
    {
        $this->id = $this->trimmed('id');

        $this->tip = Tips::staticGet('id', $this->id);

        if (empty($this->tip)) {
            // TRANS: Client exception thrown when referring to a non-existing tip.
            throw new ClientException(_m('No such tip.'), 404);
        }

        $notice = $this->tip->getNotice();

        if (empty($notice)) {
            // Did we used to have it, and it got deleted?
            // TRANS: Client exception thrown when referring to a non-existing tip.
            throw new ClientException(_m('No such tip.'), 404);
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
        return $this->tip->title;
    }
}
