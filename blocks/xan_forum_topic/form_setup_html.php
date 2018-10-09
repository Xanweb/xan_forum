<?php

defined('C5_EXECUTE') or die('Access Denied.');

$pageSelector = Core::make('helper/form/page_selector');
$formHelper = Core::make('helper/form');

echo '<h2>' . t('Topic Page') . '</h2>';
echo t('Select the page beneath which the topics are created.') . '<br />';
echo $pageSelector->selectPage('parentPageID', $parentPageID, 'ccm_selectSitemapNode');

echo '<hr></hr>';

echo '<h2>' . t('Collection Type') . '</h2>';
echo t('Specify the collection type you\'d like to use for new topics.') . '<br />';

echo '<div id="xan-forum-topic-form-ctID">';
echo $formHelper->select('ctID', $view->controller->getCollectionTypeIDs(), $ctID);
echo '</div>';

echo '<hr></hr>';

echo '<h2>' . t('Page Template') . '</h2>';
echo t('Specify the page tempate you\'d like to use for new topics.') . '<br />';

echo '<div id="xan-forum-topic-form-ptID">';
echo $formHelper->select('ptID', false, $ptID);
echo '</div>';

echo '<hr></hr>';

echo '<h2>' . t('Area') . '</h2>';
echo t('Select the area where the topics should be added.') . '<br />';

echo '<div id="xan-forum-topic-form-area">';
echo $formHelper->select('area', $view->controller->getAreas(), $area);
echo '</div>';

echo '<hr></hr>';

echo '<h2>' . t('Sort Order') . '</h2>';
echo t('Specify the sorder order you\'d like to use for the list of topics.') . '<br />';

echo '<div id="xan-forum-topic-form-sort">';
echo $formHelper->select('sortOrder', $view->controller->getSortOrders(), $sortOrder);
echo '</div>';

echo '<hr></hr>';

echo '<h2>' . t('Topics per Page') . '</h2>';
echo t('Specify the number of topics you\'d like to see on each page.') . '<br />';

echo '<div id="xan-forum-topic-form-sort">';
echo $formHelper->text('topicsPerPage', $topicsPerPage ? $topicsPerPage : 20);
echo '</div>';
?>
<script type="text/javascript">
$(function(){
    $( '#xan-forum-topic-form-ctID select').change();
});
</script>