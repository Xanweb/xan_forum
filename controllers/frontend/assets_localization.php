<?php
namespace Concrete\Package\XanForum\Controller\Frontend;

use Controller;

class AssetsLocalization extends Controller
{
    protected static function sendJavascriptHeader()
    {
        header('Content-type: text/javascript; charset=' . APP_CHARSET);
    }

    public static function getConversationsJavascript($setResponseHeaders = true)
    {
        if ($setResponseHeaders) {
            static::sendJavascriptHeader();
        } ?>
jQuery.fn.xanConversation.localize({
  Confirm_remove_message: <?php echo json_encode(t('Remove this message? Replies to it will not be removed')); ?>,
  Confirm_mark_as_spam: <?php echo json_encode(t('Are you sure you want to flag this message as spam?')); ?>,
  Warn_currently_editing: <?php echo json_encode(t('Please complete or cancel the current message editing session before editing this message.')); ?>,
  Unspecified_error_occurred: <?php echo json_encode(t('An unspecified error occurred.')); ?>,
  Error_deleting_message: <?php echo json_encode(t('Something went wrong while deleting this message, please refresh and try again.')); ?>,
  Error_flagging_message: <?php echo json_encode(t('Something went wrong while flagging this message, please refresh and try again.')); ?>
});
jQuery.fn.concreteConversationAttachments.localize({
  Too_many_files: <?php echo json_encode(t('Too many files')); ?>,
  Invalid_file_extension: <?php echo json_encode(t('Invalid file extension')); ?>,
  Max_file_size_exceeded: <?php echo json_encode(t('Max file size exceeded')); ?>,
  Error_deleting_attachment: <?php echo json_encode(t('Something went wrong while deleting this attachment, please refresh and try again.')); ?>,
  Confirm_remove_attachment: <?php echo json_encode(t('Remove this attachment?')); ?>
});
        <?php
    }
}
