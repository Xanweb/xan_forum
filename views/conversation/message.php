<?php
if ($message->isConversationMessageApproved()) {
    View::element('conversation/message', ['message' => $message, 'displayMode' => $displayMode, 'enablePosting' => $enablePosting, 'enableCommentRating' => $enableCommentRating], XanForum::pkgHandle());
} else {
    // it's a new message, but it's pending
    View::element('conversation/message/pending', ['message' => $message], XanForum::pkgHandle());
}
