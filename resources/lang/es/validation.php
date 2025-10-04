<?php

return [

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'alpha' => 'El campo :attribute solo puede contener letras.',
    'alpha_num' => 'El campo :attribute solo puede contener letras y números.',
    'array' => 'El campo :attribute debe ser un conjunto.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'unique' => 'El campo :attribute ya ha sido registrado.',
    'required' => 'El campo :attribute es obligatorio.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes personalizados
    |--------------------------------------------------------------------------
    */
    'custom' => [
        'email' => [
            'inactive' => 'Usuario inactivo o bloqueado.',
        ],
        'captcha' => [
            'required' => 'Debes completar el código de verificación.',
            'captcha' => 'El código de verificación ingresado no es válido.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'role' => 'rol',
        'estado' => 'estado',
        'foto' => 'fotografía',
        'captcha' => 'código de verificación',
    ],

    // En resources/lang/es/validation.php
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],

];
