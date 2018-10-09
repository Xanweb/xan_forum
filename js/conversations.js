/**
 * $.fn.concreteConversation
 * Functions for conversation handling
 *
 * Events:
 *    beforeInitializeConversation         : Before Conversation Initialized
 *    initializeConversation               : Conversation Initialized
 *    conversationLoaded                   : Conversation Loaded
 *    conversationPostError                : Error posting message
 *    conversationBeforeDeleteMessage      : Before deleting message
 *    conversationDeleteMessage            : Deleting message
 *    conversationDeleteMessageError       : Error deleting message
 *    conversationBeforeAddMessageFromJSON : Before adding message from json
 *    conversationAddMessageFromJSON       : After adding message from json
 *    conversationBeforeUpdateCount        : Before updating message count
 *    conversationUpdateCount              : After updating message count
 *    conversationBeforeSubmitForm         : Before submitting form
 *    conversationSubmitForm               : After submitting form
 */

/* jshint unused:vars, undef:true, browser:true, jquery:true, -W041 */
/* global CCM_TOOLS_PATH, ConcreteEvent */

(function ($, window) {
    "use strict";
    $.extend($.fn, {
        xanConversation: function (options) {
            return this.each(function () {
                var $obj = $(this);
                var data = $obj.data('concreteConversation');
                if (!data) {
                    $obj.data('concreteConversation', (data = new XanConversation($obj, options)));
                }
            });
        }
    });

    var i18n = {
        Confirm_remove_message: 'Remove this message? Replies to it will not be removed.',
        Confirm_mark_as_spam: 'Are you sure you want to flag this message as spam?',
        Warn_currently_editing: 'Please complete or cancel the current message editing session before editing this message.',
        Unspecified_error_occurred: 'An unspecified error occurred.',
        Error_deleting_message: 'Something went wrong while deleting this message, please refresh and try again.',
        Error_flagging_message: 'Something went wrong while flagging this message, please refresh and try again.'
        // Please add new translatable strings to the getConversationsJavascript of /concrete/controllers/frontend/assets_localization.php
    };
    $.fn.xanConversation.localize = function (dictionary) {
        $.extend(true, i18n, dictionary);
    };

    var XanConversation = function (element, options) {
        this.publish("beforeInitializeConversation", {element: element, options: options});
        this.init(element, options);
        this.publish("initializeConversation", {element: element, options: options});
    };
    XanConversation.fn = XanConversation.prototype = {
        publish: function (t, f) {
            f = f || {};
            f.XanConversation = this;
            window.ConcreteEvent.publish(t, f);
        },
        init: function (element, options) {
            var obj = this;

            obj.$element = element;
            obj.options = $.extend({
                method: 'ajax',
                paginate: false,
                displayMode: 'threaded',
                itemsPerPage: -1,
                activeUsers: [],
                uninitialized: true
            }, options);

            var enablePosting = (obj.options.posttoken != '') ? 1 : 0;
            var paginate = (obj.options.paginate) ? 1 : 0;
            var orderBy = (obj.options.orderBy);
            var enableOrdering = (obj.options.enableOrdering);
            var displayPostingForm = (obj.options.displayPostingForm);
            var enableCommentRating = (obj.options.enableCommentRating);
            var commentRatingUserID = (obj.options.commentRatingUserID);
            var commentRatingIP = (obj.options.commentRatingIP);
            var addMessageLabel = (obj.options.addMessageLabel) ? obj.options.addMessageLabel : '';
            var dateFormat = (obj.options.dateFormat);
            var customDateFormat = (obj.options.customDateFormat);
            var blockAreaHandle = (obj.options.blockAreaHandle);
            var maxFiles = (obj.options.maxFiles); // unused
            var maxFileSize = (obj.options.maxFileSize); // unused
            var fileExtensions = (obj.options.fileExtensions); // unused
            var attachmentsEnabled = (obj.options.attachmentsEnabled);
            var attachmentOverridesEnabled = (obj.options.attachmentOverridesEnabled);

            if (obj.options.method == 'ajax') {
                $.post(CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/view', {
                    'cnvID': obj.options.cnvID,
                    'cID': obj.options.cID,
                    'blockID': obj.options.blockID,
                    'enablePosting': enablePosting,
                    'itemsPerPage': obj.options.itemsPerPage,
                    'addMessageLabel': addMessageLabel,
                    'paginate': paginate,
                    'displayMode': obj.options.displayMode,
                    'orderBy': orderBy,
                    'enableOrdering': enableOrdering,
                    'displayPostingForm': displayPostingForm,
                    'enableCommentRating': enableCommentRating,
                    'commentRatingUserID': commentRatingUserID,
                    'commentRatingIP': commentRatingIP,
                    'dateFormat': dateFormat,
                    'customDateFormat': customDateFormat,
                    'blockAreaHandle': blockAreaHandle,
                    'attachmentsEnabled': attachmentsEnabled,
                    'attachmentOverridesEnabled': attachmentOverridesEnabled

                }, function (r) {
                    var oldobj = window.obj;
                    window.obj = obj;
                    obj.$element.empty().append(r);
                    var hash = window.location.hash.match(/^#cnv([0-9]+)Message[0-9]+$/);
                    if (hash !== null && hash[1] == obj.options.cnvID) {
                        var target = $('a' + window.location.hash).offset();
                        $('html, body').animate({scrollTop: target.top}, 800, 'linear');
                    }
                    window.obj = oldobj;
                    obj.attachBindings();
                    obj.publish('conversationLoaded');
                });
            } else {
                obj.attachBindings();
                obj.publish('conversationLoaded');
            }
        },
        mentionList: function (items, coordinates, bindTo) {
            var obj = this;
            if (!coordinates) return;
            obj.dropdown.parent.css({top: coordinates.y, left: coordinates.x});
            if (items.length == 0) {
                obj.dropdown.handle.dropdown('toggle');
                obj.dropdown.parent.remove();
                obj.dropdown.active = false;
                obj.dropdown.activeItem = -1;
                return;
            }

            obj.dropdown.list.empty();
            items.slice(0, 20).map(function (item) {
                var listitem = $('<li/>');
                var anchor = $('<a/>').appendTo(listitem).text(item.getName());
                anchor.click(function () {
                    ConcreteEvent.fire('ConversationMentionSelect', {obj: obj, item: item}, bindTo);
                });
                listitem.appendTo(obj.dropdown.list);
            });
            if (!obj.dropdown.active) {
                obj.dropdown.active = true;
                obj.dropdown.activeItem = -1;
                obj.dropdown.parent.appendTo(obj.$element);
                obj.dropdown.handle.dropdown('toggle');
            }
            if (obj.dropdown.activeItem >= 0)
                obj.dropdown.list.children().eq(obj.dropdown.activeItem).addClass('active');
        },
        attachSubscriptionBindings: function () {
            $('a[data-conversation-subscribe]').magnificPopup({
                type: 'ajax',
                callbacks: {
                    updateStatus: function (data) {
                        if (data.status == 'ready') {
                            var $form = $('form[data-conversation-form=subscribe]');
                            $('button').on('click', $form, function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                $.ajax({
                                    url: $form.attr('action'),
                                    dataType: 'json',
                                    success: function (r) {
                                        if (r.subscribed) {
                                            $('[data-conversation-subscribe=subscribe]').hide();
                                            $('[data-conversation-subscribe=unsubscribe]').show();
                                        } else {
                                            $('[data-conversation-subscribe=unsubscribe]').hide();
                                            $('[data-conversation-subscribe=subscribe]').show();
                                        }
                                        $.magnificPopup.close();
                                    }
                                });
                            });
                        }
                    },

                    beforeOpen: function () {
                        // just a hack that adds mfp-anim class to markup
                        this.st.mainClass = 'mfp-zoom-in';
                    }
                },
                closeOnContentClick: true,
                midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
            });
        },

        attachBindings: function () {

            var obj = this;
            obj.$element.unbind('.cnv');
            if (obj.options.uninitialized) {
                obj.options.uninitialized = false;
                ConcreteEvent.bind('ConversationMention', function (e, data) {
                        obj.mentionList(data.items, data.coordinates || false, data.bindTo || obj.$element.get(0));
                    },
                    obj.$element.get(0) // Bind to this conversation only.
                );
                obj.dropdown = {};
                obj.dropdown.parent = $('<div/>').css({
                    position: 'absolute',
                    height: 0,
                    width: 0
                });
                obj.dropdown.active = false;
                obj.dropdown.handle = $('<a/>').appendTo(obj.dropdown.parent);
                obj.dropdown.list = $('<ul/>').addClass('dropdown-menu').appendTo(obj.dropdown.parent);
                obj.dropdown.handle.dropdown();
                ConcreteEvent.bind('ConversationTextareaKeydownUp', function (e) {
                    if (obj.dropdown.activeItem == -1) obj.dropdown.activeItem = obj.dropdown.list.children().length;
                    obj.dropdown.activeItem -= 1;
                    obj.dropdown.activeItem += obj.dropdown.list.children().length;
                    obj.dropdown.activeItem %= obj.dropdown.list.children().length;
                    obj.dropdown.list.children().filter('.active').removeClass('active').end().eq(obj.dropdown.activeItem).addClass('active');
                }, obj.$element.get(0));
                ConcreteEvent.bind('ConversationTextareaKeydownDown', function (e) {
                    obj.dropdown.activeItem += 1;
                    obj.dropdown.activeItem += obj.dropdown.list.children().length;
                    obj.dropdown.activeItem %= obj.dropdown.list.children().length;
                    obj.dropdown.list.children().filter('.active').removeClass('active').end().eq(obj.dropdown.activeItem).addClass('active');
                }, obj.$element.get(0));
                ConcreteEvent.bind('ConversationTextareaKeydownEnter', function (e) {
                    obj.dropdown.list.children().filter('.active').children('a').click();
                }, obj.$element.get(0));
                ConcreteEvent.bind('ConversationPostError', function (e, data) {
                    var $form = data.form,
                        messages = data.messages;
                    var s = '';
                    $.each(messages, function (i, m) {
                        s += m + '<br>';
                    });
                    $form.find('div.ccm-conversation-errors').html(s).show();
                });
                ConcreteEvent.bind('ConversationSubmitForm', function (e, data) {
                    data.form.find('div.ccm-conversation-errors').hide();
                });
            }
            var paginate = (obj.options.paginate) ? 1 : 0;
            var enablePosting = (obj.options.posttoken != '') ? 1 : 0;
            var addMessageLabel = (obj.options.addMessageLabel) ? obj.options.addMessageLabel : '';

            obj.$replyholder = obj.$element.find('div.ccm-conversation-add-reply');
            obj.$newmessageform = obj.$element.find('div.ccm-conversation-add-new-message form');
            obj.$newmessageformAttachments = obj.$element.find('div.ccm-conversation-add-new-message').find('.ccm-attachment-dropzone');
            obj.$deleteholder = obj.$element.find('div.ccm-conversation-delete-message');
            obj.$attachmentdeleteholder = obj.$element.find('div.ccm-conversation-delete-attachment');
            obj.$permalinkholder = obj.$element.find('div.ccm-conversation-message-permalink');
            obj.$messagelist = obj.$element.find('div.ccm-conversation-message-list');
            obj.$messagecnt = obj.$element.find('.ccm-conversation-message-count');
            obj.$postbuttons = obj.$element.find('[data-submit=conversation-message]');
            obj.$sortselect = obj.$element.find('select[data-sort=conversation-message-list]');
            obj.$loadmore = obj.$element.find('[data-load-page=conversation-message-list]');
            obj.$messages = obj.$element.find('div.ccm-conversation-messages');
            obj.$messagerating = obj.$element.find('span.ccm-conversation-message-rating');

            obj.$element.on('click.cnv', '[data-submit=conversation-message]', function (e) {
                e.preventDefault();
                obj.submitForm($(this));
            });
            obj.$element.on('click.cnv', '[data-submit=update-conversation-message]', function () {
                obj.submitUpdateForm($(this));
                return false;
            });
            this.attachSubscriptionBindings();
            var replyIterator = 1;
            obj.$element.on('click.cnv', 'a[data-toggle=conversation-reply]', function (event) {
                event.preventDefault();
                $('.ccm-conversation-attachment-container').each(function () {
                    if ($(this).is(':visible')) {
                        $(this).toggle();
                    }
                });
                var $replyform = obj.$replyholder.appendTo($(this).closest('div[data-conversation-message-id]'));
                $replyform.attr('data-form', 'conversation-reply').show();
                $replyform.find('[data-submit=conversation-message]').attr('data-post-parent-id', $(this).attr('data-post-parent-id'));

                $replyform.attr('rel', 'new-reply' + replyIterator);
                replyIterator++;  // this may not be necessary, but might come in handy if we need to know how many times a new reply box has been triggered.
                return false;
            });

            $('.ccm-conversation-attachment-container').hide();
            $('.ccm-conversation-add-new-message .ccm-conversation-attachment-toggle').off('click.cnv').on('click.cnv', function (event) {
                event.preventDefault();
                if ($('.ccm-conversation-add-reply .ccm-conversation-attachment-container').is(':visible')) {
                    $('.ccm-conversation-add-reply .ccm-conversation-attachment-container').toggle();
                }
                $('.ccm-conversation-add-new-message .ccm-conversation-attachment-container').toggle();
            });
            $('.ccm-conversation-add-reply .ccm-conversation-attachment-toggle').off('click.cnv').on('click.cnv', function (event) {
                event.preventDefault();
                if ($('.ccm-conversation-add-new-message .ccm-conversation-attachment-container').is(':visible')) {
                    $('.ccm-conversation-add-new-message .ccm-conversation-attachment-container').toggle();
                }
                $('.ccm-conversation-add-reply .ccm-conversation-attachment-container').toggle();
            });
            obj.$element.on('click.cnv', 'a[data-submit=delete-conversation-message]', function () {
                var $link = $(this);
                obj.$deletedialog = obj.$deleteholder.clone();
                if (obj.$deletedialog.dialog) {
                    obj.$deletedialog.dialog({
                        modal: true,
                        dialogClass: 'ccm-conversation-dialog',
                        title: obj.$deleteholder.attr('data-dialog-title'),
                        buttons: [
                            {
                                'text': obj.$deleteholder.attr('data-cancel-button-title'),
                                'class': 'btn pull-left',
                                'click': function () {
                                    obj.$deletedialog.dialog('close');
                                }
                            },
                            {
                                'text': obj.$deleteholder.attr('data-confirm-button-title'),
                                'class': 'btn pull-right btn-danger',
                                'click': function () {
                                    obj.deleteMessage($link.attr('data-conversation-message-id'));
                                }
                            }
                        ]
                    });
                } else {
                    if (window.confirm(i18n.Confirm_remove_message)) {
                        obj.deleteMessage($link.attr('data-conversation-message-id'));
                    }
                }
                return false;
            });
            obj.$element.on('click.cnv', 'a[data-submit=flag-conversation-message]', function () {
                var $link = $(this);
                if (window.confirm(i18n.Confirm_mark_as_spam)) {
                    obj.flagMessage($link.attr('data-conversation-message-id'));
                }
                return false;
            });
            obj.$element.on('click.cnv', 'a[data-load=edit-conversation-message]', function () {
                if ($('.ccm-conversation-edit-message').is(':visible')) {
                    window.alert(i18n.Warn_currently_editing);
                    return false;
                }
                var $link = $(this);
                obj.editMessage($link.attr('data-conversation-message-id'));
            });

            obj.$element.on('click.cnv', '.image-popover-hover', function () {
                $.magnificPopup.open({
                    items: {
                        src: $(this).attr('data-full-image'), // can be a HTML string, jQuery object, or CSS selector
                        type: 'image',
                        verticalFit: true
                    }
                });
            });


            obj.$element.on('click.cnv', '[data-load-page=conversation-message-list]', function () {
                var nextPage = parseInt(obj.$loadmore.attr('data-next-page'));
                var totalPages = parseInt(obj.$loadmore.attr('data-total-pages'));
                var data = {
                    'cnvID': obj.options.cnvID,
                    'cID': obj.options.cID,
                    'blockID': obj.options.blockID,
                    'itemsPerPage': obj.options.itemsPerPage,
                    'displayMode': obj.options.displayMode,
                    'blockAreaHandle': obj.options.blockAreaHandle,
                    'enablePosting': enablePosting,
                    'addMessageLabel': addMessageLabel,
                    'page': nextPage,
                    'orderBy': obj.$sortselect.val(),
                    'enableCommentRating': obj.options.enableCommentRating,
                    'dateFormat': obj.options.dateFormat,
                    'customDateFormat': obj.options.customDateFormat,
                    'attachmentsEnabled': obj.options.attachmentsEnabled,
                    'attachmentOverridesEnabled': obj.options.attachmentOverridesEnabled
                };

                $.ajax({
                    type: 'post',
                    data: data,
                    url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/page',
                    success: function (html) {
                        obj.$messages.append(html);
                        $('.ccm-conversation-messages .dropdown-toggle').dropdown();
                        if ((nextPage + 1) > totalPages) {
                            obj.$loadmore.hide();
                        } else {
                            obj.$loadmore.attr('data-next-page', nextPage + 1);
                        }
                    }
                });
            });

            obj.$element.on('click.cnv', '.conversation-rate-message', function () {
                var cnvMessageID = $(this).closest('[data-conversation-message-id]').attr('data-conversation-message-id');
                var cnvRatingTypeHandle = $(this).attr('data-conversation-rating-type');
                var data = {
                    'cnvID': obj.options.cnvID,
                    'cID': obj.options.cID,
                    'blockID': obj.options.blockID,
                    'cnvMessageID': cnvMessageID,
                    'cnvRatingTypeHandle': cnvRatingTypeHandle,
                    'commentRatingUserID': obj.options.commentRatingUserID,
                    'commentRatingIP': obj.options.commentRatingIP
                };
                $.ajax({
                    type: 'post',
                    data: data,
                    url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/rate',
                    success: function (response) {
                        var parsed = JSON.parse(response);
                        $('span[data-message-rating="' + cnvMessageID + '"]').text(parsed.total_rating);
                    }
                });
            });
            obj.$element.on('click.cnv', 'a.share-popup', function () {
                var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
                var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
                var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
                var left = ((width / 2) - (300)) + dualScreenLeft;
                var top = ((height / 2) - (125)) + dualScreenTop;
                window.open($(this).attr('href'), 'cnvSocialShare', 'left:' + left + ',top:' + top + ',height=250,width=600,toolbar=no,status=no');

                return false;
            });
            obj.$element.on('click.cnv', 'a.share-permalink', function () {
                var $link = $(this);
                var permalink = $link.attr('rel');
                obj.$permalinkdialog = obj.$permalinkholder.clone();
                var $textarea = $('<textarea readonly>').text(decodeURIComponent(permalink));
                obj.$permalinkdialog.append($textarea);
                $textarea.click(function () {
                    var $this = $(this);
                    $this.select();
                    window.setTimeout(function () {
                        $this.select();
                    }, 1);
                    $this.mouseup(function () {
                        $this.unbind("mouseup");
                        return false;
                    });
                });
                if (obj.$permalinkdialog.dialog) {
                    obj.$permalinkdialog.dialog({
                        modal: true,
                        dialogClass: 'ccm-conversation-dialog',
                        title: obj.$permalinkholder.attr('data-dialog-title'),
                        buttons: [
                            {
                                'text': obj.$permalinkholder.attr('data-cancel-button-title'),
                                'class': 'btn pull-left',
                                'click': function () {
                                    obj.$permalinkdialog.dialog('close');
                                }
                            }
                        ]
                    });
                }
                return false;
            });
            if (obj.options.attachmentsEnabled > 0) {
                obj.$element.concreteConversationAttachments(obj);
            }
            $('.dropdown-toggle').dropdown();
        },
        handlePostError: function ($form, messages) {
            if (!messages) {
                messages = [i18n.Unspecified_error_occurred];
            }
            this.publish('conversationPostError', {form: $form, messages: messages});
        },
        deleteMessage: function (msgID) {
            var obj = this;
            obj.publish('conversationBeforeDeleteMessage', {msgID: msgID});
            var formArray = [{
                'name': 'cnvMessageID',
                'value': msgID
            }];

            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/delete',
                success: function (html) {
                    var $parent = $('div[data-conversation-message-id=' + msgID + ']');


                    if ($parent.length) {
                        $parent.remove();
                    }
                    obj.updateCount();
                    if (obj.$deletedialog.dialog)
                        obj.$deletedialog.dialog('close');
                    obj.publish('conversationDeleteMessage', {msgID: msgID});
                },
                error: function (e) {
                    obj.publish('conversationDeleteMessageError', {msgID: msgID, error: arguments});
                    window.alert(i18n.Error_deleting_message);
                }
            });
        },
        editMessage: function (msgID) {
            var obj = this;
            var formArray = [
                {
                    'name': 'cnvMessageID',
                    'value': msgID
                },
                {
                    'name': 'cID',
                    'value': this.options.cID
                },
                {
                    'name': 'blockAreaHandle',
                    'value': this.options.blockAreaHandle
                },
                {
                    'name': 'bID',
                    'value': this.options.blockID
                }
            ];
            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/edit',
                success: function (html) {
                    var $parent = $('div[data-conversation-message-id=' + msgID + ']');
                    var $previousContents = $parent;
                    $parent.after(html).remove();
                    $('.ccm-conversation-attachment-container').hide();
                    $('.ccm-conversation-edit-message .ccm-conversation-attachment-toggle').off('click.cnv').on('click.cnv', function (event) {
                        event.preventDefault();
                        $('.ccm-conversation-edit-message .ccm-conversation-attachment-container').toggle();
                    });
                    obj.$editMessageHolder = obj.$element.find('div.ccm-conversation-edit-message');
                    obj.$element.concreteConversationAttachments(obj);
                    $('button.cancel-update').on('click.cnv', function () {
                        $('.ccm-conversation-edit-message').replaceWith($previousContents);
                    });

                },
                error: function (e) {
                    obj.publish('conversationEditMessageError', {msgID: msgID, error: arguments});
                }
            });
        },
        flagMessage: function (msgID) {

            var obj = this;
            obj.publish('conversationBeforeFlagMessage', {msgID: msgID});
            var formArray = [{
                'name': 'cnvMessageID',
                'value': msgID
            }];

            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/flag',
                success: function (html) {
                    var $parent = $('div[data-conversation-message-id=' + msgID + ']');

                    if ($parent.length) {
                        $parent.after(html).remove();
                    }
                    obj.updateCount();
                    obj.publish('conversationFlagMessage', {msgID: msgID});
                },
                error: function (e) {
                    obj.publish('conversationFlagMessageError', {msgID: msgID, error: arguments});
                    window.alert(i18n.Error_flagging_message);
                }
            });
        },
        addMessageFromJSON: function ($form, json) {
            var obj = this;
            obj.publish('conversationBeforeAddMessageFromJSON', {json: json, form: $form});
            var enablePosting = (obj.options.posttoken != '') ? 1 : 0;
            var formArray = [{
                'name': 'cnvMessageID',
                'value': json.cnvMessageID
            }, {
                'name': 'enablePosting',
                'value': enablePosting
            }, {
                'name': 'displayMode',
                'value': obj.options.displayMode
            }, {
                'name': 'enableCommentRating',
                'value': obj.options.enableCommentRating
            }];

            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/detail',
                success: function (html) {
                    var $parent = $('div[data-conversation-message-id=' + json.cnvMessageParentID + ']');

                    if ($parent.length) {
                        $parent.after(html);
                        obj.$replyholder.appendTo(obj.$element);
                        obj.$replyholder.hide();
                        obj.$replyholder.find(".conversation-editor").val('');
                        try {
                            obj.$replyholder.find(".redactor_conversation_editor_" + obj.options.cnvID).redactor('set', '');
                        } catch (e) {
                        }
                    } else {
                        if (obj.options.orderBy == 'date_desc') {
                            obj.$messages.prepend(html);
                        } else {
                            obj.$messages.append(html);
                        }
                        obj.$element.find('.ccm-conversation-no-messages').hide();
                        obj.$newmessageform.find(".conversation-editor").val('');
                        try {
                            obj.$newmessageform.find(".redactor_conversation_editor_" + obj.options.cnvID).redactor('set', '');
                        } catch (e) {
                        }

                    }
                    obj.publish('conversationAddMessageFromJSON', {json: json, form: $form});
                    obj.updateCount();
                    var target = $('a#cnv' + obj.options.cnvID + 'Message' + json.cnvMessageID).offset();
                    $('.dropdown-toggle').dropdown();
                    $('html, body').animate({scrollTop: target.top}, 800, 'linear');
                }
            });
        },
        updateMessageFromJSON: function ($form, json) {
            var obj = this;
            var enablePosting = (obj.options.posttoken != '') ? 1 : 0;
            var formArray = [{
                'name': 'cnvMessageID',
                'value': json.cnvMessageID
            }, {
                'name': 'enablePosting',
                'value': enablePosting
            }, {
                'name': 'displayMode',
                'value': obj.options.displayMode
            }, {
                'name': 'enableCommentRating',
                'value': obj.options.enableCommentRating
            }];

            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/detail',
                success: function (html) {
                    var $parent = $('div[data-conversation-message-id=' + json.cnvMessageID + ']');
                    $parent.after(html).remove();
                    $('.dropdown-toggle').dropdown();
                }
            });
        },
        updateCount: function () {
            var obj = this;
            obj.publish('conversationBeforeUpdateCount');
            obj.$messagecnt.load(CCM_TOOLS_PATH + '/conversations/count_header', {
                'cnvID': obj.options.cnvID
            }, function () {
                obj.publish('conversationUpdateCount');
            });
        },
        submitForm: function ($btn) {
            var obj = this;
            obj.publish('conversationBeforeSubmitForm');
            var $form = $btn.closest('form');

            $btn.prop('disabled', true);
            $form.parent().addClass('ccm-conversation-form-submitted');
            var formArray = $form.serializeArray();
            var parentID = $btn.attr('data-post-parent-id');

            formArray.push({
                'name': 'token',
                'value': obj.options.posttoken
            }, {
                'name': 'cnvID',
                'value': obj.options.cnvID
            }, {
                'name': 'cnvMessageParentID',
                'value': parentID
            }, {
                'name': 'enableRating',
                'value': parentID
            });
            $.ajax({
                dataType: 'json',
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/add',
                success: function (r) {
                    if (!r) {
                        obj.handlePostError($form);
                        return false;
                    }
                    if (r.error) {
                        /*$(window).scrollTop($('.user-area').offset());
                         $('.user-area').hide();
                         $('.user-area').fadeIn(1000);*/
                        $btn.prop('disabled', false);
                        obj.handlePostError($form, r.errors);
                        return false;
                    }
                    $('.preview.processing').each(function () {
                        $('input[rel="' + $(this).attr('rel') + '"]').remove();
                    });

                    $('form.ccm-attachment-dropzone').each(function () {
                        var d = $(this).data('dropzone');
                        $.each(d.files, function (k, v) {
                            d.removeFile(v);
                        });
                    });
                    obj.redirectToLastPageInPagination();
                },
                error: function (r) {
                    obj.handlePostError($form);
                    return false;
                },
                complete: function (r) {
                    $form.parent().closest('.ccm-conversation-form-submitted').removeClass('ccm-conversation-form-submitted');
                }
            });
        },
        redirectToLastPageInPagination: function () {
            var obj = this;
            var urlLastPage = (obj.options.urlLastPage);
            $(location).attr("href", urlLastPage);
        },
        submitUpdateForm: function ($btn) {
            var obj = this;
            obj.publish('conversationBeforeSubmitForm');
            var $form = $btn.closest('form');

            $btn.prop('disabled', true);
            $form.parent().addClass('ccm-conversation-form-submitted');
            var formArray = $form.serializeArray();
            var cnvMessageID = $btn.attr('data-post-message-id');

            formArray.push({
                'name': 'token',
                'value': obj.options.posttoken
            }, {
                'name': 'cnvMessageID',
                'value': cnvMessageID
            });
            $.ajax({
                dataType: 'json',
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/update',
                success: function (r) {
                    if (!r) {
                        obj.handlePostError($form);
                        return false;
                    }
                    if (r.error) {
                        obj.handlePostError($form, r.errors);
                        return false;
                    }
                    $('.preview.processing').each(function () {
                        $('input[rel="' + $(this).attr('rel') + '"]').remove();
                    });
                    /*
                     $('form.dropzone').each(function(){
                     var d = $(this).data('dropzone');
                     $.each(d.files,function(k,v){
                     d.removeFile(v);
                     });
                     });
                     */
                    obj.updateMessageFromJSON($form, r);
                    obj.publish('conversationSubmitForm', {form: $form, response: r});
                },
                error: function (r) {
                    obj.handlePostError($form);
                    return false;
                },
                complete: function (r) {
                    $btn.prop('disabled', false);
                    $form.parent().closest('.ccm-conversation-form-submitted').removeClass('ccm-conversation-form-submitted');
                }
            });
        },
        tool: {
            setCaretPosition: function (elem, caretPos) {
                // http://stackoverflow.com/a/512542/950669
                if (elem != null) {
                    if (elem.createTextRange) {
                        var range = elem.createTextRange();
                        range.move('character', caretPos);
                        range.select();
                    }
                    else {
                        if (elem.selectionStart) {
                            elem.focus();
                            elem.setSelectionRange(caretPos, caretPos);
                        }
                        else
                            elem.focus();
                    }
                }
            },
            getCaretPosition: function (elem) {
                // http://stackoverflow.com/a/263796/950669
                if (elem.selectionStart) {
                    return elem.selectionStart;
                } else if (document.selection) {
                    elem.focus();

                    var r = document.selection.createRange();
                    if (r == null) {
                        return 0;
                    }

                    var re = elem.createTextRange(),
                        rc = re.duplicate();
                    re.moveToBookmark(r.getBookmark());
                    rc.setEndPoint('EndToStart', re);

                    return rc.text.length;
                }
                return 0;
            },
            testMentionString: function (s) {
                return /^@[a-z0-9]+$/.test(s);
            },
            getMentionMatches: function (s, u) {
                return u.filter(function (d) {
                    return (d.indexOf(s) >= 0);
                });
            },
            isSameConversation: function (o, n) {
                return (o.options.blockID === n.options.blockID && o.options.cnvID === n.options.cnvID);
            },

            // MentionUser class, use this to pass around data with your @mention names.
            MentionUser: function (name) {
                this.getName = function () {
                    return name;
                };
            }
        }
    };
})(jQuery, window);

/* jshint unused:vars, undef:true, browser:true, jquery:true, -W041 */
/* global CCM_TOOLS_PATH */

(function ($, window) {

    var i18n = {
        Too_many_files: 'Too many files',
        Invalid_file_extension: 'Invalid file extension',
        Max_file_size_exceeded: 'Max file size exceeded',
        Error_deleting_attachment: 'Something went wrong while deleting this attachment, please refresh and try again.',
        Confirm_remove_attachment: 'Remove this attachment?'
        // Please add new translatable strings to the getConversationsJavascript of /concrete/controllers/frontend/assets_localization.php
    };

    var methods = {

        init: function (options) {
            var obj = options;
            obj.$element.on('click.cnv', 'a[data-toggle=conversation-reply]', function () {
                $('.ccm-conversation-wrapper').concreteConversationAttachments('clearDropzoneQueues');
            });
            obj.$element.on('click.cnv', 'a.attachment-delete', function (event) {
                event.preventDefault();
                $(this).concreteConversationAttachments('attachmentDeleteTrigger', obj);
            });
            if ((obj.$editMessageHolder) && (!(obj.$editMessageHolder.find('.ccm-attachment-dropzone').attr('data-dropzone-applied')))) {
                obj.$editMessageHolder.find('.ccm-attachment-dropzone').not('[data-drozpone-applied="true"]').dropzone({  // dropzone reply form
                    'url': CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/file/add',
                    'success': function (file, response) {
                        //this line for activate button submit after validate upload file
                        $('button[data-submit="update-conversation-message"]').prop('disabled', false);
                        var self = this;
                        $(file.previewTemplate).click(function () {
                            self.removeFile(file);
                            $('input[rel="' + $(this).attr('rel') + '"]').remove();
                        });
                        if (!response.error) {
                            //this line for complete the progress upload file to 100% after response from server
                            file.previewTemplate.querySelector(".dz-progress .dz-upload").style.width=100+"%";
                            $(this.element).closest('div.ccm-conversation-edit-message').find('form.aux-reply-form').append('<input rel="' + response.timestamp + '" type="hidden" name="attachments[]" value="' + response.id + '" />');
                        } else {
                            var $form = $('.preview.processing[rel="' + response.timestamp + '"]').closest('form');
                            obj.handlePostError($form, [response.error]);
                            $('.preview.processing[rel="' + response.timestamp + '"]').remove();
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                        }
                    },
                    accept: function (file, done) {
                        var errors = [];
                        var attachmentCount = this.files.length;
                        if ((obj.options.maxFiles > 0) && attachmentCount > obj.options.maxFiles) {
                            errors.push(i18n.Too_many_files);
                        }
                        var requiredExtensions = obj.options.fileExtensions.split(',');
                        if (file.name.split('.').pop().toLowerCase() && requiredExtensions.indexOf(file.name.split('.').pop().toLowerCase()) == -1 && requiredExtensions != '') {
                            errors.push(i18n.Invalid_file_extension);
                        }
                        if ((obj.options.maxFileSize > 0) && file.size > obj.options.maxFileSize * 1000000) {
                            errors.push(i18n.Max_file_size_exceeded);
                        }

                        if (errors.length > 0) {
                            var self = this;
                            $('input[rel="' + $(file.previewTemplate).attr('rel') + '"]').remove();
                            var $form = $(file.previewTemplate).parent('.dropzone');
                            self.removeFile(file);
                            obj.handlePostError($form, errors);
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                            attachmentCount = -1;
                            done('error');  // not displayed, just needs to have argument to trigger.
                        } else {
                            done();
                        }
                    },
                    'sending': function (file, xhr, formData) {
                        //this line for desable button submit until validate upload file
                        $('button[data-submit="update-conversation-message"]').prop('disabled', true);
                        $(file.previewTemplate).attr('rel', new Date().getTime());
                        formData.append("timestamp", $(file.previewTemplate).attr('rel'));
                        formData.append("tag", $(obj.$editMessageHOlder).parent('div').attr('rel'));
                        formData.append("fileCount", $(obj.$editMessageHolder).find('[name="attachments[]"]').length);
                    },
                    'init': function () {
                        $(this.element).data('dropzone', this);
                    },
                    'uploadprogress' : function (a, b) {
                        //limit the progress upload file to 80% until the response from server
                        a.previewTemplate.querySelector(".progress .upload").style.width=b+"%";
                        if (b>80){
                            a.previewTemplate.querySelector(".progress .upload").style.width=80+"%";
                        }
                    }
                });
            }
            if (obj.$newmessageformAttachments.dropzone && !($(obj.$newmessageformAttachments).attr('data-dropzone-applied'))) {  // dropzone new message form
                obj.$newmessageformAttachments.dropzone({
                    accept: function (file, done) {
                        var errors = [];
                        var attachmentCount = this.files.length;
                        if ((obj.options.maxFiles > 0) && attachmentCount > obj.options.maxFiles) {
                            errors.push(i18n.Too_many_files);
                        }
                        var requiredExtensions = obj.options.fileExtensions.split(',');
                        if (file.name.split('.').pop().toLowerCase() && requiredExtensions.indexOf(file.name.split('.').pop().toLowerCase()) == -1 && requiredExtensions != '') {
                            errors.push(i18n.Invalid_file_extension);
                        }
                        if ((obj.options.maxFileSize > 0) && file.size > obj.options.maxFileSize * 1000000) {
                            errors.push(i18n.Max_file_size_exceeded);
                        }

                        if (errors.length > 0) {
                            var self = this;
                            $('input[rel="' + $(file.previewTemplate).attr('rel') + '"]').remove();
                            var $form = $(file.previewTemplate).parent('.dropzone');
                            self.removeFile(file);
                            obj.handlePostError($form, errors);
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                            attachmentCount = -1;
                            done('error'); // not displayed, just needs to have argument to trigger.
                        } else {
                            done();
                        }
                    },
                    'url': CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/file/add',
                    'success': function (file, response) {
                        //this line for activate button submit after validate upload file
                        $('button[data-post-parent-id=0]').prop('disabled', false);
                        var self = this;
                        $(file.previewTemplate).click(function () {
                            $('input[rel="' + $(this).attr('rel') + '"]').remove();
                            self.removeFile(file);
                        });
                        if (!response.error) {
                            //this line for complete the progress upload file to 100% after response from server
                            file.previewTemplate.querySelector(".progress .upload").style.width=100+"%";
                            $('div[rel="' + response.tag + '"] form.main-reply-form').append('<input rel="' + response.timestamp + '" type="hidden" name="attachments[]" value="' + response.id + '" />');
                        } else {
                            var $form = $('.preview.processing[rel="' + response.timestamp + '"]').closest('form');
                            obj.handlePostError($form, [response.error]);
                            $('.preview.processing[rel="' + response.timestamp + '"]').remove();
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                        }
                    },
                    'sending': function (file, xhr, formData) {
                        //this line for desable button submit until validate upload file
                        $('button[data-post-parent-id=0]').prop('disabled', true);

                        $(file.previewTemplate).attr('rel', new Date().getTime());
                        formData.append("timestamp", $(file.previewTemplate).attr('rel'));
                        formData.append("tag", $(obj.$newmessageform).parent('div').attr('rel'));
                        formData.append("fileCount", this.files.length);
                    },
                    'init': function () {
                        $(this.element).data('dropzone', this);
                    },
                    'uploadprogress' : function (a, b) {
                        //limit the progress upload file to 80% until the response from server
                        a.previewTemplate.querySelector(".progress .upload").style.width=b+"%";
                        if (b>80){
                            a.previewTemplate.querySelector(".progress .upload").style.width=80+"%";
                        }
                    }
                });
                $(obj.$newmessageformAttachments).attr('data-dropzone-applied', 'true');
                if ($(obj.$newmessageformAttachments).find('div.default.message').length === 0) {
                    $(obj.$newmessageformAttachments).append($("<div class=\"default message\"><span>" + obj.options.attachmentDefaultMessage + "</span></div>"));
                }

            }

            if (!($(obj.$replyholder.find('.ccm-attachment-dropzone')).attr('data-dropzone-applied'))) {
                obj.$replyholder.find('.ccm-attachment-dropzone').not('[data-drozpone-applied="true"]').dropzone({  // dropzone reply form
                    'url': CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/file/add',
                    'success': function (file, response) {
                        var self = this;
                        $(file.previewTemplate).click(function () {
                            self.removeFile(file);
                            $('input[rel="' + $(this).attr('rel') + '"]').remove();
                        });
                        if (!response.error) {
                            //this line for complete the progress upload file to 100% after response from server
                            file.previewTemplate.querySelector(".progress .upload").style.width=100+"%";
                            $(this.element).closest('div.ccm-conversation-add-reply').find('form.aux-reply-form').append('<input rel="' + response.timestamp + '" type="hidden" name="attachments[]" value="' + response.id + '" />');
                        } else {
                            var $form = $('.preview.processing[rel="' + response.timestamp + '"]').closest('form');
                            obj.handlePostError($form, [response.error]);
                            $('.preview.processing[rel="' + response.timestamp + '"]').remove();
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                        }
                    },
                    accept: function (file, done) {
                        var errors = [];
                        var attachmentCount = this.files.length;
                        if ((obj.options.maxFiles > 0) && attachmentCount > obj.options.maxFiles) {
                            errors.push(i18n.Too_many_files);
                        }
                        var requiredExtensions = obj.options.fileExtensions.split(',');
                        if (file.name.split('.').pop().toLowerCase() && requiredExtensions.indexOf(file.name.split('.').pop().toLowerCase()) == -1 && requiredExtensions != '') {
                            errors.push(i18n.Invalid_file_extension);
                        }
                        if ((obj.options.maxFileSize > 0) && file.size > obj.options.maxFileSize * 1000000) {
                            errors.push(i18n.Max_file_size_exceeded);
                        }

                        if (errors.length > 0) {
                            var self = this;
                            $('input[rel="' + $(file.previewTemplate).attr('rel') + '"]').remove();
                            var $form = $(file.previewTemplate).parent('.dropzone');
                            self.removeFile(file);
                            obj.handlePostError($form, errors);
                            $form.children('.ccm-conversation-errors').delay(3000).fadeOut('slow', function () {
                                $(this).html('');
                            });
                            attachmentCount = -1;
                            done('error');  // not displayed, just needs to have argument to trigger.
                        } else {
                            done();
                        }
                    },
                    'sending': function (file, xhr, formData) {
                        $(file.previewTemplate).attr('rel', new Date().getTime());
                        formData.append("timestamp", $(file.previewTemplate).attr('rel'));
                        formData.append("tag", $(obj.$newmessageform).parent('div').attr('rel'));
                        formData.append("fileCount", $(obj.$replyHolder).find('[name="attachments[]"]').length);
                    },
                    'init': function () {
                        $(this.element).data('dropzone', this);
                    },
                    'uploadprogress' : function (a, b) {
                        //limit the progress upload file to 80% until the response from server
                        a.previewTemplate.querySelector(".progress .upload").style.width=b+"%";
                        if (b>80){
                            a.previewTemplate.querySelector(".progress .upload").style.width=80+"%";
                        }
                    }
                });
            }
            obj.$replyholder.find('.ccm-attachment-dropzone').attr('data-dropzone-applied', 'true');
            return $.each($(this), function (i, obj) {
                $(this).find('.ccm-conversation-attachment-container').each(function () {
                    if ($(this).is(':visible')) {
                        $(this).toggle();
                    }
                });
            });
        },

        attachmentDeleteTrigger: function (options) {
            var obj = options;
            var link = $(this);
            obj.$attachmentdeletetdialog = obj.$attachmentdeleteholder.clone();
            if (obj.$attachmentdeletetdialog.dialog) {
                obj.$attachmentdeletetdialog.dialog({
                    modal: true,
                    dialogClass: 'ccm-conversation-dialog',
                    title: obj.$attachmentdeletetdialog.attr('data-dialog-title'),
                    buttons: [
                        {
                            'text': obj.$attachmentdeleteholder.attr('data-cancel-button-title'),
                            'class': 'btn pull-left',
                            'click': function () {
                                obj.$attachmentdeletetdialog.dialog('close');
                            }
                        },
                        {
                            'text': obj.$attachmentdeleteholder.attr('data-confirm-button-title'),
                            'class': 'btn pull-right btn-danger',
                            'click': function () {
                                $(this).concreteConversationAttachments('deleteAttachment', {
                                    'cnvMessageAttachmentID': link.attr('rel'),
                                    'cnvObj': obj,
                                    'dialogObj': obj.$attachmentdeletetdialog
                                });
                            }
                        }
                    ]
                });
            } else {
                if (window.confirm(i18n.Confirm_remove_attachment)) {
                    $(this).concreteConversationAttachments('deleteAttachment', {
                        'cnvMessageAttachmentID': link.attr('rel'),
                        'cnvObj': obj,
                        'dialogObj': obj.$attachmentdeletetdialog
                    });
                }
            }
            return false;
        },

        clearDropzoneQueues: function () {
            $('.preview.processing').each(function () {  // first remove any previous attachments and hide dropzone if it was open.
                $('input[rel="' + $(this).attr('rel') + '"]').remove();
            });
            $('form.dropzone').each(function () {
                var d = $(this).data('dropzone');
                $.each(d.files, function (k, v) {
                    d.removeFile(v);
                });
            });
        },

        deleteAttachment: function (options) {
            var cnvMessageAttachmentID = options.cnvMessageAttachmentID;
            var obj = options.cnvObj;
            var attachmentsDialog = options.dialogObj;
            /* var obj = this;
             obj.publish('conversationBeforeDeleteAttachment',{cnvMessageAttachmentID:cnvMessageAttachmentID}); */
            var formArray = [{
                'name': 'cnvMessageAttachmentID',
                'value': cnvMessageAttachmentID
            }];

            $.ajax({
                type: 'post',
                data: formArray,
                url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/delete-file',
                success: function (response) {
                    var parsed = JSON.parse(response);
                    var $attachment = $('p[rel="' + parsed.attachmentID + '"]').closest('.attachment-container');
                    var $attachmentsContainer = $attachment.closest('.ccm-conversation-message-controls');
                    $attachment.fadeOut(300, function () {
                        $(this).remove();
                        if ($attachmentsContainer.find('.attachment-container').length == 0) {
                            $attachmentsContainer.fadeOut(300);
                        }
                    });
                    if (attachmentsDialog.dialog) {
                        attachmentsDialog.dialog('close');
                        obj.publish('conversationDeleteAttachment', {cnvMessageAttachmentID: cnvMessageAttachmentID});
                    }
                },
                error: function (e) {
                    obj.publish('conversationDeleteAttachmentError', {
                        cnvMessageAttachmentID: cnvMessageAttachmentID,
                        error: arguments
                    });
                    window.alert(i18n.Error_deleting_attachment);
                }
            });
        }
    };

    $.fn.concreteConversationAttachments = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on concreteConversationAttachments');
        }

    };

    $.fn.concreteConversationAttachments.localize = function (dictionary) {
        $.extend(true, i18n, dictionary);
    };


})(jQuery, window);

$(document).ready(function () {
    $('#ccm-conversation-scroll-to-editor').click(function () {
        var messageLabel = $('#ccm-conversation-message-label');
        $('html, body').animate({
            scrollTop: ( messageLabel.offset().top - messageLabel.height() )
        }, 1000);
    });

    $('a.ccm-conversation-message-report').click(function () {
        $(this).find('i.ccm-conversation-message-report-loading').removeClass('hidden');
        var msgID = $(this).data("conversation-message-id");
        var formArray = [{
            'name': 'cnvMessageID',
            'value': msgID
        }];
        $.ajax({
            type: 'post',
            data: formArray,
            url: CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/conversation/message/report',
            success: function (html) {
                $('i.ccm-conversation-message-report-loading').addClass('hidden');
                $('div[data-conversation-message-id=' + msgID + ']').append(html);
                $('#ccm-conversation-message-report-alert-id-' + msgID).fadeOut(9000, function () {
                    $(this).remove();
                });
            },
            error: function (r) {
                $('i.ccm-conversation-message-report-loading').addClass('hidden');
                alert(r.responseText)
            }
        });
    })
});
