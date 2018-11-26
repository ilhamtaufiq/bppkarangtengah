<?php namespace NinjaTable\FrontEnd;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpmanageninja.com
 * @since      1.0.0
 *
 * @package    ninja_tables
 * @subpackage ninja-tables/public
 */

use NinjaTables\Classes\ArrayHelper;
use NinjaTable\FrontEnd\DataProviders\DefaultProvider;
use NinjaTable\FrontEnd\DataProviders\FluentFormProvider;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ninjat-ables
 * @subpackage ninja-tables/public
 * @author     Shahjahan Jewel <cep.jewel@gmail.com>
 */
class NinjaTablePublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version )
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->registerDataProviders();
	}

	public function register_ajax_routes()
	{
		$validRoutes = array(
			'get-all-data'    => 'getAllData',
		);

		$requestedRoute = esc_attr($_REQUEST['target_action']);

		if (isset($validRoutes[$requestedRoute])) {
			$this->{$validRoutes[$requestedRoute]}();
		}
		wp_die();
	}

	public function getAllData()
	{
		$tableId = intval( ArrayHelper::get($_REQUEST, 'table_id') );
		$defaultSorting = sanitize_text_field( ArrayHelper::get($_REQUEST, 'default_sorting') );
		$tableSettings = ninja_table_get_table_settings($tableId, 'public');
		$is_ajax_table = true;
		if( ArrayHelper::get($tableSettings, 'render_type') == 'legacy_table' ) {
			$is_ajax_table = false;
		}
		$is_ajax_table = apply_filters('ninja_table_is_public_ajax_table', $is_ajax_table, $tableId);

		if( !$tableSettings || !$is_ajax_table ) {
			wp_send_json_success([], 200);
		}

		$formatted_data = ninjaTablesGetTablesDataByID($tableId, $defaultSorting);

		$formatted_data = apply_filters('ninja_tables_get_public_data', $formatted_data, $tableId);
		wp_send_json($formatted_data, 200);
		wp_die();
	}

	public function register_table_render_functions()
	{
		// register the shortcode
		$shortCodeBase = apply_filters('ninja_tables_shortcode_base', 'ninja_tables');
		add_shortcode( $shortCodeBase, array($this, 'render_ninja_table_shortcode'));
	}

	public function render_ninja_table_shortcode($atts, $content = '')
	{

		$shortCodeDefaults = array(
			'id' => false,
			'filter' => false
		);

		$shortCodeDefaults = apply_filters('ninja_tables_shortcode_defaults', $shortCodeDefaults);

		$shortCodeData = shortcode_atts($shortCodeDefaults, $atts);

		extract($shortCodeData);

		$table_id = $id;

		if(!$table_id) {
		    return;
        }

		$table = get_post($table_id);

		if(!$table || $table->post_type != 'ninja-table') {
			return;
		}
		$tableSettings = ninja_table_get_table_settings($table_id, 'public');

		$tableSettings = apply_filters(
			'ninja_tables_rendering_table_settings', $tableSettings, $shortCodeData, $table
		);

		$tableColumns = ninja_table_get_table_columns($table_id, 'public');

		if( !$tableSettings || !$tableColumns ) {
		    return;
        }

        if(isset($tableSettings['columns_only']) && is_array($tableSettings['columns_only'])) {
			$showingColumns = $tableSettings['columns_only'];
	        $formattedColumns = array();
			foreach ($tableColumns as $columnIndex => $table_column) {
				if(isset($showingColumns[$table_column['key']])) {
					$formattedColumns[] = $table_column;
				}
			}
	        $tableColumns = $formattedColumns;
        }

		$tableArray = array(
		    'table_id' => $table_id,
			'columns' => $tableColumns,
			'settings' => $tableSettings,
			'table' => $table,
			'content' => $content,
			'shortCodeData' => $shortCodeData
		);

		$tableArray = apply_filters('ninja_table_js_config', $tableArray, $filter);

		ob_start();
		do_action('ninja_tables-render-table-'.$tableSettings['library'], $tableArray);
		return ob_get_clean();
	}

	public function enqueueNinjaTableScript()
	{
		global $post;
		if(is_a( $post, 'WP_Post' ) && get_post_meta($post->ID, '_has_ninja_tables', true)) {
			$styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public.css";
			if ( is_rtl() ) {
				$styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public-rtl.css";
			}
			wp_enqueue_style(
				'footable_styles',
				$styleSrc,
				array(),
				$this->version,
				'all'
			);
		}
	}

	protected function registerDataProviders()
	{
        $defaultProvider = new DefaultProvider();
        $defaultProvider->boot();
		$fluentFromProvider = new FluentFormProvider();
		$fluentFromProvider->boot();
	}
}
