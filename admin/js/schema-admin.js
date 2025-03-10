document.addEventListener('DOMContentLoaded', function() {
    // Validador de schemas global
    var schemaValidators = document.querySelectorAll('.schema-validator');
    
    schemaValidators.forEach(function(validatorTextarea) {
        var validateButton = validatorTextarea.nextElementSibling;
        var validationResult = validateButton && validateButton.nextElementSibling;

        if (validateButton && validationResult) {
            validateButton.addEventListener('click', function() {
                var schemaText = validatorTextarea.value.trim();
                
                try {
                    var schemaJson = JSON.parse(schemaText);
                    
                    if (schemaJson['@context'] && schemaJson['@type']) {
                        validationResult.innerHTML = '<div class="notice notice-success"><p>✅ Schema válido</p></div>';
                    } else {
                        validationResult.innerHTML = '<div class="notice notice-error"><p>❌ Schema inválido: Falta @context o @type</p></div>';
                    }
                } catch (error) {
                    validationResult.innerHTML = '<div class="notice notice-error"><p>❌ Error de sintaxis: ' + 
                        (error.message || 'Error desconocido') + '</p></div>';
                }
            });
        }
    });

    // Generador de schemas dinámico
    var schemaTypeSelect = document.getElementById('schema-type-selector');
    var schemaFieldsContainer = document.getElementById('schema-dynamic-fields');

    if (schemaTypeSelect && schemaFieldsContainer) {
        schemaTypeSelect.addEventListener('change', function() {
            var selectedType = schemaTypeSelect.value;
            
            // Limpiar campos anteriores
            schemaFieldsContainer.innerHTML = '';

            // Generar campos según el tipo de schema
            switch(selectedType) {
                case 'Article':
                    schemaFieldsContainer.innerHTML = `
                        <div class="schema-field">
                            <label>Título del Artículo</label>
                            <input type="text" name="schema_article_title" />
                        </div>
                        <div class="schema-field">
                            <label>Autor</label>
                            <input type="text" name="schema_article_author" />
                        </div>
                    `;
                    break;
                case 'Product':
                    schemaFieldsContainer.innerHTML = `
                        <div class="schema-field">
                            <label>Nombre del Producto</label>
                            <input type="text" name="schema_product_name" />
                        </div>
                        <div class="schema-field">
                            <label>Precio</label>
                            <input type="number" name="schema_product_price" step="0.01" />
                        </div>
                        <div class="schema-field">
                            <label>Disponibilidad</label>
                            <select name="schema_product_availability">
                                <option value="InStock">En Stock</option>
                                <option value="OutOfStock">Agotado</option>
                                <option value="PreOrder">Pre-orden</option>
                            </select>
                        </div>
                    `;
                    break;
                case 'LocalBusiness':
                    schemaFieldsContainer.innerHTML = `
                        <div class="schema-field">
                            <label>Nombre del Negocio</label>
                            <input type="text" name="schema_business_name" />
                        </div>
                        <div class="schema-field">
                            <label>Dirección</label>
                            <input type="text" name="schema_business_address" />
                        </div>
                        <div class="schema-field">
                            <label>Teléfono</label>
                            <input type="tel" name="schema_business_phone" />
                        </div>
                    `;
                    break;
                default:
                    schemaFieldsContainer.innerHTML = '<p>Seleccione un tipo de schema para ver los campos</p>';
            }
        });
    }

    // Generador de JSON-LD en tiempo real
    var dynamicSchemaGenerator = document.getElementById('dynamic-schema-generator');
    
    if (dynamicSchemaGenerator) {
        var schemaOutputTextarea = document.getElementById('dynamic-schema-output');
        var schemaFields = dynamicSchemaGenerator.querySelectorAll('input, select');

        function generateJsonLd() {
            var formData = new FormData(dynamicSchemaGenerator);
            var schemaData = {};

            for (var pair of formData.entries()) {
                // Convertir nombres de campos a estructura de schema
                var key = pair[0];
                var value = pair[1];
                var schemaKey = key.replace(/^schema_\w+_/, '');
                schemaData[schemaKey] = value;
            }

            var schemaType = dynamicSchemaGenerator.getAttribute('data-schema-type') || 'WebPage';

            var fullSchema = {
                '@context': 'https://schema.org',
                '@type': schemaType
            };

            // Spread de schemaData
            Object.keys(schemaData).forEach(function(key) {
                fullSchema[key] = schemaData[key];
            });

            if (schemaOutputTextarea) {
                schemaOutputTextarea.value = JSON.stringify(fullSchema, null, 2);
            }
        }

        // Agregar listeners a todos los campos
        schemaFields.forEach(function(field) {
            field.addEventListener('input', generateJsonLd);
        });
    }

    // Copiar schema al portapapeles
    var copySchemaButtons = document.querySelectorAll('.copy-schema-btn');
    
    copySchemaButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var targetSelector = button.getAttribute('data-target');
            if (targetSelector) {
                var targetTextarea = document.querySelector(targetSelector);
                
                if (targetTextarea) {
                    targetTextarea.select();
                    document.execCommand('copy');
                    
                    // Mostrar tooltip de copiado
                    var originalText = button.textContent;
                    button.textContent = 'Copiado!';
                    setTimeout(function() {
                        button.textContent = originalText;
                    }, 2000);
                }
            }
        });
    });
});