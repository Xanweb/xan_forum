<?php
namespace XanForum\Conversation\Message;

use Concrete\Core\User\User;

class AuthorFormatter extends \Concrete\Core\Conversation\Message\AuthorFormatter
{
    /**
     * @return string
     */
    public function getDisplayName()
    {
        $ui = $this->author->getUser();
        if (is_object($ui)) {
            $name = $ui->getUserDisplayName();
        } elseif ($this->author->getName()) {
            $name = $this->author->getName();
        } else {
            $name = t('Anonymous');
        }

        $currentUser = new User();

        if (is_object($ui) && ($profileURL = $ui->getUserPublicProfileUrl()) && $currentUser->isRegistered()) {
            return sprintf('<a href="%s">%s</a>', $profileURL, h($name));
        } elseif ($this->author->getWebsite()) {
            return sprintf('<a href="%s">%s</a>', h($this->author->getWebsite()), h($name));
        } else {
            return h($name);
        }
    }
}
