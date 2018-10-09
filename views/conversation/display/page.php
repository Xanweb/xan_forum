<?php
defined('C5_EXECUTE') or die("Access Denied.");

foreach ($messages as $m) {
    View::element('conversation/message', $args + ['message' => $m], XanForum::pkgHandle());
}
