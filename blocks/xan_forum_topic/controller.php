<?php
namespace Concrete\Package\XanForum\Block\XanForumTopic;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Package\XanForum\Controller\Conversations\Message as ConversationMessageController;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Page\Type\Type as CollectionType;
use XanForum\PageTemplateAreas;
use XanForum\Model\Forum;
use Concrete\Core\Support\Facade\Facade;
use XanForum\App;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\User\User;
use Concrete\Core\Page\Page;

class Controller extends BlockController
{
    protected $btTable = 'btXanForumTopic';
    protected $btInterfaceWidth = "390";
    protected $btInterfaceHeight = "450";

    /** @var \Concrete\Core\Error\Error */
    protected $error;

    public function getBlockTypeName()
    {
        return t("Board Topic");
    }

    public function getBlockTypeDescription()
    {
        return t("Board Topic.");
    }

    public function on_start()
    {
        $this->setApplication($this->app ?: Facade::getFacadeApplication());
        $this->error = $this->app->make('error');
    }

    public function on_before_render()
    {
        parent::on_before_render();
        $this->set('error', $this->error);
    }

    protected function getForumID()
    {
        return $this->parentPageID ? $this->parentPageID : Page::getCurrentPage()->getCollectionID();
    }

    /**
     * @return Forum
     */
    protected function getForum()
    {
        return Forum::getByID($this->getForumID());
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('css', 'xan_forum');
    }

    public function view()
    {
        if (!$this->topicsPerPage) {
            $this->topicsPerPage = 20;
        }

        $forum = $this->getForum();
        $topicLst = $forum->getTopicList();

        if ('lastUpdate' == $this->sortOrder) {
            $topicLst->sortByLastUpdate();
        } else {
            $topicLst->sortByPublicDateDescending();
        }

        $topicLst->setItemsPerPage($this->topicsPerPage);
        $pagination = $topicLst->getPagination();

        $this->set('forum', $forum);
        $this->set('topics', $pagination->getCurrentPageResults());
        if ($pagination->haveToPaginate()) {
            $this->set('paging', $pagination->renderDefaultView());
        }
        $this->set('canCreateTopics', $this->canCreateTopics());
    }

    public function action_new_topic()
    {
        if (!$this->canCreateTopics()) {
            $this->error->add(t("You don't have permission to add new topic."));
            $this->view();

            return;
        }

        $config = $this->app->make('config');
        $fh = $this->app->make('helper/concrete/file');

        $attachmentOptions = new \stdClass();
        if (id(new User())->isRegistered()) {
            $attachmentOptions->maxFileSize = $config->get('conversations.files.registered.max_size');
            $attachmentOptions->maxFiles = $config->get('conversations.files.registered.max');
        } else {
            $attachmentOptions->maxFileSize = $config->get('conversations.files.guest.max_size');
            $attachmentOptions->maxFiles = $config->get('conversations.files.guest.max');
        }
        $attachmentOptions->fileExtensions = $fh->unserializeUploadFileExtensions($config->get('conversations.files.allowed_types'));

        $this->set('attachmentOptions', $attachmentOptions);
        $this->set('hide_forum_header', true); //hide forum header in page add new topic
        $this->requireAsset('xan/file-upload');
        $this->render('new_topic');
    }

    public function action_save_topic()
    {
        $this->action_new_topic();

        if ($this->error->has()) {
            return;
        }

        $editor = App::getForumEditor();
        $this->set('text', $cnvMessageBody = $this->request->post($editor->getConversationEditorInputName()));
        $this->set('subject', $subject = $this->request->post('subject'));
        $this->set('attachments', $this->request->post('attachments'));

        $u = new User();
        // token session handling to avoid double topics
        $token = $this->app->make('helper/validation/token');
        if (!$token->validate('add_conversation_message')) {
            $this->error->add($token->getErrorMessage());

            return;
        }

        $ip = $this->app->make('helper/validation/ip');
        if ($ip->isBanned()) {
            $this->error->add($ip->getErrorMessage());

            return;
        }

        if (empty($subject)) {
            $this->error->add(t('Topic subject cannot be empty.'));
        }

        if (empty($cnvMessageBody)) {
            $this->error->add(t('Your message cannot be empty.'));
        }

        if ($this->error->has()) {
            return;
        }

        $txt = $this->app->make('helper/text');

        // create new page for topic
        $parentPage = $this->getForum();
        $ct = CollectionType::getByID($this->ctID);

        $data = [];
        $data['cName'] = $txt->sanitize($subject);
        $data['cDescription'] = $data['cName'];
        $data['uID'] = $u->getUserID();

        $newPage = $parentPage->add($ct, $data, \PageTemplate::getByHandle('forum'));

        $newPage->setAttribute('exclude_nav', 1);
        $newPage->setAttribute('hide_forum_sidebar', 1);

        // add answer form block
        $data = [
            'displayMode' => 'flat',
            'orderBy' => 'date_asc',
            'enableCommentRating' => 1,
            'paginate' => 1,
            'itemsPerPage' => 10,
            'enablePosting' => 1,
            'displayPostingForm' => 'bottom',
        ];

        $cnvBlock = $newPage->addBlock(BlockType::getByHandle('xan_conversation'), $this->getAreaObject()->getAreaHandle(), $data);

        $cnvMsgController = $this->app->make(ConversationMessageController::class);
        $ret = $cnvMsgController->add(true, [
            'cID' => $newPage->getCollectionID(),
            'blockAreaHandle' => $this->getAreaObject()->getAreaHandle(),
            'bID' => $cnvBlock->getBlockID(),
            'cnvID' => $cnvBlock->getController()->getConversationObject()->getConversationID(),
        ]);

        if ($ret instanceof \Concrete\Core\Error\ErrorList\ErrorList) {
            $this->error->add($ret);
            $newPage->delete();

            return;
        }

        $this->redirect($newPage);
    }

    public function canCreateTopics()
    {
        return id(new User())->isLoggedIn();
    }

    public function getAreas()
    {
        if ($this->ctID) {
            $areas = PageTemplateAreas::getAreas(intval($this->ctID));
            $ret = [];
            foreach ($areas as $area) {
                $ret[$area] = $area;
            }

            return $ret;
        }

        return ['' => t('Please select')];
    }

    public function getSortOrders()
    {
        return ['lastUpdate' => t('Last Topic Update'), 'dateCreated' => t('Topic Created')];
    }

    public function getCollectionTypeIDs()
    {
        $collectionTypes = CollectionType::getList();

        if ($this->ctID) {
            $ret = [];
        } else {
            $ret[0] = t('Please select...');
        }

        foreach ($collectionTypes as $collectionType/* @var $collectionType CollectionType */) {
            $ret[$collectionType->getPageTypeID()] = $collectionType->getPageTypeName();
        }

        return $ret;
    }
}
