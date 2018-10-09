<?php defined('C5_EXECUTE') or die('Access Denied.');
$c = Page::getCurrentPage();
$token = Core::make('token');
?>

<div class="row">
    <div class="col-xs-12">
        <div class="xan-forum-topic xan-forum-new-topic">
            <?php if (isset($error) && $error->has()):?>
                <div class="alert alert-danger">
                    <a data-dismiss="alert" href="#" class="close"><span class="text-danger">&times;</span></a>
                    <?php
                    if (1 == count($error->getList())) {
                        echo $error->offsetGet(0);
                    } else {
                        $error->output();
                    }
                    ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= $view->action('save_topic'); ?>"
                  class="xan-forum-topic-new form-vertical" enctype="multipart/form-data">
                <h3 class="underlined-title"><?= t('Start New Topic'); ?></h3>
                <?= $token->output('add_conversation_message'); ?>
                <label for="subject" class="control-label"><?= t('Subject'); ?>(*)</label>
                <div class="form-group field field-text">
                    <input type="text" class="xan-forum-topic-new-subject form-control" name="subject" value="<?= $subject; ?>"/><br/>
                </div>
                <label for="subject" class="control-label"><?= t('Message'); ?>(*)</label>
                <div class="form-group field field-textarea">
                    <div class="xan-forum-topic-detail clearfix">
                        <?= Core::make('xan/editor')->xanOutputStandardEditor('new-topic-txt', XanForum::getForumEditor()->getConversationEditorInputName(), 'xan-forum-topic-new-text', $text); ?>
                        <br/>
                    </div>
                </div>
                <?php
                if (defined('XAN_FORUM_MESSAGE_HINT')) {
                    echo "<p class=\"xan-forum-message\">" . XAN_FORUM_MESSAGE_HINT . "</p>";
                }

                ?>
                <button type="button" class="pull-left btn-forum ccm-attachment-toggle" ><i class="fa fa-image"></i> <?=t('Attach Files'); ?></button>
                <div class="clearfix"></div>
                <div class="ccm-attachment-container">
					<div class="ccm-attachment-dropzone" data-token="<?= $token->generate('upload_file'); ?>" >
                        <div class="ccm-attachment-errors alert alert-danger" style="display: none;"></div>
					</div>
				</div>
                <div class="clearfix"></div>
                <div class="spacer-row-2"></div>
                <div class="clearfix">
                    <a href="<?= URL::to($c); ?>" class="btn btn-forum pull-left"><?= t('Cancel'); ?></a>
                    <input type="submit" class="btn btn-forum pull-right"
                           value="<?= t('Publish Topic'); ?>"/>
                </div>
                <?php if (isset($attachments)): ?>
                    <?php foreach ($attachments as $attachment): ?>
                        <input type="hidden" name="attachments[]" value="<?= $attachment; ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    /*hide forum header in page add new topic*/
    $(document).ready(function () {
        var hideBanner = <?= intval($hide_forum_header); ?>;
        if (hideBanner === 1) {
            $('.forum-header').hide();
        }
        
        $('.xan-forum-new-topic > form').xanAttachments(<?= json_encode($attachmentOptions); ?>);
    });
</script>
