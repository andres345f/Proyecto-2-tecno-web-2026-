<?php

namespace App\Http\Controllers;

use App\Models\GrupoPeriodo;
use Illuminate\Http\Request;

class GrupoPeriodoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => ['required', 'exists:grupos,id'],
            'periodo_academico_id' => ['required', 'exists:periodos_academicos,id'],
            'docente_id' => ['required', 'exists:users,id'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
        ]);

        // Evitar duplicados (incluyendo eliminados lógicamente)
        $existing = GrupoPeriodo::withTrashed()
            ->where('grupo_id', $validated['grupo_id'])
            ->where('periodo_academico_id', $validated['periodo_academico_id'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update([
                    'docente_id' => $validated['docente_id'],
                    'cupo_maximo' => $validated['cupo_maximo'],
                ]);
                return redirect()->back()->with('success', 'Grupo instanciado correctamente (restaurado de eliminados).');
            }

            return redirect()->back()->withErrors([
                'grupo_id' => 'Este grupo ya está instanciado para este período académico.'
            ]);
        }

        GrupoPeriodo::create($validated);

        return redirect()->back()->with('success', 'Grupo instanciado correctamente en el período.');
    }

    public function update(Request $request, GrupoPeriodo $grupoPeriodo)
    {
        $validated = $request->validate([
            'docente_id' => ['required', 'exists:users,id'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
        ]);

        $grupoPeriodo->update($validated);

        return redirect()->back()->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(GrupoPeriodo $grupoPeriodo)
    {
        $grupoPeriodo->delete();

        return redirect()->back()->with('success', 'Grupo desasociado del período correctamente.');
    }
}
