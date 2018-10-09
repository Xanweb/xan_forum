<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="ccm-conversation-edit-message" data-conversation-message-id="<?php echo $message->getConversationMessageID(); ?>">
    <form method="post" class="aux-reply-form">
        <div class="ccm-conversation-message-form">
            <div class="ccm-conversation-errors alert alert-danger"></div>
            <?php $editor->outputConversationEditorReplyMessageForm(); ?>
            <button type="button" data-post-message-id="<?php echo $message->getConversationMessageID(); ?>" data-submit="update-conversation-message" class="pull-right btn-custom btn-custom-green btn-custom-green-hover btn-small"><?php echo t('Save'); ?></button>
            <?php if ($attachmentsEnabled) {
    ?>
                <button type="button" class="pull-right btn-custom btn-custom-green ccm-conversation-attachment-toggle" title="<?php echo t('Attach Files'); ?>"><i class="fa fa-image"></i></button>
            <?php
} ?>
            <button type="button" data-post-message-id="<?php echo $message->getConversationMessageID(); ?>" data-submit="cancel-update" class="cancel-update pull-right btn-custom btn-custom-green btn-small"><?php echo t('Cancel'); ?></button>
            <?php echo $form->hidden('blockAreaHandle', $blockAreaHandle); ?>
            <?php echo $form->hidden('cID', $cID); ?>
            <?php echo $form->hidden('bID', $bID); ?>
        </div>
    </form>
    <?php if ($attachmentsEnabled) {
        ?>
        <div class="ccm-conversation-attachment-container">
            <form action="<?= URL::to('/ccm/xan_forum/conversation/file/add'); ?>" class="ccm-attachment-dropzone" id="file-upload-reply">
                <div class="ccm-conversation-errors alert alert-danger"></div>
                <?php $token->output('add_conversations_file'); ?>
                <?php echo $form->hidden('blockAreaHandle', $blockAreaHandle); ?>
                <?php echo $form->hidden('cID', $cID); ?>
                <?php echo $form->hidden('bID', $bID); ?>
            </form>
        </div>
    <?php
    } ?>
</div>



