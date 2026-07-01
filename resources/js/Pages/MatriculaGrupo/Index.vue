<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { type SharedData } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

import WeeklyScheduleDialog from './components/WeeklyScheduleDialog.vue';
import ImportGradesDialog from './components/ImportGradesDialog.vue';
import MatriculaFiltersBar from './components/MatriculaFiltersBar.vue';
import StudentEnrollmentTable from './components/StudentEnrollmentTable.vue';
import AdminGruposTable from './components/AdminGruposTable.vue';

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

const props = defineProps<{
    matriculas?: MatriculaGrupo[];
    grupos?: {
        data: GrupoAdmin[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    periodosDisponibles?: string[];
    canEnroll?: boolean;
    canEnrollPeriod?: boolean;
    filters?: {
        search?: string;
        periodo?: string;
        rendimiento?: string;
    };
}>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

onMounted(() => {
    console.log('[DEBUG FRONT] Datos de MatriculaGrupo/Index:', {
        user: user.value,
        canEnroll: props.canEnroll,
        canEnrollPeriod: props.canEnrollPeriod,
        props: props
    });
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: user.value?.is_estudiante ? 'Mis Grupos' : 'Inscripciones a Grupos', href: route('matriculas-grupo.index') },
]);

// Weekly schedule data for student view
const studentHorarios = computed(() => {
    const list: any[] = [];
    if (!props.matriculas) return list;
    props.matriculas.forEach((matricula) => {
        if ((matricula.estado === 'en_curso' || matricula.estado === 'inscrito') && matricula.grupo?.horarios) {
            matricula.grupo.horarios.forEach((h) => {
                list.push({
                    dia: h.dia,
                    hora_inicio: h.hora_inicio,
                    hora_fin: h.hora_fin,
                    grupo_codigo: matricula.grupo.codigo,
                    materia_nombre: matricula.grupo.materia?.nombre,
                    aula_nombre: h.aula?.nombre,
                });
            });
        }
    });
    return list;
});

// Filters state
const searchQuery = ref(props.filters?.search || '');
const selectedEstado = ref('');
const selectedPeriodo = ref(props.filters?.periodo || '');
const selectedRendimiento = ref(props.filters?.rendimiento || '');

const periodosDisponibles = computed(() => {
    return props.periodosDisponibles || [];
});

const filteredStudentMatriculas = computed(() => {
    if (!props.matriculas) return [];
    return props.matriculas.filter((m) => {
        const matchesQuery =
            !searchQuery.value ||
            (m.grupo?.codigo || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (m.grupo?.materia?.nombre || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesEstado = !selectedEstado.value || m.estado === selectedEstado.value;
        return matchesQuery && matchesEstado;
    });
});

// Import grades dialog state
const isImportModalOpen = ref(false);
const activeImportGrupo = ref<GrupoAdmin | null>(null);

const openImportModal = (grupo: GrupoAdmin) => {
    activeImportGrupo.value = grupo;
    isImportModalOpen.value = true;
};

// Backend Filters and Search Debouncing for Admin View
let searchTimeout: any = null;
watch([searchQuery, selectedPeriodo, selectedRendimiento], ([newSearch, newPeriodo, newRendimiento], [oldSearch, oldPeriodo, oldRendimiento]) => {
    if (user.value.is_estudiante) return;

    if (searchTimeout) clearTimeout(searchTimeout);

    const performSearch = () => {
        router.get(
            route('matriculas-grupo.index'),
            {
                search: newSearch,
                periodo: newPeriodo,
                rendimiento: newRendimiento,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            }
        );
    };

    if (newSearch !== oldSearch) {
        searchTimeout = setTimeout(performSearch, 300);
    } else {
        performSearch();
    }
});
</script>

<template>

    <Head :title="user.is_estudiante ? 'Mis Grupos' : 'Inscripciones a Grupos'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">
                        {{ user.is_estudiante ? 'Mis Grupos' : 'Inscripciones a Grupos' }}
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        {{ user.is_estudiante
                            ? 'Administra tus materias inscritas y horarios de clases.'
                            : 'Administración y control de inscripciones de estudiantes en los diferentes grupos académicos.'
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <WeeklyScheduleDialog v-if="user.is_estudiante && studentHorarios.length > 0"
                        :horarios="studentHorarios" />
                    <Button v-if="user.is_estudiante && canEnrollPeriod" as-child variant="outline">
                        <Link :href="route('matriculas-periodo.create')">Inscribir Período</Link>
                    </Button>
                    <Button v-if="user.is_estudiante && canEnroll" as-child>
                        <Link :href="route('matriculas-grupo.create')">
                            Inscribir Grupo
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Success Alert -->
            <div v-if="$page.props.flash?.success"
                class="p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-sm text-green-600 dark:text-green-400">
                {{ $page.props.flash.success }}
            </div>

            <!-- Import Error Banner -->
            <div v-if="$page.props.errors?.import_errors"
                class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-md p-4 space-y-2">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-400">Errores en la carga de notas:</h4>
                <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-500 space-y-1">
                    <li v-for="(error, index) in $page.props.errors.import_errors" :key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Validation Error Banner -->
            <div v-if="$page.props.errors?.archivo"
                class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-md p-4">
                <p class="text-sm text-red-800 dark:text-red-400">{{ $page.props.errors.archivo }}</p>
            </div>

            <!-- Warning banner for inactive/closed enrollment period -->
            <div v-if="user.is_estudiante && !canEnroll && !canEnrollPeriod"
                class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-200 dark:border-yellow-900 rounded-md p-4 flex items-start gap-3">
                <span class="text-yellow-600 dark:text-yellow-400 mt-0.5">⚠️</span>
                <div>
                    <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-400">Inscripciones No Disponibles
                    </h4>
                    <p class="text-xs text-yellow-700 dark:text-yellow-500 mt-1">
                        El período de inscripción para tu oferta académica no está activo o ha finalizado.
                    </p>
                </div>
            </div>

            <!-- Filters Bar -->
            <MatriculaFiltersBar :is-student="user.is_estudiante" :periodos-disponibles="periodosDisponibles"
                :search-query="searchQuery" :selected-estado="selectedEstado" :selected-periodo="selectedPeriodo"
                :selected-rendimiento="selectedRendimiento" @update:search-query="searchQuery = $event"
                @update:selected-estado="selectedEstado = $event" @update:selected-periodo="selectedPeriodo = $event"
                @update:selected-rendimiento="selectedRendimiento = $event" />

            <!-- Import Grades Dialog (admin only) -->
            <ImportGradesDialog v-if="!user.is_estudiante" :grupo="activeImportGrupo"
                v-model:open="isImportModalOpen" />

            <!-- Data Table Card -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ user.is_estudiante ? 'Grupos Inscritos' : 'Listado de Grupos Académicos' }}
                    </CardTitle>
                    <CardDescription>
                        {{ user.is_estudiante
                            ? 'Materias registradas y cursadas en el ciclo actual o pasados.'
                            : 'Visualización detallada de la oferta académica por grupos, cupos e índices de aprobación.' }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <StudentEnrollmentTable v-if="user.is_estudiante" :matriculas="filteredStudentMatriculas" />
                        <AdminGruposTable v-else :grupos="grupos?.data || []" @import-grades="openImportModal" />
                    </div>

                    <!-- Pagination Links (Admin only) -->
                    <div v-if="!user.is_estudiante && grupos && grupos.links && grupos.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4 bg-card">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="grupos.prev_page_url || '#'"
                                class="relative inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !grupos.prev_page_url }"
                                preserve-scroll
                            >
                                Anterior
                            </Link>
                            <Link
                                :href="grupos.next_page_url || '#'"
                                class="relative ml-3 inline-flex items-center rounded-md border border-border bg-card px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                                :class="{ 'opacity-50 pointer-events-none': !grupos.next_page_url }"
                                preserve-scroll
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Mostrando del
                                    <span class="font-medium text-foreground">{{ grupos.from || 0 }}</span>
                                    al
                                    <span class="font-medium text-foreground">{{ grupos.to || 0 }}</span>
                                    de
                                    <span class="font-medium text-foreground">{{ grupos.total || 0 }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in grupos.links"
                                        :key="i"
                                        :href="link.url || '#'"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-semibold focus:z-20"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-primary text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                                                : 'text-foreground ring-1 ring-inset ring-border hover:bg-muted/50 focus:outline-offset-0',
                                            !link.url ? 'opacity-50 pointer-events-none' : '',
                                            i === 0 ? 'rounded-l-md' : '',
                                            i === grupos.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                        preserve-scroll
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
