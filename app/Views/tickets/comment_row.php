<?
$pin_status = "";
$unpin_status = "";

if ($comment->pinned_comment_status) {
    $pin_status = "hide";
    $unpin_status = "";
} else {
    $pin_status = "";
    $unpin_status = "hide";
}
?>

<div id="ticket-comment-container-<?php echo $comment->id; ?>" class="card p15 text-break comment-container ticket-comment-container <?php echo $comment->is_note ? "note-background" : "" ?> comment-highlight-section">
    <div class="d-flex">
        <div class="flex-shrink-0 mr10">
            <span class="avatar avatar-sm">
                <?php if (!$comment->created_by || $comment->created_by == 999999999) { ?>
                    <img src="<?php echo get_avatar("system_bot"); ?>" alt="..." />
                <?php } else { ?>
                    <img src="<?php echo get_avatar($comment->created_by_avatar); ?>" alt="..." />
                    <?php
                }
                ?>
            </span>
        </div>
        <div class="w-100">
            <div>
                <?php if (is_undefined_client_from_email($comment) && $ticket_info->client_id === "0"): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Является неопределенным клиентом, полученным из электронной почты.</strong> Добавьте клиента для отслеживания уведомлений при отправке сообщений.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php
                if ($comment->created_by == 999999999) {
                    //user is an app boot for auto reply tickets
                    echo "<span class='dark strong'>" . get_setting('app_title') . "</span>";
                } else if (is_undefined_client_from_email($comment)) {
                    //user is an undefined client from email
                    echo "<span class='dark strong'>" . $comment->creator_name . " [" . app_lang("unknown_client") . "]" . "</span>";
                } else {
                    if ($comment->user_type === "staff") {
                        echo get_team_member_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    } else {
                        echo get_client_contact_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    }
                }
                ?>
                <small class="mr10"><span class="text-off"><?php echo format_to_relative_time($comment->created_at); ?></span></small>

                <?php
                if (ticket_comment_is_not_note($comment) && $comment->sent_mails) {
                    $badge_color = "bg-danger";

                    if ($comment->read_mails > 0) {
                        $badge_color = "bg-info";
                    }

                    echo modal_anchor(get_uri("tickets/mail_ticket_modal_form"),
                            '<span class="badge '.$badge_color.'">
                                        <i data-feather="mail" class="icon-14"></i> '.$comment->sent_mails.'  
                                        <i data-feather="eye" class="icon-14"></i> '.$comment->read_mails.'
                                    </span>',
                            array("title" => "Отчет об отправке уведомлений",
                                    "class" => "ticket-email",
                                    "data-post-ticket_comment_id" => $comment->id));
                }
                ?>

                <?php if ($login_user->user_type == "staff") { ?>
                    <span class="float-end dropdown comment-dropdown">
                        <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                            <i data-feather="chevron-down" class="icon-16 clickable"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end" role="menu">
                            <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/pin_comment/" . $comment->id), "<i data-feather='map-pin' class='icon-16'></i> " . app_lang('unpin_comment'), array("id" => "unpin-comment-button-$comment->id", "class" => "dropdown-item unpin-comment-button $unpin_status", 'title' => app_lang('unpin_comment'), "data-pin-comment-id" => $comment->id, "data-fade-out-on-success" => "#pinned-comment-$comment->id")); ?> </li>
                            <li role="presentation"><?php echo js_anchor("<i data-feather='map-pin' class='icon-16'></i> " . app_lang('pin_comment'), array("id" => "pin-comment-button-$comment->id", "class" => "dropdown-item pin-comment-button $pin_status", 'title' => app_lang('pin_comment'), "data-action-url" => get_uri("tickets/pin_comment/" . $comment->id), "data-pin-comment-id" => $comment->id)); ?> </li>
                           <?php if (ticket_comment_is_not_note($comment)) { ?>
                            <li>
                                <?php echo ajax_anchor(
                                        get_uri("tickets/notify_ticket_comment"),
                                        "<i data-feather='send' class='icon-16'></i> " . app_lang('send_notification'),
                                        array("class" => "dropdown-item", "title" => app_lang('send_notification'),
                                                "data-post-ticket_id" => "$comment->ticket_id",
                                                "data-post-ticket_comment_id" => "$comment->id")
                                ); ?>
                            </li>
                            <?php } ?>
                            <li role="presentation">
                                <?php echo ajax_anchor(get_uri("tickets/delete_comment/$comment->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array("class" => "dropdown-item", "title" => app_lang('delete'), "data-fade-out-on-success" => "#ticket-comment-container-$comment->id")); ?>
                            </li>
                        </ul>
                    </span>
                <?php } ?>

                <?php if (!$comment->created_by && $comment->creator_email) { ?>
                    <div class="block text-off"><?php echo $comment->creator_email; ?></div>
                <?php } ?>
            </div>

            <p><?php echo $comment->description; ?></p>

            <div class="comment-image-box clearfix d-flex align-items-center">
                <?php
                $files = unserialize($comment->files);
                $total_files = count($files);
                echo view("includes/timeline_preview", array("files" => $files));

                if ($total_files) {
                    $icon = "<i data-feather='paperclip' class='icon-16'></i>";
                    $download_caption = $icon ." ". app_lang('download');
                    if ($total_files > 1) {
                        $download_caption = sprintf($icon ." ". app_lang('download_files'), $total_files);
                    }

                    echo anchor(get_uri("tickets/download_comment_files/" . $comment->id), $download_caption, array("class" => "ms-2", "title" => $download_caption));
                }
                ?>
            </div>
        </div>
    </div>
</div>
