<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface GrupoAdmin {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    horarios: Array<{ dia: string; hora_inicio: string; hora_fin: string; aula: { nombre: string } }>;
    cupo_maximo: number;
    inscritos_count: number;
    periodo_nombre: string;
    carrera_nombre: string;
    aprobados_count: number;
    reprobados_count: number;
    tiene_notas: boolean;
}

defineProps<{
    grupos: GrupoAdmin[];
}>();

const emit = defineEmits<{
    'import-grades': [grupo: GrupoAdmin];
}>();

const formatHorarios = (horarios: Array<{ dia: string; hora_inicio: string; hora_fin: string; aula: { nombre: string } }>) => {
    return horarios.map((h) => `${h.dia} ${h.hora_inicio.slice(0, 5)}-${h.hora_fin.slice(0, 5)} (${h.aula?.nombre})`).join(', ');
};

const occupancyPercent = (grupo: GrupoAdmin) =>
    Math.round((grupo.inscritos_count / grupo.cupo_maximo) * 100);
</script>

<template>
    <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
            <tr>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Grupo / Materia</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Inscritos (Cupos)</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Horarios</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Período / Carrera</th>
                <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Rendimiento (Notas)</th>
                <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            <tr v-for="grupo in grupos" :key="grupo.id" class="hover:bg-muted/30 transition-colors">
                <td class="px-6 py-4 align-middle">
                    <div class="flex flex-col">
                        <span class="font-semibold text-foreground">Grupo {{ grupo.codigo }}</span>
                        <span class="text-xs text-muted-foreground">
                            {{ grupo.materia?.nombre }} ({{ grupo.materia?.codigo }})
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 align-middle">
                    <div class="flex flex-col gap-1 w-32">
                        <div class="flex justify-between text-xs font-medium">
                            <span>{{ grupo.inscritos_count }} / {{ grupo.cupo_maximo }}</span>
                            <span class="text-muted-foreground">{{ occupancyPercent(grupo) }}%</span>
                        </div>
                        <div class="w-full bg-secondary h-1.5 rounded-full overflow-hidden">
                            <div
                                class="bg-primary h-full transition-all"
                                :style="{ width: `${Math.min(100, occupancyPercent(grupo))}%` }"
                            />
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 align-middle text-muted-foreground text-xs max-w-xs truncate">
                    {{ formatHorarios(grupo.horarios || []) }}
                </td>
                <td class="px-6 py-4 align-middle text-muted-foreground font-medium">
                    <div class="flex flex-col">
                        <span>{{ grupo.periodo_nombre }}</span>
                        <span class="text-xs text-muted-foreground font-normal italic">{{ grupo.carrera_nombre }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 align-middle">
                    <div v-if="grupo.tiene_notas" class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-950/20 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-900/30">
                            Aprobados: {{ grupo.aprobados_count }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-950/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/20 dark:ring-red-900/30">
                            Reprobados: {{ grupo.reprobados_count }}
                        </span>
                    </div>
                    <span v-else class="text-xs text-muted-foreground italic">Sin notas</span>
                </td>
                <td class="px-6 py-4 align-middle text-right space-x-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/matriculas-grupo/${grupo.id}`">Ver</Link>
                    </Button>
                    <Button variant="outline" size="sm" @click="emit('import-grades', grupo)">
                        📥 Notas
                    </Button>
                </td>
            </tr>
            <tr v-if="grupos.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-muted-foreground">
                    No se encontraron grupos académicos.
                </td>
            </tr>
        </tbody>
    </table>
</template>
