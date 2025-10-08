<div class="modal-body clearfix">
    <div class="container-fluid">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Отправлено</th>
                <th>От кого</th>
                <th>Кому</th>
                <th>Прочитано</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo format_to_relative_time($comment->sent_at); ?></td>
                <td>
                    <?php
                    if ($comment->user_type === "staff") {
                        echo get_team_member_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    } else {
                        echo get_client_contact_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    }
                    ?>
                    [<?php echo $comment->created_by_email; ?>]
                </td>
                <td><?php echo $sent_to_user; ?></td>
                <td><?php echo format_to_relative_time($comment->read_at); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?>
    </button>
</div>

<script type="text/javascript">
    $(document).ready(function () {

    });
</script>
