<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Bar, Doughnut, Pie, Line } from 'vue-chartjs';
import {
    GraduationCap,
    DollarSign,
    Activity,
    Building2,
    TrendingUp,
    Clock,
    CreditCard,
    AlertTriangle,
    Users,
    CheckCircle2,
    Calendar,
    ArrowUpRight,
    Search,
    FileSpreadsheet,
    FileText,
    Filter,
    RotateCcw
} from 'lucide-vue-next';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reportes',
        href: route('reportes.index'),
    },
];

interface OfertaData {
    oferta_academica_id: number;
    oferta_count?: number;
    alumnos_count?: number;
    oferta_academica?: {
        id: number;
        nombre: string;
        codigo: string;
    };
}

interface DeudorData {
    user_id: number;
    nombre: string;
    email: string;
    total_deuda: number;
    cuotas_vencidas: number;
}

interface IngresoMensual {
    mes: string;
    total: number;
}

interface MetodoPago {
    metodo: string;
    cantidad: number;
    total: number;
}

interface RendimientoMateria {
    materia_id: number;
    nombre: string;
    codigo: string;
    total: number;
    aprobados: number;
    reprobados: number;
    retirados: number;
    tasa_aprobacion: number;
    tasa_reprobacion: number;
    tasa_retirados: number;
}

interface EstadisticasTareas {
    total_tareas: number;
    total_entregas: number;
    entregas_a_tiempo: number;
    entregas_tarde: number;
    entregas_pendientes: number;
    tasa_entrega: number;
}

interface VisitasActivas {
    dau: { fecha: string; usuarios: number }[];
    mau: { mes: string; usuarios: number }[];
    roles: Record<string, number>;
}

interface PaginaVisitada {
    url: string;
    cantidad: number;
}

interface OcupacionGrupo {
    grupo_id: number;
    codigo: string;
    materia: string;
    materia_codigo: string;
    docente: string;
    inscritos: number;
    capacidad: number;
    porcentaje_ocupacion: number;
}

const props = defineProps<{
    // Filters and metadata
    periodos: { id: number; nombre: string; fecha_inicio: string; fecha_fin: string; estado: string }[];
    filters: {
        periodo_academico_id: string | number | null;
        fecha_inicio: string | null;
        fecha_fin: string | null;
    };

    // Existing
    totalRecaudado: number;
    totalPorCobrar: number;
    cuotasVencidasCount: number;
    matriculasPorOferta: OfertaData[];
    alumnosPorOferta: OfertaData[];
    alumnosDeudores: DeudorData[];
    totalMatriculasActivas: number;
    indiceAprobacion: number;
    indiceReprobacion: number;

    // New Advanced
    ingresosMensuales: IngresoMensual[];
    usoMetodosPago: MetodoPago[];
    rendimientoPorMateria: RendimientoMateria[];
    estadisticasTareas: EstadisticasTareas;
    visitasActivas: VisitasActivas;
    paginasMasVisitadas: PaginaVisitada[];
    ocupacionGrupos: OcupacionGrupo[];
}>();

// Filter State Refs
const filterPeriodo = ref(props.filters?.periodo_academico_id || '');
const filterFechaInicio = ref(props.filters?.fecha_inicio || '');
const filterFechaFin = ref(props.filters?.fecha_fin || '');

// Filter Methods
const aplicarFiltros = () => {
    router.get(route('reportes.index'), {
        periodo_academico_id: filterPeriodo.value,
        fecha_inicio: filterFechaInicio.value,
        fecha_fin: filterFechaFin.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    filterPeriodo.value = '';
    filterFechaInicio.value = '';
    filterFechaFin.value = '';
    router.get(route('reportes.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Export Methods
const exportarExcel = () => {
    const url = route('reportes.export.csv', {
        periodo_academico_id: filterPeriodo.value,
        fecha_inicio: filterFechaInicio.value,
        fecha_fin: filterFechaFin.value,
    });
    window.location.href = url;
};

const exportarPDF = () => {
    window.print();
};

// Navigation Tabs
const activeTab = ref('general');
const tabs = [
    { id: 'general', label: 'General', icon: Activity },
    { id: 'academico', label: 'Académico', icon: GraduationCap },
    { id: 'financiero', label: 'Financiero', icon: DollarSign },
    { id: 'plataforma', label: 'Plataforma', icon: Users },
    { id: 'logistica', label: 'Logística', icon: Building2 },
];

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(value);
};

// 1. Gráfico de Finanzas (Recaudado vs Por Cobrar)
const finanzasData = {
    labels: ['Total Recaudado', 'Total por Cobrar'],
    datasets: [
        {
            backgroundColor: ['#10b981', '#f59e0b'],
            borderColor: ['#059669', '#d97706'],
            borderWidth: 1,
            data: [props.totalRecaudado, props.totalPorCobrar],
        },
    ],
};

const finanzasOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                boxWidth: 12,
                font: { size: 11 },
            },
        },
    },
};

// 2. Gráfico de Matrículas por Carrera
const matriculasData = {
    labels: props.matriculasPorOferta.map((o) => o.oferta_academica?.nombre || o.oferta_academica?.codigo || 'Carrera'),
    datasets: [
        {
            label: 'Matrículas Activas',
            backgroundColor: '#3b82f6',
            borderColor: '#2563eb',
            borderWidth: 1,
            borderRadius: 6,
            data: props.matriculasPorOferta.map((o) => o.oferta_count || 0),
        },
    ],
};

const matriculasOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(156, 163, 175, 0.1)',
            },
            ticks: {
                precision: 0,
            },
        },
        x: {
            grid: { display: false },
        },
    },
};

// 3. Gráfico de Alumnos por Carrera
const alumnosData = {
    labels: props.alumnosPorOferta.map((o) => o.oferta_academica?.nombre || o.oferta_academica?.codigo || 'Carrera'),
    datasets: [
        {
            backgroundColor: ['#6366f1', '#a855f7', '#ec4899', '#14b8a6', '#f43f5e'],
            data: props.alumnosPorOferta.map((o) => o.alumnos_count || 0),
        },
    ],
};

const alumnosOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                boxWidth: 12,
                font: { size: 11 },
            },
        },
    },
};

// 4. Gráfico de Rendimiento Académico
const rendimientoData = {
    labels: ['Aprobación', 'Reprobación'],
    datasets: [
        {
            backgroundColor: ['#10b981', '#ef4444'],
            borderColor: ['#059669', '#dc2626'],
            borderWidth: 1,
            data: [props.indiceAprobacion, props.indiceReprobacion],
        },
    ],
};

const rendimientoOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                boxWidth: 12,
                font: { size: 11 },
            },
        },
    },
};

// 5. NUEVO: Gráfico de Línea - Ingresos Mensuales
const ingresosChartData = {
    labels: props.ingresosMensuales.map((i) => i.mes),
    datasets: [
        {
            label: 'Ingresos Mensuales (BOB)',
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
            pointBackgroundColor: '#10b981',
            pointRadius: 4,
            data: props.ingresosMensuales.map((i) => i.total),
        },
    ],
};

const ingresosChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(156, 163, 175, 0.1)' },
            ticks: {
                callback: (value: any) => formatCurrency(value),
            },
        },
        x: {
            grid: { display: false },
        },
    },
};

// 6. NUEVO: Gráfico Métodos de Pago
const metodosPagoChartData = {
    labels: props.usoMetodosPago.map((m) => m.metodo === 'qr_pagofacil' ? 'QR PagoFácil' : m.metodo),
    datasets: [
        {
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899'],
            data: props.usoMetodosPago.map((m) => m.total),
        },
    ],
};

// 7. NUEVO: Gráfico Rendimiento por Materia (Horizontal Bar)
const rendimientoMateriasChartData = {
    labels: props.rendimientoPorMateria.slice(0, 8).map((m) => m.nombre),
    datasets: [
        {
            label: '% Aprobación',
            backgroundColor: '#10b981',
            borderRadius: 4,
            data: props.rendimientoPorMateria.slice(0, 8).map((m) => m.tasa_aprobacion),
        },
        {
            label: '% Reprobación',
            backgroundColor: '#ef4444',
            borderRadius: 4,
            data: props.rendimientoPorMateria.slice(0, 8).map((m) => m.tasa_reprobacion),
        },
        {
            label: '% Retiro',
            backgroundColor: '#9ca3af',
            borderRadius: 4,
            data: props.rendimientoPorMateria.slice(0, 8).map((m) => m.tasa_retirados),
        },
    ],
};

const rendimientoMateriasOptions = {
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' as const },
    },
    scales: {
        x: {
            max: 100,
            grid: { color: 'rgba(156, 163, 175, 0.1)' },
            ticks: {
                callback: (value: any) => value + '%',
            },
        },
        y: {
            grid: { display: false },
        },
    },
};

// 8. NUEVO: Estado Entregas de Tareas
const tareasChartData = {
    labels: ['A Tiempo', 'Tardías', 'Pendientes'],
    datasets: [
        {
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderColor: ['#059669', '#d97706', '#dc2626'],
            borderWidth: 1,
            data: [
                props.estadisticasTareas.entregas_a_tiempo,
                props.estadisticasTareas.entregas_tarde,
                props.estadisticasTareas.entregas_pendientes
            ],
        },
    ],
};

// 9. NUEVO: Gráfico DAU
const dauChartData = {
    labels: props.visitasActivas.dau.map((d) => d.fecha),
    datasets: [
        {
            label: 'Usuarios Únicos',
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.05)',
            fill: true,
            tension: 0.35,
            borderWidth: 2,
            pointBackgroundColor: '#6366f1',
            pointRadius: 3,
            data: props.visitasActivas.dau.map((d) => d.usuarios),
        },
    ],
};

const dauChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(156, 163, 175, 0.1)' },
            ticks: { precision: 0 },
        },
        x: {
            grid: { display: false },
        },
    },
};

// 10. NUEVO: Gráfico Tráfico por Rol
const rolesChartData = {
    labels: Object.keys(props.visitasActivas.roles),
    datasets: [
        {
            label: 'Visitas Registradas',
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'],
            borderRadius: 6,
            data: Object.values(props.visitasActivas.roles),
        },
    ],
};
</script>

<template>
    <Head title="Reportes Avanzados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Reportes Ejecutivos</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Consola directiva con KPIs de finanzas, rendimiento académico, logística de grupos y tráfico de plataforma.
                    </p>
                </div>
                
                <!-- Modern Tab Selector -->
                <div class="inline-flex rounded-lg bg-muted p-1 border border-border self-start">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200',
                            activeTab === tab.id
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground hover:bg-background/50'
                        ]"
                    >
                        <component :is="tab.icon" class="w-3.5 h-3.5" />
                        <span>{{ tab.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Modern Filter and Export Panel (no-print) -->
            <Card class="no-print border border-border bg-card/60 backdrop-blur-md shadow-sm">
                <CardContent class="p-4">
                    <div class="grid gap-4 md:grid-cols-4 items-end">
                        <!-- Periodo Académico Filter -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Periodo Académico</label>
                            <select v-model="filterPeriodo" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                <option value="">Todos los periodos</option>
                                <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                            </select>
                        </div>

                        <!-- Fecha Inicio Filter -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fecha Inicio</label>
                            <input type="date" v-model="filterFechaInicio" class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" />
                        </div>

                        <!-- Fecha Fin Filter -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fecha Fin</label>
                            <input type="date" v-model="filterFechaFin" class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2" />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2 justify-end">
                            <button @click="aplicarFiltros" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors">
                                <Filter class="w-4 h-4" />
                                <span>Filtrar</span>
                            </button>
                            <button @click="limpiarFiltros" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground transition-colors">
                                <RotateCcw class="w-4 h-4" />
                                <span>Limpiar</span>
                            </button>
                            <button @click="exportarExcel" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-3 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                <FileSpreadsheet class="w-4 h-4" />
                                <span>Excel</span>
                            </button>
                            <button @click="exportarPDF" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-blue-600 px-3 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                                <FileText class="w-4 h-4" />
                                <span>PDF</span>
                            </button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tab Content: GENERAL -->
            <div v-if="activeTab === 'general'" class="flex flex-col gap-6">
                <!-- Summary Cards -->
                <div class="grid gap-6 md:grid-cols-4">
                    <Card class="hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute right-3 top-3 opacity-10 group-hover:scale-110 transition-transform">
                            <DollarSign class="w-16 h-16 text-foreground" />
                        </div>
                        <CardHeader class="pb-2">
                            <CardDescription class="text-xs uppercase font-bold tracking-wider text-muted-foreground">
                                Total Recaudado
                            </CardDescription>
                            <CardTitle class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                {{ formatCurrency(totalRecaudado) }}
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card class="hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute right-3 top-3 opacity-10 group-hover:scale-110 transition-transform">
                            <Clock class="w-16 h-16 text-foreground" />
                        </div>
                        <CardHeader class="pb-2">
                            <CardDescription class="text-xs uppercase font-bold tracking-wider text-muted-foreground">
                                Pendiente por Cobrar
                            </CardDescription>
                            <CardTitle class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-1">
                                {{ formatCurrency(totalPorCobrar) }}
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card class="hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute right-3 top-3 opacity-10 group-hover:scale-110 transition-transform">
                            <GraduationCap class="w-16 h-16 text-foreground" />
                        </div>
                        <CardHeader class="pb-2">
                            <CardDescription class="text-xs uppercase font-bold tracking-wider text-muted-foreground">
                                Tasa Aprobación
                            </CardDescription>
                            <CardTitle class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-1">
                                {{ indiceAprobacion }}%
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card class="hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute right-3 top-3 opacity-10 group-hover:scale-110 transition-transform">
                            <Users class="w-16 h-16 text-foreground" />
                        </div>
                        <CardHeader class="pb-2">
                            <CardDescription class="text-xs uppercase font-bold tracking-wider text-muted-foreground">
                                Matrículas Activas
                            </CardDescription>
                            <CardTitle class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                                {{ totalMatriculasActivas }}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <!-- Basic Analytics Charts -->
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Matrículas por Carrera</CardTitle>
                            <CardDescription>Cantidad de alumnos inscriptos por oferta académica activa.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-64">
                            <div v-if="matriculasPorOferta.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                Sin registros activos
                            </div>
                            <div v-else class="h-full">
                                <Bar :data="matriculasData" :options="matriculasOptions" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Distribución Alumnos</CardTitle>
                            <CardDescription>Representación porcentual de estudiantes por carrera.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-64">
                            <div v-if="alumnosPorOferta.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                Sin datos
                            </div>
                            <div v-else class="h-full">
                                <Pie :data="alumnosData" :options="alumnosOptions" />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Tab Content: ACADEMICO -->
            <div v-if="activeTab === 'academico'" class="flex flex-col gap-6">
                <!-- Academic Stats Row -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Tasa Aprobación General</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-emerald-600">{{ indiceAprobacion }}%</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Tasa de Entrega de Tareas</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-indigo-600">{{ estadisticasTareas.tasa_entrega }}%</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Total Tareas Asignadas</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-foreground">{{ estadisticasTareas.total_tareas }}</CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Rendimiento por Materia (Horizontal Bar) -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Rendimiento Académico por Asignatura</CardTitle>
                            <CardDescription>Porcentaje comparativo de aprobación, reprobación y retiro en materias más pobladas.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80">
                            <div v-if="rendimientoPorMateria.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                No se encontraron datos académicos.
                            </div>
                            <div v-else class="h-full">
                                <Bar :data="rendimientoMateriasChartData" :options="rendimientoMateriasOptions" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Estado Tareas -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Compromiso y Entregas</CardTitle>
                            <CardDescription>Estado global de entregas de tareas asignadas.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80 flex flex-col justify-between">
                            <div class="h-56">
                                <Doughnut :data="tareasChartData" :options="finanzasOptions" />
                            </div>
                            <div class="grid grid-cols-3 text-center text-xs mt-2 border-t border-border pt-4">
                                <div>
                                    <span class="block font-bold text-emerald-600">{{ estadisticasTareas.entregas_a_tiempo }}</span>
                                    <span class="text-[10px] text-muted-foreground uppercase">A tiempo</span>
                                </div>
                                <div>
                                    <span class="block font-bold text-amber-600">{{ estadisticasTareas.entregas_tarde }}</span>
                                    <span class="text-[10px] text-muted-foreground uppercase">Tarde</span>
                                </div>
                                <div>
                                    <span class="block font-bold text-rose-600">{{ estadisticasTareas.entregas_pendientes }}</span>
                                    <span class="text-[10px] text-muted-foreground uppercase">Pendientes</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Tab Content: FINANCIERO -->
            <div v-if="activeTab === 'financiero'" class="flex flex-col gap-6">
                <!-- Financial Cards -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Total Recaudado</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-emerald-600">{{ formatCurrency(totalRecaudado) }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Total por Cobrar</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-amber-600">{{ formatCurrency(totalPorCobrar) }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card class="bg-card">
                        <CardHeader class="pb-2">
                            <CardDescription class="font-bold text-xs uppercase text-muted-foreground">Cuotas Excedidas/Vencidas</CardDescription>
                            <CardTitle class="text-4xl font-extrabold text-rose-600">{{ cuotasVencidasCount }}</CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Line Trend Chart -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Evolución Temporal de Ingresos</CardTitle>
                            <CardDescription>Montos recaudados mensualmente de cuotas completadas.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80">
                            <div v-if="ingresosMensuales.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                Sin transacciones históricas registradas.
                            </div>
                            <div v-else class="h-full">
                                <Line :data="ingresosChartData" :options="ingresosChartOptions" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payment methods -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Medios de Pago</CardTitle>
                            <CardDescription>Distribución de ingresos por pasarela.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80">
                            <div v-if="usoMetodosPago.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                Sin datos de transacciones.
                            </div>
                            <div v-else class="h-full">
                                <Doughnut :data="metodosPagoChartData" :options="finanzasOptions" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Debtor Table -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg font-bold">Deudores Morosos</CardTitle>
                            <CardDescription>Estudiantes con cuotas pendientes vencidas a la fecha.</CardDescription>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                            {{ alumnosDeudores.length }} Alumnos en Mora
                        </span>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="alumnosDeudores.length === 0" class="p-6 text-center text-muted-foreground">
                            Excelente! Sin saldos morosos pendientes.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Alumno</th>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Email</th>
                                        <th class="h-10 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs font-mono">Cuotas Vencidas</th>
                                        <th class="h-10 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs font-mono">Deuda Acumulada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="deudor in alumnosDeudores" :key="deudor.user_id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-3.5 align-middle font-medium text-foreground">{{ deudor.nombre }}</td>
                                        <td class="px-6 py-3.5 align-middle text-muted-foreground">{{ deudor.email }}</td>
                                        <td class="px-6 py-3.5 align-middle text-right font-mono text-muted-foreground">{{ deudor.cuotas_vencidas }}</td>
                                        <td class="px-6 py-3.5 align-middle text-right font-bold text-rose-600 dark:text-rose-400 font-mono">
                                            {{ formatCurrency(deudor.total_deuda) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab Content: PLATAFORMA -->
            <div v-if="activeTab === 'plataforma'" class="flex flex-col gap-6">
                <!-- User Traffic Chart -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Actividad Diaria (DAU)</CardTitle>
                            <CardDescription>Usuarios activos únicos interactuando con el sistema durante los últimos 30 días.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80">
                            <div v-if="visitasActivas.dau.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                                Sin registros de navegación en la base de datos.
                            </div>
                            <div v-else class="h-full">
                                <Line :data="dauChartData" :options="dauChartOptions" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Breakdown roles -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-bold">Interacciones por Rol</CardTitle>
                            <CardDescription>Visitas totales desagregadas por perfil de usuario.</CardDescription>
                        </CardHeader>
                        <CardContent class="h-80">
                            <div class="h-full">
                                <Bar :data="rolesChartData" :options="matriculasOptions" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Page Rank Table -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-bold">Top 10 Páginas más Visitadas</CardTitle>
                        <CardDescription>URLs del sistema que registran mayor número de visitas acumuladas.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div v-if="paginasMasVisitadas.length === 0" class="p-6 text-center text-muted-foreground">
                            Sin visitas registradas.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Rango</th>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Ruta URL</th>
                                        <th class="h-10 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs font-mono">Visitas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="(pagina, index) in paginasMasVisitadas" :key="pagina.url" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-3.5 align-middle font-bold text-muted-foreground">#{{ index + 1 }}</td>
                                        <td class="px-6 py-3.5 align-middle font-mono text-foreground text-xs">{{ pagina.url }}</td>
                                        <td class="px-6 py-3.5 align-middle text-right font-bold text-indigo-600 font-mono">{{ pagina.cantidad }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tab Content: LOGISTICA -->
            <div v-if="activeTab === 'logistica'" class="flex flex-col gap-6">
                <!-- Logistics Alert Banner -->
                <Card class="bg-card">
                    <CardHeader class="flex flex-row items-start gap-4">
                        <div class="p-2 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            <AlertTriangle class="w-6 h-6" />
                        </div>
                        <div>
                            <CardTitle class="text-lg font-bold">Optimización y Cupos de Grupos</CardTitle>
                            <CardDescription>
                                A continuación se detalla la eficiencia de asignación estudiantil. Un grupo en rojo indica **sobrecupo** (>95%), mientras que en amarillo indica **baja matrícula** (<20%), lo que compromete la rentabilidad del grupo.
                            </CardDescription>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Groups Capacities Table -->
                <Card>
                    <CardContent class="p-0">
                        <div v-if="ocupacionGrupos.length === 0" class="p-6 text-center text-muted-foreground">
                            No hay grupos académicos creados.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b border-border">
                                    <tr>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Grupo</th>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Materia</th>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Docente</th>
                                        <th class="h-10 px-6 text-center align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Inscriptos / Cupo</th>
                                        <th class="h-10 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Capacidad (%)</th>
                                        <th class="h-10 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="grupo in ocupacionGrupos" :key="grupo.grupo_id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 align-middle font-bold text-foreground">{{ grupo.codigo }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            <span class="block font-medium text-foreground">{{ grupo.materia }}</span>
                                            <span class="text-xs text-muted-foreground">{{ grupo.materia_codigo }}</span>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-muted-foreground">{{ grupo.docente }}</td>
                                        <td class="px-6 py-4 align-middle text-center font-mono">
                                            <span class="font-bold">{{ grupo.inscritos }}</span> / {{ grupo.capacidad }}
                                        </td>
                                        <td class="px-6 py-4 align-middle w-1/4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-full bg-secondary rounded-full h-2">
                                                    <div
                                                        :class="[
                                                            'h-2 rounded-full',
                                                            grupo.porcentaje_ocupacion > 95 ? 'bg-rose-500' : grupo.porcentaje_ocupacion < 20 ? 'bg-amber-500' : 'bg-emerald-500'
                                                        ]"
                                                        :style="{ width: `${Math.min(100, grupo.porcentaje_ocupacion)}%` }"
                                                    ></div>
                                                </div>
                                                <span class="font-mono text-xs font-semibold">{{ grupo.porcentaje_ocupacion }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-right">
                                            <span
                                                v-if="grupo.porcentaje_ocupacion > 95"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300"
                                            >
                                                Sobrecupo
                                            </span>
                                            <span
                                                v-else-if="grupo.porcentaje_ocupacion < 20"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                            >
                                                Baja Matrícula
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                            >
                                                Saludable
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    /* Hide layout elements like sidebar, navbar, tab selectors, and filter controls */
    aside,
    header,
    nav,
    .no-print,
    button,
    .inline-flex,
    .tab-selector-container {
        display: none !important;
    }
    
    /* Ensure print content takes full width and backgrounds are visible */
    body {
        background: white !important;
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .flex-col, .grid {
        display: block !important;
        width: 100% !important;
    }
    
    .card, Card {
        break-inside: avoid;
        margin-bottom: 24px !important;
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
        background: white !important;
    }
    
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
}
</style>
