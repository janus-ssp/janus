<?php
// Inline styles
$this->data['head'] = '
<style type="text/css">
th {
align: left;
}
.install_box {
    border: 1px solid #000000;
}
</style>';

// Set header
$this->includeAtTemplateBase('includes/header.php');

// Stuff has been posted ad succeeded
if(isset($this->data['success']) && $this->data['success']) {
?>
        <h1><?php echo $this->t('install_header'); ?></h1>
        <p><u><?php echo $this->t('install_tables_created'); ?>:</u></p>
        <p><?php echo $this->data['prefix'] .'tokens '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'user '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'userData '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'metadata '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'attribute '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'entity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'blockedEntity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'allowedEntity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'hasEntity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'message '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'subscriptions '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'disableConsent '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'arp '. $this->t('install_created') .'.<br />'; ?></p>
        <p><u><?php echo $this->t('install_users_created'); ?>:</u></p>
        <p><?php echo $this->data['email']; ?></p>
        <p><u><?php echo $this->t('install_configuration'); ?>:</u></p>

        <p><?php echo $this->t('install_config_file'); ?>:</p>
        <textarea readonly="readonly" rows="40" cols="10" style="width: 100%;"><?php echo '$config = ' . htmlentities(var_export($this->data['config_template'], TRUE), ENT_QUOTES, 'UTF-8', false); ?>;</textarea>
        <br /><br />
        <p><b><?php echo $this->t('install_delete_install'); ?></b></p>
<?php
// Stuff has been posted but an error occured
} elseif (isset($this->data['success']) && $this->data['success'] === FALSE) {
?>
        <h1><?php echo $this->t('install_header'); ?></h1>
        <p><?php echo $this->t('install_error_db'); ?></p>
        <a href="<?php echo SimpleSAML_Module::getModuleURL('janus/install/index.php'); ?>">Tilbage</a><br /><br />
<?php
// Nothing has been posted yet
} else {
?>

<h1><?php echo $this->t('install_header'); ?></h1>
<p><?php echo $this->t('install_welcome'); ?></p>
<p><?php echo $this->t('install_instruction'); ?></p>
<form method="post" action="">
<fieldset>
<legend><?php echo $this->t('install_database'); ?></legend>
<table border="0">
<tr>
<td>
<label for="db_type"><?php echo $this->t('install_database_type'); ?></label>
</td>
<td>
<input type="text" name="dbtype" value="mysql" readonly="readonly" id="db_type" /><br />
</td>
</tr>
<tr>
<td>
<label for="db_host"><?php echo $this->t('install_database_host'); ?></label>
</td>
<td>
<input type="text" name="dbhost" id="db_host" /><br />
</td>
</tr>
<tr>
<td>
<label for="db_name"><?php echo $this->t('install_database_name'); ?></label>
</td>
<td>
<input type="text" name="dbname" id="db_name" /><br />
</td>
</tr>
<tr>
<td>
<label for="db_prefix"><?php echo $this->t('install_database_prefix'); ?></label>
</td>
<td>
<input type="text" name="dbprefix" id="db_prefix" /><br />
</td>
</tr>
<tr>
<td>
<label for="db_user"><?php echo $this->t('install_database_username'); ?></label>
</td>
<td>
<input type="text" name="dbuser" id="db_user" /><br />
</td>
</tr>
<tr>
<td>
<label for="db_pass"><?php echo $this->t('install_database_password'); ?></label>
</td>
<td>
<input type="text" name="dbpass" id="db_pass" />
</td>
</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo $this->t('install_adminuser'); ?></legend>
<table border="0">
<tr>
<td>
<label for="adminname"><?php echo $this->t('install_adminuser_name'); ?></label>
</td>
<td>
<input type="text" name="admin_name" id="adminname"/><br />
</td>
</tr>
<tr>
<td>
<label for="adminemail"><?php echo $this->t('install_adminuser_email'); ?></label>
</td>
<td>
<input type="text" name="admin_email" id="adminemail"/><br />
</td>
</tr>
</table>
</fieldset><br />
<input type="submit" name="submit_admin_user" value="<?php echo $this->t('install_install'); ?>" />
<input type="hidden" name="action" value="install" />
</form>
<?php
}
?>
<hr />
<address>
Jacob Christiasnen<br />
E-mail: <a href="mailto:jach@wayf.dk">jach@wayf.dk</a>
</address>
<br />
Copyright &copy; 2008-2011 <a href="http://wayf.dk/">WAYF</a>
<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
