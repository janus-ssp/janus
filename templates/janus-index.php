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

<h1>Users</h1>

<?php
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/newUser.php') .'">New User</a><br /><br />';

foreach($this->data['users'] AS $user) {
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/showEntities.php') .'?id='. $user['email'] .'">'. $user['email'] .'</a><br />';
}
?>

</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
