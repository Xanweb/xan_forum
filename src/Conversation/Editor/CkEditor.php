<?php
namespace XanForum\Conversation\Editor;

use Concrete\Core\Conversation\Editor\Editor;

class CkEditor extends Editor
{
    public function getConversationEditorInputName()
    {
        $cnvMsgID = "";
        if (is_object($this->getConversationMessageObject())) {
            $cnvMsgID = $this->getConversationMessageObject()->getConversationMessageID();
        }

        return parent::getConversationEditorInputName() . $cnvMsgID;
    }

    public function getConversationEditorAssetPointers()
    {
        return [];
    }

    public function outputConversationEditorReplyMessageForm()
    {
        $this->outputConversationEditorAddMessageForm();
    }

    public function formatConversationMessageBody($cnv, $cnvMessageBody, $config = ['mention' => true])
    {
        return parent::formatConversationMessageBody($cnv, $cnvMessageBody, $config);
    }
}
