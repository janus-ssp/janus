<?php


function mailMetadata($mail, $metadata) {
    assert('is_string($mail)');
    assert('is_string($metadata)');


    $subject = 'JANUS: New metadata';

    // To send HTML mail, the Content-type header must be set
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    // Additional headers
    $headers .= 'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
        'Reply-To: WAYF <no-reply@wayf.dk>' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $body = '
        <html>
        <head>
        <title>JANUS New Metadata</title>
        </head>
        <body>
            <p>Der er kommet nyt metadata:</p>
            <pre>'.$metadata.'</pre>
        </body>
        </html>';


    if(!mail($mail, $subject, $body, $headers)) {
        return "error_mail_not_send";
    }

    return 'mail_send';
}



if(isset($this->data['send_mail']) && $this->data['send_mail'] == TRUE) {
    if(isset($this->data['mail'])) {
        $msg = mailMetadata($this->data['mail'], $this->data['metadataflat']);
    }
}

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
    $("#metadataxml").hide();
    $("#metadatajson").hide();

    $("#showhide").click(function() {
        $("#metadataxml").toggle("slow");
        return true;
    });
    $("#showhidejson").click(function() {
        $("#metadatajson").toggle("slow");
        return true;
    });
});
</script>';
$this->includeAtTemplateBase('includes/header.php');
?>

<h2><?php echo $this->data['header']; ?></h2>

<?php
if(isset($msg)) {
    echo '<p>'. $this->t($msg) .'</p>';
}
?>

<p><b><?php echo $this->t('export_intro'); ?></b><br />
   <?php echo $this->t('export_text'); ?></p>

<pre class="metadatabox"><?php echo $this->data['metadataflat']; ?></pre>

<br />

<?php
echo '<a href="'. SimpleSAML_Utilities::selfURL().'&amp;send_mail">' . $this->t('send_mail') . '</a>';
?>
<br />
<a id="showhide"><?= $this->t('show_xml') ?></a>
<div id="metadataxml">
<pre class="metadatabox"><?php echo $this->data['metadata']; ?></pre>
</div>
<br />
<a id="showhidejson"><?= $this->t('show_json') ?></a>
<div id="metadatajson">
    <pre class="metadatabox"><?php echo $this->data['metadatajson']; ?></pre>
</div>



<br />
<br />

<?php
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/editentity.php') .'?eid='. $this->data['eid'] .'&amp;revisionid='. $this->data['revision'].'">' . $this->t('text_back') . '</a> - ';
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">' . $this->t('{janus:dashboard:text_dashboard}') . '</a>';

$this->includeAtTemplateBase('includes/footer.php');
?>
