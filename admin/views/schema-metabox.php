<?php
/**
 * Meta box para schema personalizado
 */
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Crear nonce para seguridad
wp_nonce_field('schema_master_save_schema', 'schema_master_nonce');

// Obtener schema personalizado existente
$schema_personalizado = get_post_meta($post->ID, '_schema_personalizado', true);

// Obtener tipo de post actual
$post_type = $post->post_type;

// Obtener configuraciones globales
$configuraciones = get_option('schema_master_configuraciones', []);
$schema_defecto_key = 'default_' . $post_type;
$schema_defecto = $configuraciones[$schema_defecto_key] ?? '';
?>

<div class="schema-master-metabox">
    <div class="schema-actions">
        <button type="button" id="schema-generar-automatico" class="button">
            <?php _e('Generar Schema Automático', 'schema-master'); ?>
        </button>
        <button type="button" id="schema-cargar-defecto" class="button">
            <?php _e('Cargar Schema por Defecto', 'schema-master'); ?>
        </button>
    </div>

    <div class="schema-textarea-container">
        <label for="schema_personalizado">
            <strong><?php _e('Schema JSON-LD Personalizado:', 'schema-master'); ?></strong>
        </label>
        <textarea 
            id="schema_personalizado" 
            name="schema_personalizado" 
            rows="10" 
            cols="80"
            placeholder="<?php _e('Ingrese su schema JSON-LD personalizado', 'schema-master'); ?>"
        ><?php echo esc_textarea($schema_personalizado); ?></textarea>
        
        <div class="schema-help">
            <p>
                <?php _e('Deje en blanco para usar el schema por defecto. Utilice un JSON-LD válido de schema.org.', 'schema-master'); ?>
            </p>
            <div id="schema-validation-result"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const schemaTextarea = document.getElementById('schema_personalizado');
    const generarAutomaticoBtn = document.getElementById('schema-generar-automatico');
    const cargarDefectoBtn = document.getElementById('schema-cargar-defecto');
    const validationResult = document.getElementById('schema-validation-result');

    // Schema por defecto
    const schemaDefecto = <?php echo json_encode($schema_defecto ?: ''); ?>;

    // Validar schema en tiempo real
    schemaTextarea.addEventListener('input', function() {
        const schemaText = this.value.trim();
        
        if (schemaText === '') {
            validationResult.innerHTML = '';
            return;
        }

        try {
            const schemaJson = JSON.parse(schemaText);
            
            if (schemaJson['@context'] && schemaJson['@type']) {
                validationResult.innerHTML = '<div class="notice notice-success"><p>✅ Schema válido</p></div>';
            } else {
                validationResult.innerHTML = '<div class="notice notice-error"><p>❌ Schema inválido: Falta @context o @type</p></div>';
            }
        } catch (error) {
            validationResult.innerHTML = `<div class="notice notice-error"><p>❌ Error de sintaxis: ${error.message}</p></div>`;
        }
    });

    // Botón para generar schema automático
    generarAutomaticoBtn.addEventListener('click', function() {
        const postTitle = '<?php echo esc_js($post->post_title); ?>';
        const postDate = '<?php echo esc_js(get_the_date('c', $post)); ?>';
        const postModified = '<?php echo esc_js(get_the_modified_date('c', $post)); ?>';
        const postType = '<?php echo esc_js($post_type); ?>';

        const schemaAutomatico = {
            '@context': 'https://schema.org',
            '@type': postType === 'post' ? 'Article' : 'WebPage',
            'headline': postTitle,
            'datePublished': postDate,
            'dateModified': postModified
        };

        schemaTextarea.value = JSON.stringify(schemaAutomatico, null, 2);
        schemaTextarea.dispatchEvent(new Event('input')); // Validar
    });

    // Botón para cargar schema por defecto
    cargarDefectoBtn.addEventListener('click', function() {
        if (schemaDefecto) {
            schemaTextarea.value = schemaDefecto;
            schemaTextarea.dispatchEvent(new Event('input')); // Validar
        } else {
            alert('No hay schema por defecto configurado para este tipo de contenido.');
        }
    });
});
</script>

<style>
.schema-master-metabox .schema-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.schema-textarea-container textarea {
    width: 100%;
    font-family: monospace;
}

.schema-help {
    margin-top: 10px;
}

.schema-validation-result {
    margin-top: 10px;
}
</style>