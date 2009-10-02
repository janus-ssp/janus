<?php
$this->includeAtTemplateBase('includes/header.php');
$this->data['head'] = '
<style type="css/text">
th {
align: left;
}
</style>
';

if($this->data['success']) {
    ?>		
        <h1><?php echo $this->t('install_header'); ?></h1>
        <p><u><?php echo $this->t('install_tables_created'); ?>:</u></p>
        <p>
        <?php echo $this->data['prefix'] .'tokens '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'user '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'userData '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'metadata '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'attribute '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'entity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'blockedEntity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'hasEntity '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'message '. $this->t('install_created') .'.<br />'; ?>
        <?php echo $this->data['prefix'] .'subscriptions '. $this->t('install_created') .'.<br />'; ?>
        </p>
        <p><u><?php echo $this->t('install_users_created'); ?>:</u></p>
        <p><?php echo $this->data['email']; ?></p>
        <p><?php echo $this->t('install_tables_created'); ?><p>
        <p><?php echo $this->t('install_add_authsource'); ?>:</p>
        <pre style="border: 1px solid #000000;">
'mailtoken' =&gt; array(
    'janus:MailToken',
    'dsn' =&gt; '<?php echo $this->data['dsn']; ?>',
    'username' =&gt; '<?php echo $this->data['user']; ?>',
    'password' =&gt; '<?php echo $this->data['pass']; ?>',
    'table' =&gt; '<?php echo $this->data['prefix']; ?>tokens',
),
    </pre>
        <p><?php echo $this->t('install_config_file'); ?>:</p>
        <pre style="border: 1px solid #000000;">
<?php echo '$config => ' . var_export($this->data['config_template'], TRUE); ?>
</pre>
        <p><b><?php echo $this->t('install_delete_install'); ?></b><p>
        <hr>
        <address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A><br /></address>
        <?php
        die();
} elseif ($this->data['success'] === FALSE) {
    ?>
        <h1><?php echo $this->t('install_header'); ?></h1>
        <p><?php echo $this->t('install_error_db'); ?><p>
        <a href="<?php echo SimpleSAML_Module::getModuleURL('janus/install/index.php'); ?>">Tilbage</a><br /><br />
    <hr>
        <address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A><br /></address>
        <?php
        die();
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
<label for="dbtype"><?php echo $this->t('install_database_type'); ?></label>
</td>
<td>
<input type="text" name="dbtype" value="mysql" readonly="readonly" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbhost"><?php echo $this->t('install_database_host'); ?></label>
</td>
<td>
<input type="text" name="dbhost" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbname"><?php echo $this->t('install_database_name'); ?></label>
</td>
<td>
<input type="text" name="dbname" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbprefix"><?php echo $this->t('install_database_prefix'); ?></label>
</td>
<td>
<input type="text" name="dbprefix" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbuser"><?php echo $this->t('install_database_username'); ?></label>
</td>
<td>
<input type="text" name="dbuser" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbpass"><?php echo $this->t('install_database_password'); ?></label>
</td>
<td>
<input type="text" name="dbpass" />
</td>
</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo $this->t('install_adminuser'); ?></legend>
<table border="0">
<tr>
<td>
<label for="admin_name"><?php echo $this->t('install_adminuser_name'); ?></label>
</td>
<td>
<input type="text" name="admin_name" /></br>
</td>
</tr>
<tr>
<td>
<label for="admin_email"><?php echo $this->t('install_adminuser_email'); ?></label>
</td>
<td>
<input type="text" name="admin_email" /></br>
</td>
</tr>
</table>
</fieldset><br />
<input type="submit" name="submit_admin_user" value="<?php echo $this->t('install_install'); ?>" />
<input type="hidden" name="action" value="install" />
</form>
<hr>
<address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A><br /></address>
<?php
}
?>
