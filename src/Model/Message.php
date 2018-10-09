<?php
namespace XanForum\Model;

use Concrete\Core\Support\Facade\Facade;
use UserInfo;

class Message extends \Concrete\Core\Conversation\Message\Message
{
    /**
     * @var \Concrete\Core\User\UserInfo
     */
    protected $author;

    /**
     * @var Topic
     */
    protected $topic;

    public function getAuthorID()
    {
        return $this->uID;
    }

    public function getAuthorName()
    {
        return is_object($this->getAuthor()) ? $this->getAuthor()->getUserName() : '-';
    }

    public function getAuthorProfileURL()
    {
        return \URL::to('/profile/view/', $this->getAuthorID());
    }

    /**
     * @return \Concrete\Core\User\UserInfo
     */
    public function getAuthor()
    {
        if (!is_object($this->author)) {
            $this->author = UserInfo::getByID($this->getAuthorID());
        }

        return $this->author;
    }

    /**
     * @return Topic
     */
    public function getTopic()
    {
        if (!is_object($this->topic) || !$this->topic->getTopicID()) {
            $this->topic = Topic::getByID($this->getConversationObject()->cID, 'ACTIVE');
        }

        return $this->topic;
    }

    /**
     * @return Forum
     */
    public function getForum()
    {
        $topic = $this->getTopic();
        if (is_object($topic)) {
            return $topic->getForum();
        }
    }

    public function getMessageDate($format = false)
    {
        /* @var $dh \Concrete\Core\Localization\Service\Date */
        $app = Facade::getFacadeApplication();
        $dh = $app->make('helper/date');
        if (!empty($format)) {
            return $dh->formatCustom($format, $this->getConversationMessageDateTime());
        } else {
            return $dh->formatDateTime($format, $this->getConversationMessageDateTime());
        }
    }
}
