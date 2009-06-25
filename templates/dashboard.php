<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
//$this->data['header'] = 'JANUS';
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
				  	$("#tabdiv").tabs();
				  	$("#tabdiv").tabs("select", 1);
					});
</script>';

$this->includeAtTemplateBase('includes/header.php');
?>
<div id="tabdiv">
<h1><?php echo $this->t('text_dashboard').' for '. $this->data['user']->getEmail(); ?></h1>
<!-- TABS -->
<ul>
	<li><a href="#userdata"><?php echo $this->t('tab_user_data_header'); ?></a></li>
	<li><a href="#entities"><?php echo $this->t('tab_entities_header'); ?></a></li>
</ul>
<!-- TABS END -->

<!-- TABS - ENTITIES -->
<div id="entities">
	<?php
		if(isset($this->data['msg']) && substr($this->data['msg'], 0, 5) === 'error') {
			echo '<div style="font-weight: bold; color: #FF0000;">'. $this->t('error_header').'</div>';
			echo '<p>'. $this->t($this->data['msg']) .'</p>';
		} else if(isset($this->data['msg'])) {
			echo '<p>'. $this->t($this->data['msg']) .'</p>';	
		}
	?>
	<h2><?php echo $this->t('tab_entities_new_entity_subheader'); ?></h2>
	<form method="post" action="">
		<input type="hidden" name="userid" value="<?php echo $this->data['userid']; ?>">
		<?php echo $this->t('tab_entities_new_entity_text'); ?>: <input type="text" name="entityid">&nbsp;&nbsp;<input type="submit" name="submit" value="<?php echo $this->t('text_submit_button'); ?>"><br/>
	</form>
	
	<h2><?php echo $this->t('tab_entities_entities_subheader'); ?></h2>
	<p><?php echo $this->t('text_entities_help'); ?></p>
	<!--<h2>List of entities</h2>-->
<?php
if(!$this->data['entities']) {
	$sps = array('Non');
	$idps = array('Non');
} else {
	$sps = array();
	$idps = array();

	foreach($this->data['entities'] AS $entity) {
		if($entity->getType() === 'sp') {
			$sps[] = '<a href="editentity.php?entityid='.$entity->getEntityid().'">'. $entity->getEntityid() . '</a><br>';
		} else {
			$idps[] = '<a href="editentity.php?entityid='.$entity->getEntityid().'">'. $entity->getEntityid() . '</a><br>';
		}
	}
}
?>
<table cellpadding="30" style="border-collapse: collapse;">
	<tr>
		<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px;">Tilsluttede tjenester</td>
		<td style="border-bottom: 1px solid #000000; padding: 4px;">Tilsluttede institutioner</td>
	</tr>
	<tr>
		<td valign="top" style="border-right: 1px solid #000000; padding-left: 4px;">
		<?php
		foreach($sps AS $sp) {
			echo $sp;
		}
		?>
		</td>
		<td valign="top" style="padding-left: 4px;">
		<?php
		foreach($idps AS $idp) {
			echo $idp;
		}
		?>
		</td>
	</tr>
</table>
</div>
<!-- TABS END - ENTITIES -->


<!-- TABS - USERDATA -->
<div id="userdata">
<form method="post" action="">
<h2><?php echo $this->t('tab_user_data_subheader');  ?></h2>
<p><?php echo $this->t('tab_user_data_username');  ?>: <?php echo $this->data['user']->getEmail(); ?></p>
<!-- <p>Type: <?php echo $this->data['user']->getType(); ?></p> -->
<p><?php echo $this->t('tab_user_data_otherinfo');  ?>:</p>
<textarea name="userdata" cols="100" rows="10">
<?php
echo $this->data['user']->getData();
?>
</textarea>
<input type="submit" name="usersubmit" value="save">
</form>
</div>
<!-- TABS END - USERDATA -->

</div><!-- TABS DIV END -->

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
