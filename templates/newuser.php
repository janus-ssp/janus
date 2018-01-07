<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
$this->data['header'] = 'JANUS';
$this->includeAtTemplateBase('includes/header.php');
$csrf_provider = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();
echo '<h1>' . $this->t('header_new_user') . '</h1>';
if(isset($this->data['user_created']) && $this->data['user_created'] === TRUE) {
    echo '<p>' . $this->t('text_new_user_created', array('%USERID%' => $this->data['userid'])) .'</p>';
    echo '<a href="'. \SimpleSAML\Module::getModuleURL('janus/index.php?selectedtab=0') .'">Dashboard</a><br /><br />';
} else {
    echo '<form method="post" action="">';
    echo '<input type="hidden" name="csrf_token" value="' . $csrf_provider->generateCsrfToken('add_user') . '" />';
    echo $this->t('text_create_new_user', array('%USERID%' => $this->data['userid']));
    echo '<input type="hidden" name="userid" value="'. $this->data['userid'].'" /><br />';
    echo '<input type="hidden" name="type" value="technical" /><br />';
    echo '<input type="submit" name="submit" value="' . $this->t('text_submit_button') . '" />';
    echo '</form>';
}
$this->includeAtTemplateBase('includes/footer.php');
