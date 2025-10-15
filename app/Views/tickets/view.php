<?php $user_id = $login_user->id; ?>

<?php if ($view_type != "modal_view") { ?>
    <div class="page-content ticket-details-view clearfix">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="ticket-titlep-section">
                        <div class="page-title no-bg clearfix mb5 no-border">
                            <h1 class="pl0">
                                <span><i data-feather="life-buoy" class='icon'></i></span>
                                <?php echo get_ticket_id($ticket_info->id) . " - " . $ticket_info->title ?>
                            </h1>

                            <div class="title-button-group mr0">
                                <span class="dropdown inline-block">
                                    <button class="btn btn-info text-white dropdown-toggle caret" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <?php if ($login_user->user_type == "staff") { ?>
                                            <li role="presentation"><?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('ticket'), "data-post-view" => "details", "data-post-id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                            <?php if ($can_create_tasks && !$ticket_info->task_id) { ?>
                                                <li role="presentation"><?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task_in_project'), array("title" => app_lang('create_new_task'), "data-post-project_id" => $ticket_info->project_id, "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                            <?php } ?>
                                            <li role="presentation"><?php echo modal_anchor(get_uri("tickets/merge_ticket_modal_form"), "<i data-feather='git-merge' class='icon-16'></i> " . app_lang('merge'), array("title" => app_lang('merge'), "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                        <?php } ?>

                                        <?php if ($ticket_info->status === "closed") { ?>
                                            <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/open"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array("class" => "dropdown-item", "title" => app_lang('mark_as_open'), "data-reload-on-success" => "1")); ?> </li>
                                        <?php } else { ?>
                                            <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/closed"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array("class" => "dropdown-item", "title" => app_lang('mark_as_closed'), "data-reload-on-success" => "1")); ?> </li>
                                        <?php } ?>
                                        <?php if ($ticket_info->assigned_to === "0" && $login_user->user_type == "staff") { ?>
                                            <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/assign_to_me/$ticket_info->id"), "<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array("class" => "dropdown-item", "title" => app_lang('assign_myself_in_this_ticket'), "data-reload-on-success" => "1")); ?></li>
                                        <?php } ?>
                                        <?php if ($ticket_info->client_id === "0" && $login_user->user_type == "staff") { ?>
                                            <?php if ($can_create_client) { ?>
                                                <li role="presentation"><?php echo modal_anchor(get_uri("clients/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('link_to_new_client'), array("title" => app_lang('link_to_new_client'), "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                            <?php } ?>
                                            <li role="presentation"><?php echo modal_anchor(get_uri("tickets/add_client_modal_form/$ticket_info->id"), "<i data-feather='link' class='icon-16'></i> " . app_lang('link_to_existing_client'), array("title" => app_lang('link_to_existing_client'), "class" => "dropdown-item")); ?></li>
                                        <?php } ?>
                                    </ul>
                                </span>
                            </div>
                        </div>
                        <?php if ($login_user->user_type === "staff") { ?>
                            <ul id="ticket-tabs" data-bs-toggle="ajax-tab" class="nav nav-pills rounded classic mb20 scrollable-tabs border-white" role="tablist">
                                <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#ticket-details-section"><?php echo app_lang("details"); ?></a></li>
                                <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("tickets/tasks/" . $ticket_info->id); ?>" data-bs-target="#ticket-tasks-section"><?php echo app_lang('tasks'); ?></a></li>
                            </ul>
                        <?php } ?>
                    </div>

                    <?php if ($login_user->user_type === "staff") { ?>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade"id="ticket-details-section">
                                <?php echo view("tickets/details"); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade grid-button" id="ticket-tasks-section"></div>
                        </div>
                    <?php } else { ?>
                        <?php echo view("tickets/details"); ?>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="modal-body clearfix general-form">
        <div class="container-fluid">
            <div class="clearfix">
                <div class="row">
                    <div class="col-md-12">
                        <ul id="ticket-tabs" data-bs-toggle="ajax-tab" class="nav nav-pills rounded classic mb20 scrollable-tabs border-white" role="tablist">
                            <li><a role="presentation" data-bs-toggle="tab" class="active" href="javascript:;" data-bs-target="#ticket-details-section"><?php echo app_lang("details"); ?></a></li>
                            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("tickets/tasks/" . $ticket_info->id); ?>" data-bs-target="#ticket-tasks-section"><?php echo app_lang('tasks'); ?></a></li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active show" id="ticket-details-section">
                            <?php echo view("tickets/details"); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane fade grid-button" id="ticket-tasks-section"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <?php if ($ticket_info->assigned_to === "0" && $login_user->user_type == "staff") { ?>
            <?php echo ajax_anchor(get_uri("tickets/assign_to_me/$ticket_info->id"), "<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array("class" => "btn btn-info text-white", "title" => app_lang('assign_myself_in_this_ticket'), "data-reload-on-success" => "1")); ?>
        <?php } ?>
        <?php if ($ticket_info->status === "closed") { ?>
            <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/open"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array("class" => "btn btn-danger", "title" => app_lang('mark_as_open'), "data-reload-on-success" => "1")); ?>
        <?php } else { ?>
            <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/closed"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array("class" => "btn btn-success", "title" => app_lang('mark_as_closed'), "data-reload-on-success" => "1")); ?>
        <?php } ?>
        <?php if ($login_user->user_type == "staff") { ?>
            <?php if ($can_create_tasks && !$ticket_info->task_id) { ?>
                <?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('create_new_task'), array("title" => app_lang('create_new_task'), "data-post-project_id" => $ticket_info->project_id, "data-post-ticket_id" => $ticket_info->id, "class" => "btn btn-default")); ?>
            <?php } ?>
            <?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('ticket'), "data-post-view" => "details", "data-post-id" => $ticket_info->id, "class" => "btn btn-default")); ?>
        <?php } ?>

        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {

        var decending = "<?php echo $sort_as_decending; ?>";

        $("#comment-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                $("#description").val("");

                if (decending) {
                    $(result.data).insertAfter("#comment-form-container");
                } else {
                    $(result.data).insertBefore("#comment-form-container");
                }

                appAlert.success(result.message, {duration: 10000});

                if (window.formDropzone) {
                    window.formDropzone['ticket-comment-dropzone'].removeAllFiles();
                }

            }
        });

        if ("<?php echo!get_setting('user_' . $user_id . '_signature') == '' ?>") {
            $("#description").text("\n" + $("#description").text());
            $("#description").focus();
        }

        window.refreshAfterAddTask = true;

        var $inputField = $("#description"), $lastFocused;

        function saveCursorPositionOfRichEditor() {
            $inputField.summernote('saveRange');
            $lastFocused = "rich-editor";
        }

        //store the cursor position
        if (AppHelper.settings.enableRichTextEditor === "1") {
            $inputField.on("summernote.change", function (e) {
                saveCursorPositionOfRichEditor();
            });

            //it'll grab only cursor clicks
            $("body").on("click", ".note-editable", function () {
                saveCursorPositionOfRichEditor();
            });
        } else {
            $inputField.focus(function () {
                $lastFocused = document.activeElement;
            });
        }

        function insertTemplate(text) {
            if ($lastFocused === undefined) {
                return;
            }

            if (AppHelper.settings.enableRichTextEditor === "1") {
                $inputField.summernote('restoreRange');
                $inputField.summernote('focus');
                $inputField.summernote('pasteHTML', text);
            } else {
                var scrollPos = $lastFocused.scrollTop;
                var pos = 0;
                var browser = (($lastFocused.selectionStart || $lastFocused.selectionStart === "0") ? "ff" : (document.selection ? "ie" : false));

                if (browser === "ff") {
                    pos = $lastFocused.selectionStart;
                }

                var front = ($lastFocused.value).substring(0, pos);
                var back = ($lastFocused.value).substring(pos, $lastFocused.value.length);
                $lastFocused.value = front + text + back;
                pos = pos + text.length;

                $lastFocused.scrollTop = scrollPos;
            }

            //close the modal
            $("#close-template-modal-btn").trigger("click");
        }

        //init uninitialized rich editor to insert template
        $("#insert-template-btn").click(function () {
            setSummernote($("#description"));
        });

        //insert ticket template
        $("body").on("click", "#ticket-template-table tr", function () {
            var template = $(this).find(".js-description").html();
            if (AppHelper.settings.enableRichTextEditor !== "1") {
                //insert only text when rich editor isn't enabled
                var template = $(this).find(".js-description").text();
            }

            if ($lastFocused === undefined) {
                if (AppHelper.settings.enableRichTextEditor === "1") {
                    $("#description").summernote("code", template);
                } else {
                    $("#description").text(template);
                }

                //close the modal
                $("#close-template-modal-btn").trigger("click");
            } else {
                insertTemplate(template);
            }

        });

        //set value 1, when click save as button
        $("#save-as-note-button").click(function () {
            $("#is-note").val('1');
            $(this).trigger("submit");
        });

        //set value 0, when click post comment button
        $("#save-ticket-comment-button").click(function () {
            $("#is-note").val('0');
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        updatePinCommentAbove();

        $(".pin-comment-button").click(function() {
            var comment_id = $(this).attr('data-pin-comment-id');
            appLoader.show();
            $.ajax({
                url: "<?php echo get_uri("tickets/pin_comment/"); ?>" + comment_id,
                type: 'POST',
                dataType: "json",
                success: function(result) {
                    if (result.success) {
                        $("#pinned-comment").append(result.data);
                        appLoader.hide();
                    } else {
                        appAlert.error(result.message);
                    }

                    if (result.status) {
                        $("#pin-comment-button-" + comment_id).addClass("hide");
                        $("#unpin-comment-button-" + comment_id).removeClass("hide");
                        $("#pinned-comment").removeClass("hide");
                    }

                    updatePinCommentAbove();
                }
            });
        });

        $(".unpin-comment-button").click(function() {
            var comment_id = $(this).attr('data-pin-comment-id');
            $("#pin-comment-button-" + comment_id).removeClass("hide");
            $("#unpin-comment-button-" + comment_id).addClass("hide");
            window.setTimeout(updatePinCommentAbove, 1000);
        });

        $(".pinned-comment-highlight-link").click(function(e) {
            var comment_id = $(this).attr('data-original-comment-link-id');
            $(".comment-highlight-section").removeClass("comment-highlight");
            $("#ticket-comment-container-" + comment_id).addClass("comment-highlight");
            window.location.hash = $(this).attr('data-original-comment-id');
            e.preventDefault();
        });

        var commentHash = window.location.hash;
        if (commentHash.indexOf('#ticket-comment-container') > -1) {
            var splitCommentId = commentHash.split("-");
            var commentId = splitCommentId[3];
            highlightSpecificComment(commentId);
        }

        function highlightSpecificComment(commentId) {
            $(".comment-highlight-section").removeClass("comment-highlight");
            $("#ticket-comment-container-" + commentId).addClass("comment-highlight");
            window.location.hash = ""; //remove first to scroll with main link
            window.location.hash = "ticket-comment-container-" + commentId;
        }

        function updatePinCommentAbove() {

            let pinCommentPreview = $(".pin-comment-preview");

            if (!pinCommentPreview.length) {
                return;
            }

            let container = $("#comment-pin-container").html("");

            container.append('<div class="box-title"><span>Закрепленные комментарии</span></div>');

            pinCommentPreview.each(function(i, el) {
                let self = $(el);
                let id = self.attr('id');

                if (!self.children().length) {
                    return true;
                }

                let origin = $('#ticket-comment-container-' + id);

                let comment = $("<div />", { class: origin.attr('class') })
                    .html(origin.html());

                comment.find(".dropdown")
                    .removeClass(['dropdown', 'comment-dropdown'])
                    .html($("<a />", { href: '#ticket-comment-container-' + id }).html(self.find(".pin").html() + self.find(".float-start").html()));

                container.append(comment);
            });
        }

        $("body").on('click', '[data-act=update-ticket-info]', function (e) {
            let $instance = $(this),
                type = $(this).attr('data-act-type'),
                source = "",
                select2Option = {},
                showbuttons = false,
                placement = "bottom",
                editableType = "",
                datepicker = {};

            if (type === "deadline") {
                editableType = "date";
            }

            $(this).appModifier({
                actionType: editableType,
                value: $(this).attr('data-value'),
                actionUrl: '<?php echo_uri("tickets/update_ticket_info") ?>/' + $(this).attr('data-id') + '/' + $(this).attr('data-act-type'),
                showbuttons: showbuttons,
                datepicker: datepicker,
                select2Option: select2Option,
                placement: placement,
                onSuccess: function (response, newValue) {
                    if (response.success) {

                        if (type === "deadline" && response.date) {
                            setTimeout(function () {
                                $instance.html(response.date);
                            }, 50);
                        }
                    }
                }
            });

            return false;
        });

        check_ticket_email();
        setInterval(check_ticket_email, 5000);

        function check_ticket_email() {
            $(".ticket-email .bg-danger").each(function (i, el) {
                let $badge = $(el);
                let $link_badge_wrapper = $badge.closest(".ticket-email");
                let comment_id = $link_badge_wrapper.attr('data-post-ticket_comment_id');

                get_ticket_comment_mails(comment_id)
            });
        }

        function get_ticket_comment_mails(comment_id) {
            $.ajax({
                url: "<?php echo get_uri("tickets/get_ticket_comment_mails/"); ?>" + comment_id,
                type: 'POST',
                dataType: "json",
                success: function(response) {
                    let $comment = $(`a[data-post-ticket_comment_id="${response.ticket_comment_id}"]`);

                    $comment.find(".number-sent-emails").text(response.number_sent_emails);

                    //check if any primary contact email read the email
                    //if yes, then mark the badge as read
                    $.each(response.sent_emails, function (i, el) {
                        if (el.to_user.is_primary_contact > 0 && el.read_at) {
                            mark_email_badge_as_read($comment.find(".badge"))
                            return false;
                        }
                    })
                }
            });
        }

        function mark_email_badge_as_read($badge) {
            $badge.removeClass("bg-danger");
            $badge.addClass("bg-info");
        }
    });
</script>
