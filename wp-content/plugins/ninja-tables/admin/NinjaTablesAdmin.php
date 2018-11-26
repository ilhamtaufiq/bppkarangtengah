<?php
/*
 * Do Not USE namespace because The Pro Add-On Used this Class
 */

use NinjaTable\TableDrivers\NinjaFooTable;
use NinjaTables\Classes\Libs\Migrations\NinjaTablesSupsysticTableMigration;
use NinjaTables\Classes\Libs\Migrations\NinjaTablesTablePressMigration;
use NinjaTables\Classes\Libs\Migrations\NinjaTablesUltimateTableMigration;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpmanageninja.com
 * @since      1.0.0
 *
 * @package    ninja-tables
 * @subpackage ninja-tables/admin
 */
class NinjaTablesAdmin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * Custom Post Type Name
     *
     * @since    1.0.0
     * @access   private
     * @var      string $cpt_name .
     */
    private $cpt_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name = 'ninja-tables', $version = NINJA_TABLES_VERSION)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->cpt_name = 'ninja-table';
    }

    /**
     * Register form post types
     *
     * @return void
     */
    public function register_post_type()
    {
        register_post_type($this->cpt_name, array(
            'label' => __('Ninja Tables', 'ninja-tables'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'query_var' => false,
            'supports' => array('title'),
            'labels' => array(
                'name' => __('Ninja Tables', 'ninja-tables'),
                'singular_name' => __('Table', 'ninja-tables'),
                'menu_name' => __('Ninja Tables', 'ninja-tables'),
                'add_new' => __('Add Table', 'ninja-tables'),
                'add_new_item' => __('Add New Table', 'ninja-tables'),
                'edit' => __('Edit', 'ninja-tables'),
                'edit_item' => __('Edit Table', 'ninja-tables'),
                'new_item' => __('New Table', 'ninja-tables'),
                'view' => __('View Table', 'ninja-tables'),
                'view_item' => __('View Table', 'ninja-tables'),
                'search_items' => __('Search Table', 'ninja-tables'),
                'not_found' => __('No Table Found', 'ninja-tables'),
                'not_found_in_trash' => __('No Table Found in Trash', 'ninja-tables'),
                'parent' => __('Parent Table', 'ninja-tables'),
            ),
        ));
    }


    /**
     * Adds a settings page link to a menu
     *
     * @link  https://codex.wordpress.org/Administration_Menus
     * @since 1.0.0
     * @return void
     */
    public function add_menu()
    {
        global $submenu;
        $capability = ninja_table_admin_role();

        // Continue only if the current user has
        // the capability to manage ninja tables
        if (!$capability) {
            return;
        }

        // Top-level page
        $menuName = __('NinjaTables', 'ninja-tables');
        if (defined('NINJATABLESPRO')) {
            $menuName .= ' Pro';
        }

        add_menu_page(
            $menuName,
            $menuName,
            $capability,
            'ninja_tables',
            array($this, 'main_page'),
            ninja_table_get_icon_url(),
            25
        );

        $submenu['ninja_tables']['all_tables'] = array(
            __('All Tables', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/',
        );

        $submenu['ninja_tables']['tools'] = array(
            __('Tools', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/tools',
            '',
            'ninja_table_tools_menu'
        );

        $submenu['ninja_tables']['import'] = array(
            __('Import a Table', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/tools',
            '',
            'ninja_table_import_menu'
        );

        if (!defined('NINJATABLESPRO')) {
            $submenu['ninja_tables']['upgrade_pro'] = array(
                __('<span style="color:#f39c12;">Get Pro</span>', 'ninja-tables'),
                $capability,
                'https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade_menu',
                '',
                'ninja_table_upgrade_menu'
            );
        } elseif (defined('NINJATABLESPRO_SORTABLE')) {
            $license = get_option('_ninjatables_pro_license_status');
            if ($license != 'valid') {
                $submenu['ninja_tables']['activate_license'] = array(
                    '<span style="color:#f39c12;">Activate License</span>',
                    $capability,
                    'admin.php?page=ninja_tables#/tools?active_menu=license',
                    '',
                    'ninja_table_license_menu'
                );
            }
        }

        $submenu['ninja_tables']['help'] = array(
            __('Help', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/help'
        );
    }

    public function main_page()
    {
        $this->enqueue_data_tables_scripts();

        include(plugin_dir_path(__FILE__) . 'partials/wp_data_tables_display.php');
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        $vendorSrc = plugin_dir_url(__DIR__) . "assets/css/ninja-tables-vendor.css";

        if (is_rtl()) {
            $vendorSrc = plugin_dir_url(__DIR__) . "assets/css/ninja-tables-vendor-rtl.css";
        }

        wp_enqueue_style(
            $this->plugin_name . '-vendor',
            $vendorSrc,
            [],
            $this->version,
            'all'
        );

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__DIR__) . "assets/css/ninja-tables-admin.css",
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        if (function_exists('wp_enqueue_editor')) {
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__DIR__) . "assets/js/ninja-tables-admin.js",
            array('jquery'),
            $this->version,
            false
        );

        $fluentUrl = admin_url('plugin-install.php?s=FluentForm&tab=search&type=term');

        $isInstalled = defined('FLUENTFORM') || defined('NINJATABLESPRO');
        $dismissed = false;
        $dismissedTime = get_option('_ninja_tables_plugin_suggest_dismiss');

        if ($dismissedTime) {
            if ((time() - intval($dismissedTime)) < 518400) {
                $dismissed = true;
            }
        } else {
            $dismissed = true;
            update_option('_ninja_tables_plugin_suggest_dismiss', time() - 345600);
        }

        $currentUser = wp_get_current_user();

        $leadStatus = false;
        $reviewOptinStatus = false;
        $tableCount = wp_count_posts($this->cpt_name);
        $totalPublishedTable = 0;
        if ($tableCount && $tableCount->publish > 1) {
            $leadStatus = apply_filters('ninja_tables_show_lead', $leadStatus);
        } else if($tableCount->publish > 2) {
            $reviewOptinStatus = apply_filters('ninja_tables_show_review_optin', $reviewOptinStatus);
        }

        if ($tableCount && $tableCount->publish > 0) {
            $totalPublishedTable = $tableCount->publish;
        }

        $hasFluentFrom = defined('FLUENTFORM_VERSION');
        $isFluentFromUpdated = false;

        // check for right version
        if ($hasFluentFrom) {
            if ($fluentVersionCompare = version_compare(FLUENTFORM_VERSION, '1.7.4') >= 1) {
                $isFluentFromUpdated = true;
            }
        }

        wp_localize_script($this->plugin_name, 'ninja_table_admin', array(
            'img_url' => plugin_dir_url(__DIR__) . "assets/img/",
            'fluentform_url' => $fluentUrl,
            'fluent_wp_url' => 'https://wordpress.org/plugins/fluentform/',
            'fluent_form_icon' => getNinjaFluentFormMenuIcon(),
            'dismissed' => $dismissed,
            'show_lead_pop_up' => $leadStatus,
            'show_review_dialog' => $reviewOptinStatus,
            'current_user_name' => $currentUser->display_name,
            'isInstalled' => $isInstalled,
            'hasPro' => defined('NINJATABLESPRO'),
            'hasFluentForm' => $hasFluentFrom,
            'isFluentFormUpdated' => $isFluentFromUpdated,
            'hasAdvancedFilters' => class_exists('NinjaTablesPro\CustomFilters'),
            'hasSortable' => defined('NINJATABLESPRO_SORTABLE'),
            'ace_path_url' => plugin_dir_url(__DIR__) . "assets/libs/ace",
            'upgradeGuide' => 'https://wpmanageninja.com/r/docs/ninja-tables/how-to-install-and-upgrade/#upgrade',
            'hasValidLicense' => get_option('_ninjatables_pro_license_status'),
            'i18n' => \NinjaTables\Classes\I18nStrings::getStrings(),
            'published_tables' => $totalPublishedTable,
            'preview_required_scripts' => array(
                plugin_dir_url(__DIR__) . "assets/css/ninjatables-public.css",
                plugin_dir_url(__DIR__) . "public/libs/footable/js/footable.min.js",
            ),
            'activated_features' => apply_filters('ninja_table_activated_features', array(
                'default_tables' => true,
                'fluentform_tables' => true
            ))
        ));

        // Elementor plugin have a bug where they throw error to parse #url, and I really don't know why they want to parse
        // other plugin's page's uri. They should fix it.
        // For now I am de-registering their script in ninja-table admin pages.
        wp_deregister_script('elementor-admin-app');
    }

    public function enqueue_data_tables_scripts()
    {
        $this->enqueue_scripts();
        $this->enqueue_styles();
    }

    public function ajax_routes()
    {
        if (!ninja_table_admin_role()) {
            return;
        }

        $valid_routes = array(
            'get-all-tables' => 'getAllTables',
            'store-a-table' => 'storeTable',
            'delete-a-table' => 'deleteTable',
            'import-table' => 'importTable',
            'import-table-from-plugin' => 'importTableFromPlugin',
            'get-tables-from-plugin' => 'getTablesFromPlugin',
            'update-table-settings' => 'updateTableSettings',
            'get-table-settings' => 'getTableSettings',
            'get-table-data' => 'getTableData',
            'store-table-data' => 'storeData',
            'edit-data' => 'editData',
            'delete-data' => 'deleteData',
            'upload-data' => 'uploadData',
            'duplicate-table' => 'duplicateTable',
            'export-data' => 'exportData',
            'dismiss_fluent_suggest' => 'dismissPluginSuggest',
            'save_custom_css' => 'saveCustomCSS',
            'get_access_roles' => 'getAccessRoles',
            'get_table_preview_html' => 'getTablePreviewHtml',
            'set-external-data-source' => 'createTableWithExternalDataSource',
            'get-fluentform-forms' => 'getFluentformForms',
            'set-fluent-form-data-source' => 'createTableWithFluentFormDataSource',
            'get_wp_post_types' => 'getAllPostTypes',
            'save_wp_post_data_source' => 'createTableWithWPPostDataSource',
            'install_fluent_form' => 'installFluentForm'
        );

        $requested_route = $_REQUEST['target_action'];
        if (isset($valid_routes[$requested_route])) {
            $this->{$valid_routes[$requested_route]}();
        }

        wp_die();
    }

    public function getAllTables()
    {
        $perPage = intval($_REQUEST['per_page']) ?: 10;

        $currentPage = intval($_GET['page']);

        $skip = $perPage * ($currentPage - 1);

        $args = array(
            'posts_per_page' => $perPage,
            'offset' => $skip,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_type' => $this->cpt_name,
            'post_status' => 'any',
        );

        if (isset($_REQUEST['search']) && $_REQUEST['search']) {
            $args['s'] = sanitize_text_field($_REQUEST['search']);
        }

        $tables = get_posts($args);

        foreach ($tables as $table) {
            $table->preview_url = site_url('?ninjatable_preview=' . $table->ID);
            $dataSourceType = ninja_table_get_data_provider($table->ID);
            $table->dataSourceType = $dataSourceType;
            if ($dataSourceType == 'fluent-form') {
                $fluentFormFormId = get_post_meta($table->ID, '_ninja_tables_data_provider_ff_form_id', true);
                if ($fluentFormFormId) {
                    $table->fluentfrom_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $fluentFormFormId);
                }
            }
        }

        $tables = apply_filters('ninja_tables_get_all_tables', $tables);

        $total = wp_count_posts('ninja-table');
        $total = intval($total->publish);
        $lastPage = ceil($total / $perPage);

        wp_send_json(array(
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => ($lastPage) ? $lastPage : 1,
            'data' => $tables,
        ), 200);
    }

    public function storeTable()
    {
        if (!$_REQUEST['post_title']) {
            wp_send_json_error(array(
                'message' => __('The name field is required.', 'ninja-tables')
            ), 423);
        }

        wp_send_json(array(
            'table_id' => $this->saveTable($postId = intval($_REQUEST['tableId'])),
            'message' => __('Table ' . ($postId ? 'updated' : 'created') . ' successfully.', 'ninja-tables')
        ), 200);
    }

    protected function saveTable($postId = null)
    {
        $attributes = array(
            'post_title' => sanitize_text_field($_REQUEST['post_title']),
            'post_content' => wp_kses_post($_REQUEST['post_content']),
            'post_type' => $this->cpt_name,
            'post_status' => 'publish'
        );

        if (!$postId) {
            $postId = wp_insert_post($attributes);
        } else {
            $attributes['ID'] = $postId;
            wp_update_post($attributes);
        }

        return $postId;
    }

    public function importTable()
    {
        $format = $_REQUEST['format'];

        if ($format == 'csv') {
            $this->uploadTableCsv();
        } elseif ($format == 'json') {
            $this->uploadTableJson();
        } elseif ($format == 'ninjaJson') {
            $this->uploadTableNinjaJson();
        }

        wp_send_json(array(
            'message' => __('No appropriate driver found for the import format.',
                'ninja-tables')
        ), 423);
    }


    public function saveCustomCSS()
    {
        $tableId = intval($_REQUEST['table_id']);
        $css = $_REQUEST['custom_css'];
        $css = wp_strip_all_tags($css);
        update_post_meta($tableId, '_ninja_tables_custom_css', $css);

        wp_send_json_success(array(
            'message' => 'Custom CSS successfully saved'
        ), 200);
    }


    private function getTablesFromPlugin()
    {
        $plugin = sanitize_text_field($_REQUEST['plugin']);
        $libraryClass = false;

        if ($plugin == 'UltimateTables') {
            $libraryClass = new NinjaTablesUltimateTableMigration();
        } elseif ($plugin == 'TablePress') {
            $libraryClass = new NinjaTablesTablePressMigration();
        } elseif ($plugin == 'supsystic') {
            $libraryClass = new NinjaTablesSupsysticTableMigration();
        } else {
            return false;
        }
        $tables = $libraryClass->getTables();

        wp_send_json(array(
            'tables' => $tables
        ), 200);
    }


    private function importTableFromPlugin()
    {
        $plugin = esc_attr($_REQUEST['plugin']);
        $tableId = intval($_REQUEST['tableId']);

        if ($plugin == 'UltimateTables') {
            $libraryClass = new NinjaTablesUltimateTableMigration();
        } elseif ($plugin == 'TablePress') {
            $libraryClass = new NinjaTablesTablePressMigration();
        } elseif ($plugin == 'supsystic') {
            $libraryClass = new NinjaTablesSupsysticTableMigration();
        } else {
            return false;
        }

        $tableId = $libraryClass->migrateTable($tableId);
        if (is_wp_error($tableId)) {
            wp_send_json_error(array(
                'message' => $tableId->get_error_message()
            ), 423);
        }

        $message = __(
            'Successfully imported. Please go to all tables and review your newly imported table.',
            'ninja-tables'
        );

        wp_send_json_success(array(
            'message' => $message,
            'tableId' => $tableId
        ), 200);
    }

    private function formatHeader($header)
    {
        return ninja_table_format_header($header);
    }

    private function uploadTableCsv()
    {
        $tmpName = $_FILES['file']['tmp_name'];

        $reader = \League\Csv\Reader::createFromPath($tmpName)->fetchAll();

        $header = array_shift($reader);
        $reader = array_reverse($reader);

        foreach ($reader as &$item) {
            // We have to convert everything to utf-8
            foreach ($item as &$entry) {
                $entry = mb_convert_encoding($entry, 'UTF-8');
            }
        }

        $tableId = $this->createTable();

        $header = $this->formatHeader($header);

        $this->storeTableConfigWhenImporting($tableId, $header);

        $this->insertDataToTable($tableId, $reader, $header);

        wp_send_json(array(
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ));
    }

    private function uploadTableJson()
    {
        $tableId = $this->createTable();

        $tmpName = $_FILES['file']['tmp_name'];

        $content = json_decode(file_get_contents($tmpName), true);

        $header = array_keys(array_pop(array_reverse($content)));

        $formattedHeader = array();
        foreach ($header as $head) {
            $formattedHeader[$head] = $head;
        }

        $this->storeTableConfigWhenImporting($tableId, $formattedHeader);

        $this->insertDataToTable($tableId, $content, $formattedHeader);

        wp_send_json(array(
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ));
    }

    private function uploadTableNinjaJson()
    {
        $tmpName = $_FILES['file']['tmp_name'];

        $content = json_decode(file_get_contents($tmpName), true);

        // validation
        if (!$content['post'] || !$content['columns'] || !$content['settings']) {
            wp_send_json(array(
                'message' => __('You have a faulty JSON file. Please export a new one.',
                    'ninja-tables')
            ), 423);
        }

        $tableAttributes = array(
            'post_title' => sanitize_title($content['post']['post_title']),
            'post_content' => wp_kses_post($content['post']['post_content']),
            'post_type' => $this->cpt_name,
            'post_status' => 'publish'
        );

        $tableId = $this->createTable($tableAttributes);

        update_post_meta($tableId, '_ninja_table_columns', $content['columns']);

        update_post_meta($tableId, '_ninja_table_settings', $content['settings']);

        if(isset($content['data_provider']) && $content['data_provider'] != 'default') {
            $metas = $content['metas'];
            foreach ($metas as $meta_key => $meta_value) {
                update_post_meta($tableId, $meta_key, $meta_value);
            }
        } else {
            if ($rows = $content['rows']) {
                $header = [];
                foreach ($content['columns'] as $column) {
                    $header[$column['key']] = $column['name'];
                }
                $this->insertDataToTable($tableId, $rows, $header);
            }
        }

        wp_send_json(array(
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ));
    }

    private function createTable($data = null)
    {
        return wp_insert_post($data
            ? $data
            : array(
                'post_title' => __('Temporary table name', 'ninja-tables'),
                'post_content' => __('Temporary table description',
                    'ninja-tables'),
                'post_type' => $this->cpt_name,
                'post_status' => 'publish'
            ));
    }

    private function storeTableConfigWhenImporting($tableId, $header)
    {
        // ninja_table_columns
        $ninjaTableColumns = array();

        foreach ($header as $key => $name) {
            $ninjaTableColumns[] = array(
                'key' => $key,
                'name' => $name,
                'breakpoints' => ''
            );
        }

        update_post_meta($tableId, '_ninja_table_columns', $ninjaTableColumns);

        // ninja_table_settings
        $ninjaTableSettings = ninja_table_get_table_settings($tableId, 'admin');

        update_post_meta($tableId, '_ninja_table_settings', $ninjaTableSettings);

        ninjaTablesClearTableDataCache($tableId);
    }

    private function insertDataToTable($tableId, $values, $header)
    {
        $header = array_keys($header);
        $time = current_time('mysql');
        $headerCount = count($header);

        foreach ($values as $item) {
            if ($headerCount == count($item)) {
                $itemTemp = array_combine($header, $item);
            } else {
                // The item can have less/more entry than the header has.
                // We have to ensure that the header and values match.
                $itemTemp = array_combine(
                    $header,
                    // We'll get the appropriate values by merging Array1 & Array2
                    array_merge(
                    // Array1 = Only the entries that the header has.
                        array_intersect_key($item, array_fill_keys(array_values($header), null)),
                        // Array2 = The remaining header entries will be blank.
                        array_fill_keys(array_diff(array_values($header), array_keys($item)), null)
                    )
                );
            }

            $data = array(
                'table_id' => $tableId,
                'attribute' => 'value',
                'value' => json_encode($itemTemp),
                'created_at' => $time,
                'updated_at' => $time
            );

            ninja_tables_DbTable()->insert($data);
        }
    }

    public function getTableSettings()
    {
        $table = get_post($tableID = intval($_REQUEST['table_id']));

        if (!$table || $table->post_type != 'ninja-table') {
            wp_send_json_error(array(
                'message' => __('No Table Found'),
                'route' => 'home'
            ), 423);
        }

        $provider = ninja_table_get_data_provider($table->ID);

        $table = apply_filters('ninja_tables_get_table_' . $provider, $table);

        $table->custom_css = get_post_meta($tableID, '_ninja_tables_custom_css', true);

        wp_send_json(array(
            'preview_url' => site_url('?ninjatable_preview=' . $tableID),
            'columns' => ninja_table_get_table_columns($tableID, 'admin'),
            'settings' => ninja_table_get_table_settings($tableID, 'admin'),
            'table' => $table,
        ), 200);
    }

    public function updateTableSettings()
    {
        $tableId = intval($_REQUEST['table_id']);

        $tableColumns = array();

        if (isset($_REQUEST['columns'])) {
            $rawColumns = $_REQUEST['columns'];
            if ($rawColumns && is_array($rawColumns)) {
                foreach ($rawColumns as $column) {
                    foreach ($column as $column_index => $column_value) {
                        if ($column_index == 'header_html_content' || $column_index == 'selections' || $column_index == 'wp_post_custom_data_value') {
                            $column[$column_index] = wp_kses_post($column_value);
                        } else {
                            if (is_array($column_value)) {
                                $column[$column_index] = ninja_tables_sanitize_array($column_value);
                            } else if (is_int($column_value)) {
                                $column[$column_index] = intval($column_value);
                            } else if (is_bool($column_value)) {
                                $column[$column_index] = $column_value;
                            } else {
                                $column[$column_index] = esc_html($column_value);
                            }
                        }
                    }
                    $tableColumns[] = $column;
                }
                $tableColumns = apply_filters('ninja_table_update_columns_' . ninja_table_get_data_provider($tableId), $tableColumns, $rawColumns, $tableId);
                do_action('ninja_table_before_update_columns_' . ninja_table_get_data_provider($tableId), $tableColumns, $rawColumns, $tableId);
                update_post_meta($tableId, '_ninja_table_columns', $tableColumns);
            }
        }

        $formattedTablePreference = array();

        if (isset($_REQUEST['table_settings'])) {
            $tablePreference = $_REQUEST['table_settings'];
            if ($tablePreference && is_array($tablePreference)) {
                foreach ($tablePreference as $key => $tab_pref) {
                    if ($tab_pref == 'false') {
                        $tab_pref = false;
                    }

                    if ($tab_pref == 'true') {
                        $tab_pref = true;
                    }

                    if (is_array($tab_pref)) {
                        $tab_pref = array_map('sanitize_text_field', $tab_pref);
                    } else {
                        $tab_pref = sanitize_text_field($tab_pref);
                    }

                    $formattedTablePreference[$key] = $tab_pref;
                }
                update_post_meta($tableId, '_ninja_table_settings', $formattedTablePreference);
            }
        }

        ninjaTablesClearTableDataCache($tableId);

        wp_send_json(array(
            'message' => __('Successfully updated configuration.', 'ninja-tables'),
            'columns' => $tableColumns,
            'settings' => $formattedTablePreference
        ), 200);
    }

    public function getTable()
    {
        $tableId = intval($_REQUEST['id']);
        $table = get_post($tableId);

        wp_send_json(array(
            'data' => $table
        ), 200);
    }

    public function deleteTable()
    {
        $tableId = intval($_REQUEST['table_id']);

        if (get_post_type($tableId) != $this->cpt_name) {
            wp_send_json(array(
                'message' => __('Invalid Table to Delete', 'ninja-tables')
            ), 300);
        }


        wp_delete_post($tableId, true);
        // Delete the post metas
        delete_post_meta($tableId, '_ninja_table_columns');
        delete_post_meta($tableId, '_ninja_table_settings');
        delete_post_meta($tableId, '_ninja_table_cache_object');
        // now delete the data
        try {
            ninja_tables_DbTable()->where('table_id', $tableId)->delete();
        } catch (Exception $e) {
            //
        }

        wp_send_json(array(
            'message' => __('Successfully deleted the table.', 'ninja-tables')
        ), 200);
    }

    public function getTableData()
    {
        $perPage = intval($_REQUEST['per_page']) ?: 10;
        $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $skip = $perPage * ($currentPage - 1);
        $tableId = intval($_REQUEST['table_id']);
        $search = esc_attr($_REQUEST['search']);

        $dataSourceType = ninja_table_get_data_provider($tableId);

        if ($dataSourceType == 'default') {
            list($orderByField, $orderByType) = $this->getTableSortingParams($tableId);

            $query = ninja_tables_DbTable()->where('table_id', $tableId);

            if ($search) {
                $query->search($search, array('value'));
            }

            $data = $query->take($perPage)
                ->skip($skip)
                ->orderBy($orderByField, $orderByType)
                ->get();

            $total = ninja_tables_DbTable()->where('table_id', $tableId)->count();

            $response = array();

            foreach ($data as $item) {
                $response[] = array(
                    'id' => $item->id,
                    'position' => property_exists($item, 'position') ? $item->position : null,
                    'values' => json_decode($item->value, true)
                );
            }
        } else {
            list($response, $total) = apply_filters(
                'ninja_tables_get_table_data_' . $dataSourceType,
                array(array(), 0),
                $tableId,
                $perPage,
                $skip
            );
        }

        // Needed for other data source providers
        list($response, $total) = apply_filters(
            'ninja_tables_get_table_data',
            array($response, $total),
            $tableId,
            $perPage,
            $skip
        );

        wp_send_json(array(
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => ceil($total / $perPage),
            'data' => $response,
            'data_source' => $dataSourceType
        ), 200);
    }

    /**
     * Get the order by field and order by type values.
     *
     * @param        $tableId
     * @param  null $tableSettings
     *
     * @return array
     */
    protected function getTableSortingParams($tableId, $tableSettings = null)
    {
        $tableSettings = $tableSettings ?: ninja_table_get_table_settings($tableId, 'admin');

        $orderByField = 'id';
        $orderByType = 'DESC';

        if (isset($tableSettings['sorting_type'])) {
            if ($tableSettings['sorting_type'] === 'manual_sort') {
                $this->migrateDatabaseIfNeeded();
                $orderByField = 'position';
                $orderByType = 'ASC';
            } elseif ($tableSettings['sorting_type'] === 'by_created_at') {
                $orderByField = 'id';
                if ($tableSettings['default_sorting'] === 'new_first') {
                    $orderByType = 'DESC';
                } else {
                    $orderByType = 'ASC';
                }
            }
        }

        return [$orderByField, $orderByType];
    }

    public function storeData()
    {
        $tableId = intval($_REQUEST['table_id']);
        $row = $_REQUEST['row'];
        $formattedRow = array();

        foreach ($row as $key => $item) {
            $formattedRow[$key] = wp_unslash($item);
        }

        $attributes = array(
            'table_id' => $tableId,
            'attribute' => 'value',
            'value' => json_encode($formattedRow, true),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($id = intval($_REQUEST['id'])) {
            ninja_tables_DbTable()->where('id', $id)->update($attributes);
        } else {
            $attributes['created_at'] = date('Y-m-d H:i:s');

            $attributes = apply_filters('ninja_tables_item_attributes', $attributes);

            $id = $insertId = ninja_tables_DbTable()->insert($attributes);
        }

        $item = ninja_tables_DbTable()->find($id);

        ninjaTablesClearTableDataCache($tableId);

        wp_send_json(array(
            'message' => __('Successfully saved the data.', 'ninja-tables'),
            'item' => array(
                'id' => $item->id,
                'values' => $formattedRow,
                'row' => json_decode($item->value),
                'position' => property_exists($item, 'position') ? $item->position : null
            )
        ), 200);
    }

    public function deleteData()
    {
        $tableId = intval($_REQUEST['table_id']);

        $id = $_REQUEST['id'];

        $ids = is_array($id) ? $id : array($id);

        $ids = array_map(function ($item) {
            return intval($item);
        }, $ids);

        ninja_tables_DbTable()->where('table_id', $tableId)->whereIn('id', $ids)->delete();

        ninjaTablesClearTableDataCache($tableId);

        wp_send_json(array(
            'message' => __('Successfully deleted data.', 'ninja-tables')
        ), 200);
    }

    public function uploadData()
    {
        $tableId = intval($_REQUEST['table_id']);
        $tmpName = $_FILES['file']['tmp_name'];

        $reader = \League\Csv\Reader::createFromPath($tmpName)->fetchAll();

        $csvHeader = array_shift($reader);
        $csvHeader = array_map('esc_attr', $csvHeader);

        $config = get_post_meta($tableId, '_ninja_table_columns', true);
        if (!$config) {
            wp_send_json(array(
                'message' => __('Please set table configuration.', 'ninja-tables')
            ), 423);
        }

        $header = array();

        foreach ($csvHeader as $item) {
            foreach ($config as $column) {
                $item = esc_attr($item);
                if ($item == $column['key'] || $item == $column['name']) {
                    $header[] = $column['key'];
                }
            }
        }

        if (count($header) != count($config)) {
            wp_send_json(array(
                'message' => __('Please use the provided CSV header structure.', 'ninja-tables')
            ), 423);
        }

        $data = array();
        $time = current_time('mysql');

        foreach ($reader as $item) {
            // If item has any ascii entry we'll convert it to utf-8
            foreach ($item as &$entry) {
                $entry = mb_convert_encoding($entry, 'UTF-8');
            }

            $itemTemp = array_combine($header, $item);

            array_push($data, array(
                'table_id' => $tableId,
                'attribute' => 'value',
                'value' => json_encode($itemTemp),
                'created_at' => $time,
                'updated_at' => $time
            ));
        }

        $replace = $_REQUEST['replace'] === 'true';

        if ($replace) {
            ninja_tables_DbTable()->where('table_id', $tableId)->delete();
        }

        $data = apply_filters('ninja_tables_import_table_data', $data, $tableId);

        ninja_tables_DbTable()->batch_insert($data);

        ninjaTablesClearTableDataCache($tableId);

        wp_send_json(array(
            'message' => __('Successfully uploaded data.', 'ninja-tables')
        ));
    }

    public function exportData()
    {
        $format = esc_attr($_REQUEST['format']);

        $tableId = intval($_REQUEST['table_id']);

        $tableTitle = get_the_title($tableId);

        $fileName = sanitize_title($tableTitle, 'Export-Table-' . date('Y-m-d-H-i-s'), 'preview');

        $tableColumns = ninja_table_get_table_columns($tableId, 'admin');

        $tableSettings = ninja_table_get_table_settings($tableId, 'admin');

        $data = ninjaTablesGetTablesDataByID($tableId);

        if ($format == 'csv') {

            $header = array();

            foreach ($tableColumns as $item) {
                $header[$item['key']] = $item['name'];
            }

            $exportData = array();

            foreach ($data as $item) {
                $temp = array();
                foreach ($header as $accessor => $name) {
                    $temp[] = $item[$accessor];
                }
                array_push($exportData, $temp);
            }
            $this->exportAsCSV(array_values($header), $exportData, $fileName . '.csv');
        } elseif ($format == 'json') {
            $table = get_post($tableId);

            $exportData = array(
                'post' => $table,
                'columns' => $tableColumns,
                'settings' => $tableSettings,
                'data_provider' => ninja_table_get_data_provider($tableId),
                'metas' => array(
                    '_ninja_tables_data_provider' => get_post_meta($tableId, '_ninja_tables_data_provider', true),
                    '_ninja_wp_posts_query_extra' => get_post_meta($tableId, '_ninja_wp_posts_query_extra', true),
                    '_ninja_table_wpposts_ds_where' => get_post_meta($tableId, '_ninja_table_wpposts_ds_where', true),
                    '_ninja_table_wpposts_ds_post_types' => get_post_meta($tableId, '_ninja_table_wpposts_ds_post_types', true),
                    '_ninja_tables_data_provider_url' => get_post_meta($tableId, '_ninja_tables_data_provider_url', true),
                    '_ninja_tables_data_provider_ff_form_id' => get_post_meta($tableId, '_ninja_tables_data_provider_url', true)
                ),
                'rows' => $data,
            );

            $this->exportAsJSON($exportData, $fileName . '.json');
        }
    }

    private function exportAsCSV($header, $data, $fileName = null)
    {
        $fileName = ($fileName) ? $fileName : 'export-data-' . date('d-m-Y') . '.csv';

        $writer = \League\Csv\Writer::createFromFileObject(new SplTempFileObject());
        $writer->setDelimiter(",");
        $writer->setNewline("\r\n");
        $writer->insertOne($header);
        $writer->insertAll($data);
        $writer->output($fileName);
        die();
    }

    private function exportAsJSON($data, $fileName = null)
    {
        $fileName = ($fileName) ? $fileName : 'export-data-' . date('d-m-Y') . '.json';

        header('Content-disposition: attachment; filename=' . $fileName);

        header('Content-type: application/json');

        echo json_encode($data);

        die();
    }

    public function add_tabales_to_editor()
    {
        if (user_can_richedit()) {
            $pages_with_editor_button = array('post.php', 'post-new.php');
            foreach ($pages_with_editor_button as $editor_page) {
                add_action("load-{$editor_page}", array($this, 'init_ninja_mce_buttons'));
            }
        }
    }

    public function init_ninja_mce_buttons()
    {
        add_filter("mce_external_plugins", array($this, 'ninja_table_add_button'));
        add_filter('mce_buttons', array($this, 'ninja_table_register_button'));
        add_action('admin_footer', array($this, 'pushNinjaTablesToEditorFooter'));
    }

    public function pushNinjaTablesToEditorFooter()
    {
        $tables = $this->getAllTablesForMce();
        ?>
        <script type="text/javascript">
            window.ninja_tables_tiny_mce = {
                label: '<?php _e('Select a Table to insert', 'ninja-tables') ?>',
                title: '<?php _e('Insert Ninja Tables Shortcode', 'ninja-tables') ?>',
                select_error: '<?php _e('Please select a table'); ?>',
                insert_text: '<?php _e('Insert Shortcode', 'ninja-tables'); ?>',
                tables: <?php echo json_encode($tables);?>
            }
        </script>
        <?php
    }

    private function getAllTablesForMce()
    {
        $args = array(
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_type' => $this->cpt_name,
            'post_status' => 'any'
        );

        $tables = get_posts($args);
        $formatted = array();

        $title = __('Select a Table', 'ninja-tables');
        if (!$tables) {
            $title = __('No Tables found. Please add a table first');
        }
        $formatted[] = array(
            'text' => $title,
            'value' => ''
        );

        foreach ($tables as $table) {
            $formatted[] = array(
                'text' => $table->post_title,
                'value' => $table->ID
            );
        }

        return $formatted;
    }

    /**
     * add a button to Tiny MCE editor
     *
     * @param $plugin_array
     *
     * @return mixed
     */
    public function ninja_table_add_button($plugin_array)
    {
        $plugin_array['ninja_table'] = NINJA_TABLES_DIR_URL . 'assets/js/ninja-table-tinymce-button.js';

        return $plugin_array;
    }

    /**
     * register a button to Tiny MCE editor
     *
     * @param $buttons
     *
     * @return mixed
     */
    public function ninja_table_register_button($buttons)
    {
        array_push($buttons, 'ninja_table');

        return $buttons;
    }

    public function dismissPluginSuggest()
    {
        update_option('_ninja_tables_plugin_suggest_dismiss', time());
    }

    /**
     * Save a flag if the a post/page/cpt have [ninja_tables] shortcode
     *
     * @param int $post_id
     *
     * @return void
     */
    public function saveNinjaTableFlagOnShortCode($post_id)
    {
        if (isset($_POST['post_content'])) {
            $post_content = $_POST['post_content'];
        } else {
            $post = get_post($post_id);
            $post_content = $post->post_content;
        }
        if (has_shortcode($post_content, 'ninja_tables')) {
            update_post_meta($post_id, '_has_ninja_tables', 1);
        } elseif (get_post_meta($post_id, '_has_ninja_tables', true)) {
            update_post_meta($post_id, '_has_ninja_tables', 0);
        }
    }

    public function duplicateTable()
    {
        $oldPostId = intval($_REQUEST['tableId']);

        $post = get_post($oldPostId);

        // Duplicate table itself.
        $attributes = array(
            'post_title' => $post->post_title . '( Duplicate )',
            'post_content' => $post->post_content,
            'post_type' => $post->post_type,
            'post_status' => 'publish'
        );

        $newPostId = wp_insert_post($attributes);

        global $wpdb;

        // Duplicate table settings.
        $postMetaTable = $wpdb->prefix . 'postmeta';

        $sql = "INSERT INTO $postMetaTable (`post_id`, `meta_key`, `meta_value`)";
        $sql .= " SELECT $newPostId, `meta_key`, `meta_value` FROM $postMetaTable WHERE `post_id` = $oldPostId";

        $wpdb->query($sql);

        // Duplicate table rows.
        $itemsTable = $wpdb->prefix . ninja_tables_db_table_name();

        $sql = "INSERT INTO $itemsTable (`position`, `table_id`, `attribute`, `value`, `created_at`, `updated_at`)";
        $sql .= " SELECT `position`, $newPostId, `attribute`, `value`, `created_at`, `updated_at` FROM $itemsTable";
        $sql .= " WHERE `table_id` = $oldPostId";

        $wpdb->query($sql);

        wp_send_json_success(array(
            'message' => __('Successfully duplicated table.', 'ninja-tables'),
            'table_id' => $newPostId
        ), 200);
    }

    public function getAccessRoles()
    {
        $roles = get_editable_roles();
        $formatted = array();
        $excludedRoles = array('subscriber', 'administrator');
        foreach ($roles as $key => $role) {
            if (!in_array($key, $excludedRoles)) {
                $formatted[] = array(
                    'name' => $role['name'],
                    'key' => $key
                );
            }
        }

        $capability = get_option('_ninja_tables_permission');

        if (is_string($capability)) {
            $capability = [];
        }

        wp_send_json(array(
            'capability' => $capability,
            'roles' => $formatted
        ), 200);
    }

    public function getTablePreviewHtml()
    {
        $tableId = intval($_REQUEST['table_id']);
        $tableColumns = ninja_table_get_table_columns($tableId, 'public');
        $tableSettings = ninja_table_get_table_settings($tableId, 'public');

        $formattedColumns = [];
        foreach ($tableColumns as $index => $column) {
            $formattedColumns[] = NinjaFooTable::getFormattedColumn($column, $index, $tableSettings, true,
                'by_created_at');
        }
        $formatted_data = ninjaTablesGetTablesDataByID($tableId, $tableSettings['default_sorting'], true, 25);

        if (count($formatted_data) > 25) {
            $formatted_data = array_slice($formatted_data, 0, 25);
        }

        echo self::loadView('public/views/table_inner_html', array(
            'table_columns' => $formattedColumns,
            'table_rows' => $formatted_data
        ));
    }

    private static function loadView($file, $data)
    {
        $file = NINJA_TABLES_DIR_PATH . $file . '.php';
        ob_start();
        extract($data);
        include $file;

        return ob_get_clean();
    }

    public function migrateDatabaseIfNeeded()
    {
        // If the database is already migrated for manual
        // sorting the option table would have a flag.
        $option = '_ninja_tables_sorting_migration';
        global $wpdb;
        $tableName = $wpdb->prefix . ninja_tables_db_table_name();

        $row = $wpdb->get_row("SELECT * FROM $tableName");

        if (!$row) {
            return;
        }
        if (property_exists($row, 'position')) {
            return;
        }

        // Update the databse to hold the sorting position number.
        $sql = "ALTER TABLE $tableName ADD COLUMN `position` INT(11) AFTER `id`;";

        $wpdb->query($sql);
        // Keep a flag on the options table that the
        // db is migrated to use for manual sorting.
        update_option($option, true);
    }

    public function getFluentformForms()
    {
        if (function_exists('wpFluentForm')) {
            wpFluentForm('FluentForm\App\Modules\Form\Form')->index();
        }
    }

    public function createTableWithFluentFormDataSource()
    {
        $tableId = $_REQUEST['table_Id'];
        $formId = $_REQUEST['form']['id'];

        $messages = array();

        if (!$formId) {
            // Validate Title
            if (empty( $_REQUEST['post_title'] ) ) {
                $messages['title'] = __('The title field is required.', 'ninja-tables');
            }
        }

        // Validate Columns
        $fields = isset($_REQUEST['form']['fields']) ? $_REQUEST['form']['fields'] : array();
        if (!($fields = ninja_tables_sanitize_array($fields))) {
            $messages['fields'] = __('No fields were selected.', 'ninja-tables');
        }

        // If Validation failed
        if (array_filter($messages)) {
            wp_send_json_error(array('message' => $messages), 422);
            wp_die();
        }

        $headers = array();
        foreach ($fields as $field) {
            $headers[] = reset(array_values($field));
        }

        $headers = $this->formatHeader($headers);
        $columns = array();
        foreach ($headers as $key => $column) {
            $columns[] = array(
                'name' => $column,
                'key' => $key,
                'breakpoints' => null,
                'data_type' => 'text',
                'dateFormat' => null,
                'header_html_content' => null,
                'enable_html_content' => false,
                'contentAlign' => null,
                'textAlign' => null,
                'original_name' => $column
            );
        }

        if ($tableId) {
            $oldColumns = get_post_meta($tableId, '_ninja_table_columns', true);
            foreach ($columns as $key => $newColumn) {
                foreach ($oldColumns as $oldColumn) {
                    if ($oldColumn['original_name'] == $newColumn['original_name']) {
                        $columns[$key] = $oldColumn;
                    }
                }
            }

            // Reset/Reorder array indices
            $columns = array_values($columns);
        } else {
            $tableId = $this->saveTable();
        }

        update_post_meta($tableId, '_ninja_table_columns', $columns);
        update_post_meta($tableId, '_ninja_tables_data_provider', 'fluent-form');
        update_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', $formId);

        wp_send_json_success(array('table_id' => $tableId, 'form_id' => $_REQUEST['form']['id']));
    }

    public function getAllAuthors()
    {
        $allUsers = get_users('orderby=post_count&order=DESC');

        foreach ($allUsers as $key => $currentUser) {
            if (in_array('subscriber', $currentUser->roles)) {
                unset($allUsers[$key]);
            } else {
                $allUsers[$key] = $currentUser->data;
            }
        }

        return $allUsers;
    }

    public function getAllPostTypes()
    {
        global $wpdb;

        $authors = $this->getAllAuthors();
        $postStatuses = ninjaTablesGetPostStatuses();
        $post_fields = $wpdb->get_col("DESC {$wpdb->prefix}posts");
        $post_types = array_diff(get_post_types(), ['ninja-table']);

        foreach ($post_types as $type) {
            $taxonomies = get_object_taxonomies($type);
            $taxonomies = array_combine($taxonomies, $taxonomies);

            foreach ($taxonomies as $taxonomy) {
                $taxonomies[$taxonomy] = get_terms([
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ]);
            }

            $post_types[$type] = array(
                'taxonomies' => $taxonomies,
                'fields' => array_map(function ($taxonomy) use ($type) {
                    return "{$type}.{$taxonomy}";
                }, array_keys($taxonomies))
            );
        }

        wp_send_json_success(
            compact('post_fields', 'post_types', 'authors', 'postStatuses'), 200
        );
    }

    public function installFluentForm()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to install a plugin, Please ask your administrator to install WP Fluent Form')
            ), 423);
            return;
        }

        if (is_multisite()) {
            wp_send_json_error(array(
                'message' => __('You are using wp multisite environment so please install WP FluentForm manually')
            ), 423);
            return;
        }

        $result = $this->install_plugin('fluentform', 'fluentform.php');
        $status = !is_wp_error($result);

        if ($status) {
            wp_send_json_success(array(
                'message' => __('WP Fluent Form successfully installed and activated, You are redirecting to WP Fluent Form Now'),
                'redirect_url' => admin_url('admin.php?page=fluent_forms')
            ), 200);
            return;
        } else {
            wp_send_json_error(array(
                'message' => __('There was an error to install the plugin. Please install the plugin manually.')
            ), 423);
            return;
        }
    }

    public function install_plugin($slug, $file)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin_basename = $slug . '/' . $file;

        // if exists and not activated
        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_basename)) {
            return activate_plugin($plugin_basename);
        }

        // seems like the plugin doesn't exists. Download and activate it
        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());

        $api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            return $result;
        }

        return activate_plugin($plugin_basename);
    }

}
