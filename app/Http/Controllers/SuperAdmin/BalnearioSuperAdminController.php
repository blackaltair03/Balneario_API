<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Balneario;
use App\Models\Servicio; 
use Illuminate\Http\Request;

class BalnearioSuperAdminController extends Controller
{
    public function index()
    {
        $balnearios = Balneario::with('servicios')->get();
        return response()->json($balnearios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'capacidad' => 'required|integer|min:1',
        ]);

        $balneario = Balneario::create($request->only(['nombre', 'capacidad']));
        return response()->json($balneario, 201);
    }

    public function show(Balneario $balneario)
    {
        return response()->json($balneario->load('servicios'));
    }

    public function update(Request $request, Balneario $balneario)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'capacidad' => 'sometimes|integer|min:1',
            'aforo_actual' => 'sometimes|integer|min:0',
        ]);

        $balneario->update($request->all());

        return response()->json($balneario);
    }
    public function destroy(Balneario $balneario)
    {
        $balneario->delete();
        return response()->json(null, 204);
    }

    public function agregarServicio(Request $request, Balneario $balneario)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0',
        ]);


        $servicio = $balneario->servicios()->create($request->all());

        return response()->json($servicio, 201);
    }

    public function eliminarServicio(Balneario $balneario, Servicio $servicio)
    {
        if ($servicio->balneario_id !== $balneario->id) {
            return response()->json(['error' => 'El servicio no pertenece a este balneario'], 400);
        }

        $servicio->delete();
        return response()->json(null, 204);
    }
}


