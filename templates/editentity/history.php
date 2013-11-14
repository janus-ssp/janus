<div id="history">
    <?php
    if($this->data['uiguard']->hasPermission('entityhistory', $wfstate, $this->data['user']->getType())) {

        $historyTab = $this->data['entity_type'] == 'saml20-sp' ? 8 : 9;
        $history_size = $this->data['mcontroller']->getHistorySize();

        if ($history_size === 0) {
            echo "No history fo entity ". htmlspecialchars($this->data['entity']->getEntityId()) . '<br /><br />';
        } else {
            if (isset($this->data['revision_compare'])) {
                $revisionInfo = $this->data['revision_compare'];
                $revisions = $revisionInfo['data'];
                $json = '<script type="text/javascript">var jsonCompareRevisions = {};'."\n";;
                foreach ($revisions as $nbr => $rev) {
                    $rev = str_replace(array('\n', '\r', '\t', '\x09'), '', $rev);
                    $json .= "jsonCompareRevisions[$nbr] = JSON.parse('$rev');"."\n";
                }
                $json .= '</script>';
                echo $json;
                echo '<div class="compareRevisionContainer" id="compareRevisions" data-start-revision="'.$revisionInfo['compareRevisionId'].'" data-end-revision="'.$revisionInfo['revisionId'].'">';

                foreach ($revisions as $nbr => $rev) {
                    if ($nbr == $revisionInfo['revisionId']) {
                        continue;
                    }
                    echo '<h3>'. $this->t('tab_edit_entity_revision_compare') .' ( rev '. $nbr.' versus rev '. ($nbr + 1) .' )</h3>';
                    echo "<div id=\"toggle_unchanged_attr_container$nbr\">";
                    echo "<input class=\"toggle_unchanged_attr\" data-revision-nrb=\"$nbr\" type=\"checkbox\"><label>".$this->t('tab_edit_entity_show_hide_revision_compare').'</label></div>';
                    echo '<div id="compareRevisionsContent' . $nbr . '"></div>';
                }
                echo '</div>';
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
                    echo ' - <a  class="janus_button" href="?compareRevision=true&amp;eid='. $data->getEid() .'&amp;compareRevisiondid='. $data->getRevisionid() . '&amp;revisionid=' . $this->data['revisionid'] . '&amp;selectedtab='.$historyTab.'">Revision history</a>';
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

            echo '<div id="historycontainer" data-entity-eid="' . $this->data['entity']->getEid() . '" data-current-revision-id="'.  $this->data['revisionid'] .'" data-history-tab="'.$historyTab.'"><p>';
            echo $this->t('tab_edit_entity_loading_revisions');
            echo '</p></div>';
        }
    } else {
        echo $this->t('error_no_access');
    }
    ?>
</div>