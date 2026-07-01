<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\OfertaAcademica;
use Illuminate\Database\Seeder;

class MallaCurricularSeeder extends Seeder
{
    public function run(): void
    {
        /*
        $tsDs = OfertaAcademica::where('codigo', 'TS-DS')->first();
        $tmRt = OfertaAcademica::where('codigo', 'TS-RT')->first();
        $tmDg = OfertaAcademica::where('codigo', 'TM-DG')->first();
        */
        $taMd = OfertaAcademica::where('codigo', 'TA-MD')->first();

        // Retrieve all materias
        $materias = Materia::all()->keyBy('codigo');

        /*
        // --- TS-DS SEMESTERS ---
        // Semestre 1
        $tsDs->materias()->attach($materias['DS-101']->id, ['semestre_orden' => 1]);
        $tsDs->materias()->attach($materias['DS-102']->id, ['semestre_orden' => 1]);
        $tsDs->materias()->attach($materias['DS-103']->id, ['semestre_orden' => 1]);
        $tsDs->materias()->attach($materias['DS-104']->id, ['semestre_orden' => 1]);
        // Semestre 2
        $tsDs->materias()->attach($materias['DS-201']->id, ['semestre_orden' => 2]);
        $tsDs->materias()->attach($materias['DS-202']->id, ['semestre_orden' => 2]);
        $tsDs->materias()->attach($materias['DS-203']->id, ['semestre_orden' => 2]);
        $tsDs->materias()->attach($materias['DS-204']->id, ['semestre_orden' => 2]);
        // Semestre 3
        $tsDs->materias()->attach($materias['DS-301']->id, ['semestre_orden' => 3]);
        $tsDs->materias()->attach($materias['DS-302']->id, ['semestre_orden' => 3]);
        $tsDs->materias()->attach($materias['DS-303']->id, ['semestre_orden' => 3]);
        $tsDs->materias()->attach($materias['DS-304']->id, ['semestre_orden' => 3]);
        // Semestre 4
        $tsDs->materias()->attach($materias['DS-401']->id, ['semestre_orden' => 4]);
        $tsDs->materias()->attach($materias['DS-402']->id, ['semestre_orden' => 4]);
        $tsDs->materias()->attach($materias['DS-403']->id, ['semestre_orden' => 4]);
        $tsDs->materias()->attach($materias['DS-404']->id, ['semestre_orden' => 4]);
        // Semestre 5
        $tsDs->materias()->attach($materias['DS-501']->id, ['semestre_orden' => 5]);
        $tsDs->materias()->attach($materias['DS-502']->id, ['semestre_orden' => 5]);
        $tsDs->materias()->attach($materias['DS-503']->id, ['semestre_orden' => 5]);
        $tsDs->materias()->attach($materias['DS-504']->id, ['semestre_orden' => 5]);
        // Semestre 6
        $tsDs->materias()->attach($materias['DS-601']->id, ['semestre_orden' => 6]);
        $tsDs->materias()->attach($materias['DS-602']->id, ['semestre_orden' => 6]);
        $tsDs->materias()->attach($materias['DS-603']->id, ['semestre_orden' => 6]);
        $tsDs->materias()->attach($materias['DS-604']->id, ['semestre_orden' => 6]);

        // --- TS-RT SEMESTERS ---
        // Semestre 1
        $tmRt->materias()->attach($materias['RT-101']->id, ['semestre_orden' => 1]);
        $tmRt->materias()->attach($materias['RT-102']->id, ['semestre_orden' => 1]);
        $tmRt->materias()->attach($materias['RT-103']->id, ['semestre_orden' => 1]);
        $tmRt->materias()->attach($materias['RT-104']->id, ['semestre_orden' => 1]);
        // Semestre 2
        $tmRt->materias()->attach($materias['RT-201']->id, ['semestre_orden' => 2]);
        $tmRt->materias()->attach($materias['RT-202']->id, ['semestre_orden' => 2]);
        $tmRt->materias()->attach($materias['RT-203']->id, ['semestre_orden' => 2]);
        $tmRt->materias()->attach($materias['RT-204']->id, ['semestre_orden' => 2]);
        // Semestre 3
        $tmRt->materias()->attach($materias['RT-301']->id, ['semestre_orden' => 3]);
        $tmRt->materias()->attach($materias['RT-302']->id, ['semestre_orden' => 3]);
        $tmRt->materias()->attach($materias['RT-303']->id, ['semestre_orden' => 3]);
        $tmRt->materias()->attach($materias['RT-304']->id, ['semestre_orden' => 3]);
        // Semestre 4
        $tmRt->materias()->attach($materias['RT-401']->id, ['semestre_orden' => 4]);
        $tmRt->materias()->attach($materias['RT-402']->id, ['semestre_orden' => 4]);
        $tmRt->materias()->attach($materias['RT-403']->id, ['semestre_orden' => 4]);
        $tmRt->materias()->attach($materias['RT-404']->id, ['semestre_orden' => 4]);

        // --- TM-DG SEMESTERS ---
        // Semestre 1
        $tmDg->materias()->attach($materias['DG-101']->id, ['semestre_orden' => 1]);
        $tmDg->materias()->attach($materias['DG-102']->id, ['semestre_orden' => 1]);
        $tmDg->materias()->attach($materias['DG-103']->id, ['semestre_orden' => 1]);
        $tmDg->materias()->attach($materias['DG-104']->id, ['semestre_orden' => 1]);
        // Semestre 2
        $tmDg->materias()->attach($materias['DG-201']->id, ['semestre_orden' => 2]);
        $tmDg->materias()->attach($materias['DG-202']->id, ['semestre_orden' => 2]);
        $tmDg->materias()->attach($materias['DG-203']->id, ['semestre_orden' => 2]);
        $tmDg->materias()->attach($materias['DG-204']->id, ['semestre_orden' => 2]);
        // Semestre 3
        $tmDg->materias()->attach($materias['DG-301']->id, ['semestre_orden' => 3]);
        $tmDg->materias()->attach($materias['DG-302']->id, ['semestre_orden' => 3]);
        $tmDg->materias()->attach($materias['DG-303']->id, ['semestre_orden' => 3]);
        $tmDg->materias()->attach($materias['DG-304']->id, ['semestre_orden' => 3]);
        // Semestre 4
        $tmDg->materias()->attach($materias['DG-401']->id, ['semestre_orden' => 4]);
        $tmDg->materias()->attach($materias['DG-402']->id, ['semestre_orden' => 4]);
        $tmDg->materias()->attach($materias['DG-403']->id, ['semestre_orden' => 4]);
        $tmDg->materias()->attach($materias['DG-404']->id, ['semestre_orden' => 4]);
        */

        // --- TA-MD SEMESTERS ---
        // Semestre 1
        $taMd->materias()->attach($materias['MD-101']->id, ['semestre_orden' => 1]);
        $taMd->materias()->attach($materias['MD-102']->id, ['semestre_orden' => 1]);
        $taMd->materias()->attach($materias['MD-103']->id, ['semestre_orden' => 1]);
        $taMd->materias()->attach($materias['MD-104']->id, ['semestre_orden' => 1]);
        // Semestre 2
        $taMd->materias()->attach($materias['MD-201']->id, ['semestre_orden' => 2]);
        $taMd->materias()->attach($materias['MD-202']->id, ['semestre_orden' => 2]);
        $taMd->materias()->attach($materias['MD-203']->id, ['semestre_orden' => 2]);
        $taMd->materias()->attach($materias['MD-204']->id, ['semestre_orden' => 2]);

        // Helper to attach prerequisite via MallaCurricular
        $attachPrereq = function ($oferta, $materiaCode, $prereqCode) use ($materias) {
            if (!$oferta) return;
            $mallaEntry = \App\Models\MallaCurricular::where('oferta_academica_id', $oferta->id)
                ->where('materia_id', $materias[$materiaCode]->id)
                ->first();

            $prereqEntry = \App\Models\MallaCurricular::where('oferta_academica_id', $oferta->id)
                ->where('materia_id', $materias[$prereqCode]->id)
                ->first();

            if ($mallaEntry && $prereqEntry) {
                $mallaEntry->prerrequisitos()->attach($prereqEntry->id);
            }
        };

        /*
        // --- PREREQUISITES (TS-DS) ---
        $attachPrereq($tsDs, 'DS-201', 'DS-101');
        $attachPrereq($tsDs, 'DS-202', 'DS-101');
        $attachPrereq($tsDs, 'DS-301', 'DS-201');
        $attachPrereq($tsDs, 'DS-302', 'DS-203');
        $attachPrereq($tsDs, 'DS-303', 'DS-201');
        $attachPrereq($tsDs, 'DS-401', 'DS-301');
        $attachPrereq($tsDs, 'DS-402', 'DS-303');
        $attachPrereq($tsDs, 'DS-403', 'DS-303');
        $attachPrereq($tsDs, 'DS-501', 'DS-401');
        $attachPrereq($tsDs, 'DS-502', 'DS-402');
        $attachPrereq($tsDs, 'DS-503', 'DS-202');
        $attachPrereq($tsDs, 'DS-601', 'DS-502');
        $attachPrereq($tsDs, 'DS-602', 'DS-402');

        // --- PREREQUISITES (TS-RT) ---
        $attachPrereq($tmRt, 'RT-201', 'RT-101');
        $attachPrereq($tmRt, 'RT-202', 'RT-101');
        $attachPrereq($tmRt, 'RT-203', 'RT-102');
        $attachPrereq($tmRt, 'RT-301', 'RT-201');
        $attachPrereq($tmRt, 'RT-302', 'RT-201');
        $attachPrereq($tmRt, 'RT-303', 'RT-203');
        $attachPrereq($tmRt, 'RT-304', 'RT-204');
        $attachPrereq($tmRt, 'RT-401', 'RT-302');
        $attachPrereq($tmRt, 'RT-402', 'RT-303');

        // --- PREREQUISITES (TM-DG) ---
        $attachPrereq($tmDg, 'DG-201', 'DG-101');
        $attachPrereq($tmDg, 'DG-202', 'DG-104');
        $attachPrereq($tmDg, 'DG-204', 'DG-104');
        $attachPrereq($tmDg, 'DG-301', 'DG-201');
        $attachPrereq($tmDg, 'DG-302', 'DG-101');
        $attachPrereq($tmDg, 'DG-303', 'DG-202');
        $attachPrereq($tmDg, 'DG-304', 'DG-204');
        $attachPrereq($tmDg, 'DG-401', 'DG-301');
        $attachPrereq($tmDg, 'DG-402', 'DG-304');
        $attachPrereq($tmDg, 'DG-403', 'DG-204');
        */

        // --- PREREQUISITES (TA-MD) ---
        $attachPrereq($taMd, 'MD-201', 'MD-101');
        $attachPrereq($taMd, 'MD-204', 'MD-104');
    }
}
