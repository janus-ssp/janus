<?php
/**
 * No newuser created template
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Templete
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
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
