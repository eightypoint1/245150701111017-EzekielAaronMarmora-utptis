<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Diciptakan untuk memenuhi tugas UTP Mata Kuliah",
    title: "E-Commerce API"
)]
class ProductController extends Controller
{
    private array $products = [
        [
            'id' => 1,
            'name' => 'Pil Kafein 200mg 100 tablet',
            'price' => 157000,
            'stock' => 25
        ],
        [
            'id' => 2,
            'name' => 'Charger Infinix 45 watt',
            'price' => 676767,
            'stock' => 67
        ]
    ];

    #[OA\Get(
        path: "/api/products",
        summary: "Menampilkan daftar produk",
        tags: ["Products"]
    )]
    #[OA\Response(
        response: 200,
        description: "Request Berhasil",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "name", type: "string", example: "Pil Kafein 200mg 100 tablet"),
                            new OA\Property(property: "price", type: "integer", example: 157000),
                            new OA\Property(property: "stock", type: "integer", example: 25)
                        ]
                    )
                )
            ]
        )
    )]
    public function index()
    {
        return response()->json([
            'data' => $this->products
        ], 200);
    }

    #[OA\Post(
        path: "/api/products",
        summary: "Menambahkan data ke daftar",
        tags: ["Products"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "price", "stock"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Pil Kafein 200mg 100 tablet"),
                new OA\Property(property: "price", type: "integer", example: 157000),
                new OA\Property(property: "stock", type: "integer", example: 200)
            ]
        )
    )]    
    #[OA\Response(response: 201, description: "Request Berhasil")]
    #[OA\Response(response: 422, description: "Data Tidak Valid")]
    public function store(Request $request)
    {
        $names = [];
        foreach ($this->products as $product) {
            $names[] = $product['name'];
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', fn($_, $val, $fail) => in_array($val, $names) ? $fail("Name not unique") : null],
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $productData = [
            'id' => rand(3, 1000),
            'name' => $validated['name'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
        ];

        return response()->json([
            'message' => 'Request Berhasil',
            'data' => $productData
        ], 201);
    }

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Menampilkan data berdasarkan ID",
        tags: ["Products"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Request Berhasil",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Pil Kafein 200mg 100 tablet"),
                        new OA\Property(property: "price", type: "integer", example: 157000),
                        new OA\Property(property: "stock", type: "integer", example: 25)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404, 
        description: "ID invalid",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "ID Invalid")
            ]
        )
    )]
    public function show($id)
    {
        $product = collect($this->products)->firstWhere('id', (int) $id);

        if (!$product) {
            return response()->json([
                'message' => "ID Invalid"
            ], 404);
        }

        return response()->json([
            'data' => $product
        ], 200);
    }

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Update data pada daftar",
        tags: ["Products"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "price", "stock"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Pil Kafein 200mg 100 tablet"),
                new OA\Property(property: "price", type: "integer", example: 157000),
                new OA\Property(property: "stock", type: "integer", example: 200)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Request Berhasil")]
    #[OA\Response(response: 404, description: "ID invalid")]
    #[OA\Response(response: 422, description: "data invalid")]
    public function update(Request $request, $id)
    {
        $product = collect($this->products)->firstWhere('id', (int) $id);

        if (!$product) {
            return response()->json([
                'message' => "id Invalid"
            ], 404);
        }

        $names = [];
        foreach ($this->products as $p) {
            if ($p['id'] !== (int) $id) {
                $names[] = $p['name'];
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', fn($_, $val, $fail) => in_array($val, $names) ? $fail("Name not unique") : null],
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        return response()->json([
            'message' => "Request Berhasil",
            'update' => $validated
        ], 200);
    }

    #[OA\Patch(
        path: "/api/products/{id}",
        summary: "Mengubah data produk (patch)",
        tags: ["Products"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(properties: [new OA\Property(property: "stock", type: "integer", example: 67)])
    )]
    #[OA\Response(response: 200, description: "Request berhasil")]
    #[OA\Response(response: 404, description: "id invalid")]
    #[OA\Response(response: 422, description: "Validasi Gagal")]
    public function patchs(Request $request, $id)
    {
        $product = collect($this->products)->firstWhere('id', (int) $id);

        if (!$product) {
            return response()->json([
                'message' => "ID invalid"
            ], 404);
        }

        $names = [];
        foreach ($this->products as $p) {
            if ($p['id'] !== (int) $id) {
                $names[] = $p['name'];
            }
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', fn($_, $val, $fail) => in_array($val, $names) ? $fail("Name not unique") : null],
            'price' => 'sometimes|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        return response()->json([
            'message' => "Request Berhasil",
            'update' => array_merge($product, $validated)
        ], 200);
    }

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Delete data dari daftar",
        tags: ["Products"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Request Berhasil")]
    #[OA\Response(response: 404, description: "ID invalid")]
    public function destroy($id)
    {
        $product = collect($this->products)->firstWhere('id', (int) $id);

        if (!$product) {
            return response()->json([
                'message' => "ID invalid"
            ], 404);
        }

        return response()->json([
            'message' => "Request Berhasil"
        ], 200);
    }
}