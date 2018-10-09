<?php
namespace XanForum\Model;

use XanForum\Model\Message as ForumMessage;
use Concrete\Core\Search\ItemList\Database\ItemList as DatabaseItemList;
use Concrete\Core\Search\Pagination\Pagination;
use PDO;

class MessageList extends DatabaseItemList
{
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct cnvm.cnvMessageID)')->setMaxResults(1);
        });

        return new Pagination($this, $adapter);
    }

    public function createQuery()
    {
        $this->query->select('cnvm.cnvMessageID')->from('ConversationMessages', 'cnvm');
        $this->joinTopicPage();
    }

    protected function joinTopicPage()
    {
        $this->query->innerJoin('cnvm', 'Conversations', 'cnv', 'cnvm.cnvID = cnv.cnvID')
            ->leftJoin('cnv', 'CollectionVersions', 'cv', '(cnv.cID = cv.cID and cv.cvIsApproved = 1)')
            ->leftJoin('cv', 'Pages', 'tp', 'cv.cID = tp.cID');
    }

    /**
     * @param array $queryRow
     *
     * @return ForumMessage
     */
    public function getResult($queryRow)
    {
        return ForumMessage::getByID($queryRow['cnvMessageID']);
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct cnvm.cnvMessageID)')->setMaxResults(1)->execute()->fetchColumn();
    }

    public function filterByKeywords($keywords)
    {
        $this->query->andWhere('(cnvMessageSubject like :keywords or cnvMessageBody like :keywords or cvName like :keywords)');
        $this->query->setParameter('keywords', '%' . $keywords . '%');
    }

    public function filterByForum(Forum $forum)
    {
        $this->query->andWhere('tp.cParentID = :forumID');
        $this->query->setParameter('forumID', $forum->getForumID(), PDO::PARAM_INT);
    }

    public function filterByTopic(Topic $topic)
    {
        $this->query->andWhere('cv.cID = :topicID');
        $this->query->setParameter('topicID', $topic->getTopicID(), PDO::PARAM_INT);
    }

    public function filterByConversation(Conversation $cnv)
    {
        $this->query->andWhere('cnvm.cnvID = :cnvID');
        $this->query->setParameter('cnvID', $cnv->getConversationID(), PDO::PARAM_INT);
    }

    public function filterByFlag(FlagType $type)
    {
        $this->query->innerJoin('cnvm', 'ConversationFlaggedMessages', 'cnf', 'cnvm.cnvMessageID = cnf.cnvMessageID');
        $this->query->andWhere('cnf.cnvMessageFlagTypeID = :cnvMessageFlagTypeID');
        $this->query->setParameter('cnvMessageFlagTypeID', $type->getConversationFlagTypeID(), PDO::PARAM_INT);
    }

    public function filterByApproved()
    {
        $this->query->andWhere('cnvm.cnvIsMessageApproved = 1');
    }

    public function filterByNotDeleted()
    {
        $this->query->andWhere('cnvm.cnvIsMessageDeleted = 0');
    }

    public function filterByUnapproved()
    {
        $this->query->andWhere('cnvm.cnvIsMessageApproved = 0');
    }

    public function filterByUser($uID)
    {
        $this->query->andWhere('cnvm.uID = :uID');
        $this->query->setParameter('uID', $uID, PDO::PARAM_INT);
    }

    public function filterByDeleted()
    {
        $this->query->andWhere('cnvm.cnvIsMessageDeleted = 1');
    }

    public function sortByDateDescending()
    {
        $this->sortBy('cnvm.cnvMessageDateCreated', 'desc');
    }

    public function sortByDateAscending()
    {
        $this->sortBy('cnvm.cnvMessageDateCreated', 'asc');
    }

    public function sortByRating()
    {
        $this->sortBy('cnvm.cnvMessageTotalRatingScore', 'desc');
    }
}
