<?php
/**
 * Página de configuración del plugin Schema Master
 */
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap schema-master-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="schema-master-container">
        <form method="post" action="options.php">
            <?php
            // Mostrar campos de configuración
            settings_fields($this->opciones_grupo);
            do_settings_sections('schema-master');
            
            // Botón de submit
            submit_button(__('Guardar Configuraciones', 'schema-master'));
            ?>
        </form>

        <div class="schema-master-sidebar">
            <div class="schema-master-box">
                <h2><?php _e('Ayuda y Documentación', 'schema-master'); ?></h2>
                <p><?php _e('Aquí encontrarás información sobre cómo usar los schemas correctamente:', 'schema-master'); ?></p>
                <ul>
                    <li><?php _e('- Los schemas son configuraciones de metadatos para motores de búsqueda', 'schema-master'); ?></li>
                    <li><?php _e('- Usa JSON-LD válido de schema.org', 'schema-master'); ?></li>
                    <li><?php _e('- Los schemas personalizados tienen prioridad sobre los globales', 'schema-master'); ?></li>
                </ul>
                <a href="#" class="button"><?php _e('Ver Documentación Completa', 'schema-master'); ?></a>
            </div>

            <div class="schema-master-box">
                <h2><?php _e('Validador de Schemas', 'schema-master'); ?></h2>
                <textarea id="schema-validator" rows="5" placeholder="<?php _e('Pega tu JSON-LD aquí para validar', 'schema-master'); ?>"></textarea>
                <div id="schema-validation-result"></div>
                <button id="validate-schema" class="button"><?php _e('Validar Schema', 'schema-master'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const validatorTextarea = document.getElementById('schema-validator');
    const validateButton = document.getElementById('validate-schema');
    const validationResult = document.getElementById('schema-validation-result');

    validateButton.addEventListener('click', function() {
        const schemaText = validatorTextarea.value.trim();
        
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
});
</script>

<style>
.schema-master-container {
    display: flex;
    gap: 20px;
}

.schema-master-sidebar {
    width: 300px;
}

.schema-master-box {
    background: #f8f9fb;
    border: 1px solid #e2e4e7;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

#schema-validator {
    width: 100%;
    margin-bottom: 10px;
}

#schema-validation-result {
    margin-bottom: 10px;
}
</style>