<?php
/**
 * Clase de gestión del frontend para schemas
 */
class SchemaMasterFrontend {
    private $generator;

    public function __construct() {
        $this->generator = new SchemaMasterGenerator();
        
        // Añadir schemas al head
        add_action('wp_head', [$this, 'imprimir_schemas'], 99);
    }

    /**
     * Imprimir schemas en el head de la página
     */
    public function imprimir_schemas() {
        // Solo imprimir en páginas individuales
        if (!is_singular()) {
            return;
        }

        global $post;
        
        // Obtener configuraciones globales
        $configuraciones = get_option('schema_master_configuraciones', []);
        
        // Obtener tipo de post actual
        $post_type = get_post_type();
        
        // Schema personalizado de la página (tiene prioridad)
        $schema_personalizado = get_post_meta($post->ID, '_schema_personalizado', true);
        
        // Schema por defecto para el tipo de contenido
        $schema_defecto_key = 'default_' . $post_type;
        $schema_defecto = $configuraciones[$schema_defecto_key] ?? '';

        // Decidir qué schema usar
        $schema_final = $schema_personalizado ?: $schema_defecto;

        // Generar schema contextual si está vacío
        if (empty($schema_final)) {
            $schema_final = $this->generar_schema_contextual($post);
        }

        // Validar y mostrar schema
        if (!empty($schema_final)) {
            echo $this->preparar_schema_para_output($schema_final);
        }
    }

    /**
     * Generar schema contextual basado en el tipo de contenido
     * 
     * @param WP_Post $post Post actual
     * @return string Schema JSON-LD generado
     */
    private function generar_schema_contextual($post) {
        $schema_base = [
            '@context' => 'https://schema.org',
            '@type' => $this->mapear_tipo_post($post->post_type),
            'headline' => $post->post_title,
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
        ];

        // Añadir autor
        $autor = get_the_author_meta('display_name', $post->post_author);
        if ($autor) {
            $schema_base['author'] = [
                '@type' => 'Person',
                'name' => $autor
            ];
        }

        // Añadir imagen destacada
        $imagen_id = get_post_thumbnail_id($post->ID);
        if ($imagen_id) {
            $imagen = wp_get_attachment_image_src($imagen_id, 'full');
            if ($imagen) {
                $schema_base['image'] = $imagen[0];
            }
        }

        return wp_json_encode($schema_base, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mapear tipos de post de WordPress a tipos de schema
     * 
     * @param string $post_type Tipo de post de WordPress
     * @return string Tipo de schema correspondiente
     */
    private function mapear_tipo_post($post_type) {
        $mapeo = [
            'post' => 'Article',
            'page' => 'WebPage',
            'product' => 'Product',
            // Añadir más mapeos según sea necesario
        ];

        return $mapeo[$post_type] ?? 'WebPage';
    }

    /**
     * Preparar schema para su salida en el HTML
     * 
     * @param string $schema_raw Schema en formato JSON
     * @return string Schema envuelto en etiquetas de script
     */
    private function preparar_schema_para_output($schema_raw) {
        // Validar el schema
        if (!$this->generator->validar_jsonld($schema_raw)) {
            return '';
        }

        // Devolver schema envuelto en etiquetas de script
        return sprintf(
            '<script type="application/ld+json">%s</script>',
            $schema_raw
        );
    }
}