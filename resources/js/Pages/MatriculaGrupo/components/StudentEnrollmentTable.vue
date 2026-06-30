<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface MatriculaGrupo {
    id: number;
    nota_final: string | null;
    estado: string;
    grupo: {
        id: number;
        codigo: string;
        materia: { nombre: string; codigo: string };
        horarios: Array<{ dia: string; hora_inicio: string; hora_fin: string; aula: { nombre: string } }>;
    };
    matricula_periodo: {
        periodo_academico: { nombre: string };
        matricula_carrera: { usuario: { name: string }; oferta_academica: { nombre: string } };
    };
}

defineProps<{
    matriculas: MatriculaGrupo[];
}>();

const estadoBadge = (estado: string) => {
    switch (estado) {
        case 'inscrito':    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'en_curso':    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'aprobado':    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'reprobado':   return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:            return 'bg-secondary text-secondary-foreground';
    }
};

const formatHorarios = (horarios: Array<{ dia: string; hora_inicio: string; hora_fin: string; aula: { nombre: string } }>) => {
    return horarios.map((h) => `${h.dia} ${h.hora_inicio.slice(0, 5)}-${h.hora_fin.slice(0, 5)} (${h.aula?.nombre})`).join(', ');
};

const confirmWithdraw = (matricula: MatriculaGrupo) => {
    if (confirm('¿Estás seguro de que deseas retirarte de este grupo?')) {
        router.delete(`/matriculas-grupo/${matricula.id}`);
    }
};
</script>

<template>
    <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
            <tr>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Grupo</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Materia</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Horarios</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Período</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nota Final</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            <tr v-for="matricula in matriculas" :key="matricula.id" class="hover:bg-muted/30 transition-colors">
                <td class="px-6 py-4 align-middle font-semibold text-foreground">
                    {{ matricula.grupo?.codigo }}
                </td>
                <td class="px-6 py-4 align-middle text-muted-foreground">
                    {{ matricula.grupo?.materia?.nombre }}
                </td>
                <td class="px-6 py-4 align-middle text-muted-foreground text-xs max-w-xs truncate">
                    {{ formatHorarios(matricula.grupo?.horarios || []) }}
                </td>
                <td class="px-6 py-4 align-middle text-muted-foreground">
                    {{ matricula.matricula_periodo?.periodo_academico?.nombre }}
                </td>
                <td class="px-6 py-4 align-middle font-mono font-semibold text-foreground">
                    {{ matricula.nota_final ?? '—' }}
                </td>
                <td class="px-6 py-4 align-middle">
                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(matricula.estado)]">
                        {{ matricula.estado }}
                    </span>
                </td>
                <td class="px-6 py-4 align-middle text-right space-x-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/matriculas-grupo/${matricula.id}`">Ver</Link>
                    </Button>
                    <Button
                        v-if="matricula.estado === 'en_curso' || matricula.estado === 'inscrito'"
                        variant="outline"
                        size="sm"
                        as-child
                    >
                        <Link :href="`/grupos/${matricula.grupo?.id}/tareas`">Tareas</Link>
                    </Button>
                    <Button
                        v-if="matricula.estado === 'en_curso' || matricula.estado === 'inscrito'"
                        variant="destructive"
                        size="sm"
                        @click="confirmWithdraw(matricula)"
                    >
                        Retirarse
                    </Button>
                </td>
            </tr>
            <tr v-if="matriculas.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-muted-foreground">
                    No se encontraron grupos inscritos.
                </td>
            </tr>
        </tbody>
    </table>
</template>
