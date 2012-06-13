<?php
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
