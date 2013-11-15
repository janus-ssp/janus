<div id="arp">
    <?php
//    echo $this->data['entity']->getArpAttributes();
//    echo json_encode($this->data['arp_attributes_configuration'], true);
/**
 * TODO, iterate over all configuration attributes and add table rows. Check if the value is set
 * in the current attributes and if so make hidden input fields for this.
 *
 * ARP.delete = remove hidden input field
 * ARP.add = add hidden input field
 * <input type="hidden" name="arp_attributes[urn:mace:dir:attribute-def:eduPersonAffiliation][]" value="*">

 *
 * Also make visible when there is a value for an attribute
 * We need a + sign for those attributes (use-data type) that are selectable
 *
 * In the controller construct the arp - setting it to null if no POST data is there
 *
 * Use a special checkbox with name to detect NO_ARP
 * Use extensive documentation
 *
 */
    ?>


    <fieldset>
        <label><?php echo $this->t('text_attributes'); ?></label>
        <table id="arp_attributes" border="0" style="border-collapse: collapse;">
            <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Prefix Matching</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr id="attribute_select_row">
                <td class="arp_select_attribute">

                    <select id="attribute_select"
                            name="attribute_key"
                            onchange="ARP.addAttribute(this)"
                            class="attribute_selector">
                        <option value="">-- <?php echo $this->t('tab_edit_entity_select'); ?> --</option>
                        <?php foreach ($this->data['arp_attributes_configuration'] AS $label => $attribute): ?>
                            <option value="<?php echo htmlentities($attribute['name'], ENT_QUOTES, "UTF-8"); ?>">
                                <?php echo htmlentities($label, ENT_QUOTES, "UTF-8"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <script>
                        ARP.availableAttributes = <?php echo json_encode($this->data['arp_attributes_configuration']); ?>;
                    </script>
                </td>
                <td class="arp_select_attribute_value" style="display: none">
                    <input id="attribute_select_value" type="text" value="" size="50"/>
                    Prefix Match
                    <input id="attribute_is_prefix_match" type="checkbox"/>
                    <img style="display: inline"
                         alt="Add"
                         src="resources/images/pm_plus_16.png"
                         onclick="ARP.addAttribute($('#attribute_select'))"/>
                    <script type="text/javascript">
                        $('#attribute_select_value').keypress(function (e) {
                            var code = (e.keyCode ? e.keyCode : e.which);
                            if (code == 13) {
                                ARP.addAttribute($('#attribute_select'));
                                e.preventDefault();
                            }
                        });
                    </script>
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

</div>
