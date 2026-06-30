<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { CheckCircle2, AlertCircle, PlayCircle, BookOpen, GraduationCap } from 'lucide-vue-next';

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

interface MatriculaCarrera {
    id: number;
    oferta_academica_id: number;
    oferta_academica?: OfertaAcademica;
}

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
}

interface MallaItem {
    id: number;
    oferta_academica_id: number;
    materia_id: number;
    semestre_orden: number;
    materia?: Materia;
    prerrequisitos?: {
        id: number;
        materia_id: number;
        materia?: Materia;
    }[];
}

interface HistorialMateria {
    aprobada: boolean;
    en_curso: boolean;
    reprobaciones_count: number;
}

const props = defineProps<{
    matriculasCarrera: MatriculaCarrera[];
    ofertaSeleccionadaId: number | null;
    malla: MallaItem[];
    historialMaterias: Record<number, HistorialMateria>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Malla Curricular',
        href: '/malla-curricular',
    },
];

const selectedOfertaId = ref(props.ofertaSeleccionadaId);

const currentOferta = computed(() => {
    return props.matriculasCarrera.find(
        (m) => m.oferta_academica_id === selectedOfertaId.value
    )?.oferta_academica;
});

// Agrupar malla por semestres
const mallaPorSemestre = computed(() => {
    const grupos: Record<number, MallaItem[]> = {};
    props.malla.forEach((item) => {
        const sem = item.semestre_orden || 1;
        if (!grupos[sem]) {
            grupos[sem] = [];
        }
        grupos[sem].push(item);
    });
    return grupos;
});

// Semestres ordenados
const semestresOrdenados = computed(() => {
    return Object.keys(mallaPorSemestre.value)
        .map(Number)
        .sort((a, b) => a - b);
});

// Comprobar estado de materia
const isAprobada = (materiaId: number): boolean => {
    return props.historialMaterias[materiaId]?.aprobada === true;
};

const isInCurso = (materiaId: number): boolean => {
    return props.historialMaterias[materiaId]?.en_curso === true && !isAprobada(materiaId);
};

const getReprobaciones = (materiaId: number): number => {
    return props.historialMaterias[materiaId]?.reprobaciones_count || 0;
};

// Cambiar de carrera / oferta académica
const changeOferta = () => {
    router.get(
        route('malla-curricular.estudiante'),
        { oferta_id: selectedOfertaId.value },
        { preserveState: false }
    );
};
</script>

<template>

    <Head title="Mi Malla Curricular" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header section with career selector -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground flex items-center gap-2">
                        <GraduationCap class="h-8 w-8 text-primary" />
                        Mi Malla Curricular
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Visualiza el progreso de tus asignaturas, requisitos y estado académico.
                    </p>
                </div>

                <!-- Dropdown selector when enrolled in multiple careers -->
                <div v-if="matriculasCarrera.length > 0" class="w-full md:w-72">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">
                        Oferta Académica
                    </label>
                    <select v-model="selectedOfertaId" @change="changeOferta"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option v-for="mat in matriculasCarrera" :key="mat.id" :value="mat.oferta_academica_id">
                            {{ mat.oferta_academica?.nombre }} ({{ mat.oferta_academica?.codigo }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty State when not enrolled in any career -->
            <div v-if="matriculasCarrera.length === 0"
                class="flex flex-col items-center justify-center p-12 border border-dashed rounded-xl">
                <AlertCircle class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-semibold text-foreground">Sin Matrículas Activas</h3>
                <p class="text-sm text-muted-foreground text-center max-w-sm mt-1">
                    No pareces tener ninguna oferta académica registrada en este momento.
                </p>
            </div>

            <!-- Main curriculum grid grouped by semester -->
            <div v-else class="space-y-10">
                <!-- Academic progress summary -->
                <Card class="bg-muted/30">
                    <CardContent class="py-4 px-6 flex flex-wrap gap-6 items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-muted-foreground">Oferta Seleccionada:</span>
                            <span class="text-sm font-bold text-foreground">{{ currentOferta?.nombre }}</span>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-1.5 text-xs">
                                <div class="w-3.5 h-3.5 rounded bg-emerald-500"></div>
                                <span class="text-muted-foreground font-medium">Aprobada</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <div class="w-3.5 h-3.5 rounded bg-blue-500"></div>
                                <span class="text-muted-foreground font-medium">En Curso</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <div class="w-3.5 h-3.5 rounded border border-border bg-card"></div>
                                <span class="text-muted-foreground font-medium">Pendiente</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <div class="w-3.5 h-3.5 rounded bg-rose-500"></div>
                                <span class="text-muted-foreground font-medium">Reprobación</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grid of Semesters -->
                <div class="space-y-8">
                    <div v-for="semestre in semestresOrdenados" :key="semestre" class="space-y-4">
                        <div class="border-b border-border pb-2">
                            <h2 class="text-xl font-bold text-foreground flex items-center gap-2">
                                <BookOpen class="h-5 w-5 text-muted-foreground" />
                                Nivel {{ semestre }}
                            </h2>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <div v-for="item in mallaPorSemestre[semestre]" :key="item.id" :class="[
                                'group relative rounded-xl border p-4 transition-all shadow-sm',
                                isAprobada(item.materia_id)
                                    ? 'bg-emerald-500/5 dark:bg-emerald-950/10 border-emerald-200 dark:border-emerald-900/60'
                                    : isInCurso(item.materia_id)
                                        ? 'bg-blue-500/5 dark:bg-blue-950/10 border-blue-200 dark:border-blue-900/60'
                                        : 'bg-card border-border hover:border-muted-foreground/30'
                            ]">
                                <!-- Status indicator icons -->
                                <div class="absolute top-4 right-4">
                                    <CheckCircle2 v-if="isAprobada(item.materia_id)" class="h-5 w-5 text-emerald-500" />
                                    <PlayCircle v-else-if="isInCurso(item.materia_id)"
                                        class="h-5 w-5 text-blue-500 animate-pulse" />
                                </div>

                                <!-- Code and Name -->
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground bg-muted px-2 py-0.5 rounded">
                                    {{ item.materia?.codigo }}
                                </span>
                                <h4
                                    class="mt-2 text-sm font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2">
                                    {{ item.materia?.nombre }}
                                </h4>

                                <!-- Prerequisites -->
                                <div v-if="item.prerrequisitos && item.prerrequisitos.length > 0"
                                    class="mt-4 pt-3 border-t border-dashed border-border">
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">
                                        Prerrequisitos
                                    </p>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="prereq in item.prerrequisitos" :key="prereq.id" :class="[
                                            'text-[10px] px-2 py-0.5 rounded font-medium border transition-colors',
                                            isAprobada(prereq.materia_id)
                                                ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50'
                                                : 'bg-muted text-muted-foreground border-border'
                                        ]" :title="prereq.materia?.nombre">
                                            {{ prereq.materia?.codigo }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Failures indicators (red boxes) -->
                                <div v-if="getReprobaciones(item.materia_id) > 0"
                                    class="mt-4 pt-3 border-t border-dashed border-border flex items-center justify-between">
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                        Historial fallas:
                                    </span>
                                    <div class="flex gap-1.5">
                                        <div v-for="n in getReprobaciones(item.materia_id)" :key="n"
                                            class="w-3.5 h-3.5 bg-rose-500 dark:bg-rose-600 rounded shadow-sm hover:scale-110 transition-transform cursor-help"
                                            title="Intento reprobado"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
