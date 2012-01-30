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


class SubbscribeListItem extends NoticeListItemAdapter
{
    function showNotice()
    {
        $this->nli->out->elementStart('div', 'entry-title');
        $this->nli->showAuthor();
        $this->showContent();
        $this->nli->out->elementEnd('div');
    }

    function showContent()
    {
        $notice = $this->nli->notice;
        $out    = $this->nli->out;

        $subscribe = Subscribe::fromNotice($notice);

        if (empty($subscribe)) {
            // TRANS: Content for a deleted Subscribe.
            $out->element('p', null, _m('Deleted.'));
            return;
        }

        $out->elementStart('div', 'subscribe');
        $out->raw($subscribe->asHTML());
        $out->elementEnd('div');
        return;
    }
}
