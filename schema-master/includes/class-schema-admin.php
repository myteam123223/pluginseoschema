<?php
/**
 * Clase de administración del plugin
 */
if (!defined('ABSPATH')) {
    exit;
}

class SchemaMasterAdmin {
    private $generator;
    private $opciones_grupo = 'schema_master_options';

    public function __construct() {
        $this->generator = new SchemaMasterGenerator();
        
        // Hooks de administración
        add_action('admin_menu', [$this, 'agregar_pagina_admin']);
        add_action('admin_init', [$this, 'registrar_configuraciones']);
        add_action('add_meta_boxes', [$this, 'agregar_meta_box_schema']);
        add_action('save_post', [$this, 'guardar_schema_personalizado'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'cargar_scripts_admin']);
    }

    /**
     * Agregar página de administración
     */
    public function agregar_pagina_admin() {
        // Página principal
        add_menu_page(
            'Schema Master', 
            'Schema Master', 
            'manage_options', 
            'schema-master', 
            [$this, 'renderizar_pagina_ajustes_generales'],
            'dashicons-share-alt',
            99
        );
        
        // Submenús
        add_submenu_page(
            'schema-master',
            __('Ajustes Generales', 'schema-master'),
            __('Ajustes Generales', 'schema-master'),
            'manage_options',
            'schema-master',
            [$this, 'renderizar_pagina_ajustes_generales']
        );
        
        add_submenu_page(
            'schema-master',
            __('Schema Auto', 'schema-master'),
            __('Schema Auto', 'schema-master'),
            'manage_options',
            'schema-master-auto',
            [$this, 'renderizar_pagina_schema_auto']
        );
        
        add_submenu_page(
            'schema-master',
            __('Schema Personalizado', 'schema-master'),
            __('Schema Personalizado', 'schema-master'),
            'manage_options',
            'schema-master-personalizado',
            [$this, 'renderizar_pagina_schema_personalizado']
        );
    }

    /**
     * Renderizar página de ajustes generales
     */
    public function renderizar_pagina_ajustes_generales() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'schema-master'));
        }

        // Incluir vista de ajustes generales
        include SCHEMA_MASTER_PATH . 'admin/views/schema-settings-page.php';
    }
    
    /**
     * Renderizar página de schema auto
     */
    public function renderizar_pagina_schema_auto() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'schema-master'));
        }

        // Incluir vista de schema auto
        include SCHEMA_MASTER_PATH . 'admin/views/schema-auto-page.php';
    }
    
    /**
     * Renderizar página de schema personalizado
     */
    public function renderizar_pagina_schema_personalizado() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'schema-master'));
        }

        // Incluir vista de schema personalizado
        include SCHEMA_MASTER_PATH . 'admin/views/schema-personalizado-page.php';
    }

    /**
     * Registrar configuraciones del plugin
     */
    public function registrar_configuraciones() {
        register_setting(
            $this->opciones_grupo, 
            'schema_master_configuraciones',
            [$this, 'sanitizar_configuraciones']
        );

        // Sección de configuración global
        add_settings_section(
            'schema_configuracion_global', 
            __('Configuración Global de Schemas', 'schema-master'), 
            [$this, 'seccion_configuracion_global'], 
            'schema-master'
        );

        // Campos para cada tipo de schema
        foreach ($this->generator->get_tipos_schema() as $tipo => $etiqueta) {
            add_settings_field(
                'schema_default_' . strtolower($tipo), 
                sprintf(__('Schema por Defecto - %s', 'schema-master'), $etiqueta), 
                [$this, 'campo_schema_defecto'], 
                'schema-master', 
                'schema_configuracion_global',
                ['tipo' => $tipo, 'etiqueta' => $etiqueta]
            );
        }
    }

    /**
     * Descripción de la sección de configuración global
     */
    public function seccion_configuracion_global() {
        echo '<p>' . __('Configure los schemas por defecto para diferentes tipos de contenido', 'schema-master') . '</p>';
    }

    /**
     * Campo para schema por defecto
     */
    public function campo_schema_defecto($args) {
        $configuraciones = get_option('schema_master_configuraciones', []);
        $tipo = $args['tipo'];
        $etiqueta = $args['etiqueta'];
        $schema_actual = $configuraciones['default_' . strtolower($tipo)] ?? '';
        ?>
        <textarea 
            name="schema_master_configuraciones[default_<?php echo strtolower($tipo); ?>]" 
            rows="5" 
            cols="50"
            placeholder="<?php printf(__('Ingrese JSON-LD para %s', 'schema-master'), $etiqueta); ?>"
        ><?php echo esc_textarea($schema_actual); ?></textarea>
        <p class="description">
            <?php printf(__('Schema por defecto para %s', 'schema-master'), $etiqueta); ?>
        </p>
        <?php
    }

    /**
     * Sanitizar configuraciones
     */
    public function sanitizar_configuraciones($input) {
        $output = [];

        // Sanitizar cada configuración de schema
        foreach ($this->generator->get_tipos_schema() as $tipo => $etiqueta) {
            $key = 'default_' . strtolower($tipo);
            
            if (isset($input[$key])) {
                // Validar que sea un JSON-LD válido
                $jsonld = trim($input[$key]);
                if ($this->generator->validar_jsonld($jsonld)) {
                    $output[$key] = $jsonld;
                } else {
                    // Añadir mensaje de error
                    add_settings_error(
                        'schema_master_configuraciones', 
                        'invalid_jsonld', 
                        sprintf(__('El schema para %s no es un JSON-LD válido', 'schema-master'), $etiqueta)
                    );
                }
            }
        }

        return $output;
    }

    /**
     * Agregar meta box de schema en entradas/páginas
     */
    public function agregar_meta_box_schema() {
        $pantallas = ['post', 'page', 'product'];
        
        foreach ($pantallas as $pantalla) {
            add_meta_box(
                'schema_personalizado', 
                __('Schema Personalizado', 'schema-master'), 
                [$this, 'renderizar_meta_box_schema'], 
                $pantalla, 
                'normal', 
                'high'
            );
        }
    }

    /**
     * Renderizar meta box de schema
     */
    public function renderizar_meta_box_schema($post) {
        // Incluir vista de meta box
        include SCHEMA_MASTER_PATH . 'admin/views/schema-metabox.php';
    }

    /**
     * Guardar schema personalizado
     */
    public function guardar_schema_personalizado($post_id, $post) {
        // Verificaciones de seguridad
        if (
            !isset($_POST['schema_master_nonce']) || 
            !wp_verify_nonce($_POST['schema_master_nonce'], 'schema_master_save_schema')
        ) {
            return;
        }

        // Omitir autoguardado
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Guardar schema personalizado
        if (isset($_POST['schema_personalizado'])) {
            $schema_personalizado = trim($_POST['schema_personalizado']);
            
            if ($this->generator->validar_jsonld($schema_personalizado)) {
                update_post_meta($post_id, '_schema_personalizado', $schema_personalizado);
            } else if (empty($schema_personalizado)) {
                // Si está vacío, también lo guardamos (para eliminar schemas anteriores)
                update_post_meta($post_id, '_schema_personalizado', '');
            }
        }
    }
    
    /**
     * Cargar scripts y estilos para la administración
     */
    public function cargar_scripts_admin($hook) {
        // Solo cargar en las páginas del plugin
        if (strpos($hook, 'schema-master') === false && $hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }

        // Registrar y encolar estilos
        wp_enqueue_style(
            'schema-master-admin', 
            SCHEMA_MASTER_URL . 'admin/css/schema-admin.css', 
            [], 
            SCHEMA_MASTER_VERSION
        );

        // Registrar y encolar scripts
        wp_enqueue_script(
            'schema-master-admin', 
            SCHEMA_MASTER_URL . 'admin/js/schema-admin.js', 
            ['jquery'], 
            SCHEMA_MASTER_VERSION, 
            true
        );
    }
} // Cierre de la clase SchemaMasterAdmin