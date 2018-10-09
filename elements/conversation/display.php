<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
if (!is_array($messages)) {
    $messages = [];
}
$u = new User();
$ui = UserInfo::getByID($u->getUserID());
$page = Page::getByID($cID);
$ms = \Concrete\Core\Multilingual\Page\Section\Section::getBySectionOfSite($page);
if (is_object($ms)) {
    Localization::changeLocale($ms->getLocale());
}
$editor = XanForum::getForumEditor();
$editor->setConversationObject($args['conversation']);
$val = Core::make('token');
$form = Core::make('helper/form');
?>

<?php View::element('conversation/message/add_form', [
    'blockAreaHandle' => $blockAreaHandle,
    'cID' => $cID,
    'bID' => $bID,
    'editor' => $editor,
    'addMessageLabel' => $addMessageLabel,
    'attachmentsEnabled' => $attachmentsEnabled,
    'displayForm' => $displayForm,
    'displayPostingForm' => $displayPostingForm,
    'position' => 'top',
    'enablePosting' => $enablePosting,
    'conversation' => $conversation,
], XanForum::pkgHandle()); ?>


<div class="ccm-conversation-message-list ccm-conversation-messages-<?php echo $displayMode; ?>">

    <div class="ccm-conversation-delete-message" data-dialog-title="<?php echo t('Delete Message'); ?>"
         data-cancel-button-title="<?php echo t('Cancel'); ?>"
         data-confirm-button-title="<?php echo t('Delete Message'); ?>">
        <?php echo t('Remove this message?.'); ?>
    </div>
    <div class="ccm-conversation-delete-attachment" data-dialog-title="<?php echo t('Delete Attachment'); ?>"
         data-cancel-button-title="<?php echo t('Cancel'); ?>"
         data-confirm-button-title="<?php echo t('Delete Attachment'); ?>">
        <?php echo t('Remove this attachment?'); ?>
    </div>
    <div class="ccm-conversation-message-permalink" data-dialog-title="<?php echo t('Link'); ?>"
         data-cancel-button-title="<?php echo t('Close'); ?>">
    </div>

    <div class="ccm-conversation-messages-header">
        <?php if ($enableOrdering) {
    ?>
            <select class="form-control pull-right ccm-sort-conversations" data-sort="conversation-message-list">
                <option value="date_asc"
                        <?php if ('date_asc' == $orderBy) {
        ?>selected="selected"<?php
    } ?>><?php echo t('Earliest First'); ?></option>
                <option value="date_desc"
                        <?php if ('date_desc' == $orderBy) {
        ?>selected="selected"<?php
    } ?>><?php echo t('Most Recent First'); ?></option>
                <option value="rating"
                        <?php if ('rating' == $orderBy) {
        ?>selected="selected"<?php
    } ?>><?php echo t('Highest Rated'); ?></option>
            </select>
        <?php
} ?>
    </div>


    <div class="ccm-conversation-messages">

        <?php $showHeader = true;
        foreach ($messages as $i => $m) {
            View::element('conversation/message', [
                'index' => $i, 'cID' => $cID, 'message' => $m, 'bID' => $bID,
                'page' => $currentPage, 'showHeader' => $showHeader, 'blockAreaHandle' => $blockAreaHandle,
                'enablePosting' => $enablePosting, 'displayMode' => $displayMode,
                'enableCommentRating' => $enableCommentRating, 'dateFormat' => $dateFormat,
                'customDateFormat' => $customDateFormat,
            ], XanForum::pkgHandle());
            $showHeader = false;
        }
        ?>

    </div>


</div>

<?php View::element('conversation/message/add_form', [
    'blockAreaHandle' => $blockAreaHandle,
    'cID' => $cID,
    'bID' => $bID,
    'editor' => $editor,
    'addMessageLabel' => $addMessageLabel,
    'attachmentsEnabled' => $attachmentsEnabled,
    'displayForm' => $displayForm,
    'displayPostingForm' => $displayPostingForm,
    'position' => 'bottom',
    'enablePosting' => $enablePosting,
    'conversation' => $conversation,
], XanForum::pkgHandle()); ?>

