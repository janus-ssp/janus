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
        <h1>JANUS - Installation</h1>
        <p><u>Følgende tabeller er blevet oprettet:</u></p>
        <p>
        <?php echo $this->data['prefix'] .'tokens oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'user oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'userData oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'metadata oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'attribute oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'entity oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'blockedEntity oprettet.<br />'; ?>
        <?php echo $this->data['prefix'] .'hasEntity oprettet.<br />'; ?>
        </p>
        <p><u>Følgende bruger er blevet oprettet:</u></p>
        <p><?php echo $this->data['email']; ?></p>
        <p>Tillykke. JANUS er nu installeret.<p>
        <p>Du skal tilføje følgende til <tt>authsources.php</tt> for at login modulet virker:</p>
        <pre style="border: 1px solid #000000;">
'mailtoken' =&gt; array(
    'janus:MailToken',
    'dsn' =&gt; '<?php echo $this->data['dsn']; ?>',
    'username' =&gt; '<?php echo $this->data['user']; ?>',
    'password' =&gt; '<?php echo $this->data['pass']; ?>',
    'table' =&gt; '<?php echo $this->data['prefix']; ?>tokens',
),
    </pre>
        <p>Config fil:</p>
        <pre style="border: 1px solid #000000;">
<?php echo '$config => ' . var_export($this->data['config_template'], TRUE); ?>
</pre>
        <p><b>Husk at slette installationsbiblioteket, da dininstallation ellers kan overskrives.</b><p>
        <hr>
        <address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A>, contact person for JANUS<br /></address>
        <?php
        die();
} elseif ($this->data['success'] === FALSE) {
    ?>
        <h1>JANUS - Installation</h1>
        <p>Der er sket en fejl. Kontroller at forbindelsen til din database, samt konfigurationen er i orden og prøv igen.<p>
        <a href="<?php echo SimpleSAML_Module::getModuleURL('janus/install/index.php'); ?>">Tilbage</a><br /><br />
    <hr>
        <address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A>, contact person for JANUS<br /></address>
        <?php
        die();
} else {
?>


<h1>JANUS - Installation</h1>
<p>Velkommen til JANUS installationen.</p>
<p>Når du trykker `Install` oprettes alle tabeller som JANUS skal bruge inkl. tabeller til autensificerings modulet. Derudover laves der en konfigurationsfil, som du selv skal kopierer til din SimpleSAMLphp installation. Kode til authsource laves også.</p>
<p><strong>OBS!</strong> Denne installer er kun til brug med en MySQL database.</p>
<p>Du skal desuden udfylde informationer om administratoren. Der vil efterfølgende blive oprettet en admin bruger med disse informationer.</p>
<form method="post" action="">
<fieldset>
<legend>Database</legend>
<table border="0">
<tr>
<td>
<label for="dbtype">Database type</label>
</td>
<td>
<input type="text" name="dbtype" value="mysql" readonly="readonly" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbhost">Database host</label>
</td>
<td>
<input type="text" name="dbhost" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbname">Database name</label>
</td>
<td>
<input type="text" name="dbname" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbprefix">Database prefix</label>
</td>
<td>
<input type="text" name="dbprefix" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbuser">Database username</label>
</td>
<td>
<input type="text" name="dbuser" /><br />
</td>
</tr>
<tr>
<td>
<label for="dbpass">Database password</label>
</td>
<td>
<input type="text" name="dbpass" />
</td>
</tr>
</table>
</fieldset>
<fieldset>
<legend>Administrator bruger</legend>
<table border="0">
<tr>
<td>
<label for="admin_name">Navn</label>
</td>
<td>
<input type="text" name="admin_name" /></br>
</td>
</tr>
<tr>
<td>
<label for="admin_email">E-mail</label>
</td>
<td>
<input type="text" name="admin_email" /></br>
</td>
</tr>
</table>
</fieldset><br />
<input type="submit" name="submit_admin_user" value="Install" />
<input type="hidden" name="action" value="install" />
</form>
<hr>
<address><a href="mailto:jach@wayf.dk">Jacob Christiansen</A>, contact person for JANUS<br /></address>
<?php
}
?>
