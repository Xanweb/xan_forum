<?php
namespace XanForum\Model;

use PageList;
use Permissions;

class TopicList extends PageList
{
    public function filterByForum(Forum $forum)
    {
        $this->filterByParentID($forum->getForumID());
    }

    public function getResult($queryRow)
    {
        $c = Topic::getByID($queryRow['cID'], 'ACTIVE');
        if (is_object($c) && $this->checkPermissions($c)) {
            if (self::PAGE_VERSION_RECENT == $this->pageVersionToRetrieve) {
                $cp = new Permissions($c);
                if ($cp->canViewPageVersions() || $this->permissionsChecker === -1) {
                    $c->loadVersionObject('RECENT');
                }
            }
            if (isset($queryRow['cIndexScore'])) {
                $c->setPageIndexScore($queryRow['cIndexScore']);
            }

            return $c;
        }
    }

    public function sortByLastUpdate($dir = 'DESC')
    {
        $qb = \Database::connection()->createQueryBuilder();
        $qb->select('cnvm.cnvMessageDateCreated')->from('ConversationMessages', 'cnvm')
            ->innerJoin('cnvm', 'Conversations', 'cnv', 'cnvm.cnvID = cnv.cnvID')
            ->where('cnvm.cnvIsMessageApproved = 1 AND cnvm.cnvIsMessageDeleted = 0 AND cnv.cID = p.cID')
            ->orderBy('cnvm.cnvMessageDateCreated', 'desc')
            ->setMaxResults(1);

        $this->query->addSelect('(' . $qb->getSQL() . ') as lastUpdate')
            ->orderBy('lastUpdate', $dir);
    }
}
