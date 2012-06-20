<?php

/** @var $this SimpleSAML_XHTML_Template */

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '
<style type="text/css">
#changes {
    width: 100%;
    overflow: auto;
}
table tr td {
    padding: 3px;
    min-width: 50px;
}
</style>';

$this->includeAtTemplateBase('includes/header.php');

if(isset($this->data['message']) && substr($this->data['message'], 0, 5) === 'error') {
    echo '<h2 class="editentity_error">'. $this->t('error_header').'</h2>';
    echo '<p>'. $this->t($this->data['message']) .'</p>';
}
echo "<hr />";
if ($this->data['update']) {
    echo "<p>" . $this->t('text_apply_following_changes') . ":</p>";
    echo '<div id="changes">
            <table border="1">
                <thead>
                    <tr>
                        <th>' . $this->t('tab_edit_entity_entry') . '</th>
                        <th>' . $this->t('text_old_value') . '</th>
                        <th>' . $this->t('text_new_value') . '</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($this->data['changes'] as $changedKey => $changedValue) {
        $oldValue = '';
        if (isset($this->data['old'][$changedKey])) {
            $oldValue = $this->data['old'][$changedKey];
        }
        echo "<tr><th>$changedKey</th><td>$oldValue</td><td>$changedValue</td></tr>";
    }
    echo "</tbody></table></div>";
}
else {
    echo "<p>" . $this->t('text_no_changes_to_apply') . "</p>";
}
?>
<hr />
<a href="#" onclick="history.back();"><?=$this->t('text_back'); ?></a>
<?php
if ($this->data['update']) {
?>
<form id="applyForm" method="post" action="" style="display: inline">
    <input type="hidden" name="apply" value="1" />
    <a href="#" class="janus_button" onclick="$('#applyForm').submit();" style="float: right;">
        <?php echo $this->t('button_apply_changes') ?>
    </a>
</form>
<?php
}
$this->includeAtTemplateBase('includes/footer.php');