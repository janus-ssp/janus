<?php
/**
 * No newuser created template
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Templete
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.5.0
 */
$this->data['header'] = 'JANUS';
$this->includeAtTemplateBase('includes/header.php');
?>
<div id="content">
    <h1><?php echo $this->t('header_no_new_user'); ?></h1>
    <p><?php echo $this->t('text_no_new_user'); ?><br>
    <br />
    <?php echo '<a href="mailto:' . $this->data['admin_email'] . '">' .
         $this->t('admin_contact') . '</a>';
    ?>
    </p>
</div>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
