<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
$this->data['header'] = 'JANUS';
$this->includeAtTemplateBase('includes/header.php');

?>
<div id="content">

<h2><?php echo($this->data['header']); ?></h2>



</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
