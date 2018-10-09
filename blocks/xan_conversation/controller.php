<?php
namespace Concrete\Package\XanForum\Block\XanConversation;

use Concrete\Core\Conversation\Message\ThreadedList;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\Conversation\Message\MessageList;
use Concrete\Core\Feature\ConversationFeatureInterface;
use Page;
use Core;
use Database;
use Permissions;

class Controller extends BlockController implements ConversationFeatureInterface
{
    protected $btCacheBlockRecord = true;
    protected $btTable = 'btXanConversation';
    protected $conversation;
    protected $btWrapperClass = 'ccm-ui';
    protected $btCopyWhenPropagate = true;
    protected $btFeatures = [
        'conversation',
    ];

    public function getBlockTypeDescription()
    {
        return t("Displays conversations on a page.");
    }

    public function getBlockTypeName()
    {
        return t("Xan Conversation");
    }

    public function getSearchableContent()
    {
        $ml = new MessageList();
        $ml->filterByConversation($this->getConversationObject());
        $messages = $ml->get();
        if (!count($messages)) {
            return '';
        }

        $content = '';
        foreach ($messages as $message) {
            $content .= $message->getConversationMessageSubject() . ' ' .
                strip_tags($message->getConversationMessageBody()) . ' ';
        }

        return rtrim($content);
    }

    public function getConversationFeatureDetailConversationObject()
    {
        return $this->getConversationObject();
    }

    public function getConversationObject()
    {
        if (!isset($this->conversation)) {
            // i don't know why this->cnvid isn't sticky in some cases, leading us to query
            // every damn time
            $db = Database::get();
            $cnvID = $db->GetOne('select cnvID from btXanConversation where bID = ?', [$this->bID]);
            $this->conversation = Conversation::getByID($cnvID);
        }

        return $this->conversation;
    }

    public function duplicate_master($newBID, $newPage)
    {
        parent::duplicate($newBID);
        $db = Database::get();
        $conv = Conversation::add();
        $conv->setConversationPageObject($newPage);
        $this->conversation = $conv;
        $db->Execute('update btXanConversation set cnvID = ? where bID = ?', [$conv->getConversationID(), $newBID]);
    }

    public function edit()
    {
        $fileSettings = $this->getFileSettings();
        $this->set('maxFilesGuest', $fileSettings['maxFilesGuest']);
        $this->set('maxFilesRegistered', $fileSettings['maxFilesRegistered']);
        $this->set('maxFileSizeGuest', $fileSettings['maxFileSizeGuest']);
        $this->set('maxFileSizeRegistered', $fileSettings['maxFileSizeRegistered']);
        $this->set('fileExtensions', $fileSettings['fileExtensions']);
        $this->set('attachmentsEnabled', $fileSettings['attachmentsEnabled'] > 0 ? $fileSettings['attachmentsEnabled'] : '');
        $this->set('attachmentOverridesEnabled', $fileSettings['attachmentOverridesEnabled'] > 0 ? $fileSettings['attachmentOverridesEnabled'] : '');

        $conversation = $this->getConversationObject();
        $this->set('notificationOverridesEnabled', $conversation->getConversationNotificationOverridesEnabled());
        $this->set('subscriptionEnabled', $conversation->getConversationSubscriptionEnabled());
        $this->set('notificationUsers', $conversation->getConversationSubscribedUsers());
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('xan/conversation');
        $this->requireAsset('core/lightbox');
        $u = new \User();
        if (!$u->isRegistered()) {
            $this->requireAsset('css', 'core/frontend/captcha');
        }
    }

    public function view()
    {
        $fileSettings = $this->getFileSettings();
        $conversation = $this->getConversationObject();
        if (is_object($conversation)) {
            $this->set('conversation', $conversation);
            if ($this->enablePosting) {
                $token = Core::make('helper/validation/token')->generate('add_conversation_message');
            } else {
                $token = '';
            }
            $this->set('posttoken', $token);
            $this->set('cID', Page::getCurrentPage()->getCollectionID());
            $this->set('users', $this->getActiveUsers(true));
            $this->set('maxFilesGuest', $fileSettings['maxFilesGuest']);
            $this->set('maxFilesRegistered', $fileSettings['maxFilesRegistered']);
            $this->set('maxFileSizeGuest', $fileSettings['maxFileSizeGuest']);
            $this->set('maxFileSizeRegistered', $fileSettings['maxFileSizeRegistered']);
            $this->set('fileExtensions', $fileSettings['fileExtensions']);
            $this->set('attachmentsEnabled', $fileSettings['attachmentsEnabled']);
            $this->set('attachmentOverridesEnabled', $fileSettings['attachmentOverridesEnabled']);
            //set args to dialogView
            $record = $this->record;
            switch ($record->displayMode) {
                case 'flat':
                    $ml = new MessageList();
                    $ml->filterByConversation($conversation);
                    break;
                default: // threaded
                    $ml = new ThreadedList($conversation);
                    break;
            }
            //get only message not deleted
            $ml->filterByNotDeleted();
            switch ($record->orderBy) {
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
            $itemsPerPage = $record->itemsPerPage;
            $ml->setItemsPerPage($itemsPerPage);

            $addMessageLabel = t('Add Message');
            if (null != $record->addMessageLabel) {
                $addMessageLabel = $record->addMessageLabel;
            }
            $summary = $ml->getSummary();
            $totalPages = $summary->pages;

            $displayForm = true;
            $enablePosting = $this->enablePosting ? Conversation::POSTING_ENABLED : Conversation::POSTING_DISABLED_MANUALLY;
            $cp = new Permissions($conversation);
            if (!$cp->canAddConversationMessage()) {
                $enablePosting = Conversation::POSTING_DISABLED_PERMISSIONS;
            }

            //get current page of pagination
            $currentPage = $ml->getPagination()->getCurrentPage() + 1;
            //number of last page in pagination
            $lastPageNumber = $totalPages;
            //get url of last page in pagination
            $urlLastPage = str_replace("%pageNum%", $lastPageNumber, $ml->getPagination()->URL);
            $record = $this->record;
            $args = [
                'cID' => Page::getCurrentPage()->getCollectionID(),
                'bID' => $record->bID,
                'conversation' => $conversation,
                'messages' => $ml->getPage(),
                'displayMode' => $record->displayMode,
                'displayForm' => $displayForm,
                'enablePosting' => $enablePosting,
                'addMessageLabel' => $addMessageLabel,
                'currentPage' => 1,
                'totalPages' => $totalPages,
                'orderBy' => $record->orderBy,
                'enableOrdering' => $record->enableOrdering,
                'displayPostingForm' => $record->displayPostingForm,
                'enableCommentRating' => $record->enableCommentRating,
                'dateFormat' => $record->dateFormat,
                'customDateFormat' => $record->customDateFormat,
                'blockAreaHandle' => $this->getAreaObject()->getAreaHandle(),
                'attachmentsEnabled' => $fileSettings['attachmentsEnabled'],
                'attachmentOverridesEnabled' => $fileSettings['attachmentOverridesEnabled'],
                'displayPagination' => $ml->displayPagingV2(false, true),
                'currentPage' => $currentPage,
                'urlLastPage' => $urlLastPage,
            ];
            $this->set('args', $args);
        }
    }

    public function getFileSettings()
    {
        $conversation = $this->getConversationObject();
        $helperFile = Core::make('helper/concrete/file');
        $maxFilesGuest = $conversation->getConversationMaxFilesGuest();
        $attachmentOverridesEnabled = $conversation->getConversationAttachmentOverridesEnabled();
        $maxFilesRegistered = $conversation->getConversationMaxFilesRegistered();
        $maxFileSizeGuest = $conversation->getConversationMaxFileSizeGuest();
        $maxFileSizeRegistered = $conversation->getConversationMaxFileSizeRegistered();
        $fileExtensions = $conversation->getConversationFileExtensions();
        $attachmentsEnabled = $conversation->getConversationAttachmentsEnabled();

        $fileExtensions = implode(',', $helperFile->unserializeUploadFileExtensions($fileExtensions)); //unserialize and implode extensions into comma separated string

        $fileSettings = [];
        $fileSettings['maxFileSizeRegistered'] = $maxFileSizeRegistered;
        $fileSettings['maxFileSizeGuest'] = $maxFileSizeGuest;
        $fileSettings['maxFilesGuest'] = $maxFilesGuest;
        $fileSettings['maxFilesRegistered'] = $maxFilesRegistered;
        $fileSettings['fileExtensions'] = $fileExtensions;
        $fileSettings['attachmentsEnabled'] = $attachmentsEnabled;
        $fileSettings['attachmentOverridesEnabled'] = $attachmentOverridesEnabled;

        return $fileSettings;
    }

    public function getActiveUsers($lower = false)
    {
        $cnv = $this->getConversationObject();
        $uobs = $cnv->getConversationMessageUsers();
        $users = [];
        foreach ($uobs as $user) {
            if ($lower) {
                $users[] = strtolower($user->getUserName());
            } else {
                $users[] = $user->getUserName();
            }
        }

        return $users;
    }

    public function save($post)
    {
        $helperFile = Core::make('helper/concrete/file');
        $db = Database::get();
        $cnvID = $db->GetOne('select cnvID from btXanConversation where bID = ?', [$this->bID]);
        if (!$cnvID) {
            $conversation = Conversation::add();
            $b = $this->getBlockObject();
            $xc = $b->getBlockCollectionObject();
            $conversation->setConversationPageObject($xc);
        } else {
            $conversation = Conversation::getByID($cnvID);
        }
        $values = $post + [
                'attachmentOverridesEnabled' => null,
                'attachmentsEnabled' => null,
                'itemsPerPage' => null,
                'maxFilesGuest' => null,
                'maxFilesRegistered' => null,
                'maxFileSizeGuest' => null,
                'maxFileSizeRegistered' => null,
                'enableOrdering' => null,
                'enableCommentRating' => null,
                'notificationOverridesEnabled' => null,
                'subscriptionEnabled' => null,
                'fileExtensions' => null,
            ];
        if ($values['attachmentOverridesEnabled']) {
            $conversation->setConversationAttachmentOverridesEnabled(intval($values['attachmentOverridesEnabled']));
        } else {
            $conversation->setConversationAttachmentOverridesEnabled(0);
        }
        if ($values['attachmentsEnabled']) {
            $conversation->setConversationAttachmentsEnabled(intval($values['attachmentsEnabled']));
        }
        if (!$values['itemsPerPage']) {
            $values['itemsPerPage'] = 0;
        }
        if ($values['maxFilesGuest']) {
            $conversation->setConversationMaxFilesGuest(intval($values['maxFilesGuest']));
        }
        if ($values['maxFilesRegistered']) {
            $conversation->setConversationMaxFilesRegistered(intval($values['maxFilesRegistered']));
        }
        if ($values['maxFileSizeGuest']) {
            $conversation->setConversationMaxFileSizeGuest(intval($values['maxFileSizeGuest']));
        }
        if ($values['maxFileSizeRegistered']) {
            $conversation->setConversationMaxFilesRegistered(intval($values['maxFileSizeRegistered']));
        }
        if (!$values['enableOrdering']) {
            $values['enableOrdering'] = 0;
        }
        if (!$values['enableCommentRating']) {
            $values['enableCommentRating'] = 0;
        }

        if ($values['notificationOverridesEnabled']) {
            $conversation->setConversationNotificationOverridesEnabled(true);
            $users = [];
            if (is_array($this->post('notificationUsers'))) {
                foreach ($this->post('notificationUsers') as $uID) {
                    $ui = \UserInfo::getByID($uID);
                    if (is_object($ui)) {
                        $users[] = $ui;
                    }
                }
            }
            $conversation->setConversationSubscribedUsers($users);
            $conversation->setConversationSubscriptionEnabled(intval($values['subscriptionEnabled']));
        } else {
            $conversation->setConversationNotificationOverridesEnabled(false);
            $conversation->setConversationSubscriptionEnabled(0);
        }

        if ($values['fileExtensions']) {
            $receivedExtensions = preg_split('{,}', strtolower($values['fileExtensions']), null, PREG_SPLIT_NO_EMPTY);
            $fileExtensions = $helperFile->serializeUploadFileExtensions($receivedExtensions);
            $conversation->setConversationFileExtensions($fileExtensions);
        }

        $values['cnvID'] = $conversation->getConversationID();
        parent::save($values);
    }
}
