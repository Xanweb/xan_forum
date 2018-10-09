<?php
defined('C5_EXECUTE') or die('Access Denied.');

/* @var $forum XanForum\Model\Forum */
/* @var $topic XanForum\Model\Topic */

$config = Core::make('site')->getSite()->getConfigRepository();
?>

<div class="xan-forum-topic ">
    <?php if ($error->has()): ?>
        <div class="alert alert-danger">
            <?php
            if (1 == count($error->getList())) {
                echo $error->offsetGet(0);
            } else {
                $error->output();
            }
            ?>
        </div>
    <?php endif; ?>
    <?php

    if ($message) {
        echo '<div class="xan-forum-topic-message">';
        echo $message;
        echo '</div>';
    }
    ?>
    <?php if ($canCreateTopics): ?>
        <a href="<?= $view->action('new_topic'); ?>" class="newTopic">
            <i class="fa fa-edit"></i> <?= t('Create a New Topic'); ?>
        </a>
    <?php endif; ?>
    <div class="forum">
        <div class="title"><?= $forum->getForumName(); ?></div>
        <div class="table-responsive">
            <table class="topics table">
                <thead>
                    <tr>
                        <th width="80%"><?= t('Topic'); ?></th>
                        <th width="20%"><?= t('Last Message'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (is_array($topics) && !empty($topics)): ?>
                    <?php foreach ($topics as $topic): ?>
                        <tr>
                            <td>
                                <a href="<?= $topic->getTopicURL(); ?>"><?= $topic->getTopicName(); ?></a>
                                <small> <?= t('Posted by'); ?>
                                    <?php if ($config->get('user.profiles_enabled') && User::isLoggedIn()) {
        ?>
                                        <a href="<?= $topic->getAuthorProfileURL(); ?>">
                                            <?= $topic->getAuthorName(); ?>
                                        </a>
                                    <?php
    } else {
        ?>
                                        <span><?= $topic->getAuthorName(); ?></span>
                                    <?php
    } ?> <br>

                                </small>
                            </td>
                            <td>
                                <?php if (is_object($lastMessage = $topic->getLastMessage())): ?>
                                    <small> <?= t('Last Message'); ?>

                                            <span><?= $lastMessage->getAuthorName(); ?></span>
                                        <br>
                                        <?= $lastMessage->getMessageDate(XanForum::cfg('datetime_format')); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">
                            <div class="text-left no-results"> <?= t('No topics'); ?></div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
                <?php if ($canCreateTopics): ?>
                <tfoot>
                    <tr>
                        <th colspan="2">
                                <a href="<?= $view->action('new_topic'); ?>" class="newTopic no-margin-bottom">
                                    <i class=" fa fa-edit"></i> <?= t('Create a New Topic'); ?>
                                </a>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php if ($paging): ?>
        <div id="pagination"><?= $paging; ?></div>
    <?php endif; ?>

</div>
