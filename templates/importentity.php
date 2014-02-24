<?php

/** @var $this SimpleSAML_XHTML_Template */

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head'] = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/import.css" />' . "\n";

$this->includeAtTemplateBase('includes/header.php');
?>
<?php if (isset($this->data['message']) && substr($this->data['message'], 0, 5) === 'error'): ?>
    <h2 class="editentity_error"><?php echo $this->t('error_header'); ?></h2>
    <p><?php echo $this->t($this->data['message']); ?></p>;
<?php endif; ?>
    <hr>
<?php if ($this->data['update']): ?>

    <h3><?php echo $this->t('text_apply_following_changes'); ?></h3>
    <div id="changes">
        <h2>Metadata</h2>

        <form id="applyForm" method="post" action="">
            <input type="hidden" name="apply" value="1"/>

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
                        <td class="center"><input type="checkbox" name="excluded_metadata_keys[]" value="<?php echo $changedKey; ?>">
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
            <div class="center">
                <input type="submit" class="janus_button" value="<?php echo $this->t('button_apply_changes') ?>">
            </div>
        </form>
    </div>
<?php else: ?>
    <p><?php echo $this->t('text_no_changes_to_apply'); ?></p>
<?php endif; ?>

<?php
$this->includeAtTemplateBase('includes/footer.php');
