<?php
defined('C5_EXECUTE') or die("Access Denied.");
$form = Core::make('helper/form');
$val = Core::make('token');
$u = new User();
?>

<?php if ($displayForm && $displayPostingForm == $position) {
    ?>

    <?php if ($addMessageLabel) {
        ?>
        <h4 id="ccm-conversation-message-label"><?php echo $addMessageLabel; ?></h4>
    <?php
    } ?>

    <?php if (Conversation::POSTING_ENABLED == $enablePosting) {
        ?>
        <div class="ccm-conversation-add-new-message" id="ccm-conversation-add-new-message" rel="main-reply-form">
            <form method="post" class="main-reply-form">
                <div class="ccm-conversation-message-form">
                    <div class="ccm-conversation-errors alert alert-danger"></div>
                    <?php $editor->outputConversationEditorAddMessageForm(); ?>
                    <?php echo $form->hidden('blockAreaHandle', $blockAreaHandle); ?>
                    <?php echo $form->hidden('cID', $cID); ?>
                    <?php echo $form->hidden('bID', $bID); ?>
                    <button type="button" data-post-parent-id="0" data-submit="conversation-message"
                            class="pull-right btn-submit btn-forum"><?php echo t('Submit'); ?></button>
                    <?php if ($attachmentsEnabled) {
            ?>
                        <button type="button" class="pull-right btn-forum ccm-conversation-attachment-toggle" href="#"
                                title="<?php echo t('Attach Files'); ?>"><i class="fa fa-image"></i></button>
                    <?php
        } ?>
                    <?php if ($conversation->getConversationSubscriptionEnabled() && $u->isRegistered()) {
            ?>
                        <a href="<?php echo URL::to('/ccm/system/dialogs/conversation/subscribe', $conversation->getConversationID()); ?>"
                           data-conversation-subscribe="unsubscribe"
                           <?php if (!$conversation->isUserSubscribed($u)) {
                ?>style="display: none"<?php
            } ?>
                           class="btn pull-right btn-default"><?php echo t('Un-Subscribe'); ?></a>
                        <a href="<?php echo URL::to('/ccm/system/dialogs/conversation/subscribe', $conversation->getConversationID()); ?>"
                           data-conversation-subscribe="subscribe"
                           <?php if ($conversation->isUserSubscribed($u)) {
                ?>style="display: none"<?php
            } ?>
                           class="btn pull-right btn-default"><?php echo t('Subscribe to Conversation'); ?></a>
                    <?php
        } ?>
                </div>
            </form>
            <?php if ($attachmentsEnabled) {
            ?>
                <div class="ccm-conversation-attachment-container">
                    <form action="<?= URL::to('/ccm/xan_forum/conversation/file/add'); ?>"
                          class="ccm-attachment-dropzone">
                        <div class="ccm-conversation-errors alert alert-danger"></div>
                        <?php $val->output('add_conversations_file'); ?>
                        <?php echo $form->hidden('blockAreaHandle', $blockAreaHandle); ?>
                        <?php echo $form->hidden('cID', $cID); ?>
                        <?php echo $form->hidden('bID', $bID); ?>
                    </form>
                </div>
            <?php
        } ?>

        </div>

    <?php
    } else {
        ?>
        <?php switch ($enablePosting) {
            case Conversation::POSTING_DISABLED_MANUALLY:
                print '<p>' . t('Adding new message is disabled for this conversation.') . '</p>';
                break;
            case Conversation::POSTING_DISABLED_PERMISSIONS:
                print '<p>';
                echo ' ';
                if (!$u->isRegistered()) {
                    echo '<div class="login_area">' . t('You must <a class="login-form" >sign in</a> to post to this conversation.');
                    echo '</div>';
                } else {
                    echo t('You do not have permission to post this to conversation.');
                }
                echo '</p>';
                break;
        } ?>
    <?php
    } ?>

<?php
} ?>
<script language="javascript">
    <?php if (!isset($error)): ?>
    $(".login-form-panel").hide();
    <?php endif; ?>

    $(".login_area .login-form").click(function (event) {
        var loginFormPanel = $('.login_area .login-form-panel');
        event.stopPropagation();

        if (loginFormPanel.length < 1) {
            $(".login-form-panel").show();
            $(".login-form-panel").clone().appendTo($(".login_area"));
            $("nav.login-nav .login-form-panel").hide();
        }
        else {
            loginFormPanel.toggle();
        }
    });

    $('body').click(function (event) {
        var loginFormPanel = $('.login_area .login-form-panel');
        var target = $(event.target);
        if (target.hasClass('login-form-panel') || target.is('input') || target.is('label')) {
            return;
        }

        if (loginFormPanel.is(':visible')) {
            loginFormPanel.hide();
        }
    });
</script>

