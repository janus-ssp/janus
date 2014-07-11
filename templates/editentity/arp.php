<div id="arp" class="arp_tab">
    <?php
    $arp = $this->data['entity']->getArpAttributes();
    $arpConfiguration = $this->data['arp_attributes_configuration'];

    function attributeNameValuePairId($arpAttrName, $value)
    {
        return str_replace(array(':', '*', '-', '.'), '_', $arpAttrName . '_' . $value);
    }

    ?>

    <input type="checkbox" id="arp_no_arp_attributes" name="arp_no_arp_attributes" value="arp_no_arp_attributes"
        <?php echo($arp === null ? 'checked="checked"' : ''); ?> />
    <label for="arp_no_arp_attributes"><?php echo $this->t('text_arp_no_arp'); ?></label>
    <hr/>
    <div id="arp_attributes" <?php echo($arp === null ? 'style="display: none;"' : ''); ?>>
        <h4><?php echo $this->t('text_arp_attributes'); ?></h4>
        <table class="arp_attributes_table">
            <thead>
            <tr>
                <th class="arpAttributeName">Name</th>
                <th class="arpAttributeEnabled">Enabled</th>
                <th class="arpMatchingRule">Matching rule</th>
                <th class="arpAddSpecifyValue"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->data['arp_attributes_configuration'] AS $label => $attribute): ?>
                <?php
                $arpAttributeUsed = $arp !== null && array_key_exists($attribute['name'], $arp);
                $arpSpecifyValues = (isset($attribute['specify_values']) && $attribute['specify_values']);
                $arpAttrName = htmlentities($attribute['name'], ENT_QUOTES, "UTF-8");
                ?>
                <tr class="attribute_select_row">
                    <td>
                        <label><?php echo htmlentities($label, ENT_QUOTES, "UTF-8"); ?></label>
                    </td>
                    <td data-specify-values="<?php echo $arpSpecifyValues ? 'true' : 'false'; ?>">
                        <?php if ($arpAttributeUsed): ?>
                            <?php foreach ($arp[$attribute['name']] as $value): ?>
                                <div id="<?php echo attributeNameValuePairId($arpAttrName, $value); ?>"
                                     data-attribute-name="<?php echo $arpAttrName; ?>"
                                    <?php echo $arpSpecifyValues ? 'data-attribute-specify-value="true"' : ''; ?>
                                    >
                                    <input type="checkbox" name="arp_attribute_enabled" checked/>
                                    <label class="arpSpecifiedValue"><?php echo $value ?></label>
                                    <input type="hidden" name="arp_attributes[<?php echo $arpAttrName ?>][]"
                                           value="<?php echo $value ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div id="<?php echo attributeNameValuePairId($arpAttrName, '*'); ?>"
                                 data-attribute-name="<?php echo $arpAttrName; ?>"
                                <?php echo $arpSpecifyValues ? 'data-attribute-specify-value="true"' : ''; ?>
                                >
                                <input type="checkbox" name="arp_attribute_enabled"/>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td data-matching-rule="true">
                        <?php if ($arpAttributeUsed): ?>
                            <?php foreach ($arp[$attribute['name']] as $value): ?>
                                <?php
                                $wildCard = ($value === '*');
                                $prefixMatch = (!$wildCard && substr($value, -strlen('*')) === '*');
                                ?>
                                <div id="<?php echo attributeNameValuePairId($arpAttrName, $value) . '_match_rule'; ?>">
                                    <label>
                                        <?php if ($wildCard): ?>
                                            Wildcard
                                        <?php elseif ($prefixMatch): ?>
                                            Prefix
                                        <?php else: ?>
                                            Exact
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td class="center">
                        <?php if ($arpSpecifyValues): ?>
                            <a class="nonDecorated" href="#" data-add-specify-value="true">
                                <img alt="Add" src="resources/images/pm_plus_16.png"/>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>
