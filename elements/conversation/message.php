<?php
defined('C5_EXECUTE') or die("Access Denied.");

use XanForum\Conversation\Message\AuthorFormatter;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

/* @var $message \Concrete\Core\Conversation\Message\Message */
/* @var $dh Concrete\Core\Localization\Service\Date */
$dh = $app->make('helper/date');
$im = $app->make('helper/image');

$isTopic = (1 == $page && 0 == $index);
$mp = new Permissions($message);
$canDeleteMessage = $mp->canDeleteConversationMessage();
$canFlagMessage = $mp->canFlagConversationMessage();
$canEditMessage = $mp->canEditConversationMessage();
$canRateMessage = $mp->canRateConversationMessage();

$ui = $message->getConversationMessageUserObject();
$class = 'message ccm-conversation-message ccm-conversation-message-level' . $message->getConversationMessageLevel();
if ($message->isConversationMessageDeleted()) {
    $class .= ' ccm-conversation-message-deleted';
}

if ('custom' == $dateFormat && $customDateFormat) {
    $dateFormat = [$customDateFormat];
}
if (!$message->isConversationMessageApproved()) {
    $class .= ' ccm-conversation-message-flagged';
}
$cnvMessageID = $message->getConversationMessageID();
$cnvID = $message->getConversationID();
$c = Page::getByID($cID);
$cnvMessageURL = urlencode(URL::to($c) . '#cnv' . $cnvID . 'Message' . $cnvMessageID);

if ((!$message->isConversationMessageDeleted() && $message->isConversationMessageApproved()) || $message->conversationMessageHasActiveChildren()) {
    $author = $message->getConversationMessageAuthorObject();
    $formatter = new AuthorFormatter($author); ?>
    <?php if ($isTopic || $showHeader): ?>
        <div class="forum">
            <div class="title"><?= $c->getCollectionName(); ?>
                <span>
                    <a id="ccm-conversation-scroll-to-editor" href="javascript:void(0)"
                       class="ccm-conversation-message-btn-reply">
                    <i class="fa fa-reply-all"></i> <?php echo t('Reply'); ?>
                    </a>
                </span>
            </div>
        </div>
    <?php endif; ?>
    <div data-conversation-message-id="<?php echo $message->getConversationMessageID(); ?>"
         data-conversation-message-level="<?php echo $message->getConversationMessageLevel(); ?>"
         class="<?php echo $class; ?> forum">
        <a id="cnv<?php echo $cnvID; ?>Message<?php echo $cnvMessageID; ?>"></a>
        <div class="table-responsive">
            <table class="topics table">
                <?php if ($isTopic || $showHeader): ?>
                    <thead>
                    <tr>
                        <th width="20%"><?= t('Author'); ?></th>
                        <th width="80%"><?= t('Message'); ?></th>
                    </tr>
                    </thead>
                <?php endif; ?>
                <tbody>
                <tr>
                    <td width="20%">
                        <div class="ccm-conversation-message-user">
                            <span class="ccm-conversation-message-uName"><?= $formatter->getDisplayName(); ?>
                                <?php echo $app['xan/user/status']->getStatusIcon($message->getConversationMessageUserID()); ?>
                            </span>
                            <span class="ccm-conversation-avatar"><?= $formatter->getAvatar(); ?></span>
                            <?php if (!$message->isConversationMessageDeleted() && $message->isConversationMessageApproved()): ?>
                                <?php if ($enableCommentRating && $canRateMessage) {
        ?>
                                    <div class="clearfix text-center">
                                        <span class="ccm-conversation-message-rating-score"
                                              data-message-rating="<?= $message->getConversationMessageID(); ?>">
                                            <?= $message->getConversationMessageTotalRatingScore(); ?>
                                        </span>
                                        <?php
                                        $ratingTypes = ConversationRatingType::getList();
        foreach ($ratingTypes as $ratingType) {
            ?>
                                            <?php echo $ratingType->outputRatingTypeHTML(); ?>
                                        <?php
        } ?>
                                        <span class="ccm-conversation-social-share">
                                            <a class="ccm-conversation-message-control-icon share-permalink"
                                               data-message-id="<?php echo $messageID; ?>"
                                               rel="<?php echo $cnvMessageURL; ?>"
                                               title="<?php echo t('Get message URL.'); ?>"
                                               data-dialog-title="<?php echo t('Link'); ?>" href="#"><i
                                                    class="fa fa-link"></i></a>
                                        </span>
                                    </div>
                                <?php
    } ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td width="80%">
                        <div class="clearfix"><?= $message->getConversationMessageBodyOutput(); ?></div>
                        <?php if (count($message->getAttachments($message->getConversationMessageID()))): ?>
                            <div class="ccm-conversation-message-controls">
                                <span class="ccm-conversation-message-controls-title"><?= t('Attachments'); ?></span>
                                <div class="message-attachments">
                                    <?php
                                    foreach ($message->getAttachments($message->getConversationMessageID()) as $attachment) {
                                        ?>
                                        <div class="attachment-container">
                                        <?php $file = File::getByID($attachment['fID']);
                                        if (is_object($file)) {
                                            if (false !== strpos($file->getMimeType(), 'image')) {
                                                $paragraphPadding = 'image-preview';
                                                $thumb = $im->getThumbnail($file, '90', '90', true); ?>
                                                <div class="image-popover-hover"
                                                     data-full-image="<?php echo $file->getURL(); ?>">
                                                    <div class="glyph-container">
                                                        <i class="fa fa-search"></i>
                                                    </div>
                                                </div>
                                                <div class="attachment-preview-container">
                                                    <img class="posted-attachment-image"
                                                         src="<?php echo $thumb->src; ?>"
                                                         width="<?php echo $thumb->width; ?>"
                                                         height="<?php echo $thumb->height; ?>" alt="attachment image"/>
                                                </div>
                                            <?php
                                            } ?>
                                            <p class="<?php echo $paragraphPadding; ?> filename"
                                               rel="<?php echo $attachment['cnvMessageAttachmentID']; ?>"><a
                                                    href="<?php echo $file->getDownloadURL(); ?>"><?php echo $file->getFileName(); ?></a>
                                                <?php
                                                if (!$message->isConversationMessageDeleted() && $canEditMessage) {
                                                    ?>
                                                    <a rel="<?php echo $attachment['cnvMessageAttachmentID']; ?>"
                                                       class="attachment-delete ccm-conversation-message-control-icon"
                                                       href="#"><i class="fa fa-trash-o"></i></a>
                                                <?php
                                                } ?>
                                            </p>
                                            </div>
                                        <?php
                                        }
                                        $paragraphPadding = '';
                                    } ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th class="text-center">
                        <small><?= $dh->date(XanForum::cfg('datetime_format'), $message->getConversationMessageDateTime()); ?></small>
                    </th>
                    <th>
                        <?php if (User::isLoggedIn()) {
                                        ?>
                            <span class="ccm-conversation-message-divider">
                                    <a href="javascript:void(0)" class="btn-forum ccm-conversation-message-report  post"
                                       data-conversation-message-id="<?php echo $message->getConversationMessageID(); ?>">
                                        <i class="fa fa-times"></i> <?php echo t('Report'); ?>
                                        <i class=" ccm-conversation-message-report-loading hidden fa fa-spinner fa-pulse fa-lg fa-fw"></i>
                                    </a>
                                </span>
                        <?php
                                    } ?>
                        <?php if ($canDeleteMessage && !$isTopic) {
                                        ?>
                            <span class="ccm-conversation-message-divider">
                                    <a href="#" class="btn-forum admin-delete post"
                                       data-submit="delete-conversation-message"
                                       data-conversation-message-id="<?php echo $message->getConversationMessageID(); ?>">
                                        <i class="fa fa-trash"></i> <?php echo t('Delete'); ?>
                                    </a>
                                </span>
                        <?php
                                    } ?>
                        <?php if ($canEditMessage) {
                                        ?>
                            <span class="ccm-conversation-message-divider">
                                    <a href="javascript:void(0)" class="btn-forum admin-edit post"
                                       data-conversation-message-id="<?php echo $message->getConversationMessageID(); ?>"
                                       data-load="edit-conversation-message">
                                        <i class="fa fa-edit"></i> <?php echo t('Edit'); ?>
                                    </a>
                                </span>
                        <?php
                                    } ?>
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php
} ?>
<!--to add style highlight in messages received from ajax-->
<script>
    $(document).ready(function () {
        $('pre code').not('.hljs').each(function (i, block) {
            hljs.highlightBlock(block);
        });
    });
</script>