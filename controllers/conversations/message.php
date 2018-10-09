<?php
namespace Concrete\Package\XanForum\Controller\Conversations;

use Concrete\Core\Conversation\Message\Message as CoreMessage;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\Conversation\Rating\Type as ConversationRatingType;
use Concrete\Core\Conversation\FlagType\FlagType as ConversationFlagType;
use Symfony\Component\HttpFoundation\JsonResponse;
use XanForum\Conversation\Message\Message as ConversationMessage;
use XanForum\Conversation\Message\AuthorFormatter;
use Concrete\Core\Conversation\Message\Author;
use Concrete\Core\Application\EditResponse;
use Concrete\Core\View\DialogView;
use XanForum\App;
use Concrete\Core\File\File;
use PermissionKey;
use Permissions;
use Controller;
use UserInfo;
use Config;
use User;
use Page;
use Area;
use Block;
use URL;

class Message extends Controller
{
    protected $controllerActionPath = "/ccm/xan_forum/conversation/message";

    /**
     * @return ConversationMessage|\Concrete\Core\Error\ErrorList\ErrorList
     */
    public function add($return = false, $data = [])
    {
        $vs = $this->app->make('helper/validation/strings');
        $ve = $this->app->make('helper/validation/error');
        $vn = $this->app->make('helper/validation/numbers');
        $as = $this->app->make('helper/validation/antispam');
        $ax = $this->app->make('helper/ajax');
        $token = $this->app->make('token');

        $u = isset($data['authorID']) ? User::getByUserID($data['authorID']) : new User();
        $pageObj = Page::getByID($this->post('cID', $data['cID']));
        $areaObj = Area::get($pageObj, $this->post('blockAreaHandle', $data['blockAreaHandle']));
        $blockObj = Block::getByID($this->post('bID', $data['bID']), $pageObj, $areaObj);
        $cnvMessageSubject = null;

        if (isset($data['cnvMessageSubject'])) {
            $cnvMessageSubject = $data['cnvMessageSubject'];
        }

        $pk = PermissionKey::getByHandle('add_conversation_message');

        if (!is_object($blockObj)) {
            $ve->add(t('Invalid Block Object.'));
        }

        $cnvID = $this->post('cnvID', $data['cnvID']);
        if ($vn->integer($cnvID)) {
            $cn = Conversation::getByID($cnvID);
        }

        if (!is_object($cn)) {
            $ve->add(t('Invalid conversation.'));
        } else {
            $pp = new Permissions($cn);
            if (!$pk->validate()) {
                $ve->add(t('You do not have access to add a message to this conversation.'));
            } else {
                // We know that we have access. So let's check to see if the user is logged in. If they're not we're going
                // to validate their name and email.
                $author = new Author();
                if (!$u->isRegistered()) {
                    $cnvMessageAuthorName = $this->post('cnvMessageAuthorName', $data['cnvMessageAuthorName']);
                    if (!$vs->notempty($cnvMessageAuthorName)) {
                        $ve->add(t('You must enter your name to post this message.'));
                    } else {
                        $author->setName($cnvMessageAuthorName);
                    }

                    $cnvMessageAuthorEmail = $this->post('cnvMessageAuthorEmail', $data['cnvMessageAuthorEmail']);
                    if (!$vs->email($cnvMessageAuthorEmail)) {
                        $ve->add(t('You must enter a valid email address to post this message.'));
                    } else {
                        $author->setEmail($cnvMessageAuthorEmail);
                    }

                    $author->setWebsite($this->post('cnvMessageAuthorWebsite', $data['cnvMessageAuthorEmail']));
                } else {
                    $author->setUser($u);
                }
            }
        }

        if (!is_object($pageObj) || !is_object($blockObj)) {
            $ve->add(t('Invalid Page.'));
        } else {
            $attachments = $this->post('attachments', $data['attachments']);
            if (is_array($attachments) && count($attachments)) {
                if (is_object($pp) && !$pp->canAddConversationMessageAttachments()) {
                    $ve->add(t('You do not have permission to add attachments.'));
                } else {
                    $cnt = $blockObj->getController();
                    $maxFiles = $u->isRegistered() ? $cnt->maxFilesRegistered : $cnt->maxFilesGuest;
                    if ($maxFiles > 0 && count($attachments) > $maxFiles) {
                        $ve->add(t('You have too many attachments.'));
                    }
                }
            }
        }

        if (!isset($data['cnvMessageBody']) && !$token->validate('add_conversation_message', $this->post('token'))) {
            $ve->add(t('Invalid conversation post token.'));
        }

        $editor = App::getForumEditor();
        $cnvMessageBody = $this->post($editor->getConversationEditorInputName(), $data['cnvMessageBody']);
        if (!$vs->notempty($cnvMessageBody)) {
            $ve->add(t('Your message cannot be empty.'));
        }

        $cnvMessageParentID = $this->post('cnvMessageParentID', $data['cnvMessageParentID']);
        if ($vn->integer($cnvMessageParentID) && $cnvMessageParentID > 0) {
            $parent = ConversationMessage::getByID($cnvMessageParentID);
            if (!is_object($parent)) {
                $ve->add(t('Invalid parent message.'));
            }
        }

        if (Config::get('conversations.banned_words') && $this->app->make('helper/validation/banned_words')->hasBannedWords($cnvMessageBody)) {
            $ve->add(t('Banned words detected.'));
        }

        if ($ve->has()) {
            if ($return) {
                return $ve;
            } else {
                $ax->sendError($ve);
            }
        } else {
            $msg = ConversationMessage::add($cn, $author, $cnvMessageSubject, $cnvMessageBody, $parent);
            if (!$as->check($cnvMessageBody, 'conversation_comment')) {
                $msg->flag(ConversationFlagType::getByHandle('spam'));
            } else {
                $assignment = $pk->getMyAssignment();
                if ($assignment->approveNewConversationMessages()) {
                    $msg->approve();
                }
            }

            if (is_array($attachments)) {
                foreach ($attachments as $attachmentID) {
                    $msg->attachFile(File::getByID($attachmentID));
                }
            }

            if ($return) {
                return $msg;
            } else {
                $ax->sendResult($msg);
            }
        }
    }

    public function edit()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $u = new User();

        if ($vn->integer($_POST['cnvMessageID']) && $_POST['cnvMessageID'] > 0) {
            $message = ConversationMessage::getByID($_POST['cnvMessageID']);
            if (is_object($message)) {
                $mp = new Permissions($message);
                if ($mp->canEditConversationMessage()) {
                    $editor = App::getForumEditor();
                    $editor->setConversationMessageObject($message);
                    $conversation = $message->getConversationObject();
                    if (is_object($conversation)) {
                        if ($conversation->getConversationAttachmentOverridesEnabled() > 0) {
                            $attachmentsEnabled = $conversation->getConversationAttachmentsEnabled();
                        } else {
                            $attachmentsEnabled = Config::get('conversations.attachments_enabled');
                        }
                    }

                    $this->set('blockAreaHandle', $this->post('blockAreaHandle'));
                    $this->set('cID', $this->post('cID'));
                    $this->set('bID', $this->post('bID'));
                    $this->set('ui', UserInfo::getByID($u->getUserID()));
                    $this->set('token', $this->app->make('token'));
                    $this->set('form', $this->app->make('helper/form'));
                    $this->set('message', $message);
                    $this->set('editor', $editor);
                    $this->set('attachmentsEnabled', $attachmentsEnabled);

                    $view = new DialogView('/conversation/edit_message');
                    $view->setPackageHandle(App::pkgHandle());
                    $this->setViewObject($view);
                }
            }
        }
    }

    public function update()
    {
        $ax = $this->app->make('helper/ajax');
        $vs = $this->app->make('helper/validation/strings');
        $ve = $this->app->make('helper/validation/error');
        $vn = $this->app->make('helper/validation/numbers');
        $token = $this->app->make('token');

        $editor = App::getForumEditor();
        $pageObj = Page::getByID($this->post('cID'));
        $areaObj = Area::get($pageObj, $this->post('blockAreaHandle'));
        $blockObj = Block::getByID($this->post('bID'), $pageObj, $areaObj);

        if (!$token->validate('add_conversation_message', $this->post('token'))) {
            $ve->add(t('Invalid conversation post token.'));
        }

        $cnvMessageID = $this->post('cnvMessageID');
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $message = ConversationMessage::getByID($cnvMessageID);

            if (!is_object($message)) {
                $ve->add(t('Invalid message object.'));
            } else {
                $mp = new Permissions($message);
                if (!$mp->canEditConversationMessage()) {
                    $ve->add(t('You do not have access to edit this message.'));
                } else {
                    $editor->setConversationMessageObject($message);
                    if (!$vs->notempty($this->post($editor->getConversationEditorInputName()))) {
                        $ve->add(t('Your message cannot be empty.'));
                    }
                }
            }
        }

        $messageAttachmentCount = count($message->getAttachments($cnvMessageID));
        $attachments = $this->post('attachments');
        $attachmentsToAddCount = count($attachments);
        $totalCurrentAttachments = intval($attachmentsToAddCount) + intval($messageAttachmentCount);
        if ($attachments && $attachmentsToAddCount) {
            if (is_object($pp) && !$pp->canAddConversationMessageAttachments()) {
                $ve->add(t('You do not have permission to add attachments.'));
            } else {  // this will require more maths to calc vs existing attachments
                $cnt = $blockObj->getController();
                $maxFiles = id(new User())->isRegistered() ? $cnt->maxFilesRegistered : $cnt->maxFilesGuest;
                if ($maxFiles > 0 && $totalCurrentAttachments > $maxFiles) {
                    $ve->add(t('You have too many attachments.'));
                }
            }
        }

        if (!$ve->has()) {
            $message->setMessageBody($this->post($editor->getConversationEditorInputName()));
            if ($attachments && $attachmentsToAddCount) {
                foreach ($attachments as $attachmentID) {
                    $message->attachFile(File::getByID($attachmentID));
                }
            }
            $ax->sendResult($message);
        } else {
            $ax->sendError($ve);
        }
    }

    public function delete()
    {
        $vn = $this->app->make('helper/validation/numbers');

        $cnvMessageID = $this->post('cnvMessageID');
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $message = ConversationMessage::getByID($cnvMessageID);
            if (is_object($message)) {
                $mp = new Permissions($message);
                if ($mp->canDeleteConversationMessage()) {
                    $message->delete();

                    $r = \Request::getInstance();
                    $types = $r->getAcceptableContentTypes();
                    if ('application/json' == $types[0]) {
                        $r = new EditResponse();
                        $r->setMessage(t('Message deleted successfully.'));
                        $r->outputJSON();
                    } else {
                        $this->detail();
                    }
                }
            }
        }
    }

    public function flag()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $as = $this->app->make('helper/validation/antispam');

        $cnvMessageID = $this->post('cnvMessageID');
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $message = ConversationMessage::getByID($cnvMessageID);
            if (is_object($message)) {
                $mp = new Permissions($message);
                if ($mp->canFlagConversationMessage()) {
                    $message->flag(ConversationFlagType::getByHandle('spam'));
                    $message->unapprove();
                    $author = $message->getConversationMessageAuthorObject();

                    $as->report($message->getConversationMessageBody(),
                        $author->getName(),
                        $author->getEmail(),
                        $message->getConversationMessageSubmitIP(),
                        $message->getConversationMessageSubmitUserAgent()
                    );

                    $r = \Request::getInstance();
                    $types = $r->getAcceptableContentTypes();
                    if ('application/json' == $types[0]) {
                        $r = new EditResponse();
                        $r->setMessage(t('Message flagged successfully.'));
                        $r->outputJSON();
                    } else {
                        $this->detail();
                    }
                }
            }
        }
    }

    public function deleteFile()
    {
        $vn = $this->app->make('helper/validation/numbers');

        $cnvMessageAttachmentID = $this->post('cnvMessageAttachmentID');
        if ($vn->integer($cnvMessageAttachmentID) && $cnvMessageAttachmentID > 0) {
            $message = ConversationMessage::getByAttachmentID($cnvMessageAttachmentID);
            if (is_object($message)) {
                $mp = new Permissions($message);
                if ($mp->canEditConversationMessage()) {
                    $message->removeFile($cnvMessageAttachmentID);
                    $attachmentDeleted = new \stdClass();
                    $attachmentDeleted->attachmentID = $cnvMessageAttachmentID;

                    return JsonResponse::create($attachmentDeleted);
                }
            }
        }

        return JsonResponse::create([t('Invalid Attachment requested')], JsonResponse::HTTP_NO_CONTENT);
    }

    public function detail()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $enablePosting = $this->post('enablePosting') ? true : false;
        $displayMode = $this->post('displayMode');

        if (!in_array($displayMode, ['flat'])) {
            $displayMode = 'threaded';
        }

        $cnvMessageID = $this->post('cnvMessageID');
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $message = ConversationMessage::getByID($cnvMessageID);
            if (is_object($message)) {
                $this->set('message', $message);
                if ($message->isConversationMessageApproved()) {
                    $this->set('displayMode', $displayMode);
                    $this->set('enablePosting', $enablePosting);
                    $this->set('enableCommentRating', $this->post('enableCommentRating'));
                }
                $view = new DialogView('/conversation/message');
                $view->setPackageHandle(App::pkgHandle());
                $this->setViewObject($view);
            }
        }
    }

    public function rate()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $cnvMessageID = $this->post('cnvMessageID');
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $msg = ConversationMessage::getByID($cnvMessageID);
            $msp = new Permissions($msg);
            if ($msp->canRateConversationMessage()) {
                $ratingType = ConversationRatingType::getByHandle($this->post('cnvRatingTypeHandle'));
                $msg->rateMessage($ratingType, $this->post('commentRatingIP'), $this->post('commentRatingUserID'));
                $msg = ConversationMessage::getByID($cnvMessageID);
                $totalRating = $msg->getConversationMessageTotalRatingScore();
                $this->app->make('helper/ajax')->sendResult(['total_rating' => $totalRating]);
            }
        }
    }

    public function report()
    {
        $vn = $this->app->make('helper/validation/numbers');
        $cnvMessageID = $this->post('cnvMessageID');
        $configEmail = Config::get('forum.email.report_message');
        $mail = $this->app->make('mail');
        $uir = $this->app->make('Concrete\Core\User\UserInfoRepository');
        $uia = $uir->getByID(USER_SUPER_ID);

        $resultReport = false;
        if ($vn->integer($cnvMessageID) && $cnvMessageID > 0) {
            $reportMessage = CoreMessage::getByID($cnvMessageID);
            $cnv = $reportMessage->getConversationObject();
            $c = $cnv->getConversationPageObject();
            $cnvID = $cnv->getConversationID();
            $cnvMessageURL = urlencode(URL::to($c) . '#cnv' . $cnvID . 'Message' . $cnvMessageID);
            $authorReportMessage = $reportMessage->getConversationMessageAuthorObject();
            $formatter = new AuthorFormatter($authorReportMessage);
            $mail->from($configEmail['address']);
            $mail->to($uia->getUserEmail());
            $mail->addParameter('title', $c->getCollectionName());
            $mail->addParameter('link', $cnvMessageURL);
            $mail->addParameter('poster', $formatter->getDisplayName());
            $mail->addParameter('body', $this->app->make('helper/text')->prettyStripTags($reportMessage->getConversationMessageBody()));
            $mail->load('report_conversation_message', 'xan_forum');
            $mail->sendMail();
            $resultReport = true;
        }
        $this->set('cnvMessageID', $cnvMessageID);
        $this->set('resultReport', $resultReport);
        $view = new DialogView('/conversation/alert_report_conversation_message');
        $view->setPackageHandle(App::pkgHandle());
        $this->setViewObject($view);
    }
}
