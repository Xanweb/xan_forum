<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $site . " " . t("Remove Account - Validate Email Address");
$body = t("

You must click the following URL in order to remove definitively your account for %s:

%s 

Thanks for your interest in %s

", $site, URL::to('/account/remove_account/email_remove_account_validation', $uHash), $site);
