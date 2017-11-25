<?php

/** @var $this SimpleSAML_XHTML_Template */

$csrf_provider = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();

$this->data['jquery'] = array('version' => '1.8', 'core' => true, 'ui' => true, 'css' => true);
$this->data['head'] = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/import.css" />' . "\n";

$this->includeAtTemplateBase('includes/header.php');
echo '<a href="'.SimpleSAML_Module::getModuleURL('janus/index.php').'">'.$this->t('text_dashboard').'</a>';

if (isset($this->data['message']) && substr($this->data['message'], 0, 5) === 'error'): ?>
    <h2 class="editentity_error"><?php echo $this->t('error_header'); ?></h2>
    <p><?php echo $this->t($this->data['message']); ?></p>
<?php endif; ?>
    <hr />
<?php if ($this->data['update']): ?>

    <h3><?php echo $this->t('text_apply_following_changes'); ?></h3>
    <div id="changes">
        <h2>Metadata</h2>

        <form id="applyForm" method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_provider->generateCsrfToken('import_entity') ?>" />
            <input type="hidden" name="apply" value="1" />

            <table border="1">
                <thead>
                <tr>
                    <th><?php echo $this->t('tab_edit_entity_apply_change'); ?></th>
                    <th><?php echo $this->t('tab_edit_entity_entry'); ?></th>
                    <th><?php echo $this->t('text_old_value'); ?></th>
                    <th><?php echo $this->t('text_new_value'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->data['changes'] as $changedKey => $changedValue): ?>
                    <?php
                    $oldValue = isset($this->data['old'][$changedKey]) ? $this->data['old'][$changedKey] : '';
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="excluded_metadata_keys[]" value="<?php echo $changedKey; ?>" />
                        </td>
                        <th><?php echo $changedKey; ?></th>
                        <td><?php echo $oldValue; ?></td>
                        <td><?php echo $changedValue; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Whitelist / Blacklist</h2>
            <table border="1">
                <thead>
                <th></th>
                <th>Old</th>
                <th>New</th>
                </thead>
                <tbody>
                <tr>
                    <th>Allowed all</th>
                    <td><?php echo $this->data['oldAcl']['AllowedAll']; ?></td>
                    <td><?php echo $this->data['newAcl']['AllowedAll']; ?></td>
                </tr>
                <tr>
                    <th>Whitelist</th>
                    <td>
                        <?php if (!empty($this->data['oldAcl']['Allowed'])): ?>
                            <ul>
                                <li>
                                    <?php echo implode('</li><li>', $this->data['oldAcl']['Allowed']); ?>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($this->data['newAcl']['Allowed'])): ?>
                            <ul>
                                <li>
                                    <?php echo implode('</li><li>', $this->data['newAcl']['Allowed']); ?>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Blacklist</th>
                    <td>
                        <?php if (!empty($this->data['oldAcl']['Blocked'])): ?>
                            <ul>
                                <li>
                                    <?php echo implode('</li><li>', $this->data['oldAcl']['Blocked']); ?>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($this->data['newAcl']['Blocked'])): ?>
                            <ul>
                                <li>
                                    <?php echo implode('</li><li>', $this->data['newAcl']['Blocked']); ?>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <h2>Attribute Release Policy</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Attribute</th>
                        <th><?php echo $this->t('text_old_value'); ?></th>
                        <th><?php echo $this->t('text_new_value'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (is_array($this->data['arpChanges'])) foreach ($this->data['arpChanges'] as $changedKey => $changedValues): ?>
                    <?php
                    $oldValues = !empty($this->data['oldArp'][$changedKey]) ? $this->data['oldArp'][$changedKey] : array();
                    ?>
                    <tr>
                        <th><?php echo $changedKey; ?></th>
                        <?php if (empty($oldValues)): ?>
                        <td class="center" style="font-style: italic">&lt;empty&gt;</td>
                        <?php else: ?>
                        <td class="center"><ul><li><?php echo implode('</li><li>', $oldValues); ?></li></ul></td>
                        <?php endif ?>
                        <?php if (count($changedValues) === 1): ?>
                        <td class="center"><?= $changedValues[0] ?></td>
                        <?php else: ?>
                        <td class="center"><ul><li><?php echo implode('</li><li>', $changedValues); ?></li></ul></td>
                        <?php endif ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="center">
                <input type="submit" class="janus_button" value="<?php echo $this->t('button_apply_changes') ?>" />
            </div>
        </form>
    </div>
<?php else: ?>
    <p><?php echo $this->t('text_no_changes_to_apply'); ?></p>
<?php endif; ?>

<?php
$this->includeAtTemplateBase('includes/footer.php');
