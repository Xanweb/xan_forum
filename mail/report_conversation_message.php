<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = t('Report Message on Conversation: %s', $title);
$body = t("
link to author %s  ,
content of report message in conversation \"%s\":

%s

You can view this report conversation message at

%s

", $poster, $title, $body, $link);
