<?php
$sortField = isset($_GET['sort']) ? $_GET['sort'] : null;
$sortOrder = isset($_GET['order']) ? $_GET['order'] : null;
if(isset($_GET['submit_search']) && !empty($_GET['q'])) {
    $et->data['entities'] = $userController->searchEntities(
        $_GET['q'],
        $entity_filter,
        $entity_filter_exclude,
        $sortField,
        $sortOrder
    );
}else {
    $et->data['entities'] = $userController->getEntities(
        false,
        $entity_filter,
        $entity_filter_exclude,
        $sortField,
        $sortOrder
    );
}
$et->data['entity_filter'] = $entity_filter;
$et->data['entity_filter_exclude'] = $entity_filter_exclude;
$et->data['query'] = isset($_GET['q']) ? $_GET['q'] : '';
$et->data['order'] = $sortOrder;
$et->data['sort'] = $sortField;
$et->data['is_searching'] = !empty($et->data['order']) ||
    !empty($et->data['sort']) ||
    !empty($et->data['query']) ||
    !empty($et->data['entity_filter']) ||
    !empty($et->data['entity_filter_exclude']);