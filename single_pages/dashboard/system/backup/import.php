<?php
defined('C5_EXECUTE') or die('Access Denied.');

$formHelper = Core::make('helper/form');
?>

<h1>Import</h1>

<form method="post" enctype="multipart/form-data" action="<?=$this->action('import_xml'); ?>">

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('Forum Page'); ?>
        </label>
        <div class="col-sm-9 col-lg-10">
            <?=Core::make('helper/form/page_selector')->selectPage('forumPage'); ?>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('Collection Type'); ?>
        </label>
        <div class="col-sm-9 col-lg-10" id="xan-forum-topic-form-ctID">
            <?=$formHelper->select('ctID', $view->controller->getCollectionTypeIDs()); ?>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('Page Template'); ?>
        </label>
        <div class="col-sm-9 col-lg-10" id="xan-forum-topic-form-ptID">
            <?=$formHelper->select('ptID', false); ?>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('Area'); ?>
        </label>
        <div class="col-sm-9 col-lg-10" id="xan-forum-topic-form-area">
            <?=$formHelper->select('area', []); ?>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('Mail to new users'); ?>
        </label>
        <div class="col-sm-9 col-lg-10">
            <label>
                <?=$formHelper->checkbox('sendWelcomeMail', 1); ?>
                <?=t('Send welcome mail to new users'); ?>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('XML File'); ?>
        </label>
        <div class="col-sm-9 col-lg-10">
            <input type="file" name="xml">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 col-lg-10 col-lg-offset-2 col-md-offset-3">
            <input type="submit" class="btn btn-primary " value="<?=t('Import XML'); ?>">
        </div>
    </div>
</form>

<hr>

<h1>Merge</h1>

<form method="post" enctype="multipart/form-data" action="<?=$this->action('merge_xml'); ?>">
    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('XML File 1'); ?>
        </label>
        <div class="col-sm-9 col-lg-10">
            <input type="file" name="xml1">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-lg-2">
            <?=t('XML File 2');?>
        </label>
        <div class="col-sm-9 col-lg-10">
            <input type="file" name="xml2">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 col-lg-10 col-lg-offset-2 col-md-offset-3">
            <input type="submit" class="btn btn-primary " value="<?=t('Merge XML');?>">
        </div>
    </div>
</form>


<script type="text/javascript">
    $(function () {

        $('body').on('change', '#xan-forum-topic-form-ctID select', function (e) {
            var initialValue = $("#xan-forum-topic-form-ptID select").attr('ccm-passed-value');
            $("#xan-forum-topic-form-ptID option, #xan-forum-topic-form-area option").remove();
            $.post(CCM_DISPATCHER_FILENAME + "/ccm/xan_forum/tools/get/templates/" + $("#xan-forum-topic-form-ctID select").val(),
                function (collectionTemplates) {
                    for (var collectionTemplate in collectionTemplates) {
                        $("#xan-forum-topic-form-ptID select").append(
                            '<option value="' + collectionTemplate + '" '
                            + ((initialValue == collectionTemplate)?'selected="selected" ':'') + '>'
                            + collectionTemplates[collectionTemplate] + '</option>'
                        );
                    }
                    $("#xan-forum-topic-form-ptID select").change();
                }, 'json');
        });

        $('body').on('change', '#xan-forum-topic-form-ptID select', function () {
            var initialValue = $("#xan-forum-topic-form-area select").attr('ccm-passed-value');
            $("#xan-forum-topic-form-area option").remove();
            $("#xan-forum-topic-form-area select").append('<option value="">Please select</option>');
            if($(this).val() == ""){
                return false;
            }
            $.post(CCM_DISPATCHER_FILENAME + "/ccm/xan_forum/tools/get/areas/" + $(this).val(),
                function (areas) {
                    for (var i in areas) {
                        var area = areas[i];
                        $("#xan-forum-topic-form-area select").append(
                            '<option value="' + area + '" '
                            + ((initialValue == area)?'selected="selected" ':'') + '>'
                            + area + '</option>'
                        );
                    }
                }, 'json');
        });

    });
</script>