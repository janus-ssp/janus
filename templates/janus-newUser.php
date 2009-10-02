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

<h1><?php echo $this->t('header_new_user'); ?></h1>
<?php
if($this->data['user_created'] === TRUE) {
	echo '<p>' . $this->t('text_new_user_created', array('%USERID%' => $this->data['userid'])) .'</p>';
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php?selectedtab=0') .'">Dashboard</a><br /><br />';
} else {
?>
<form method="post" action="">
	<?php
	echo $this->t('text_create_new_user', array('%USERID%' => $this->data['userid']));
	echo '<input type="hidden" name="userid" value="'. $this->data['userid'].'" /><br />';
	echo '<input type="hidden" name="type" value="technical" /><br />';
	/*
	echo 'Type: <select name="type">';
	foreach($this->data['usertypes'] AS $type) {
		echo '<option value="'. $type .'">'. $type .'</option>';
	}
		
	echo '</select><br />';		

	if(isset($this->data['mail'])) {
		echo 'E-mail: <input type="text" name="email" value="'. $this->data['mail'].'" /><br />';
	} else {
		echo 'E-mail: <input type="text" name="email" /><br />';
	}
	*/
	?>
	<br />
	<br />
	<input type="submit" name="submit" value="<?php echo $this->t('text_submit_button'); ?>">
</form>

<br />
<?php
}
//foreach($this->data['users'] AS $user) {
//	echo $user['uid'] .' - '. $user['type'] .' - '. $user['email'] .' - '. $user['update'] .' - '. $user['created'] .' - '. $user['ip'] .'<br />';
//}




?>



</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
