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