<?php
    if (!array_key_exists('icon', $this->data)) $this->data['icon'] = 'lock.png';

    $this->data['autofocus'] = 'mail';

    $this->includeAtTemplateBase('includes/header.php');
    $showform = FALSE;
?>

<?php if ($this->data['msg'] !== NULL) { ?>
    <div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5">
    <?php
        if(substr($this->data['msg'], 0, 5) == 'error') {
            echo '<img src="/'. $this->data['baseurlpath'] .'resources/icons/bomb.png" style="float: left; margin: 15px " />';
            echo '<h2>'. $this->t('{login:error_header}') .'</h2>';
            $showform = TRUE;
        } else {
            echo '<img src="/'. $this->data['baseurlpath'] .'resources/icons/checkmark48.png" style="float: left; margin: 15px " />';
            echo "<br />";
            //echo '<h2>'. $this->t('text_success_header') .'</h2>';
        }
    ?>
        <p><?php echo $this->t($this->data['msg'], array('%USERMAIL%' => $this->data['mail'])); ?></p>
        </div>
    <?php
        } else {
            $showform = TRUE;
        }

        if($showform) {
            ?>
    <h2 style="break: both"><?php echo $this->t('text_login_header'); ?></h2>
    <form action="?" method="post" name="f">
        <table border="0">
            <tr>
                <td rowspan="2"><img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/pencil.png" /></td>
                <td style="padding: .3em;">E-mail</td>
                <td>
                    <input type="text" id="mail" tabindex="1" name="mail" />
                </td>
                <td style="padding: .4em;" rowspan="1">
                    <input type="submit" tabindex="3" value="<?php echo $this->t('text_send_button'); ?>" />
                    <?php
                    // Extra state parameters
                    foreach ($this->data['stateparams'] as $name => $value) {
                        echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="3"><?php echo $this->t('text_login_help'); ?></td>
            </tr>
        </table>
    </form>

    <h2 style="break: both"><?php echo $this->t('text_create_account_header'); ?></h2>
    <form action="?" method="post" name="f">
        <table border="0">
            <tr>
                <td rowspan="2"><img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/pencil.png" /></td>
                <td style="padding: .3em;">E-mail</td>
                <td>
                    <input type="text" id="mail" tabindex="1" name="mail" />
                </td>
                <td style="padding: .4em;" rowspan="1">
                    <input type="submit" tabindex="3" value="<?php echo $this->t('text_send_button'); ?>" />
                    <?php
                    // Extra state parameters
                    foreach ($this->data['stateparams'] as $name => $value) {
                        echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="3"><?php echo $this->t('text_create_account_help'); ?></td>
            </tr>
        </table>
    </form>
                <?php
        }
?>

    <h2><?php echo $this->t('help_header'); ?></h2>
    <p><?php echo $this->t('help_text', array('%ADMINNAME%' => $this->data['adminname'], '%ADMINEMAIL%' => $this->data['adminemail'])); ?></p>


<?php $this->includeAtTemplateBase('includes/footer.php'); ?>