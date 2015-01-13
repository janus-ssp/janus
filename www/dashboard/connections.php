<?php

require '../_includes.php';

$sortField = isset($_GET['sort']) ? $_GET['sort'] : null;
$sortOrder = isset($_GET['order']) ? $_GET['order'] : null;
if(isset($_GET['submit_search']) && !empty($_GET['q'])) {
    $template->data['entities'] = $userController->searchEntities(
        $_GET['q'],
        $entity_filter,
        $entity_filter_exclude,
        $sortField,
        $sortOrder
    );
}else {
    $template->data['entities'] = $userController->getEntities(
        false,
        $entity_filter,
        $entity_filter_exclude,
        $sortField,
        $sortOrder
    );
}
$template->data['entity_filter'] = $entity_filter;
$template->data['entity_filter_exclude'] = $entity_filter_exclude;
$template->data['query'] = isset($_GET['q']) ? $_GET['q'] : '';
$template->data['order'] = $sortOrder;
$template->data['sort'] = $sortField;
$template->data['is_searching'] = !empty($template->data['order']) ||
    !empty($template->data['sort']) ||
    !empty($template->data['query']) ||
    !empty($template->data['entity_filter']) ||
    !empty($template->data['entity_filter_exclude']);
