<div id="history">
    <?php
    if($this->data['uiguard']->hasPermission('entityhistory', $wfstate, $this->data['user']->getType())) {

        $history_size = $this->data['mcontroller']->getHistorySize();

        if ($history_size === 0) {
            echo "No history fo entity ". htmlspecialchars($this->data['entity']->getEntityId()) . '<br /><br />';
        } else {
            if (isset($this->data['revision_compare'])) {
                $revs = $this->data['revision_compare'];
                $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();
                $index = 0;
                foreach ($revs as $rev) {
                    $jsonContent = $serializer->serialize($rev, 'json', \JMS\Serializer\SerializationContext::create()->setGroups(array('compare')));
                    echo "<script type=\"text/javascript\">var jsonCompareRevision$index = JSON.parse('$jsonContent')</script>";
                    $index++;
                }
            }
            echo '<div id="compareRevisions"></div>';

            echo '<h2>'. $this->t('tab_edit_entity_history') .'</h2>';
            if ($history_size > 10) {
                $history = $this->data['mcontroller']->getHistory(0, 10);
                echo '<p><a id="showhide">'. $this->t('tab_edit_entity_show_hide') .'</a></p>';
            } else {
                $history = $this->data['mcontroller']->getHistory();
            }

            $user = new sspmod_janus_User($janus_config->getValue('store'));
            $wstates = $janus_config->getArray('workflowstates');
            $curLang = $this->getLanguage();

            foreach($history AS $data) {
                echo '<a href="?eid='. $data->getEid() .'&amp;revisionid='. $data->getRevisionid().'">'. $this->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
                if (strlen($data->getRevisionnote()) > 80) {
                    echo ' - '. htmlspecialchars(substr($data->getRevisionnote(), 0, 79)) . '...';
                } else {
                    echo ' - '. htmlspecialchars($data->getRevisionnote());
                }
                // Show edit user if present
                $user->setUid($data->getUser());
                if($user->load()) {
                    echo ' - ' . $user->getUserid();
                }
                echo ' - ' . date('Y-m-d H:i', strtotime($data->getCreated()));
                if (isset($wstates[$data->getWorkflow()]['name'][$curLang])) {
                    echo ' - ' . $wstates[$data->getWorkflow()]['name'][$curLang];
                } else if (isset($wstates[$data->getWorkflow()]['name']['en'])) {
                    echo ' - ' . $wstates[$data->getWorkflow()]['name']['en'];
                } else {
                    echo ' - ' . $data->getWorkflow();
                }
                echo ' - <a href="?compareRevision=true&amp;eid='. $data->getEid() .'&amp;currentRevisiondid='. $this->data['revisionid'] . '&amp;revisionid=' . $data->getRevisionid() . '&amp;selectedtab=7">Compare with ' . $this->data['revisionid'] . '</a>';
                echo '<br />';
            }

            echo '<div id="historycontainer" data-entity-eid="' . $this->data['entity']->getEid() . '"><p>';
            echo $this->t('tab_edit_entity_loading_revisions');
            echo '</p></div>';
        }
    } else {
        echo $this->t('error_no_access');
    }
    ?>
</div>