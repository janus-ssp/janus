<?php
/**
 * Main template for JANUS.
 *
 * @author Sixto Martín, <smartin@yaco.es>
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->includeAtTemplateBase('includes/header.php');
?>
<div id="tabdiv">
<a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php'); ?>"><?php echo $this->t('text_dashboard'); ?></a>
<h2><?php echo $this->t('tab_entities_federation_entity_subheader'); ?></h2>
<?php
if($this->data['uiguard']->hasPermission('exportallentities', null, $this->data['user_type'], TRUE)) {
    echo '<p>'.$this->t('text_export_federation_desc').'</p>';
    if(isset($this->data['msg']))
    {
        echo '<p>' . $this->t($this->data['msg']) . '</p>';
    }
?>
<form method="get" action="">
    <fieldset>
        <legend>Params</legend>
        <table>
            <tr>
                <td>
                    <label for="type"><?php echo $this->t('admin_type'); ?></label>
                </td>
                <td>
                    <select name="type[]" multiple="multiple">
                    <?php
                        foreach($this->data['types'] AS $id => $vals) {
                            if($vals['enable']) {
                                echo '<option value="' . $id . '">' . $id . '</option>';
                            }
                        }
                    ?>
                    </select>
                    (<?php echo $this->t('text_select_multiple'); ?>)
                </td>
            </tr>
            <tr>
                <td>
                    <label for="state"><?php echo $this->t('tab_edit_entity_state'); ?></label>
                </td>
                <td>
                    <select name="state">
                        <option value="null">-- <?php echo $this->t('text_select_state'); ?> --</option>
                        <?php
                            foreach($this->data['states'] AS $id => $vals) {
                                echo '<option value="' . $id . '">' . $vals['name'][$this->getLanguage()] . '</option>';
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="mimetype"><?php echo $this->t('text_mimetype'); ?></label>
                </td>
                <td>
                    <select name="mimetype">
                        <option>-- <?php echo $this->t('text_select_mimetype'); ?> --</option>
                        <option value="application/xml">application/xml</option>
                        <option value="application/samlmetadata+xml">application/samlmetadata+xml</option>
                        <option value="text/plain">text/plain</option>
                        <option value="ssp">SSP flat file format</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="mimetype"><?php echo $this->t('text_external'); ?></label>
                </td>
                <td>
                    <select name="external">
                        <option value="null">-- <?php echo $this->t('text_select_external'); ?> --</option>
                        <?php
                        foreach($this->data['external'] AS $key => $value)
                        {
                            echo '<option value="' . $key . '">' . $value['name'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="<?php echo $this->t('tab_edit_entity_export'); ?>">
                </td>
            </tr>
        </table>
    </fieldset>
</form>
<?php
} else {
    echo $this->t('error_no_access');
}
?>
<!-- END CONTENT -->
</div>
<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
