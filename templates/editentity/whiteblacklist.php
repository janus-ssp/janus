<!-- Whitelist/blacklist tab for editentities -->
<div id="remoteentities">


   <?php
        define('JANUS_ALLOW_BLOCK_REMOTE_ENTITY', $this->data['security.context']->isGranted('blockremoteentity', $this->data['entity']));

       $allowAllCheckState = '';
       $blockAllCheckedState = '';

       if($this->data['entity']->getAllowedAll() === 'yes') {
           $allowAllCheckState = JANUS_FORM_ELEMENT_CHECKED;
       }

       if($this->data['entity']->getAllowedAll() === 'no' && count($this->data['allowed_entities'])==0 && count($this->data['blocked_entities'])==0) {
           $blockAllCheckedState = JANUS_FORM_ELEMENT_CHECKED;
       }

        echo '<input id="allowall_check" type="checkbox" name="allowall" ' . $allowAllCheckState . '" /> ' . $this->t('tab_remote_entity_allowall');
        echo "<br/>\n";
        echo '<input id="allownone_check" type="checkbox" name="allownone" ' . $blockAllCheckedState . ' /> ' . $this->t('tab_remote_entity_allownone');
        echo "\n";

    ?>


    <?php
        foreach ( array('blacklist','whitelist') as $list )
        {
            if ($list=='blacklist' && !$this->data['useblacklist']) continue;
            if ($list=='whitelist' && !$this->data['usewhitelist']) continue;

            // title
            echo "<h2>";
            echo $this->t("tab_remote_entity_{$this->data['entity']->getType()}");
            echo ' ';
            echo $this->t("tab_remote_entity_{$list}");
            echo "</h2>\n";
            echo "<p>";
            echo $this->t("tab_remote_entity_help_${list}_{$this->data['entity']->getType()}");
            echo "</p>\n";
            echo "<hr/>\n";

            echo "<table id=\"entity_$list\" class=\"entity_sort entity_acl\">";

            // header
            echo '<thead><tr>';
            echo '<th class="acl_check   sorter-digit">✓</th>';
            echo '<th class="acl_state   sorter-text ">St</th>';
            echo '<th class="acl_blocked sorter-text "><img src="resources/images/pm_stop_16.png"/></th>';
            echo '<th class="acl_entity  sorter-text ">Name</th>';
            echo '<th class="acl_notes   sorter-text "><img src="resources/images/information.png"/></th>';
            echo '<th class="acl_desc    sorter-text ">EntityID</th>';
            echo "</tr></thead>";

            echo "<tbody>\n";

            foreach($this->data['remote_entities_acl_sorted'] AS $remote_data) 
            {

                echo '<tr>';

                // only thing different between blacklist and whitelist really 
                // is this input field.
                if ($list=='whitelist') {
                    $name    = "addAllowed[]";
                    $class   = "remote_check_w";
                    $checked = (array_key_exists($remote_data['eid'], $this->data["allowed_entities"]) ? JANUS_FORM_ELEMENT_CHECKED : '');
                } else { // blacklist
                    $name    = "addBlocked[]";
                    $class   = "remote_check_b";
                    $checked = (array_key_exists($remote_data['eid'], $this->data["blocked_entities"]) ? JANUS_FORM_ELEMENT_CHECKED : '');
                }
                $value   = htmlspecialchars($remote_data['eid']);

                # checkbox
                echo '<td class="acl_check">';
                echo "<input class=\"$class\" type=\"checkbox\" name=\"$name\" value=\"$value\" $checked/>";
                echo '</td>';

                # Workflow state
                echo '<td class="acl_state">';
                echo htmlspecialchars($remote_data['state']);
                echo '</td>';

                # stop-sign
                echo '<td class="acl_blocked">';
                if ($remote_data['blocked']) {
                    echo '<img style="display: inline; vertical-align: bottom;" src="resources/images/pm_stop_16.png" '.
                               'alt="(BLOCKED BY ENTITY)" title="This remote entity has disabled access for the current entity" />';
                }
                echo '</td>';

                # entity name + editing link
                echo '<td class="acl_entity"' . (isset($remote_data['textColor']) ? " style=\"color:{$remote_data['textColor']}\"" : '') . '>';
                if ($remote_data['editable']) {
                    $eid = urlencode($remote_data['eid']);
                    $rev = urlencode($remote_data['revisionid']);
                    echo "<a href=\"editentity.php?eid=$eid&amp;revisionid=$rev\">";
                }
                echo htmlspecialchars($remote_data['name'][$this->getLanguage()]);
                if ($remote_data['editable']) {
                    echo '</a>';
                }
                echo '</td>';

                # notes icon
                echo '<td class="acl_notes">';
                if ($remote_data['notes']) {
                    echo '<a href="#" class="simptip-position-top simptip-smooth simptip-multiline no-border" data-tooltip="'.
                         $remote_data['notes'] . '">'.
                         '<img src="resources/images/information.png" alt="(i)"/>'.
                         '</a>';
                }
                echo '</td>';

                # description
                echo '<td class="acl_desc">';
                echo htmlentities($remote_data['description'][$this->getLanguage()], ENT_QUOTES, "UTF-8");
                echo '</td>';

                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n";

        }

        // disable checkboxes if user has no permission;  actual access 
        // control is handled in web/editenty.php backend
        echo '<script type="text/javascript">//<![CDATA[';
        // check "allow all/none" boxes if applicable
        if ($this->data['entity']->getAllowedAll() == 'yes') 
        { 
            echo '$("input#allowall_check" ).attr("checked","checked");';
        } 
        elseif (count($this->data['allowed_entities'])==0 && count($this->data['blocked_entities'])==0) 
        { 
            echo '$("input#allownone_check" ).attr("checked","checked");';
        }
        // disable all checkboxes if user has no permission to change them
        if(!JANUS_ALLOW_BLOCK_REMOTE_ENTITY) 
        { 
            echo '$("input#allowall_check" ).attr("disabled","disabled");';
            echo '$("input#allownone_check").attr("disabled","disabled");';

            if ($this->data['useblacklist']) echo '$("input.remote_check_b").attr("disabled","disabled");';
            if ($this->data['usewhitelist']) echo '$("input.remote_check_w").attr("disabled","disabled");';
        }
        echo "]]></script>\n";
    ?>

    <script type="text/javascript" src="resources/scripts/entities-wblist.js"></script>
</div>
