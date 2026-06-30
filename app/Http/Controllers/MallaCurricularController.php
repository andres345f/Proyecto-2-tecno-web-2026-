<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMallaRequest;
use App\Models\MallaCurricular;
use App\Models\Materia;
use App\Models\OfertaAcademica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MallaCurricularController extends Controller
{
    public function store(StoreMallaRequest $request, OfertaAcademica $oferta)
    {
        $oferta->materias()->attach($request->materia_id, [
            'semestre_orden' => $request->semestre_orden,
        ]);

        return redirect()->route('ofertas-academicas.show', $oferta);
    }

    public function destroy(OfertaAcademica $oferta, Materia $materia)
    {
        $mallaEntry = MallaCurricular::where('oferta_academica_id', $oferta->id)
            ->where('materia_id', $materia->id)
            ->first();

        if (!$mallaEntry) {
            abort(404, 'Materia not found in this malla curricular.');
        }

        // Also clean up any prerequisites associated with this malla entry
        DB::table('materia_prerequisito')
            ->where('malla_curricular_id', $mallaEntry->id)
            ->orWhere('prerequisito_malla_id', $mallaEntry->id)
            ->delete();

        $mallaEntry->delete();

        return redirect()->route('ofertas-academicas.show', $oferta);
    }

    public function storePrerrequisito(Request $request, OfertaAcademica $oferta, Materia $materia)
    {
        $request->validate([
            'prerequisito_id' => 'required|exists:materias,id',
        ]);

        // Prevent self-reference
        if ($request->prerequisito_id == $materia->id) {
            return back()->withErrors(['prerequisito_id' => 'Una materia no puede ser prerrequisito de sí misma.']);
        }

        $mallaEntry = MallaCurricular::where('oferta_academica_id', $oferta->id)
            ->where('materia_id', $materia->id)
            ->first();

        if (!$mallaEntry) {
            return back()->withErrors(['prerequisito_id' => 'La materia no pertenece a la malla curricular de esta oferta académica.']);
        }

        $prereqMallaEntry = MallaCurricular::where('oferta_academica_id', $oferta->id)
            ->where('materia_id', $request->prerequisito_id)
            ->first();

        if (!$prereqMallaEntry) {
            return back()->withErrors(['prerequisito_id' => 'El prerrequisito seleccionado debe pertenecer a la misma oferta académica o carrera.']);
        }

        // Check if relationship already exists
        $exists = DB::table('materia_prerequisito')
            ->where('malla_curricular_id', $mallaEntry->id)
            ->where('prerequisito_malla_id', $prereqMallaEntry->id)
            ->exists();

        if (!$exists) {
            $mallaEntry->prerrequisitos()->attach($prereqMallaEntry->id);
        }

        return redirect()->route('ofertas-academicas.show', $oferta);
    }

    public function destroyPrerrequisito(OfertaAcademica $oferta, Materia $materia, Materia $prerequisito)
    {
        $mallaEntry = MallaCurricular::where('oferta_academica_id', $oferta->id)
            ->where('materia_id', $materia->id)
            ->firstOrFail();

        $prereqMallaEntry = MallaCurricular::where('oferta_academica_id', $oferta->id)
            ->where('materia_id', $prerequisito->id)
            ->firstOrFail();

        DB::table('materia_prerequisito')
            ->where('malla_curricular_id', $mallaEntry->id)
            ->where('prerequisito_malla_id', $prereqMallaEntry->id)
            ->delete();

        return redirect()->route('ofertas-academicas.show', $oferta);
    }

    public function estudianteIndex(Request $request)
    {
        $user = $request->user();

        // 1. Obtener matriculas de carrera del estudiante
        $matriculasCarrera = \App\Models\MatriculaCarrera::where('usuario_id', $user->id)
            ->with('ofertaAcademica')
            ->get();

        // 2. Determinar oferta activa
        $ofertaId = $request->input('oferta_id') ?? ($matriculasCarrera->first()?->oferta_academica_id);

        $malla = [];
        $historialMaterias = [];

        if ($ofertaId) {
            // 3. Obtener materias de la malla curricular de esa oferta
            $malla = MallaCurricular::where('oferta_academica_id', $ofertaId)
                ->with(['materia', 'prerrequisitos.materia'])
                ->get()
                ->sortBy('semestre_orden')
                ->values();

            // 4. Obtener inscripciones a grupo del estudiante
            $inscripciones = \App\Models\MatriculaGrupo::whereHas('matriculaPeriodo.matriculaCarrera', function ($q) use ($user) {
                $q->where('usuario_id', $user->id);
            })
            ->with('grupoPeriodo.grupo')
            ->get();

            foreach ($inscripciones as $inscripcion) {
                $materiaId = $inscripcion->grupoPeriodo?->grupo?->materia_id;
                if (!isset($historialMaterias[$materiaId])) {
                    $historialMaterias[$materiaId] = [
                        'aprobada' => false,
                        'en_curso' => false,
                        'reprobaciones_count' => 0,
                    ];
                }
                if ($inscripcion->estado === 'aprobado') {
                    $historialMaterias[$materiaId]['aprobada'] = true;
                } elseif ($inscripcion->estado === 'reprobado') {
                    $historialMaterias[$materiaId]['reprobaciones_count']++;
                } elseif (in_array($inscripcion->estado, ['inscrito', 'en_curso'])) {
                    $historialMaterias[$materiaId]['en_curso'] = true;
                }
            }
        }

        return Inertia::render('MallaCurricular/EstudianteIndex', [
            'matriculasCarrera' => $matriculasCarrera,
            'ofertaSeleccionadaId' => $ofertaId ? (int)$ofertaId : null,
            'malla' => $malla,
            'historialMaterias' => $historialMaterias,
        ]);
    }
}
