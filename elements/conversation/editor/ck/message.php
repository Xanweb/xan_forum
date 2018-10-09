<?php defined('C5_EXECUTE') or die("Access Denied.");

$obj = $editor->getConversationObject();
$cnvID = is_object($obj) ? $obj->getConversationID() : 0;
$msgObj = $editor->getConversationMessageObject();
$content = is_object($msgObj) ? $msgObj->getConversationMessageBody() : '';

echo Core::make('xan/editor')->xanOutputStandardEditor($editor->getConversationEditorInputName(), $editor->getConversationEditorInputName(), 'conversation-editor ckeditor_conversation_editor_' . $cnvID, $content);
?>

