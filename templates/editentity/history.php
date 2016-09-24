<?php
/** @var sspmod_janus_Entity $entity */
$entity = $this->data['entity'];
/** @var array[] $revisions */
$revisions = $this->data['revisions'];
?>
<div id="history">
    <h2><?php echo $this->t('tab_edit_entity_history'); ?></h2>
    <input id="show_all_changes" type="checkbox" />
    <label for="show_all_changes"><?php echo $this->t('tab_edit_entity_show_hide_all_revision_compare') ?></label>
    <input id="latestRevisionNbr" type="hidden" value="<?php echo $this->data['latestRevisionNbr']; ?>" />

        <script type="text/javascript">//<![CDATA[
            var jsonCompareRevisions = {};
            <?php
            foreach ($revisions as $revisionInfo):
                /** @var \Janus\ServiceRegistry\Entity\Connection\Revision $revision */
                $revision = $revisionInfo['revision']; ?>
            jsonCompareRevisions[<?php echo $revision->getRevisionNr(); ?>] = <?php echo $revisionInfo['json']; ?>;
            <?php endforeach; ?>
        //]]></script>

        <?php foreach ($revisions as $revisionInfo): ?>
            <?php
            /** @var \Janus\ServiceRegistry\Entity\Connection\Revision $revision */
            $revision = $revisionInfo['revision'];
            ?>
            <div class="revision">
                <table class="revision">
                    <tr>
                        <td class="revisionLink">
                            <a class="janus_button"
                               href="?eid=<?php echo $revision->getConnection()->getId(); ?>&amp;revisionid=<?php echo $revision->getRevisionNr(); ?>">Revision <?php echo $revision->getRevisionNr(); ?></a>
                        </td>
                        <td class="revisionAttributes">Revision notes:</td>
                        <td><?php echo $revision->getRevisionNote() ? htmlspecialchars($revision->getRevisionNote()) : 'No revision notes'; ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>User:</td>
                        <td><?php echo $revision->getUpdatedByUser() ? $revision->getUpdatedByUser()->getUsername() : '-'; ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Last update:</td>
                        <td><?php echo $revision->getCreatedAtDate()->format('Y-m-d H:i'); ?></td>
                    </tr>
                    <tr>
                        <?php
                        $labelId = 'label_' . $revision->getConnection()->getId() . '_' . $revision->getRevisionNr() . 'id';
                        ?>
                        <td>
                            <?php if ($revision->getRevisionNr() > 0): ?>
                                <input id="<?php echo $labelId; ?>"
                                       class="toggle_show_changes"
                                       type="checkbox"
                                       data-revision-nbr="<?php echo $revision->getRevisionNr(); ?>" />
                                <label
                                    for="<?php echo $labelId; ?>"><?php echo $this->t('tab_edit_entity_show_hide_revision_compare') ?></label>
                            <?php endif; ?>
                        </td>
                        <td>Status:</td>
                        <td><?php echo $revision->getState(); ?></td>
                    </tr>
                </table>
                <?php if ($revision->getRevisionNr() > 0): ?>
                    <div id="compare_revisions_content_<?php echo $revision->getRevisionNr(); ?>" class="hidden compareRevisionsContent">
                        <span style="font-style: italic;">
                            <?php echo $this->t('tab_edit_entity_revision_compare') . ' (revision ' . ($revision->getRevisionNr()-1) . ' versus revision ' . ($revision->getRevisionNr()) . ')'; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <div id="history_pagination_controls" style="width: 50%">
        <br style="clear: both" />
        <?php if (isset($this->data['history_prev_offset'])): ?>
        <a style="display: inline-block; float: left"
           href="editentity.php?eid=<?= htmlspecialchars(urlencode($entity->getEid())) ?>&amp;selectedtab=8&amp;history_offset=<?= $this->data['history_prev_offset'] ?>#history">&leftarrow; Later revisions</a>
        <?php endif ?>
        <?php if (isset($this->data['history_next_offset'])): ?>
        <a style="display: inline-block; float: right"
           href="editentity.php?eid=<?= htmlspecialchars(urlencode($entity->getEid())); ?>&amp;selectedtab=8&amp;history_offset=<?= $this->data['history_next_offset'] ?>#history">Earlier revisions &rightarrow;</a>
        <?php endif ?>
    </div>
</div>
