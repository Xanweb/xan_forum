<?php
namespace XanForum\Conversation\Message;

use Concrete\Core\Conversation\Message\Author;
use Concrete\Core\Conversation\Message\Message as MessageBasic;
use Concrete\Core\Conversation\Message\MessageEvent;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\Support\Facade\Application;
use ConversationEditor;

class Message extends MessageBasic
{
    /**
     * @override
     */
    public static function add(
        \Concrete\Core\Conversation\Conversation $cnv,
        Author $author,
        $cnvMessageSubject,
        $cnvMessageBody,
        $parentMessage = false
    ) {
        $app = Application::getFacadeApplication();

        /* @var \Concrete\Core\Database\Connection\Connection $db */
        $db = $app['database']->connection();
        $date = $app['helper/date']->getOverridableNow();

        $uID = 0;
        $user = $author->getUser();
        $cnvMessageAuthorName = $author->getName();
        $cnvMessageAuthorEmail = $author->getEmail();
        $cnvMessageAuthorWebsite = $author->getWebsite();

        if (is_object($user)) {
            $uID = $user->getUserID();
        }

        $cnvMessageParentID = 0;
        $cnvMessageLevel = 0;
        if (is_object($parentMessage)) {
            $cnvMessageParentID = $parentMessage->getConversationMessageID();
            $cnvMessageLevel = $parentMessage->getConversationMessageLevel() + 1;
        }

        $cnvID = 0;
        if ($cnv instanceof Conversation) {
            $cnvID = $cnv->getConversationID();
        }

        $editor = ConversationEditor::getActive();
        $cnvEditorID = $editor->getConversationEditorID();

        $ip = $app['ip']->getRequestIPAddress();
        $r = $db->executeQuery('insert into ConversationMessages (cnvMessageSubject, cnvMessageBody, cnvMessageDateCreated, cnvMessageParentID, cnvEditorID, cnvMessageLevel, cnvID, uID, cnvMessageAuthorName, cnvMessageAuthorEmail, cnvMessageAuthorWebsite, cnvMessageSubmitIP, cnvMessageSubmitUserAgent) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $cnvMessageSubject,
                $cnvMessageBody,
                $date,
                $cnvMessageParentID,
                $cnvEditorID,
                $cnvMessageLevel,
                $cnvID,
                $uID,
                $cnvMessageAuthorName,
                $cnvMessageAuthorEmail,
                $cnvMessageAuthorWebsite,
                (false === $ip) ? ('') : ($ip->getIp()),
                $_SERVER['HTTP_USER_AGENT'],
            ]);

        $cnvMessageID = $db->lastInsertId();

        $message = static::getByID($cnvMessageID);

        $event = new MessageEvent($message);
        $app['director']->dispatch('on_new_conversation_message', $event);

        if ($cnv instanceof \Concrete\Core\Conversation\Conversation) {
            $cnv->updateConversationSummary();
            $users = $cnv->getConversationUsersToEmail();
            $c = $cnv->getConversationPageObject();
            if (is_object($c)) {
                $formatter = new AuthorFormatter($author);
                $cnvMessageBody = html_entity_decode($cnvMessageBody, ENT_QUOTES, APP_CHARSET);
                foreach ($users as $ui) {
                    if ($ui->getUserID() != $author->getUser()->getUserID()) {
                        $mail = $app->make('mail');
                        $mail->to($ui->getUserEmail());
                        /* @var \Concrete\Core\User\UserInfo $ui */
                        $mail->addParameter('username', $ui->getUserName());
                        $mail->addParameter('title', $c->getCollectionName());
                        $mail->addParameter('link', $c->getCollectionLink(true));
                        $mail->addParameter('poster', $formatter->getDisplayName());
                        $mail->addParameter('body', $app['helper/text']->prettyStripTags($cnvMessageBody));
                        $mail->load('new_conversation_message', 'xan_forum');
                        $mail->sendMail();
                    }
                }
            }
        }

        return static::getByID($cnvMessageID);
    }
}
