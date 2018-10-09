<?php
namespace XanForum\Model;

use Concrete\Core\Page\Page;

class Forum extends Page
{
    public function getForumID()
    {
        return $this->getCollectionID();
    }

    public function getForumName()
    {
        return $this->getCollectionName();
    }

    public function getForumURL()
    {
        return \URL::to($this);
    }

    /**
     * @return TopicList get topic list of Forum
     */
    public function getTopicList()
    {
        $topicList = new TopicList();
        $topicList->filterByForum($this);

        return $topicList;
    }

    /**
     * @return Topic
     */
    public function getLatestTopic()
    {
        $topicList = $this->getTopicList();
        $topicList->sortByPublicDateDescending();
        $topicList->getQueryObject()->setMaxResults(1);

        return reset($topicList->getResults());
    }

    /**
     * @return Message
     */
    public function getLatestMessage()
    {
        $msgList = new MessageList();
        $msgList->filterByNotDeleted();
        $msgList->filterByApproved();
        $msgList->filterByForum($this);
        $msgList->sortByDateDescending();
        $msgList->getQueryObject()->setMaxResults(1);

        return reset($msgList->getResults());
    }
}
