<?php
/* @var $config EE_Custom_Csv_Importer_Config */
?>
<div class="padding">
    <h4>
        <?php _e('Custom CSV Importer Settings', 'event_espresso'); ?>
    </h4>
    <table class="ee-admin-two-column-layout form-table">
        <tbody>

            <tr>
                <th><?php _e("Reset Custom CSV Importer Settings?", 'event_espresso');?></th>
                <td>
                    <?php echo EEH_Form_Fields::select(__('Reset Custom CSV Importer Settings?', 'event_espresso'), 0, $yes_no_values, 'reset_custom_csv_importer', 'reset_custom_csv_importer'); ?><br/>
                    <span class="description">
                        <?php _e('Set to \'Yes\' and then click \'Save\' to confirm reset all basic and advanced Event Espresso Custom CSV Importer settings to their plugin defaults.', 'event_espresso'); ?>
                    </span>
                </td>
            </tr>

        </tbody>
    </table>

</div>

<input type='hidden' name="return_action" value="<?php echo $return_action?>">

