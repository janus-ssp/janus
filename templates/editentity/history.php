<div id="history">
    <?php
    $revisions = $this->data['revisions'];
    ?>
    <h2><?php echo $this->t('tab_edit_entity_history'); ?></h2>
    <input id="show_all_changes" type="checkbox">
    <label for="show_all_changes"><?php echo $this->t('tab_edit_entity_show_hide_all_revision_compare') ?></label>
    <input id="latestRevisionNbr" type="hidden" value="<?php echo $this->data['latestRevisionNbr']; ?>">

    <p>

        <script type="text/javascript">
            var jsonCompareRevisions = {};
            <?php foreach ($revisions as $revisionInfo): ?>
            jsonCompareRevisions[<?php echo $revisionInfo['revision']->getRevisionNr(); ?>] = <?php echo $revisionInfo['json']; ?>;
            <?php endforeach; ?>
        </script>

        <?php foreach ($revisions as $revisionInfo): ?>
            <?php
            $revision = $revisionInfo['revision'];
            ?>
            <section class="revision">
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
                        $labelId = $revision->getConnection()->getId() . '_' . $revision->getRevisionNr() . 'id';
                        ?>
                        <td>
                            <?php if ($revision->getRevisionNr() != $this->data['latestRevisionNbr']): ?>
                                <input id="<?php echo $labelId; ?>" class="toggle_show_changes" type="checkbox"
                                       data-revision-nbr="<?php echo $revision->getRevisionNr(); ?>">
                                <label
                                    for="<?php echo $labelId; ?>"><?php echo $this->t('tab_edit_entity_show_hide_revision_compare') ?></label>
                            <?php endif; ?>
                        </td>
                        <td>Status:</td>
                        <td><?php echo $revision->getState(); ?></td>
                    </tr>
                </table>
                <?php if ($revision->getRevisionNr() != $this->data['latestRevisionNbr']): ?>
                    <div id="compare_revisions_content_<?php echo $revision->getRevisionNr(); ?>" class="hidden compareRevisionsContent">
                        <em>
                            <?php echo $this->t('tab_edit_entity_revision_compare') . ' (revision ' . $revision->getRevisionNr() . ' versus revision ' . ($revision->getRevisionNr() + 1) . ')'; ?>
                        </em>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
</div>