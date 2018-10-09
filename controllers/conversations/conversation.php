<?php
namespace Concrete\Package\XanForum\Controller\Conversations;

use Conversation as CoreConversation;
use Concrete\Core\Conversation\Message\MessageList as ConversationMessageList;
use Concrete\Core\Conversation\Message\ThreadedList as ConversationMessageThreadedList;
use Concrete\Core\View\DialogView;
use XanForum\App;
use Concrete\Core\Controller\Controller;
use FileImporter;
use Concrete\Core\Entity\File\Version as FileVersion;
use Permissions;
use FileSet;
use Block;
use User;
use Page;
use Area;

class Conversation extends Controller
{
    protected $controllerActionPath = "/ccm/xan_forum/conversation";

    public function view()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $security = $this->app->make('helper/security');

        $cnv = CoreConversation::getByID($this->request->post('cnvID'));
        if (is_object($cnv)) {
            $displayForm = true;
            $enableOrdering = (1 == $this->request->post('enableOrdering')) ? true : false;
            $enablePosting = (1 == $this->request->post('enablePosting')) ? CoreConversation::POSTING_ENABLED : CoreConversation::POSTING_DISABLED_MANUALLY;
            $paginate = (1 == $this->request->post('paginate')) ? true : false;

            $cp = new Permissions($cnv);
            if (!$cp->canAddConversationMessage()) {
                $enablePosting = CoreConversation::POSTING_DISABLED_PERMISSIONS;
            }

            if (in_array($this->request->post('displayMode'), ['flat'])) {
                $displayMode = $this->request->post('displayMode');
            } else {
                $displayMode = 'threaded';
            }

            $addMessageLabel = t('Add Message');
            if ($this->request->post('addMessageLabel')) {
                $addMessageLabel = $security->sanitizeString($this->request->post('addMessageLabel'));
            }
            switch ($this->request->post('task')) {
                case 'get_messages':
                    $displayForm = false;
                    break;
            }

            switch ($displayMode) {
                case 'flat':
                    $ml = new ConversationMessageList();
                    $ml->filterByConversation($cnv);
                    break;
                default: // threaded
                    $ml = new ConversationMessageThreadedList($cnv);
                    break;
            }

            switch ($this->request->post('orderBy')) {
                case 'date_desc':
                    $ml->sortByDateDescending();
                    break;
                case 'date_asc':
                    $ml->sortByDateAscending();
                    break;
                case 'rating':
                    $ml->sortByRating();
                    break;
            }

            if ($paginate && $vn->integer($this->request->post('itemsPerPage'))) {
                $ml->setItemsPerPage($this->request->post('itemsPerPage'));
            } else {
                $ml->setItemsPerPage(-1);
            }

            $summary = $ml->getSummary();
            $totalPages = $summary->pages;
            $args = [
                'cID' => intval($this->request->post('cID')),
                'bID' => intval($this->request->post('blockID')),
                'conversation' => $cnv,
                'messages' => $ml->getPage(),
                'displayMode' => $displayMode,
                'displayForm' => $displayForm,
                'enablePosting' => $enablePosting,
                'addMessageLabel' => $addMessageLabel,
                'currentPage' => 1,
                'totalPages' => $totalPages,
                'orderBy' => $this->request->post('orderBy'),
                'enableOrdering' => $enableOrdering,
                'displayPostingForm' => $this->request->post('displayPostingForm'),
                'enableCommentRating' => $this->request->post('enableCommentRating'),
                'dateFormat' => $this->request->post('dateFormat'),
                'customDateFormat' => $this->request->post('customDateFormat'),
                'blockAreaHandle' => $this->request->post('blockAreaHandle'),
                'attachmentsEnabled' => $this->request->post('attachmentsEnabled'),
                'attachmentOverridesEnabled' => $this->request->post('attachmentOverridesEnabled'),
            ];
            $this->set('args', $args);
            $view = new DialogView('/conversation/display');
            $view->setPackageHandle(App::pkgHandle());
            $this->setViewObject($view);
        }

        $editor = App::getForumEditor();
        foreach ((array) $editor->getConversationEditorAssetPointers() as $assetPointer) {
            $this->requireAsset($assetPointer->getType(), $assetPointer->getHandle());
        }
    }

    public function page()
    {
        $cnv = CoreConversation::getByID($this->request->post('cnvID'));
        if (is_object($cnv)) {
            $vn = $this->app->make('helper/validation/numbers');
            $enableOrdering = (1 == $this->request->post('enableOrdering')) ? true : false;
            $enablePosting = (1 == $this->request->post('enablePosting')) ? CoreConversation::POSTING_ENABLED : CoreConversation::POSTING_DISABLED_MANUALLY;
            $currentPage = $vn->integer($this->request->post('page')) ? $this->request->post('page') : 1;

            $cp = new Permissions($cnv);
            if (!$cp->canAddConversationMessage()) {
                $enablePosting = CoreConversation::POSTING_DISABLED_PERMISSIONS;
            }

            if (in_array($this->request->post('displayMode'), ['flat'])) {
                $displayMode = $this->request->post('displayMode');
            } else {
                $displayMode = 'threaded';
            }

            switch ($displayMode) {
                case 'flat':
                    $ml = new ConversationMessageList();
                    $ml->filterByConversation($cnv);
                    break;
                default: // threaded
                    $ml = new ConversationMessageThreadedList($cnv);
                    break;
            }

            switch ($this->request->post('orderBy')) {
                case 'date_desc':
                    $ml->sortByDateDescending();
                    break;
                case 'date_asc':
                    $ml->sortByDateAscending();
                    break;
                case 'rating':
                    $ml->sortByRating();
                    break;
            }

            $ml->setItemsPerPage($this->request->post('itemsPerPage'));

            $this->set('messages', $ml->getPage($currentPage));
            $this->set('args', [
                'cID' => intval($this->request->post('cID')),
                'bID' => intval($this->request->post('blockID')),
                'page' => Page::getByID(intval($this->request->post('cID'))),
                'blockAreaHandle' => $this->request->post('blockAreaHandle'),
                'enablePosting' => $enablePosting,
                'displayMode' => $displayMode,
                'enableCommentRating' => $this->request->post('enableCommentRating'),
                'dateFormat' => $this->request->post('dateFormat'),
                'customDateFormat' => $this->request->post('customDateFormat'),
                'blockAreaHandle' => $this->request->post('blockAreaHandle'),
                'attachmentsEnabled' => $this->request->post('attachmentsEnabled'),
                'attachmentOverridesEnabled' => $this->request->post('attachmentOverridesEnabled'),
            ]);

            $view = new DialogView('/conversation/display/page');
            $view->setPackageHandle(App::pkgHandle());
            $this->setViewObject($view);
        }
    }

    public function countMessagesHeader()
    {
        $cnvID = $this->post('cnvID');
        if ($this->app->make('helper/validation/numbers')->integer($cnvID) && $cnvID > 0) {
            $this->set('conversation', CoreConversation::getByID($cnvID));
            $view = new DialogView('/conversation/header');
            $view->setPackageHandle(App::pkgHandle());
            $this->setViewObject($view);
        }
    }

    public function addFile()
    {
        $token = $this->app->make('token');
        $ajax = $this->app->make('helper/ajax');
        $config = $this->app->make('config');
        $helperFile = $this->app->make('helper/concrete/file');

        $file = new \stdClass();
        $file->timestamp = $this->request->post('timestamp');

        $error = [];
        $pageObj = Page::getByID($this->request->post('cID'));
        $areaObj = Area::get($pageObj, $this->request->post('blockAreaHandle'));
        $blockObj = Block::getByID($this->request->post('bID'), $pageObj, $areaObj);

        do {
            if (!$token->validate('add_conversations_file')) {  // check token
                $error[] = t('Bad token');
            }

            if ($_FILES["file"]["error"] > 0) {
                $error[] = $_FILES["file"]["error"];
            }

            if (!is_object($blockObj) || $pageObj->isError()) {
                $error[] = t('Block ID or Page ID not sent');
                break;
            }

            if ('xan_conversation' != $blockObj->getBlockTypeHandle()) {
                $error[] = t('Invalid block');
                break;
            }

            $p = new Permissions($blockObj);
            if (!$p->canRead()) {    // block read permissions check
                $error[] = t('You do not have permission to view this conversation');
                break;
            }

            $conversation = $blockObj->getController()->getConversationObject();
            if (!(is_object($conversation))) {
                $error[] = t('Invalid Conversation.');
                break;
            }

            // check individual conversation for allowing attachments.
            if ($conversation->getConversationAttachmentOverridesEnabled() > 0) {
                if (1 != $conversation->getConversationAttachmentsEnabled()) {
                    $error[] = t('This conversation does not allow file attachments.');
                    break;
                }
                // check global config settings for whether or not file attachments should be allowed.
            } elseif (!$config->get('conversations.attachments_enabled')) {
                $error[] = t('This conversation does not allow file attachments.');
                break;
            }
            break;
        } while (false);

        if (!empty($error)) {
            $errorStr = implode(', ', $error);
            $file->error = $errorStr . '.';
            $ajax->sendResult($file);
        }

        $blockRegisteredSizeOverride = $conversation->getConversationMaxFileSizeRegistered();
        $blockGuestSizeOverride = $conversation->getConversationMaxFilesGuest();
        $blockRegisteredQuantityOverride = $conversation->getConversationMaxFilesRegistered();
        $blockGuestQuantityOverride = $conversation->getConversationMaxFilesGuest();
        $blockExtensionsOverride = $conversation->getConversationFileExtensions();

        if (id(new User())->isRegistered()) {
            if ($conversation->getConversationAttachmentOverridesEnabled()) {
                $maxFileSize = $blockRegisteredSizeOverride;
                $maxQuantity = $blockRegisteredQuantityOverride;
            } else {
                $maxFileSize = $config->get('conversations.files.registered.max_size');
                $maxQuantity = $config->get('conversations.files.registered.max');
            }
        } else {
            if ($conversation->getConversationAttachmentOverridesEnabled()) {
                $maxFileSize = $blockGuestSizeOverride;
                $maxQuantity = $blockGuestQuantityOverride;
            } else {
                $maxFileSize = $config->get('conversations.files.guest.max_size');
                $maxQuantity = $config->get('conversations.files.guest.max');
            }
        }

        if ($maxFileSize > 0 && filesize($_FILES["file"]["tmp_name"]) > $maxFileSize * 1000000) {  // max upload size
            $error[] = t('File size exceeds limit');
        }

        // check file count (this is just for presentation, final count check is done on message submit).
        if ($maxQuantity > 0 && ($this->request->post('fileCount')) > $maxQuantity) {
            $error[] = t('Attachment limit reached');
        }

        // check filetype extension and overrides
        if ($conversation->getConversationAttachmentOverridesEnabled()) {
            $extensionList = $blockExtensionsOverride;
        } else {
            $extensionList = $config->get('conversations.files.allowed_types');
        }

        $extensionList = $helperFile->unserializeUploadFileExtensions($extensionList);
        $incomingExtension = end(explode('.', $_FILES["file"]["name"]));
        if ($incomingExtension && count($extensionList)) {  // check against block file extensions override
            foreach ($extensionList as $extension) {
                if (strtolower($extension) == strtolower($incomingExtension)) {
                    $validExtension = true;
                    break;
                }
            }
            if (!$validExtension) {
                $error[] = t('Invalid File Extension');
            }
        }

        if (!empty($error)) {
            $errorStr = implode(', ', $error);
            $file->error = $errorStr . '.';
            $ajax->sendResult($file);
        }
        // -- end intitial validation -- //

        // begin file import
        $fi = new FileImporter();
        $fv = $fi->import($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
        if (!($fv instanceof FileVersion)) {
            $file->error = $fi->getErrorMessage($fv);
        } else {
            $file_set = $config->get('conversations.attachments_pending_file_set');
            $fs = FileSet::getByName($file_set);
            if (!is_object($fs)) {
                $fs = FileSet::createAndGetSet($file_set, FileSet::TYPE_PUBLIC, USER_SUPER_ID);
            }
            $fs->addFileToSet($fv);
            $file->id = $fv->getFileID();
            $file->tag = $this->request->post('tag');
        }

        $ajax->sendResult($file);
    }
}
