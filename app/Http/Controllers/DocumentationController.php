<?php

namespace App\Http\Controllers;

class DocumentationController extends Controller
{
    public function swagger()
    {
        $file = public_path('api/swagger.json');

        if (file_exists($file)) {
            return response()->file($file, ['Content-Type' => 'application/json']);
        }

        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'POS System API',
                'description' => 'REST API untuk Sistem Point of Sale',
                'version' => '1.0.0',
                'contact' => [
                    'email' => 'support@pos-system.com',
                ],
            ],
            'servers' => [
                [
                    'url' => url('/api'),
                    'description' => 'API Server',
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'token',
                    ],
                ],
                'schemas' => [
                    'User' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'username' => ['type' => 'string'],
                            'role' => ['type' => 'string', 'enum' => ['admin', 'kasir']],
                            'is_active' => ['type' => 'boolean'],
                        ],
                    ],
                    'Kategori' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'nama_kategori' => ['type' => 'string'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'Barang' => [
                        'type' => 'object',
                        'properties' => [
                            'kode_barang' => ['type' => 'string'],
                            'kategori_id' => ['type' => 'integer'],
                            'nama' => ['type' => 'string'],
                            'harga_beli' => ['type' => 'number'],
                            'harga_jual' => ['type' => 'number'],
                            'stok' => ['type' => 'integer'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'Pelanggan' => [
                        'type' => 'object',
                        'properties' => [
                            'id_pelanggan' => ['type' => 'string'],
                            'nama' => ['type' => 'string'],
                            'domisili' => ['type' => 'string'],
                            'jenis_kelamin' => ['type' => 'string', 'enum' => ['PRIA', 'WANITA']],
                            'poin' => ['type' => 'integer'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'Penjualan' => [
                        'type' => 'object',
                        'properties' => [
                            'id_nota' => ['type' => 'string'],
                            'tgl' => ['type' => 'string', 'format' => 'date'],
                            'kode_pelanggan' => ['type' => 'string', 'nullable' => true],
                            'user_id' => ['type' => 'integer'],
                            'subtotal' => ['type' => 'number'],
                            'diskon' => ['type' => 'number'],
                            'pajak' => ['type' => 'number'],
                            'total_akhir' => ['type' => 'number'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'ApiResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean'],
                            'message' => ['type' => 'string'],
                            'data' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/login' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Login user dengan email',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'email' => ['type' => 'string', 'format' => 'email', 'example' => 'admin@example.com'],
                                            'password' => ['type' => 'string', 'format' => 'password', 'example' => 'password'],
                                        ],
                                        'required' => ['email', 'password'],
                                    ],
                                    'example' => [
                                        'email' => 'admin@example.com',
                                        'password' => 'password',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Login berhasil',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean', 'example' => true],
                                                'message' => ['type' => 'string', 'example' => 'Login berhasil'],
                                                'data' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'user' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id' => ['type' => 'integer', 'example' => 1],
                                                                'name' => ['type' => 'string', 'example' => 'Administrator'],
                                                                'email' => ['type' => 'string', 'example' => 'admin@example.com'],
                                                                'role' => ['type' => 'string', 'example' => 'admin'],
                                                            ],
                                                        ],
                                                        'token' => ['type' => 'string', 'example' => '1|AbCdEf123456...'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '401' => ['description' => 'Email atau password salah'],
                        ],
                    ],
                ],
                '/logout' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Logout user',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Logout berhasil'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/me' => [
                    'get' => [
                        'tags' => ['Auth'],
                        'summary' => 'Get current user',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'User data',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['\$ref' => '#/components/schemas/ApiResponse'],
                                    ],
                                ],
                            ],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/kategoris' => [
                    'get' => [
                        'tags' => ['Kategoris'],
                        'summary' => 'Get all kategoris',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'List of kategoris',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['\$ref' => '#/components/schemas/ApiResponse'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Kategoris'],
                        'summary' => 'Create kategori',
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'nama_kategori' => ['type' => 'string'],
                                        ],
                                        'required' => ['nama_kategori'],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Kategori created'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/kategoris/{kategori}' => [
                    'get' => [
                        'tags' => ['Kategoris'],
                        'summary' => 'Get kategori detail',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'kategori',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Kategori detail'],
                            '404' => ['description' => 'Kategori not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Kategoris'],
                        'summary' => 'Update kategori',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'kategori',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'nama_kategori' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Updated'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Kategoris'],
                        'summary' => 'Delete kategori',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'kategori',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Deleted'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/barangs' => [
                    'get' => [
                        'tags' => ['Barangs'],
                        'summary' => 'Get all barangs',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'kategori_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'List of barangs'],
                        ],
                    ],
                ],
                '/pelanggans' => [
                    'get' => [
                        'tags' => ['Pelanggans'],
                        'summary' => 'Get all pelanggans',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'List of pelanggans'],
                        ],
                    ],
                ],
                '/penjualans' => [
                    'get' => [
                        'tags' => ['Penjualans'],
                        'summary' => 'Get all penjualans',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'List of penjualans'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Penjualans'],
                        'summary' => 'Create penjualan',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '201' => ['description' => 'Penjualan created'],
                        ],
                    ],
                ],
                '/analytics/summary' => [
                    'get' => [
                        'tags' => ['Analytics'],
                        'summary' => 'Get sales summary',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Sales summary'],
                        ],
                    ],
                ],
                '/analytics/top-kategori' => [
                    'get' => [
                        'tags' => ['Analytics'],
                        'summary' => 'Get top categories',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Top categories'],
                        ],
                    ],
                ],
                '/analytics/kasir-performance' => [
                    'get' => [
                        'tags' => ['Analytics'],
                        'summary' => 'Get cashier performance',
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Cashier performance'],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($spec);
    }
}
