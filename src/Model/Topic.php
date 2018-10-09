<?php
namespace XanForum\Model;

use UserInfo;
use XanForum\Model\Message as ForumMessage;
use Concrete\Core\Page\Page;
use Core;

class Topic extends Page
{
    /**
     * @var \Concrete\Core\User\UserInfo
     */
    protected $author;

    /**
     * @var Forum
     */
    protected $forum;

    public function getTopicID()
    {
        return $this->getCollectionID();
    }

    public function getTopicName()
    {
        return $this->getCollectionName();
    }

    public function getTopicURL()
    {
        return \URL::to($this);
    }

    public function getAuthorID()
    {
        return $this->getCollectionUserID();
    }

    public function getAuthorName()
    {
        return is_object($this->getAuthor()) ? $this->getAuthor()->getUserName() : '-';
    }

    public function getAuthorProfileURL()
    {
        return \URL::to('/profile/view/', $this->getCollectionUserID());
    }

    /**
     * @return \Concrete\Core\User\UserInfo
     */
    public function getAuthor()
    {
        if (!is_object($this->author)) {
            $this->author = UserInfo::getByID($this->getCollectionUserID());
        }

        return $this->author;
    }

    /**
     * @return Forum
     */
    public function getForum()
    {
        if (!is_object($this->forum) || !$this->forum->getForumID()) {
            $this->forum = Forum::getByID($this->getCollectionParentID());
        }

        return $this->forum;
    }

    public function getLastUpdate($format = false)
    {
        $lastMessage = $this->getLastMessage();
        if (is_object($lastMessage)) {
            return $lastMessage->getMessageDate($format);
        }
    }

    /**
     * @return ForumMessage
     */
    public function getLastMessage()
    {
        $msgList = new MessageList();
        $msgList->filterByTopic($this);
        $msgList->filterByApproved();
        $msgList->filterByNotDeleted();
        $msgList->sortByDateDescending();
        $msgList->getQueryObject()->setMaxResults(2);
        $messages = $msgList->getResults();

        // If there is more than one message so we have at least one reply
        if (count($messages) > 1) {
            return reset($messages);
        }
    }

    public function getFirstWordsOfTopicMessage()
    {
        $topicMessage = $this->getTopicMessage();
        if (is_object($topicMessage)) {
            $messageBody = $topicMessage->getConversationMessageBody();
            $th = Core::getFacadeApplication()->make('helper/text');

            return $th->shortenTextWord($messageBody, 40);
        }

        return $text = '';
    }

    /**
     * return topic message.
     *
     * @return Message
     */
    public function getTopicMessage()
    {
        $msgList = new MessageList();
        $msgList->filterByTopic($this);
        $msgList->filterByApproved();
        $msgList->filterByNotDeleted();
        $msgList->sortByDateAscending();
        $msgList->getQueryObject()->setMaxResults(1);

        return reset($msgList->getResults());
    }
}
