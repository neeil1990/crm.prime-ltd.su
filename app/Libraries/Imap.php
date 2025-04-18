<?php

namespace App\Libraries;

use App\Controllers\App_Controller;

class Imap {

    private $ci;

    public function __construct() {
        $this->ci = new App_Controller();

        //load EmailReplyParser resources
        require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php");

        //load ddeboer-imap resources
        $current_php_version = PHP_VERSION;
        if ($current_php_version >= "8.1") {
            require_once(APPPATH . "ThirdParty/Imap/ddeboer-imap-php8/vendor/autoload.php");
        } else {
            require_once(APPPATH . "ThirdParty/Imap/ddeboer-imap/vendor/autoload.php");
        }

        //load mail-mime-parser resources
        require_once(APPPATH . "ThirdParty/Imap/mail-mime-parser/vendor/autoload.php");
    }

    function authorize_imap_and_get_inbox($is_cron = false) {
        $host = get_setting("imap_host");
        $port = get_setting("imap_port");
        $encryption = get_setting('imap_encryption');
        $email_address = get_setting("imap_email");
        $password = decode_password(get_setting('imap_password'), "imap_password");

        $server = new \Ddeboer\Imap\Server($host, $port, $encryption);

        //reset failed login attempts count after running from settings page
        if (!$is_cron) {
            $this->ci->Settings_model->save_setting("imap_failed_login_attempts_count", 0);
        }

        //try to login 10 times and save the count on each load of cron job
        //after a success login, reset the count to 0
        try {
            $connection = $server->authenticate($email_address, $password);

            //the credentials is valid. store to settings that it's authorized
            $this->ci->Settings_model->save_setting("imap_authorized", 1);

            //reset failed login attempts count
            $this->ci->Settings_model->save_setting("imap_failed_login_attempts_count", 0);

            return $connection;
        } catch (\Exception $exc) {
            //the credentials is invalid, increase attempt count and store
            $attempts_count = get_setting("imap_failed_login_attempts_count");
            if ($is_cron) {
                $attempts_count = $attempts_count ? ($attempts_count * 1 + 1) : 1;
                $this->ci->Settings_model->save_setting("imap_failed_login_attempts_count", $attempts_count);
            }

            //log error for every exception
            log_message('error', $exc);

            if ($attempts_count === 10 || !$is_cron) {
                //flag it's unauthorized, only after 10 failed attempts
                $this->ci->Settings_model->save_setting("imap_authorized", 0);
            }

            return false;
        }
    }

    public function run_imap() {
        $connection = $this->authorize_imap_and_get_inbox(true);
        if (!$connection) {
            return false;
        }


        $mailbox_name = "";

        if ($connection->hasMailbox("INBOX")) {
            $mailbox_name = "INBOX";
        } else if ($connection->hasMailbox("Inbox")) {
            $mailbox_name = "Inbox";
        } else if ($connection->hasMailbox("inbox")) {
            $mailbox_name = "inbox";
        }

        if (!$mailbox_name) {
            log_message('error', 'IMAP integration will not work since there is no mailbox named INBOX');
            return false;
        }

        $mailbox = $connection->getMailbox($mailbox_name); //get mails of inbox only

        $messages = $mailbox->getMessages();

        $email_address = get_setting("imap_email");
        $last_seen_settings_name = "last_seen_imap_message_number_" . $email_address;
        $saved_last_message = get_setting($last_seen_settings_name);
        $saved_last_message = $saved_last_message ? $saved_last_message : 0;

        $last_number = 0;
        foreach ($messages as $key => $message) {

            $last_number = $messages[$key];

            //Skip already seen messages Nothing to do there.
            if ($saved_last_message <= $last_number) {
                //create tickets for unread mails
                if (!$message->isSeen()) {

                    $this->_create_ticket_from_imap($message);

                    //mark the mail as read
                    $message->markAsSeen();
                }
            }
        }

        $this->ci->Settings_model->save_setting($last_seen_settings_name, $last_number);
    }

    private function _create_ticket_from_imap($message_info = "") {
        if ($message_info) {
            $email = $message_info->getFrom()->getAddress();
            $subject = $message_info->getSubject();

            //check if there has any client containing this email address
            //if so, go through with the client id
            $client_info = $this->ci->Users_model->get_one_where(array("email" => $email, "user_type" => "client", "deleted" => 0));

            if (get_setting("create_tickets_only_by_registered_emails") && !$client_info->id) {
                return false;
            }

            $ticket_id = $this->_get_ticket_id_from_subject($subject);

            //check if the ticket is exists on the app
            //if not, that will be considered as a new ticket
            //but for this case, it's a replying email. we've to parse the message
            $replying_email = false;
            if ($ticket_id) {
                $existing_ticket_info = $this->ci->Tickets_model->get_one_where(array("id" => $ticket_id, "deleted" => 0));
                if (!$existing_ticket_info->id) {
                    $ticket_id = "";
                    $replying_email = true;
                }
            }

            if ($ticket_id) {
                //if the message have ticket id, we have to assume that, it's a reply of the specific ticket
                $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $message_info, $client_info, true);

                if ($ticket_id && $ticket_comment_id) {
                    log_notification("ticket_commented", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
                }
            } else {

                $creator_name = $message_info->getFrom()->getName();
                $now = get_current_utc_time();
                $ticket_data = array(
                    "title" => $subject ? $subject : $email, //show creator's email as ticket's title, if there is no subject
                    "created_at" => $now,
                    "creator_name" => $creator_name ? $creator_name : "",
                    "creator_email" => $email ? $email : "",
                    "client_id" => $client_info->id ? $client_info->client_id : 0,
                    "created_by" => $client_info->id ? $client_info->id : 0,
                    "last_activity_at" => $now
                );

                $ticket_id = $this->ci->Tickets_model->ci_save($ticket_data);

                if ($ticket_id) {
                    //save email message as the ticket's comment
                    $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $message_info, $client_info, $replying_email);

                    if ($ticket_id && $ticket_comment_id) {
                        log_notification("ticket_created", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
                    }
                }
            }
        }
    }

    private function _prepare_replying_message($message = "") {
        try {
            $reply_parser = new \EmailReplyParser\EmailReplyParser();
            return $reply_parser->parseReply($message);
        } catch (\Exception $ex) {
            return "";
        }
    }

    //save tickets comment
    private function _save_tickets_comment($ticket_id, $message_info, $client_info, $is_reply = false) {
        if ($ticket_id) {
            $description = $message_info->getBodyHtml();

            if (!$description) {
                $description = $message_info->getBodyText();
                $description = str_replace("\n", "<br>", $description);
            }

            // if ($is_reply) {
                // $description = $this->_prepare_replying_message($description);
            // }

            if (!$description) {
                //parse email content if the predefined method returns empty
                $encoding_type = $message_info->getEncoding();
                $raw_content = $message_info->getRawMessage();

                //parse with another library
                try {
                    $mail_mime_parser = \ZBateson\MailMimeParser\Message::from($raw_content, false);
                    $description = $mail_mime_parser->getHtmlContent();

                    //get content inside body tag only if it exists
                    if ($description) {
                        preg_match("/<body[^>]*>(.*?)<\/body>/is", $description, $body_matches);
                        $description = isset($body_matches[1]) ? $body_matches[1] : $description;
                    }
                } catch (\Exception $ex) {

                }

                if (!$description) {
                    //get content after X-Yandex-Forward: random strings (32) + new lines
                    $description = substr($raw_content, strpos($raw_content, "X-Yandex-Forward") + 52);

                    //parse for different encoding types
                    if ($encoding_type == "7bit") {
                        $description = quoted_printable_decode($description);
                    } else if ($encoding_type == "base64") {
                        $description = imap_base64($description);
                    } else if ($encoding_type == "quoted-printable") {
                        $description = imap_qprint($description);
                    }
                }
            }

            $description = preg_replace('/<(style|script)\b[^>]*>(.*?)<\/\1>/is', '', $description);
            $description = preg_replace('/[\x{10000}-\x{10FFFF}]/u', ' :)', $description);
            $description = preg_replace('/<base\b[^>]*\/?>/i', '', $description);

            $comment_data = array(
                "description" => $description,
                "ticket_id" => $ticket_id,
                "created_by" => $client_info->id ? $client_info->id : 0,
                "created_at" => get_current_utc_time()
            );

            // $comment_data = clean_data($comment_data);

            $files_data = $this->_prepare_attachment_data_of_mail($message_info);

            foreach ($files_data as $cid => $file) {
                if (preg_match("/cid:$cid/", $comment_data["description"])) {
                    $comment_data["description"] = str_replace("cid:$cid", "/" . get_setting("timeline_file_path") . $file["file_name"], $comment_data["description"]);
                    unset($files_data[$cid]);
                }
            }

            $comment_data["files"] = serialize($files_data);

            //add client_replied status when it's a reply
            if ($is_reply) {
                $ticket_data = array(
                    "status" => "client_replied",
                    "last_activity_at" => get_current_utc_time()
                );

                $this->ci->Tickets_model->ci_save($ticket_data, $ticket_id);
            }

            $ticket_comment_id = $this->ci->Ticket_comments_model->ci_save($comment_data);

            if (!$is_reply) {
                add_auto_reply_to_ticket($ticket_id);
            }

            return $ticket_comment_id;
        }
    }

    //get ticket id
    private function _get_ticket_id_from_subject($subject = "") {
        if ($subject) {
            $find_hash = strpos($subject, "#");
            if ($find_hash) {
                $rest_from_hash = substr($subject, $find_hash + 1); //get the rest text from ticket's #
                $ticket_id = (int) substr($rest_from_hash, 0, strpos($rest_from_hash, " "));

                if ($ticket_id && is_int($ticket_id)) {
                    return $ticket_id;
                }
            }
        }
    }

    //download attached files to local
    private function _prepare_attachment_data_of_mail($message_info = "") {
        if ($message_info) {
            $files_data = array();
            $attachments = $message_info->getAttachments();

            foreach ($attachments as $idx => $attachment) {

                $structure = $attachment->getStructure();
                $cid = (isset($structure->id)) ? trim($structure->id, '<>') : $idx;

                //move files to the directory
                $file_data = move_temp_file(
                    $attachment->getFilename(),
                    get_setting("timeline_file_path"),
                    "imap_ticket",
                    NULL,
                    "",
                    $attachment->getDecodedContent()
                );

                $files_data[$cid] = $file_data;
            }

            return $files_data;
        }
    }
}
