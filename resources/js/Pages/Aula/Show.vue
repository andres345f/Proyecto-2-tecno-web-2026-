<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import WeeklySchedule from '@/components/WeeklySchedule.vue';

interface Horario {
    id: number;
    dia: string;
    hora_inicio: string;
    hora_fin: string;
    grupo_periodo?: {
        periodo_academico_id: number;
        grupo?: {
            codigo: string;
            materia?: {
                nombre: string;
            };
        };
        docente?: {
            name: string;
        };
    };
}

interface Periodo {
    id: number;
    nombre: string;
    estado: string;
    oferta_academica?: {
        nombre: string;
    };
}

interface Aula {
    id: number;
    nombre: string;
    codigo: string;
    capacidad: number;
    horarios?: Horario[];
}

const props = defineProps<{
    aula: Aula;
    periodos?: Periodo[];
}>();

const selectedPeriodoId = ref<number | null>(null);

onMounted(() => {
    if (props.periodos && props.periodos.length > 0) {
        const activePeriod = props.periodos.find(p => p.estado !== 'terminado');
        selectedPeriodoId.value = activePeriod ? activePeriod.id : props.periodos[0].id;
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Aulas', href: route('aulas.index') },
    { title: props.aula.nombre, href: route('aulas.show', props.aula.id) },
];

const mappedHorarios = computed(() => {
    if (!props.aula.horarios) return [];

    let filtered = props.aula.horarios;
    if (selectedPeriodoId.value) {
        filtered = props.aula.horarios.filter(h => h.grupo_periodo?.periodo_academico_id === selectedPeriodoId.value);
    }

    return filtered.map(h => ({
        dia: h.dia,
        hora_inicio: h.hora_inicio,
        hora_fin: h.hora_fin,
        grupo_codigo: h.grupo_periodo?.grupo?.codigo,
        materia_nombre: h.grupo_periodo?.grupo?.materia?.nombre,
        docente_name: h.grupo_periodo?.docente?.name
    }));
});
</script>

<template>

    <Head :title="aula.nombre" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ aula.nombre }}
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">Detalles del espacio físico y su asignación de
                        horarios.</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('aulas.edit', props.aula.id)"
                        class="rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors">
                        Editar Aula
                    </Link>
                    <Link :href="route('aulas.index')"
                        class="rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 transition-colors">
                        Volver
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Info Card -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-xs">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Aula</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nombre</dt>
                            <dd class="text-sm font-medium text-foreground mt-1">{{ aula.nombre }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Código</dt>
                            <dd class="text-sm font-mono font-medium text-foreground mt-1">{{ aula.codigo }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Capacidad
                                Máxima</dt>
                            <dd class="text-sm font-medium text-foreground mt-1">{{ aula.capacidad }} personas</dd>
                        </div>
                    </dl>
                </div>

                <!-- Schedule timestable directly visible -->
                <div class="md:col-span-2 space-y-4">
                    <div v-if="periodos && periodos.length > 0"
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-card border border-border p-4 rounded-xl shadow-xs">
                        <select id="filter-periodo" v-model="selectedPeriodoId"
                            class="flex h-9 w-full sm:w-64 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option v-for="p in periodos" :key="p.id" :value="p.id">
                                {{ p.nombre }} ({{ p.oferta_academica?.nombre ?? 'General' }}) — {{ p.estado }}
                            </option>
                        </select>
                        <label for="filter-periodo" class="text-sm font-semibold text-foreground">
                            Filtrar por Período Académico:
                        </label>

                    </div>
                    <WeeklySchedule title="Ocupación Semanal del Aula" :horarios="mappedHorarios" type="aula" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
