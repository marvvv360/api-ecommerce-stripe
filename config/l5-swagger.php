<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'E-Commerce API Documentation',
            ],

            'routes' => [
                /*
                 * Ruta para acceder a la interfaz gráfica de Swagger UI.
                 */
                'api' => 'api/documentation',
            ],

            'paths' => [
                /*
                 * Uso de rutas absolutas para la generación de archivos.
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * Directorio físico donde se guardan los archivos compilados.
                 */
                'docs' => storage_path('api-docs'),

                /*
                 * Nombres de los archivos generados.
                 */
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_SWAGGER_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Directorios excluidos durante el análisis.
                 */
                'excludes' => [],

                /*
                 * Ruta base del proyecto.
                 */
                'base' => env('L5_SWAGGER_BASE_PATH', null),

                /*
                 * Directorio donde Swagger buscará las anotaciones/atributos.
                 */
                'annotations' => [
                    base_path('app'), // Escanea toda la carpeta app
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_by_api_key' => false,
        ],

        'paths' => [
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
            'docs_physical' => storage_path('api-docs'),
            'format_to_use_for_docs' => env('L5_SWAGGER_FORMAT_TO_USE_FOR_DOCS', 'json'),
            'error_log_path' => storage_path('logs/swagger-error.log'),
        ],

        'scanOptions' => [
            'analyser' => null,
            'analysis' => null,
            'pattern' => '*.php',
            'exclude' => [],
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'description' => 'Ingresa tu token JWT con el formato: Bearer {token}',
                    'name' => 'Authorization',
                    'in' => 'header',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
            'security' => [
                [
                    'bearerAuth' => [],
                ],
            ],
        ],

        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,
        'ui' => [
            'text' => [
                'title' => 'E-Commerce API Documentation',
            ],
            'display' => [
                'dark_theme' => env('L5_SWAGGER_UI_DARK_THEME', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', "false"),
                'oauth2' => [
                    'use_pkce_with_response_type_code_grant' => false,
                ],
            ],
        ],
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://127.0.0.1:8000'),
        ],
    ],
];