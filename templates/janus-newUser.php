<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$this->data['header'] = 'JANUS';
$this->includeAtTemplateBase('includes/header.php');

?>
<div id="content">

<h1>Create new user</h1>
<?php
echo $this->data['user_status'];
?>
<form method="post" action="">
	Type: <select name="type">
	<?php
		foreach($this->data['usertypes'] AS $type) {
			echo '<option value="'. $type .'">'. $type .'</option>';
		}
	?>
	</select><br />		

	<?php
	if(isset($this->data['mail'])) {
		echo 'E-mail: <input type="text" name="email" value="'. $this->data['mail'].'" /><br />';
	} else {
		echo 'E-mail: <input type="text" name="email" /><br />';
	}
	?>
	<input type="submit" name="submit" value="Create">
</form>

<br />
<?php

foreach($this->data['users'] AS $user) {
	echo $user['uid'] .' - '. $user['type'] .' - '. $user['email'] .' - '. $user['update'] .' - '. $user['created'] .' - '. $user['ip'] .'<br />';
}




echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>



</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
