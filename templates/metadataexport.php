<?php
$this->data['header'] = 'JANUS - ' . $this->t('title');
//$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->includeAtTemplateBase('includes/header.php');
$language = $this->getLanguage();
?>
<style>
    form input {
        width: 250px;
        margin-left: 10px;
    }
    form select {
        width: 250px;
        margin-left: 10px;
    }
    :invalid {
        background-color: #F0DDDD;
    }
</style>
<a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php') ?>"><?php echo $this->t('back') ?></a>
<h2><?php echo $this->t('title') ?></h2>
<?php
// Display errors
if (isset($this->data['errors'])) {
    echo "<h3>Error</h3>";
    echo "<p><b>" . $this->t($this->data['error_type']) . "</b></p>";
    echo $this->data['errors'];
}
?>
<h3>Options</h3>
<ul>
    <li><b><?php echo $this->t('type') ?></b>: <?php echo $this->t('type_description') ?></li>
    <li><b><?php echo $this->t('state') ?></b>: <?php echo $this->t('state_description') ?></li>
    <li><b><?php echo $this->t('mime') ?></b>: <?php echo $this->t('mime_description') ?></li>
    <li><b><?php echo $this->t('filename') ?></b>: <?php echo $this->t('filename_description') ?></li>
    <li><b><?php echo $this->t('exclude') ?></b>: <?php echo $this->t('exclude_description') ?></li>
    <li><b><?php echo $this->t('post_processor') ?></b>: <?php echo $this->t('post_processor_description') ?></li>
    <li><b><?php echo $this->t('ignore_errors') ?></b>: <?php echo $this->t('ignore_errors_description') ?></li>
</ul>
<form method="GET" action="">
    <input type="hidden" name="md" />
    <fieldset>
        <table>
            <tr>
                <td>
                <label for="md_type"><?php $this->t('type') ?></label>
                </td>
                <td>
                    <select name="type[]" id="md_type" multiple required autofocus size="2">
                        <?php
                        foreach ($this->data['types'] AS $keytype => $type) {
                            // Only allow enabled types
                            if (isset($type['enable']) && $type['enable']) {
                                echo '<option value="' . $keytype . '">' . $type['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_state"><?php echo $this->t('state') ?></label>
                </td>
                <td>
                    <select name="state" id="md_state" required pattern="[A-Za-z]">
                        <option value="">-- <?php echo $this->t('text_select_state') ?> --</option> 
                        <?php
                        foreach ($this->data['states'] AS $keystate =>$state) {
                            // Only allow deployable states
                            if (isset($state['isDeployable']) && $state['isDeployable']) {
                                echo '<option value="' . $keystate . '">';
                                if (isset($state['name'][$language])) {
                                    echo $state['name'][$language];
                                } else {
                                    $state['name']['en'];
                                }
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_mime"><?php echo $this->t('mime') ?></label>
                </td>
                <td>
                    <select name="mime" id="md_mime" required>
                        <option value="">-- <?php echo $this->t('text_select_mimetype') ?> --</option> 
                        <?php
                        foreach ($this->data['allowed_mime'] AS $mime) {
                            echo '<option value="' . $mime . '">' . $mime . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_filename"><?php echo $this->t('filename') ?></label>
                </td>
                <td>
                    <input type="text" name="filename" id="md_filename" placeholder="federation.xml" />
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_exclude"><?php echo $this->t('exclude') ?></label>
                </td>
                <td>
                <input type="text" name="exclude" id="md_exclude" placeholder="http://example.com,http://example.org"/> <?php $this->t('exclude_hint') ?>
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_postpro"><?php echo $this->t('post_processor') ?></label>
                </td>
                <td>
                    <select name="postpro" id="md_postpro">
                        <option value="">-- <?php echo $this->t('text_select_postprocessor') ?> --</option> 
                        <?php
                        foreach ($this->data['postprocessor'] AS $keypostpro => $postpro) {
                            echo '<option value="' . $keypostpro . '">' . $postpro['name'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                <label for="md_ignoreerrors"><?php echo $this->t('ignore_errors') ?></label>
                </td>
                <td>
                    <input type="checkbox" name="ignoreerrors" id="md_ignoreerrors" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                <input type="submit" name="submit" value="<?php echo $this->t('generate') ?>" />
                </td>
            </tr>
        </table>
    </fieldset>
</form>
<?php
$this->includeAtTemplateBase('includes/footer.php');
