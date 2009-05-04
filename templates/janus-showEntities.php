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

<h1>Entities</h1>

<?php
if(!$this->data['entities']) {
	echo 'Not entities for user '. $_GET['id']. '<br /><br />';
} else {
	foreach($this->data['entities'] AS $entity) {
		echo '<a href="showMetadata.php?entityid='.$entity->getEntityid().'">'. $entity->getRevisionid() .' - '. $entity->getEntityid() . '</a><br>';
	}
}
?>
<form method="post" action="">
	<input type="hidden" name="userid" value="<?php echo $this->data['userid']; ?>">
	Entityid: <input type="text" name="entityid"><br/>
	<input type="submit" name="submit" value="Create"><br/>
</form>



<?php

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';

?>
</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
