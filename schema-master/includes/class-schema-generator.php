<?php
/**
 * Clase principal de generación de Schemas
 */
if (!defined('ABSPATH')) {
    exit;
}

class SchemaMasterGenerator {
    // Tipos de schemas soportados
    private $tipos_schema = [
        'Article' => 'Artículo',
        'Product' => 'Producto',
        'LocalBusiness' => 'Negocio Local',
        'Event' => 'Evento',
        'Organization' => 'Organización',
        'WebPage' => 'Página Web',
        'Person' => 'Persona'
    ];

    /**
     * Validar y generar un schema
     * 
     * @param string $tipo Tipo de schema
     * @param array $datos Datos para generar el schema
     * @return array|false Schema generado o false si no es válido
     */
    public function generar_schema($tipo, $datos) {
        // Validar que el tipo de schema exista
        if (!isset($this->tipos_schema[$tipo])) {
            return false;
        }

        // Schema base
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $tipo
        ];

        // Agregar datos
        foreach ($datos as $key => $value) {
            // Sanitizar y validar datos
            $schema[$key] = $this->sanitizar_valor($value);
        }

        return $schema;
    }

    /**
     * Sanitizar y validar un valor para schema
     * 
     * @param mixed $valor Valor a sanitizar
     * @return mixed Valor sanitizado
     */
    private function sanitizar_valor($valor) {
        // Lógica de sanitización según el tipo de dato
        if (is_string($valor)) {
            return sanitize_text_field($valor);
        }
        
        if (is_numeric($valor)) {
            return floatval($valor);
        }

        // Manejar arrays y objetos
        if (is_array($valor)) {
            return array_map([$this, 'sanitizar_valor'], $valor);
        }

        return $valor;
    }

    /**
     * Convertir schema a JSON-LD
     * 
     * @param array $schema Schema a convertir
     * @return string JSON-LD
     */
    public function schema_to_jsonld($schema) {
        // Validar schema
        if (!is_array($schema)) {
            return false;
        }

        // Convertir a JSON-LD
        return wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Obtener tipos de schemas disponibles
     * 
     * @return array Tipos de schemas
     */
    public function get_tipos_schema() {
        return $this->tipos_schema;
    }

    /**
     * Validar un schema JSON-LD
     * 
     * @param string $jsonld Cadena JSON-LD
     * @return bool Si el JSON es válido
     */
    public function validar_jsonld($jsonld) {
        // Intentar decodificar el JSON
        $decoded = json_decode($jsonld, true);
        
        // Verificar estructura básica
        return $decoded !== null 
            && isset($decoded['@context']) 
            && isset($decoded['@type']);
    }
}