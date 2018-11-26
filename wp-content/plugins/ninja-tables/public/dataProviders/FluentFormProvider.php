<?php

namespace NinjaTable\FrontEnd\DataProviders;

use NinjaTables\Classes\ArrayHelper;

class FluentFormProvider
{
    public function boot()
    {
        add_filter('ninja_tables_get_table_fluent-form', array($this, 'getTableSettings'));
        add_filter('ninja_tables_get_table_data_fluent-form', array($this, 'getTableData'), 10, 4);
        add_filter('ninja_tables_fetching_table_rows_fluent-form', array($this, 'data'), 10, 2);
    }

    public function getTableSettings($table)
    {
        $table->isEditable = false;
        $table->dataSourceType = 'fluent-form';
        $table->isEditableMessage = 'You may edit your table settings here.';
        $table->fluentFormFormId = get_post_meta(
            $table->ID, '_ninja_tables_data_provider_ff_form_id', true
        );
        $table->isExportable = true;
        $table->isImportable = false;
        $table->isCreatedSortable = true;
        $table->isSortable = false;
        $table->hasCacheFeature = false;
        return $table;
    }

    public function getTableData($data, $tableId, $perPage = -1, $offset = 0)
    {
        if (function_exists('wpFluentForm')) {
            $formId = get_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', true);
            $entries = wpFluentForm('FluentForm\App\Modules\Entries\Entries')->_getEntries(
                intval($formId),
                isset($_GET['page']) ? intval($_GET['page']) : 1,
                intval($perPage),
                $this->getOrderBy($tableId),
                'all',
                null
            );

            $formattedEntries = array();
            foreach ($entries['submissions']['data'] as $key => $value) {
                $formattedEntries[] = array(
                    'id' => $value->id,
                    'position' => $key,
                    'values' => $value->user_inputs
                );
            }

            return array(
                $formattedEntries,
                $entries['submissions']['paginate']['total']
            );
        }

        return $data;
    }

    public function data($data, $tableId)
    {
        if (function_exists('wpFluentForm')) {
            $formId = get_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', true);

            $entryLimit = apply_filters('ninja_tables_fluentform_per_page', -1, $tableId, $formId);
            $orderBy = apply_filters('ninja_tables_fluentform_order_by', $this->getOrderBy($tableId), $tableId, $formId);
            $entryStatus = apply_filters('ninja_tables_fluentform_entry_status', 'all', $tableId, $formId);

            $entries = wpFluentForm('FluentForm\App\Modules\Entries\Entries')->_getEntries(
                intval($formId), -1, $entryLimit, $orderBy, $entryStatus, null
            );

            $formattedEntries = array();
            foreach ($entries['submissions']['data'] as $key => $value) {
                // @todo: We should only return the data those are available on column settings
                // At least in the public data facing function
                $formattedEntries[] = $value->user_inputs;
            }
            return $formattedEntries;
        }
    }

    private function getOrderBy($tableId)
    {
        $tableSettings = get_post_meta($tableId, '_ninja_table_settings', true);
        if(ArrayHelper::get($tableSettings, 'default_sorting') == 'old_first') {
            return 'ASC';
        } else {
            return 'DESC';
        }
    }
}
