<?php
if (is_object($conversation)) {
    View::element('conversation/count_header', ['conversation' => $conversation], XanForum::pkgHandle());
}
