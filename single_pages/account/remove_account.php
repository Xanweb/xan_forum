<?php
/**
 * @Author Ben Ali Faker
 * @Engineer/ProjectManager
 * @Company Xanweb
 * Date: 10/05/17.
 */
defined('C5_EXECUTE') or die("Access Denied.");
?>
<h2><?=t('Remove account'); ?></h2>
<form method="post" action="<?php echo $view->action('view'); ?>" enctype="multipart/form-data">
     <fieldset>
        <div class="form-group">
            <?php echo $form->label('currentPassword', t('Current Password')); ?>
            <?php echo $form->password('uPassword', ['autocomplete' => 'off', "required" => true]); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label('confirmCurrentPassword', t('Confirm Current Password')); ?>
            <div class="controls">
                <?php echo $form->password('uPasswordConfirm', ['autocomplete' => 'off', "required" => true]); ?>
            </div>
        </div>
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
                <input type="submit" name="save" value="<?=t('Confirm');?>" class="btn btn-primary pull-right" />
        </div>
    </div>

</form>
