<!-- TABS - ENTITIES -->
<div id="entities">
    <?php
    $enablematrix = $util->getAllowedTypes();

    if ($this->data['security.context']->isGranted('createnewentity')) {
        ?>
        <a class="janus_button" onclick="$('#options').toggle('fast');  $('#options input[name=\'entityid\']').focus();"><?php echo $this->t('text_entities_create'); ?></a>
        <form method="post" action="<?php echo FORM_ACTION_URL;?>">
            <input type="hidden" name="csrf_token" value="<?=$csrf_provider->generateCsrfToken('entity_create');?>" />
            <table border="0" id="options" class="frontpagebox" <?php if (!isset($this->data['msg'])) echo 'style="display: none;"'; ?>>
                <tr>
                    <td>
                        <input type="hidden" name="userid" value="<?php echo htmlspecialchars($this->data['userid']); ?>" />
                        <?php echo $this->t('tab_entities_new_entity_text'); ?>:
                    </td>
                    <td>
                        <?php
                        if (isset($this->data['old_entityid'])) {
                            echo '<input type="text" size="40" name="entityid" value="' . htmlspecialchars($this->data['old_entityid']) .'" />';
                        } else {
                            echo '<input type="text" size="40" name="entityid" />';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        echo '<select name="entitytype">';
                        echo '<option value="">' . $this->t('text_select_type') . '</option>';
                        foreach ($enablematrix AS $typeid => $typedata) {
                            if ($typedata['enable'] === true) {
                                if (isset($this->data['old_entitytype']) && $this->data['old_entitytype'] == $typeid) {
                                    echo '<option value="' . htmlspecialchars($typeid) .'" selected="selected">'. htmlspecialchars($typedata['name']) .'</option>';
                                } else {
                                    echo '<option value="'. $typeid .'">'. htmlspecialchars($typedata['name']) .'</option>';
                                }
                            }
                        }
                        echo '</select>';
                        ?>
                    </td>
                    <td>
                        <input class="janus_button" type="submit" name="submit" value="<?php echo $this->t('text_submit_button'); ?>" />
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->t('tab_entities_new_entity_from_url_text'); ?></td>
                    <td><input type="text" size="40" name="entity_metadata_url" placeholder="Put the metadata URL here..."/></td></tr>
                <tr>
                    <td style="vertical-align: top;"><?php echo $this->t('tab_entities_new_entity_from_xml_text'); ?></td>
                    <td colspan="2">
                        <textarea name="metadata_xml" cols="60" rows="5" placeholder="Put the XML here..."></textarea>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </form>
    <?php
    }
    ?>
    <br />
    <?php
    // If we are not currently searching for something and the default view shows less then 50 entities,
    // hide the search form behind a button
    if (!$this->data['is_searching'] && count($this->data['entities']) < 50): ?>
        <a class="janus_button" onclick="$('#search').toggle('fast'); $('#search input[name=\'q\']').focus();"><?php echo $this->t('text_entities_search'); ?></a>
    <?php endif; ?>
    <form method="get" action="<?php echo FORM_ACTION_URL;?>">
        <table id="search"
               class="frontpagebox"
               style="display: <?php
               // If we are searching or the number of entities shown is more or equal to 50, show the search form.
               echo ($this->data['is_searching'] || count($this->data['entities']) >= 50) ? 'block' : 'none'; ?>;">
            <tr>
                <td>Search:</td>
                <td><input type="text" name="q" value="<?php echo htmlspecialchars($this->data['query']); ?>" /></td>
                <td><input type="submit" value="<?php echo $this->t('text_entities_search'); ?>" name="submit_search" class="janus_button" /></td>
            </tr>
            <tr>
                <td colspan="3"><b><?php echo $this->t('text_entities_filter'); ?></b></td>
            </tr>
            <tr>
                <td><?php echo $this->t('text_entities_filter_state'); ?>:</td>
                <td>
                    <select name="entity_filter">
                        <?php
                        $states = $janus_config->getArray('workflowstates');
                        echo '<option value="nofilter">' . $this->t('text_entities_filter_select') . '</option>';
                        $languageCode = $this->getLanguage();
                        foreach($states AS $key => $val) {
                            if (isset($val['name'][$languageCode])) {
                                $translatedValue = $val['name'][$languageCode];
                            } else {
                                $translatedValue = $key;
                            }

                            if($key == $this->data['entity_filter']) {
                                echo '<option value="' . htmlspecialchars($key) . '" selected="selected">' . htmlspecialchars($translatedValue) . '</option>';
                            } else  {
                                echo '<option value="' . htmlspecialchars($key) . '">' . htmlspecialchars($translatedValue) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
                <td></td>
            </tr>
            <tr>
                <td><?php echo $this->t('text_entities_filter_state_exclude'); ?>:</td>
                <td>
                    <select name="entity_filter_exclude">
                        <?php
                        $states = $janus_config->getArray('workflowstates');
                        echo '<option value="noexclude">-- Exclude</option>';
                        foreach($states AS $key => $val) {
                            if($key == $this->data['entity_filter_exclude']) {
                                echo '<option value="' . htmlspecialchars($key) . '" selected="selected">' . htmlspecialchars($val['name'][$this->getLanguage()]) . '</option>';
                            } else  {
                                echo '<option value="' . htmlspecialchars($key) . '">' . htmlspecialchars($val['name'][$this->getLanguage()]) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
                <td></td>
            </tr>
            <tr>
                <td><?php echo $this->t('text_entities_filter_order'); ?>:</td>
                <td>
                    <select name="sort">
                        <option value="name" <?php if ($this->data['sort'] == 'name') echo 'selected="selected"'; ?>><?php echo $this->t('text_entities_filter_sort_name'); ?></option>
                        <option value="created" <?php if ($this->data['sort'] == 'created') echo 'selected="selected"'; ?>><?php echo $this->t('text_entities_filter_sort_created'); ?></option>
                    </select>
                    <select name="order">
                        <option value="ASC" <?php if ($this->data['order'] == 'ASC') echo 'selected="selected"'; ?>><?php echo $this->t('text_entities_filter_order_asc'); ?></option>
                        <option value="DESC" <?php if ($this->data['order'] == 'DESC') echo 'selected="selected"'; ?>><?php echo $this->t('text_entities_filter_order_desc'); ?></option>
                    </select>
                </td>
                <td></td>
            </tr>
        </table>
    </form>
    <br />
    <p><?php echo $this->t('text_entities_help'); ?></p>
    <?php
    $connections = array();

    foreach($enablematrix AS $typeid => $typedata) {
        if($typedata['enable'] === true) {
            $connections[$typeid] = array();
        }
    }
    $count_types = count($connections);
    foreach($this->data['entities'] AS $entity) {
        $connections[$entity->getType()][] = $entity;
    }
    $theader = '';
    $tfooter = '';

    // Create table showing accessible entities
    $theader .= '<tr>';
    $tfooter .= '<tr>';
    foreach($connections AS $ckey => $cval) {
        $theader.= '<td class="connection_header" width="' . (int) 100/$count_types . '%"><b>' . $this->t('text_'.$ckey) . ' - ' . count($cval) . '</b></td>';

        $tfooter .= '<td valign="top" class="connection_footer">';
        $tfooter .= '<table class="connection">';
        $i = 0;
        foreach($cval AS $sp) {
            //Only show disabled entities if allentities permission is granted
            $states = $janus_config->getArray('workflowstates');
            $textColor = isset($states[$sp->getWorkflow()]['textColor']) ? $states[$sp->getWorkflow()]['textColor'] : 'black';
            $tfooter .= '<tr id="list-'.$sp->getEid().'">';
            $tfooter .= '<td class="'.($i % 2 == 0 ? 'even' : 'odd').'" ';
            if ($sp->getActive() == 'no') {
                $tfooter .= ' style="background-color: #A9D0F5;" ';
            }
            $tfooter .= '>';
            $tfooter .= '<a style="color:' . $textColor . '" title="' . htmlspecialchars($sp->getEntityid()) . '" href="editentity.php?eid='.htmlspecialchars($sp->getEid()) . '">'. htmlspecialchars($sp->getPrettyname()) . ' - r' . htmlspecialchars($sp->getRevisionid()) . '</a></td>';
            $tfooter .= '</tr>';
            $i++;
        }
        $tfooter .= '</table>';
        $tfooter .= '</td>';
    }
    $theader .= '</tr>';
    $tfooter .= '</tr>';

    // Show the table
    echo '<table cellpadding="30" class="dashboard_container">';
    echo $theader;
    echo $tfooter;
    echo '</table>';
    ?>

</div>
