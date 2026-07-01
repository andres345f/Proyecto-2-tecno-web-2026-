<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { type SharedData } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const isEstudiante = computed(() => user.value?.is_estudiante);

interface MatriculaGrupo {
    id: number;
    nota_final: string | null;
    estado: string;
    created_at: string;
    grupo: {
        id: number;
        codigo: string;
        materia: { nombre: string; codigo: string; descripcion: string };
        docente: { name: string; email?: string };
        horarios: Array<{
            dia: string;
            hora_inicio: string;
            hora_fin: string;
            aula: { nombre: string; codigo: string; capacidad: number };
        }>;
    };
    matricula_periodo: {
        periodo_academico: { nombre: string; fecha_inicio: string; fecha_fin: string };
        matricula_carrera: {
            usuario: { name: string; email: string };
            oferta_academica: { nombre: string; codigo: string };
        };
    };
}

interface GrupoAdmin {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string; descripcion: string };
    docente: { name: string; email?: string };
    inscritos_count: number;
    cupo_maximo: number;
    horarios: Array<{
        dia: string;
        hora_inicio: string;
        hora_fin: string;
        aula: { nombre: string; codigo: string; capacidad: number };
    }>;
    tareas: Array<{
        id: number;
        titulo: string;
        descripcion: string;
        fecha_limite: string;
        puntos: number;
    }>;
    matriculas_grupo: Array<{
        id: number;
        nota_final: string | null;
        estado: string;
        matricula_periodo: {
            matricula_carrera: {
                usuario: { name: string; email: string; codigo_estudiante: string };
            };
        };
    }>;
}

const props = defineProps<{
    matric?: MatriculaGrupo; // compatibility / fallback
    matricula?: MatriculaGrupo;
    grupo?: GrupoAdmin;
}>();

// Resolve active matricula property (accounting for different naming if any)
const activeMatricula = computed(() => props.matricula || props.matric);

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (isEstudiante.value) {
        return [
            { title: 'Dashboard', href: route('dashboard') },
            { title: 'Mis Grupos', href: route('matriculas-grupo.index') },
            { title: activeMatricula.value?.grupo?.codigo || '', href: route('matriculas-grupo.show', activeMatricula.value?.id || 0) },
        ];
    } else {
        return [
            { title: 'Dashboard', href: route('dashboard') },
            { title: 'Inscripciones a Grupos', href: route('matriculas-grupo.index') },
            { title: props.grupo?.codigo || '', href: route('matriculas-grupo.show', props.grupo?.id || 0) },
        ];
    }
});

const estadoBadge = (estado: string) => {
    switch (estado) {
        case 'inscrito':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'en_curso':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'aprobado':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'reprobado':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'retirado':
            return 'bg-secondary text-secondary-foreground';
        default:
            return 'bg-secondary text-secondary-foreground';
    }
};

const confirmWithdraw = (id: number, isStudent: boolean = false) => {
    const text = isStudent 
        ? '¿Estás seguro de que deseas retirarte de este grupo?' 
        : '¿Estás seguro de que deseas retirar a este estudiante del grupo?';
    if (confirm(text)) {
        router.delete(route('matriculas-grupo.destroy', id));
    }
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Mass notes upload
const importFileForm = useForm({
    archivo: null as File | null,
});

const importFileInput = ref<HTMLInputElement | null>(null);

const handleImportFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    importFileForm.archivo = target.files?.[0] || null;
};

const clearImportFile = () => {
    importFileForm.archivo = null;
    if (importFileInput.value) importFileInput.value.value = '';
};

const submitImportGrades = () => {
    if (!props.grupo) return;
    importFileForm.post(route('matriculas-grupo.importar-notas', props.grupo.id), {
        preserveScroll: true,
        onSuccess: () => {
            importFileForm.reset();
            clearImportFile();
        },
    });
};
</script>

<template>
    <Head :title="isEstudiante ? `Grupo ${activeMatricula?.grupo?.codigo}` : `Grupo ${grupo?.codigo}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl">
            
            <!-- Success Alert -->
            <div v-if="$page.props.flash?.success"
                class="p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-sm text-green-600 dark:text-green-400">
                {{ $page.props.flash.success }}
            </div>

            <!-- Import Error Banner -->
            <div v-if="$page.props.errors?.import_errors" class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-md p-4 space-y-2">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-400">Errores en la carga de notas:</h4>
                <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-500 space-y-1">
                    <li v-for="(error, index) in $page.props.errors.import_errors" :key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Validation Error Banner -->
            <div v-if="$page.props.errors?.archivo" class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-md p-4">
                <p class="text-sm text-red-800 dark:text-red-400">{{ $page.props.errors.archivo }}</p>
            </div>

            <!-- STUDENT VIEW -->
            <template v-if="isEstudiante && activeMatricula">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Grupo {{ activeMatricula.grupo?.codigo }}</h1>
                        <p class="text-sm text-muted-foreground mt-1">Detalle de la inscripción del grupo y horarios correspondientes.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button
                            v-if="activeMatricula.estado === 'en_curso' || activeMatricula.estado === 'inscrito'"
                            as-child
                        >
                            <Link :href="route('grupos.tareas.index', activeMatricula.grupo?.id)">Ver Tareas</Link>
                        </Button>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(activeMatricula.estado)]">
                            {{ activeMatricula.estado }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Grupo Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Grupo</CardTitle>
                            <CardDescription>Detalles académicos de la materia y el docente asignado.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Materia</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ activeMatricula.grupo?.materia?.nombre }}
                                    </dd>
                                    <dd class="text-xs text-muted-foreground">Código: {{ activeMatricula.grupo?.materia?.codigo }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Docente</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ activeMatricula.grupo?.docente?.name }}
                                    </dd>
                                    <dd v-if="activeMatricula.grupo?.docente?.email" class="text-xs text-muted-foreground">
                                        {{ activeMatricula.grupo?.docente?.email }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Descripción</dt>
                                    <dd class="text-sm text-foreground mt-1">
                                        {{ activeMatricula.grupo?.materia?.descripcion || 'Sin descripción' }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Matrícula Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Información de Matrícula</CardTitle>
                            <CardDescription>Detalles de tu inscripción administrativa y desempeño.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Estudiante</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ activeMatricula.matricula_periodo?.matricula_carrera?.usuario?.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Carrera</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ activeMatricula.matricula_periodo?.matricula_carrera?.oferta_academica?.nombre }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Período</dt>
                                    <dd class="text-sm text-foreground mt-1">
                                        {{ activeMatricula.matricula_periodo?.periodo_academico?.nombre }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Nota Final</dt>
                                    <dd class="text-sm text-foreground mt-1 font-semibold font-mono">
                                        {{ activeMatricula.nota_final ?? 'Pendiente' }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <!-- Horarios -->
                <Card>
                    <CardHeader>
                        <CardTitle>Horarios del Grupo</CardTitle>
                        <CardDescription>Días, horas y aulas asignadas para este grupo.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="activeMatricula.grupo?.horarios?.length" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Día</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Hora Inicio</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Hora Fin</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Aula</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Capacidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="horario in activeMatricula.grupo?.horarios" :key="horario.dia + horario.hora_inicio" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle font-semibold text-foreground">{{ horario.dia }}</td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-mono">{{ horario.hora_inicio }}</td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-mono">{{ horario.hora_fin }}</td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-medium">
                                            {{ horario.aula?.nombre }} ({{ horario.aula?.codigo }})
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                            {{ horario.aula?.capacidad }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="p-6 text-center text-muted-foreground">No hay horarios asignados.</p>
                    </CardContent>
                </Card>

                <div>
                    <Button variant="ghost" as-child>
                        <Link :href="route('matriculas-grupo.index')">
                            ← Volver a Mis Grupos
                        </Link>
                    </Button>
                </div>
            </template>

            <!-- ADMINISTRATOR VIEW -->
            <template v-else-if="grupo">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Grupo {{ grupo.codigo }}</h1>
                        <p class="text-sm text-muted-foreground mt-1">Panel administrativo del grupo, asignación de tareas, horarios y listado de inscritos.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button as-child>
                            <Link :href="route('grupos.tareas.index', grupo.id)">Gestionar Tareas</Link>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Grupo Info Admin -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Grupo</CardTitle>
                            <CardDescription>Detalles académicos y capacidad actual.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Materia</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ grupo.materia?.nombre }}
                                    </dd>
                                    <dd class="text-xs text-muted-foreground">Código: {{ grupo.materia?.codigo }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Docente</dt>
                                    <dd class="text-sm text-foreground mt-1 font-medium">
                                        {{ grupo.docente?.name || 'No asignado' }}
                                    </dd>
                                    <dd v-if="grupo.docente?.email" class="text-xs text-muted-foreground">
                                        {{ grupo.docente?.email }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-muted-foreground">Cupos Ocupados</dt>
                                    <dd class="text-sm text-foreground mt-1">
                                        <div class="flex flex-col gap-1.5 max-w-xs mt-1">
                                            <div class="flex justify-between text-xs font-semibold">
                                                <span>{{ grupo.inscritos_count }} / {{ grupo.cupo_maximo }} estudiantes</span>
                                                <span>{{ Math.round((grupo.inscritos_count / grupo.cupo_maximo) * 100) }}%</span>
                                            </div>
                                            <div class="w-full bg-secondary h-2 rounded-full overflow-hidden">
                                                <div 
                                                    class="bg-primary h-full transition-all" 
                                                    :style="{ width: `${Math.min(100, (grupo.inscritos_count / grupo.cupo_maximo) * 100)}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Horarios Admin -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Horarios de Clases</CardTitle>
                            <CardDescription>Planificación horaria semanal del grupo.</CardDescription>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div v-if="grupo.horarios?.length" class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead class="bg-muted/50 border-b border-border">
                                        <tr>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Día</th>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Inicio / Fin</th>
                                            <th class="h-10 px-4 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Aula</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="horario in grupo.horarios" :key="horario.dia + horario.hora_inicio" class="hover:bg-muted/30 transition-colors">
                                            <td class="px-4 py-3 align-middle font-semibold text-foreground">{{ horario.dia }}</td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground font-mono">
                                                {{ horario.hora_inicio.slice(0, 5) }} - {{ horario.hora_fin.slice(0, 5) }}
                                            </td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground font-medium">
                                                {{ horario.aula?.nombre }} ({{ horario.aula?.codigo }})
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="p-6 text-center text-muted-foreground text-sm">No hay horarios asignados a este grupo.</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Tareas del Grupo -->
                <Card>
                    <CardHeader>
                        <CardTitle>Tareas del Grupo</CardTitle>
                        <CardDescription>Tareas y actividades asignadas a los alumnos en este grupo.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="grupo.tareas?.length" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Tarea</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Fecha Límite</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Puntos</th>
                                        <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="tarea in grupo.tareas" :key="tarea.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-foreground">{{ tarea.titulo }}</span>
                                                <span class="text-xs text-muted-foreground max-w-sm truncate">{{ tarea.descripcion }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                            {{ formatDate(tarea.fecha_limite) }}
                                        </td>
                                        <td class="px-6 py-4 align-middle text-foreground font-semibold font-mono">
                                            {{ tarea.puntos }} pts
                                        </td>
                                        <td class="px-6 py-4 align-middle text-right">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="route('grupos.tareas.show', { grupo: grupo.id, tarea: tarea.id })">Ver Detalle</Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="p-6 text-center text-muted-foreground">No se han creado tareas en este grupo todavía.</p>
                    </CardContent>
                </Card>

                <!-- Estudiantes Matriculados -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4 border-b border-border">
                        <div>
                            <CardTitle>Estudiantes Matriculados</CardTitle>
                            <CardDescription>Lista de estudiantes inscritos activamente en este grupo.</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" as-child>
                                <a :href="route('matriculas-grupo.plantilla-notas', grupo.id)" download>
                                    📥 Descargar Plantilla de Notas
                                </a>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <!-- Inline mass notes upload section -->
                        <div class="p-4 bg-muted/30 border-b border-border flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-semibold text-foreground">Carga Masiva de Notas</h4>
                                <p class="text-xs text-muted-foreground">Sube el archivo CSV rellenado para actualizar las notas del grupo.</p>
                            </div>
                            <form @submit.prevent="submitImportGrades" class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="file"
                                        ref="importFileInput"
                                        accept=".csv,.txt"
                                        @change="handleImportFileSelect"
                                        class="flex h-9 w-64 rounded-md border border-input bg-background px-3 py-1.5 text-xs ring-offset-background file:border-0 file:bg-transparent file:text-xs file:font-medium text-muted-foreground"
                                    />
                                    <Button v-if="importFileForm.archivo" type="button" variant="ghost" size="sm" @click="clearImportFile" class="text-xs h-7">
                                        Limpiar
                                    </Button>
                                </div>
                                <Button type="submit" size="sm" :disabled="!importFileForm.archivo || importFileForm.processing">
                                    Cargar Notas
                                </Button>
                            </form>
                        </div>

                        <div v-if="grupo.matriculas_grupo?.length" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estudiante</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Código</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                                        <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">Nota Final</th>
                                        <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="mat in grupo.matriculas_grupo" :key="mat.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-foreground">
                                                    {{ mat.matricula_periodo?.matricula_carrera?.usuario?.name }}
                                                </span>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ mat.matricula_periodo?.matricula_carrera?.usuario?.email }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground font-mono text-xs">
                                            {{ mat.matricula_periodo?.matricula_carrera?.usuario?.codigo_estudiante || 'Sin código' }}
                                        </td>
                                        <td class="px-6 py-4 align-middle">
                                            <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider', estadoBadge(mat.estado)]">
                                                {{ mat.estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 align-middle font-mono font-semibold text-foreground">
                                            {{ mat.nota_final ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 align-middle text-right">
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="confirmWithdraw(mat.id, false)"
                                            >
                                                Retirarse Estudiante
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="p-6 text-center text-muted-foreground">No hay estudiantes matriculados en este grupo.</p>
                    </CardContent>
                </Card>

                <div>
                    <Button variant="ghost" as-child>
                        <Link :href="route('matriculas-grupo.index')">
                            ← Volver a Grupos
                        </Link>
                    </Button>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
