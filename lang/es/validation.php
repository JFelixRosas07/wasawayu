<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de idioma de validación
    |--------------------------------------------------------------------------
    */

    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'accepted_if'          => 'El campo :attribute debe ser aceptado cuando :other sea :value.',
    'active_url'           => 'El campo :attribute no es una URL válida.',
    'after'                => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal'       => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                => 'El campo :attribute solo puede contener letras.',
    'alpha_dash'           => 'El campo :attribute solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num'            => 'El campo :attribute solo puede contener letras y números.',
    'array'                => 'El campo :attribute debe ser un arreglo.',
    'before'               => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file'    => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean'              => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación del campo :attribute no coincide.',
    'current_password'     => 'La contraseña es incorrecta.',
    'date'                 => 'El campo :attribute no es una fecha válida.',
    'date_equals'          => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format'          => 'El campo :attribute no coincide con el formato :format.',
    'different'            => 'Los campos :attribute y :other deben ser diferentes.',
    'digits'               => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between'       => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email'                => 'El campo :attribute debe ser una dirección de correo válida.',
    'exists'               => 'El campo :attribute seleccionado no es válido.',
    'file'                 => 'El campo :attribute debe ser un archivo.',
    'image'                => 'El campo :attribute debe ser una imagen.',
    'in'                   => 'El campo :attribute seleccionado no es válido.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'max'                  => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'file'    => 'El archivo :attribute no puede pesar más de :max kilobytes.',
        'string'  => 'El campo :attribute no puede tener más de :max caracteres.',
        'array'   => 'El campo :attribute no puede tener más de :max elementos.',
    ],
    'min'                  => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file'    => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in'               => 'El campo :attribute seleccionado no es válido.',
    'numeric'              => 'El campo :attribute debe ser un número.',
    'required'             => 'El campo :attribute es obligatorio.',
    'same'                 => 'Los campos :attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file'    => 'El archivo :attribute debe pesar :size kilobytes.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
        'array'   => 'El campo :attribute debe contener :size elementos.',
    ],
    'string'               => 'El campo :attribute debe ser texto.',
    'unique'               => 'El campo :attribute ya ha sido registrado.',
    'uploaded'             => 'El campo :attribute no se pudo subir.',
    'url'                  => 'El campo :attribute no es una URL válida.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes personalizados
    |--------------------------------------------------------------------------
    */
    'custom' => [
        'password' => [
            'min' => 'La contraseña debe tener al menos :min caracteres.',
        ],
        'name' => [
            'regex' => 'El campo :attribute solo puede contener letras y espacios.',
        ],
        'email' => [
            'inactive' => 'Usuario inactivo o bloqueado.',
        ],
        'captcha' => [
            'required' => 'Debes completar el código de verificación.',
            'captcha'  => 'El código de verificación ingresado no es válido.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos personalizados
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'name'                  => 'nombre',
        'email'                 => 'correo electrónico',
        'password'              => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'role'                  => 'rol',
        'estado'                => 'estado',
        'foto'                  => 'fotografía',
        'captcha'               => 'código de verificación',
    ],

];
