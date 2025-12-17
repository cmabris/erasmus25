<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de Idioma de Validación
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas de idioma contienen los mensajes de error
    | predeterminados utilizados por la clase validador. Algunas de estas
    | reglas tienen múltiples versiones como las reglas de tamaño.
    | Siéntase libre de ajustar cada uno de estos mensajes aquí.
    |
    */

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other sea :value.',
    'active_url' => 'El campo :attribute debe ser una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y números.',
    'any_of' => 'El campo :attribute no es válido.',
    'array' => 'El campo :attribute debe ser un array.',
    'ascii' => 'El campo :attribute solo debe contener caracteres alfanuméricos y símbolos de un solo byte.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El campo :attribute debe tener entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'can' => 'El campo :attribute contiene un valor no autorizado.',
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'contains' => 'El campo :attribute no contiene un valor requerido.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute debe coincidir con el formato :format.',
    'decimal' => 'El campo :attribute debe tener :decimal lugares decimales.',
    'declined' => 'El campo :attribute debe ser rechazado.',
    'declined_if' => 'El campo :attribute debe ser rechazado cuando :other sea :value.',
    'different' => 'El campo :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute tiene dimensiones de imagen inválidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'doesnt_contain' => 'El campo :attribute no debe contener ninguno de los siguientes: :values.',
    'doesnt_end_with' => 'El campo :attribute no debe terminar con uno de los siguientes: :values.',
    'doesnt_start_with' => 'El campo :attribute no debe comenzar con uno de los siguientes: :values.',
    'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'encoding' => 'El campo :attribute debe estar codificado en :encoding.',
    'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes: :values.',
    'enum' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'extensions' => 'El campo :attribute debe tener una de las siguientes extensiones: :values.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute debe tener un valor.',
    'gt' => [
        'array' => 'El campo :attribute debe tener más de :value elementos.',
        'file' => 'El campo :attribute debe ser mayor que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string' => 'El campo :attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe tener :value elementos o más.',
        'file' => 'El campo :attribute debe ser mayor o igual que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o más.',
    ],
    'hex_color' => 'El campo :attribute debe ser un color hexadecimal válido.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'in_array' => 'El campo :attribute debe existir en :other.',
    'in_array_keys' => 'El campo :attribute debe contener al menos una de las siguientes claves: :values.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El campo :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El campo :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El campo :attribute debe ser una cadena JSON válida.',
    'list' => 'El campo :attribute debe ser una lista.',
    'lowercase' => 'El campo :attribute debe estar en minúsculas.',
    'lt' => [
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
        'file' => 'El campo :attribute debe ser menor que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'string' => 'El campo :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El campo :attribute no debe tener más de :value elementos.',
        'file' => 'El campo :attribute debe ser menor o igual que :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o menos.',
    ],
    'mac_address' => 'El campo :attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
        'file' => 'El campo :attribute no debe ser mayor que :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'max_digits' => 'El campo :attribute no debe tener más de :max dígitos.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'file' => 'El campo :attribute debe ser de al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser de al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => 'El campo :attribute debe tener al menos :min dígitos.',
    'missing' => 'El campo :attribute no debe estar presente.',
    'missing_if' => 'El campo :attribute no debe estar presente cuando :other sea :value.',
    'missing_unless' => 'El campo :attribute no debe estar presente a menos que :other sea :value.',
    'missing_with' => 'El campo :attribute no debe estar presente cuando :values esté presente.',
    'missing_with_all' => 'El campo :attribute no debe estar presente cuando :values estén presentes.',
    'multiple_of' => 'El campo :attribute debe ser un múltiplo de :value.',
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato del campo :attribute no es válido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'El campo :attribute debe contener al menos una letra.',
        'mixed' => 'El campo :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe contener al menos un número.',
        'symbols' => 'El campo :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'El :attribute proporcionado ha aparecido en una filtración de datos. Por favor, elija un :attribute diferente.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
    'present_if' => 'El campo :attribute debe estar presente cuando :other sea :value.',
    'present_unless' => 'El campo :attribute debe estar presente a menos que :other sea :value.',
    'present_with' => 'El campo :attribute debe estar presente cuando :values esté presente.',
    'present_with_all' => 'El campo :attribute debe estar presente cuando :values estén presentes.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other sea :value.',
    'prohibited_if_accepted' => 'El campo :attribute está prohibido cuando :other sea aceptado.',
    'prohibited_if_declined' => 'El campo :attribute está prohibido cuando :other sea rechazado.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
    'regex' => 'El formato del campo :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe contener entradas para: :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other sea :value.',
    'required_if_accepted' => 'El campo :attribute es obligatorio cuando :other sea aceptado.',
    'required_if_declined' => 'El campo :attribute es obligatorio cuando :other sea rechazado.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values esté presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values estén presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no esté presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values esté presente.',
    'same' => 'El campo :attribute debe coincidir con :other.',
    'size' => [
        'array' => 'El campo :attribute debe contener :size elementos.',
        'file' => 'El campo :attribute debe ser de :size kilobytes.',
        'numeric' => 'El campo :attribute debe ser :size.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes: :values.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'timezone' => 'El campo :attribute debe ser una zona horaria válida.',
    'unique' => 'El :attribute ya está en uso.',
    'uploaded' => 'El :attribute no se pudo subir.',
    'uppercase' => 'El campo :attribute debe estar en mayúsculas.',
    'url' => 'El campo :attribute debe ser una URL válida.',
    'ulid' => 'El campo :attribute debe ser un ULID válido.',
    'uuid' => 'El campo :attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Líneas de Idioma de Validación Personalizadas
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar mensajes de validación personalizados para
    | atributos usando la convención "attribute.rule" para nombrar las líneas.
    | Esto hace que sea rápido especificar una línea de idioma personalizada
    | específica para una regla de atributo determinada.
    |
    */

    'custom' => [
        'code' => [
            'required' => 'El código del programa es obligatorio.',
            'string' => 'El código del programa debe ser una cadena de texto.',
            'max' => 'El código del programa no puede exceder 255 caracteres.',
            'unique' => 'Ya existe un programa con este código.',
        ],
        'name' => [
            'required' => 'El nombre del programa es obligatorio.',
            'string' => 'El nombre del programa debe ser una cadena de texto.',
            'max' => 'El nombre del programa no puede exceder 255 caracteres.',
        ],
        'slug' => [
            'string' => 'El slug debe ser una cadena de texto.',
            'max' => 'El slug no puede exceder 255 caracteres.',
            'unique' => 'Ya existe un programa con este slug.',
        ],
        'description' => [
            'string' => 'La descripción debe ser una cadena de texto.',
        ],
        'is_active' => [
            'boolean' => 'El estado activo debe ser verdadero o falso.',
        ],
        'order' => [
            'integer' => 'El orden debe ser un número entero.',
        ],
        'year' => [
            'required' => 'El año académico es obligatorio.',
            'string' => 'El año académico debe ser una cadena de texto.',
            'regex' => 'El año académico debe tener el formato YYYY-YYYY (ej: 2024-2025).',
            'unique' => 'Ya existe un año académico con este valor.',
        ],
        'start_date' => [
            'required' => 'La fecha de inicio es obligatoria.',
            'date' => 'La fecha de inicio debe ser una fecha válida.',
        ],
        'end_date' => [
            'required' => 'La fecha de fin es obligatoria.',
            'date' => 'La fecha de fin debe ser una fecha válida.',
            'after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ],
        'is_current' => [
            'boolean' => 'El estado actual debe ser verdadero o falso.',
        ],
        'program_id' => [
            'required' => 'El programa es obligatorio.',
            'exists' => 'El programa seleccionado no es válido.',
        ],
        'academic_year_id' => [
            'required' => 'El año académico es obligatorio.',
            'exists' => 'El año académico seleccionado no es válido.',
        ],
        'title' => [
            'required' => 'El título es obligatorio.',
            'string' => 'El título debe ser una cadena de texto.',
            'max' => 'El título no puede exceder 255 caracteres.',
        ],
        'type' => [
            'required' => 'El tipo es obligatorio.',
            'in' => 'El tipo seleccionado no es válido.',
        ],
        'modality' => [
            'required' => 'La modalidad es obligatoria.',
            'in' => 'La modalidad seleccionada no es válida.',
        ],
        'number_of_places' => [
            'required' => 'El número de plazas es obligatorio.',
            'integer' => 'El número de plazas debe ser un número entero.',
            'min' => 'El número de plazas debe ser al menos 1.',
        ],
        'destinations' => [
            'required' => 'Los destinos son obligatorios.',
            'array' => 'Los destinos deben ser un array.',
        ],
        'destinations.*' => [
            'string' => 'Cada destino debe ser una cadena de texto.',
        ],
        'estimated_start_date' => [
            'date' => 'La fecha estimada de inicio debe ser una fecha válida.',
        ],
        'estimated_end_date' => [
            'date' => 'La fecha estimada de fin debe ser una fecha válida.',
            'after' => 'La fecha estimada de fin debe ser posterior a la fecha estimada de inicio.',
        ],
        'scoring_table' => [
            'array' => 'La tabla de baremación debe ser un array.',
        ],
        'status' => [
            'in' => 'El estado seleccionado no es válido.',
        ],
        'published_at' => [
            'date' => 'La fecha de publicación debe ser una fecha válida.',
        ],
        'closed_at' => [
            'date' => 'La fecha de cierre debe ser una fecha válida.',
        ],
        'call_id' => [
            'required' => 'La convocatoria es obligatoria.',
            'exists' => 'La convocatoria seleccionada no es válida.',
        ],
        'phase_type' => [
            'required' => 'El tipo de fase es obligatorio.',
            'in' => 'El tipo de fase seleccionado no es válido.',
        ],
        'call_phase_id' => [
            'required' => 'La fase es obligatoria.',
            'exists' => 'La fase seleccionada no es válida.',
        ],
        'official_date' => [
            'required' => 'La fecha oficial es obligatoria.',
            'date' => 'La fecha oficial debe ser una fecha válida.',
        ],
        'excerpt' => [
            'string' => 'El extracto debe ser una cadena de texto.',
        ],
        'content' => [
            'required' => 'El contenido es obligatorio.',
            'string' => 'El contenido debe ser una cadena de texto.',
        ],
        'country' => [
            'string' => 'El país debe ser una cadena de texto.',
            'max' => 'El país no puede exceder 255 caracteres.',
        ],
        'city' => [
            'string' => 'La ciudad debe ser una cadena de texto.',
            'max' => 'La ciudad no puede exceder 255 caracteres.',
        ],
        'host_entity' => [
            'string' => 'La entidad de acogida debe ser una cadena de texto.',
            'max' => 'La entidad de acogida no puede exceder 255 caracteres.',
        ],
        'mobility_type' => [
            'in' => 'El tipo de movilidad seleccionado no es válido.',
        ],
        'mobility_category' => [
            'in' => 'La categoría de movilidad seleccionada no es válida.',
        ],
        'author_id' => [
            'exists' => 'El autor seleccionado no es válido.',
        ],
        'reviewed_by' => [
            'exists' => 'El revisor seleccionado no es válido.',
        ],
        'reviewed_at' => [
            'date' => 'La fecha de revisión debe ser una fecha válida.',
        ],
        'category_id' => [
            'required' => 'La categoría es obligatoria.',
            'exists' => 'La categoría seleccionada no es válida.',
        ],
        'document_type' => [
            'required' => 'El tipo de documento es obligatorio.',
            'in' => 'El tipo de documento seleccionado no es válido.',
        ],
        'version' => [
            'string' => 'La versión debe ser una cadena de texto.',
            'max' => 'La versión no puede exceder 255 caracteres.',
        ],
        'created_by' => [
            'exists' => 'El usuario creador seleccionado no es válido.',
        ],
        'updated_by' => [
            'exists' => 'El usuario actualizador seleccionado no es válido.',
        ],
        'event_type' => [
            'required' => 'El tipo de evento es obligatorio.',
            'in' => 'El tipo de evento seleccionado no es válido.',
        ],
        'location' => [
            'string' => 'La ubicación debe ser una cadena de texto.',
            'max' => 'La ubicación no puede exceder 255 caracteres.',
        ],
        'is_public' => [
            'boolean' => 'El estado público debe ser verdadero o falso.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos de Validación Personalizados
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas de idioma se utilizan para reemplazar nuestro
    | marcador de posición de atributo con algo más amigable para el lector,
    | como "Dirección de correo electrónico" en lugar de "email". Esto
    | simplemente nos ayuda a hacer nuestro mensaje más expresivo.
    |
    */

    'attributes' => [
        'code' => 'código del programa',
        'name' => 'nombre del programa',
        'slug' => 'slug',
        'description' => 'descripción',
        'is_active' => 'estado activo',
        'order' => 'orden',
        'year' => 'año académico',
        'start_date' => 'fecha de inicio',
        'end_date' => 'fecha de fin',
        'is_current' => 'año actual',
        'program_id' => 'programa',
        'academic_year_id' => 'año académico',
        'title' => 'título',
        'type' => 'tipo',
        'modality' => 'modalidad',
        'number_of_places' => 'número de plazas',
        'destinations' => 'destinos',
        'estimated_start_date' => 'fecha estimada de inicio',
        'estimated_end_date' => 'fecha estimada de fin',
        'requirements' => 'requisitos',
        'documentation' => 'documentación',
        'selection_criteria' => 'criterios de selección',
        'scoring_table' => 'tabla de baremación',
        'status' => 'estado',
        'published_at' => 'fecha de publicación',
        'closed_at' => 'fecha de cierre',
        'call_id' => 'convocatoria',
        'phase_type' => 'tipo de fase',
        'call_phase_id' => 'fase',
        'evaluation_procedure' => 'procedimiento de evaluación',
        'official_date' => 'fecha oficial',
        'excerpt' => 'extracto',
        'content' => 'contenido',
        'country' => 'país',
        'city' => 'ciudad',
        'host_entity' => 'entidad de acogida',
        'mobility_type' => 'tipo de movilidad',
        'mobility_category' => 'categoría de movilidad',
        'author_id' => 'autor',
        'reviewed_by' => 'revisor',
        'reviewed_at' => 'fecha de revisión',
        'category_id' => 'categoría',
        'document_type' => 'tipo de documento',
        'version' => 'versión',
        'created_by' => 'creado por',
        'updated_by' => 'actualizado por',
        'event_type' => 'tipo de evento',
        'location' => 'ubicación',
        'is_public' => 'público',
    ],

];
