<?php
/**
 * Plugin Name: Schema Master
 * Plugin URI: https://seobyleo.com/schema-master
 * Description: Generador avanzado de schemas estructurados para WordPress
 * Version: 1.0.0
 * Author: Seo by Leo - Leo Ramos
 * Author URI: https://seobyleo.com
 * Text Domain: schema-master
 * Domain Path: /languages
 */

// Prevenir acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Constantes del plugin
define('SCHEMA_MASTER_VERSION', '1.0.0');
define('SCHEMA_MASTER_PATH', plugin_dir_path(__FILE__));
define('SCHEMA_MASTER_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once SCHEMA_MASTER_PATH . 'includes/class-schema-generator.php';
require_once SCHEMA_MASTER_PATH . 'includes/class-schema-admin.php';
require_once SCHEMA_MASTER_PATH . 'includes/class-schema-frontend.php';

/**
 * Clase principal del plugin
 */
class SchemaMaster {
    private static $instance = null;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Método de instancia Singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar componentes del plugin
     */
    private function init() {
        // Cargar traduciones
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Inicializar componentes
        new SchemaMasterAdmin();
        new SchemaMasterFrontend();
    }

    /**
     * Cargar dominio de texto para traducciones
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'schema-master', 
            false, 
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Activación del plugin
     */
    public static function activate() {
        // Lógica de activación
        // Crear tablas personalizadas si es necesario
        // Establecer opciones por defecto
    }

    /**
     * Desactivación del plugin
     */
    public static function deactivate() {
        // Limpiar datos si es necesario
    }
}

// Registrar hooks de activación y desactivación
register_activation_hook(__FILE__, ['SchemaMaster', 'activate']);
register_deactivation_hook(__FILE__, ['SchemaMaster', 'deactivate']);

// Inicializar el plugin
function schema_master_init() {
    SchemaMaster::get_instance();
}
add_action('plugins_loaded', 'schema_master_init');