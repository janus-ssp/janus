<?php
$this->data['head'] = '
<script type="text/javascript" src="resources/components/jquery/jquery.min.js"></script>
<script type="text/javascript" src="resources/components/jqueryui/ui/minified/jquery-ui.custom.min.js"></script>
<link rel="stylesheet" media="screen" type="text/css" href="resources/components/jqueryui/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="resources/style.css" />' . "\n";

//$this->data['head'] = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery.autosize.min.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/metadata-export.js"></script>' . "\n";

$this->includeAtTemplateBase('includes/header.php');

?>

    <h2><?php echo $this->data['header']; ?></h2>

<?php
if (isset($msg)) {
    echo '<p>' . $this->t($msg) . '</p>';
}
?>

    <p><span style="font-weight: bold;"><?php echo $this->t('export_intro'); ?></span><br />
        <?php echo $this->t('export_text'); ?></p>

    <a class="show-hide"><?php echo $this->t('show_json') ?></a>
    <div id="metadatajson">
        <textarea class="metadatabox" readonly><?php echo $this->data['metadatajson']; ?></textarea>
    </div>

    <hr />

    <a class="show-hide"><?php echo $this->t('show_php') ?></a>
    <div id="metadataphp">
        <textarea class="metadatabox" readonly><?php echo $this->data['metadataflat']; ?></textarea>
    </div>

    <hr />

    <a class="show-hide"><?php echo $this->t('show_xml') ?></a>
    <div id="metadataxml">
        <textarea class="metadatabox" readonly><?php echo $this->data['metadata']; ?></textarea>
    </div>
    <hr />

<?php
echo '<a href="' . \SimpleSAML\Module::getModuleURL('janus/editentity.php') . '?eid=' . $this->data['eid'] . '&amp;revisionid=' . $this->data['revision'] . '">' . $this->t('text_back') . '</a> - ';
echo '<a href="' . \SimpleSAML\Module::getModuleURL('janus/index.php') . '">' . $this->t('{janus:dashboard:text_dashboard}') . '</a>';

$this->includeAtTemplateBase('includes/footer.php');
