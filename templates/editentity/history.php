<div id="history">
    <?php
    if($this->data['uiguard']->hasPermission('entityhistory', $wfstate, $this->data['user']->getType())) {

        $history_size = $this->data['mcontroller']->getHistorySize();

        if ($history_size === 0) {
            echo "No history fo entity ". htmlspecialchars($this->data['entity']->getEntityId()) . '<br /><br />';
        } else {
            if (isset($this->data['revision_compare'])) {
                $revisionInfo = $this->data['revision_compare'];
                $revisions = $revisionInfo['data'];
                $index = 0;
                foreach ($revisions as $rev) {
                    echo "<script type=\"text/javascript\">var jsonCompareRevision$index = JSON.parse('$rev')</script>";
                    $index++;
                }
                echo '<div class="compareRevisionContainer" id="compareRevisions">'.'<h2>'. $this->t('tab_edit_entity_revision_compare') .' ( rev '. $revisionInfo['compareRevisionId'].' versus ref '.$revisionInfo['revisionId'] .' )</h2>';
                echo '<input id="toggle_unchanged_attr" type="checkbox"><label for="toggle_unchanged_attr">'.$this->t('tab_edit_entity_show_hide_revision_compare').'</label>';
                echo '<div id="compareRevisionsContent"></div></div>';
            }



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
                echo '<section class="revision">';
                echo '<a href="?eid='. $data->getEid() .'&amp;revisionid='. $data->getRevisionid().'">'. $this->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
                if ($data->getRevisionid() !== $this->data['revisionid']) {
                    $historyTab = $this->data['entity_type'] == 'saml20-sp' ? 7 : 8;
                    echo ' - <a  class="janus_button" href="?compareRevision=true&amp;eid='. $data->getEid() .'&amp;compareRevisiondid='. $data->getRevisionid() . '&amp;revisionid=' . $this->data['revisionid'] . '&amp;selectedtab='.$historyTab.'">Compare with revision ' . $this->data['revisionid'] . '</a>';
                }
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
                echo '</section>';
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