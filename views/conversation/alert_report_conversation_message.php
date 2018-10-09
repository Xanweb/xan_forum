<?php if ($resultReport) {
    ?>
<div id='ccm-conversation-message-report-alert-id-<?=$cnvMessageID; ?>' class='alert alert-success' role='alert'>
    <?=t('An Email has been sent to administrators'); ?>
</div>
<?php
} else {
        ?>
<div id='ccm-conversation-message-report-alert-id-<?=$cnvMessageID; ?>' class='alert alert-danger' role='alert'>
   <?= t('Error email not sent to administrators.'); ?>
</div>
<?php
    } ?>