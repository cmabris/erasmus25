<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute field must be accepted.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'The :attribute field must be a date after :date.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'any_of' => 'The :attribute field is invalid.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute field must be a date before :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute field must have between :min and :max items.',
        'file' => 'The :attribute field must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute field must be between :min and :max.',
        'string' => 'The :attribute field must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'contains' => 'The :attribute field is missing a required value.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute field must be a valid date.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_contain' => 'The :attribute field must not contain any of the following: :values.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'encoding' => 'The :attribute field must be encoded in :encoding.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field must exist in :other.',
    'in_array_keys' => 'The :attribute field must contain at least one of the following keys: :values.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_if_accepted' => 'The :attribute field is prohibited when :other is accepted.',
    'prohibited_if_declined' => 'The :attribute field is prohibited when :other is declined.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'code' => [
            'required' => 'The program code is required.',
            'string' => 'The program code must be a string.',
            'max' => 'The program code may not be greater than 255 characters.',
            'unique' => 'A program with this code already exists.',
        ],
        'name' => [
            'required' => 'The program name is required.',
            'string' => 'The program name must be a string.',
            'max' => 'The program name may not be greater than 255 characters.',
        ],
        'slug' => [
            'string' => 'The slug must be a string.',
            'max' => 'The slug may not be greater than 255 characters.',
            'unique' => 'A program with this slug already exists.',
        ],
        'description' => [
            'string' => 'The description must be a string.',
        ],
        'is_active' => [
            'boolean' => 'The active status must be true or false.',
        ],
        'order' => [
            'integer' => 'The order must be an integer.',
        ],
        'year' => [
            'required' => 'The academic year is required.',
            'string' => 'The academic year must be a string.',
            'regex' => 'The academic year must be in the format YYYY-YYYY (e.g., 2024-2025).',
            'unique' => 'An academic year with this value already exists.',
        ],
        'start_date' => [
            'required' => 'The start date is required.',
            'date' => 'The start date must be a valid date.',
        ],
        'end_date' => [
            'required' => 'The end date is required.',
            'date' => 'The end date must be a valid date.',
            'after' => 'The end date must be after the start date.',
        ],
        'is_current' => [
            'boolean' => 'The current status must be true or false.',
        ],
        'program_id' => [
            'required' => 'The program is required.',
            'exists' => 'The selected program is invalid.',
        ],
        'academic_year_id' => [
            'required' => 'The academic year is required.',
            'exists' => 'The selected academic year is invalid.',
        ],
        'title' => [
            'required' => 'The title is required.',
            'string' => 'The title must be a string.',
            'max' => 'The title may not be greater than 255 characters.',
        ],
        'type' => [
            'required' => 'The type is required.',
            'in' => 'The selected type is invalid.',
        ],
        'modality' => [
            'required' => 'The modality is required.',
            'in' => 'The selected modality is invalid.',
        ],
        'number_of_places' => [
            'required' => 'The number of places is required.',
            'integer' => 'The number of places must be an integer.',
            'min' => 'The number of places must be at least 1.',
        ],
        'destinations' => [
            'required' => 'The destinations are required.',
            'array' => 'The destinations must be an array.',
        ],
        'destinations.*' => [
            'string' => 'Each destination must be a string.',
        ],
        'estimated_start_date' => [
            'date' => 'The estimated start date must be a valid date.',
        ],
        'estimated_end_date' => [
            'date' => 'The estimated end date must be a valid date.',
            'after' => 'The estimated end date must be after the estimated start date.',
        ],
        'scoring_table' => [
            'array' => 'The scoring table must be an array.',
        ],
        'status' => [
            'in' => 'The selected status is invalid.',
        ],
        'published_at' => [
            'date' => 'The publication date must be a valid date.',
        ],
        'closed_at' => [
            'date' => 'The closing date must be a valid date.',
        ],
        'call_id' => [
            'required' => 'The call is required.',
            'exists' => 'The selected call is invalid.',
        ],
        'phase_type' => [
            'required' => 'The phase type is required.',
            'in' => 'The selected phase type is invalid.',
        ],
        'call_phase_id' => [
            'required' => 'The phase is required.',
            'exists' => 'The selected phase is invalid.',
        ],
        'official_date' => [
            'required' => 'The official date is required.',
            'date' => 'The official date must be a valid date.',
        ],
        'excerpt' => [
            'string' => 'The excerpt must be a string.',
        ],
        'content' => [
            'required' => 'The content is required.',
            'string' => 'The content must be a string.',
        ],
        'country' => [
            'string' => 'The country must be a string.',
            'max' => 'The country may not be greater than 255 characters.',
        ],
        'city' => [
            'string' => 'The city must be a string.',
            'max' => 'The city may not be greater than 255 characters.',
        ],
        'host_entity' => [
            'string' => 'The host entity must be a string.',
            'max' => 'The host entity may not be greater than 255 characters.',
        ],
        'mobility_type' => [
            'in' => 'The selected mobility type is invalid.',
        ],
        'mobility_category' => [
            'in' => 'The selected mobility category is invalid.',
        ],
        'author_id' => [
            'exists' => 'The selected author is invalid.',
        ],
        'reviewed_by' => [
            'exists' => 'The selected reviewer is invalid.',
        ],
        'reviewed_at' => [
            'date' => 'The review date must be a valid date.',
        ],
        'category_id' => [
            'required' => 'The category is required.',
            'exists' => 'The selected category is invalid.',
        ],
        'document_type' => [
            'required' => 'The document type is required.',
            'in' => 'The selected document type is invalid.',
        ],
        'version' => [
            'string' => 'The version must be a string.',
            'max' => 'The version may not be greater than 255 characters.',
        ],
        'created_by' => [
            'exists' => 'The selected creator is invalid.',
        ],
        'updated_by' => [
            'exists' => 'The selected updater is invalid.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'code' => 'program code',
        'name' => 'program name',
        'slug' => 'slug',
        'description' => 'description',
        'is_active' => 'active status',
        'order' => 'order',
        'year' => 'academic year',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'is_current' => 'current year',
        'program_id' => 'program',
        'academic_year_id' => 'academic year',
        'title' => 'title',
        'type' => 'type',
        'modality' => 'modality',
        'number_of_places' => 'number of places',
        'destinations' => 'destinations',
        'estimated_start_date' => 'estimated start date',
        'estimated_end_date' => 'estimated end date',
        'requirements' => 'requirements',
        'documentation' => 'documentation',
        'selection_criteria' => 'selection criteria',
        'scoring_table' => 'scoring table',
        'status' => 'status',
        'published_at' => 'publication date',
        'closed_at' => 'closing date',
        'call_id' => 'call',
        'phase_type' => 'phase type',
        'call_phase_id' => 'phase',
        'evaluation_procedure' => 'evaluation procedure',
        'official_date' => 'official date',
        'excerpt' => 'excerpt',
        'content' => 'content',
        'country' => 'country',
        'city' => 'city',
        'host_entity' => 'host entity',
        'mobility_type' => 'mobility type',
        'mobility_category' => 'mobility category',
        'author_id' => 'author',
        'reviewed_by' => 'reviewer',
        'reviewed_at' => 'review date',
        'category_id' => 'category',
        'document_type' => 'document type',
        'version' => 'version',
        'created_by' => 'created by',
        'updated_by' => 'updated by',
    ],

];
