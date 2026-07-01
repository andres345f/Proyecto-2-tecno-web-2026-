<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';

interface Docente {
    id: number;
    name: string;
}

interface Aula {
    id: number;
    nombre: string;
    codigo: string;
}

interface Horario {
    id: number;
    dia: string;
    hora_inicio: string;
    hora_fin: string;
    aula?: Aula;
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
}

interface GrupoPeriodo {
    id: number;
    grupo_id: number;
    periodo_academico_id: number;
    docente_id: number;
    cupo_maximo: number;
    grupo: Grupo;
    docente?: Docente;
    horarios: Horario[];
}

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    fecha_inicio: string;
    fecha_fin: string;
    oferta_academica: { nombre: string; codigo: string };
    grupo_periodos?: GrupoPeriodo[];
}

const props = defineProps<{
    periodo: PeriodoAcademico;
    docentes: Docente[];
    gruposCatalog: Grupo[];
    aulas: Aula[];
    tienePeriodoAnterior: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Períodos Académicos', href: route('periodos-academicos.index') },
    { title: props.periodo.nombre, href: route('periodos-academicos.show', props.periodo.id) },
];

const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

// Modales states
const showAddGroupModal = ref(false);
const showEditGroupModal = ref(false);
const showAddHorarioModal = ref(false);

const selectedGrupoPeriodo = ref<GrupoPeriodo | null>(null);

// Forms
const addGroupForm = useForm({
    grupo_id: '',
    periodo_academico_id: props.periodo.id,
    docente_id: '',
    cupo_maximo: 35,
});

const editGroupForm = useForm({
    docente_id: '',
    cupo_maximo: 35,
});

const addHorarioForm = useForm({
    dia: '',
    hora_inicio: '',
    hora_fin: '',
    aula_id: '',
    grupo_periodo_id: '' as string | number,
});

// Computed properties
const availableCatalogGroups = computed(() => {
    const activeGroupIds = props.periodo.grupo_periodos?.map(gp => gp.grupo_id) || [];
    return props.gruposCatalog.filter(g => !activeGroupIds.includes(g.id));
});

// Handlers
const openAddGroupModal = () => {
    addGroupForm.reset();
    showAddGroupModal.value = true;
};

const submitAddGroup = () => {
    addGroupForm.post(route('grupo-periodo.store'), {
        onSuccess: () => {
            showAddGroupModal.value = false;
            addGroupForm.reset();
        }
    });
};

const openEditGroupModal = (gp: GrupoPeriodo) => {
    selectedGrupoPeriodo.value = gp;
    editGroupForm.docente_id = gp.docente_id ? gp.docente_id.toString() : '';
    editGroupForm.cupo_maximo = gp.cupo_maximo;
    showEditGroupModal.value = true;
};

const submitEditGroup = () => {
    if (!selectedGrupoPeriodo.value) return;
    editGroupForm.put(route('grupo-periodo.update', selectedGrupoPeriodo.value.id), {
        onSuccess: () => {
            showEditGroupModal.value = false;
            selectedGrupoPeriodo.value = null;
        }
    });
};

const deleteGrupoPeriodo = (id: number) => {
    if (confirm('¿Estás seguro de que deseas quitar este grupo del período académico? Esto también eliminará las matrículas y entregas relacionadas.')) {
        router.delete(route('grupo-periodo.destroy', id));
    }
};

const openAddHorarioModal = (gp: GrupoPeriodo) => {
    selectedGrupoPeriodo.value = gp;
    addHorarioForm.reset();
    addHorarioForm.grupo_periodo_id = gp.id;
    showAddHorarioModal.value = true;
};

const submitAddHorario = () => {
    addHorarioForm.post(route('horarios.store'), {
        onSuccess: () => {
            showAddHorarioModal.value = false;
            addHorarioForm.reset();
        }
    });
};

const deleteHorario = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este horario?')) {
        router.delete(route('horarios.destroy', id));
    }
};

const copiarDesdeAnterior = () => {
    if (confirm('¿Deseas clonar todos los grupos y horarios del período anterior? No se duplicarán los grupos que ya estén asignados en el período actual.')) {
        router.post(route('periodos-academicos.copiar-grupos', props.periodo.id));
    }
};

const formatTime = (time: string) => {
    if (!time) return '';
    return time.substring(0, 5);
};
</script>

<template>
    <Head :title="periodo.nombre" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto w-full">
            
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">{{ periodo.nombre }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Configuración del ciclo lectivo y administración de la oferta de grupos.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('periodos-academicos.edit', props.periodo.id)">Editar Período</Link>
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link :href="route('periodos-academicos.index')">Volver</Link>
                    </Button>
                </div>
            </div>

            <!-- Details Card -->
            <Card class="border border-muted">
                <CardHeader>
                    <CardTitle>Información del Período</CardTitle>
                    <CardDescription>Resumen de fechas y datos específicos asignados.</CardDescription>
                </CardHeader>
                <CardContent class="p-6 pt-0">
                    <dl class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Oferta Académica</dt>
                            <dd class="text-base font-bold text-foreground">
                                {{ periodo.oferta_academica?.nombre }}
                                <span class="text-xs font-mono font-normal text-muted-foreground bg-muted px-2 py-0.5 rounded ml-1">
                                    {{ periodo.oferta_academica?.codigo }}
                                </span>
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tipo</dt>
                            <dd class="text-base font-bold text-foreground capitalize">{{ periodo.tipo }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Fecha Inicio</dt>
                            <dd class="text-base font-medium text-foreground font-mono">
                                {{ new Date(periodo.fecha_inicio).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Fecha Fin</dt>
                            <dd class="text-base font-medium text-foreground font-mono">
                                {{ new Date(periodo.fecha_fin).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Grupos y Oferta Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground">Grupos Ofertados en el Período</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Asignación de asignaturas, docentes, horarios y control de cupos.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button 
                            v-if="tienePeriodoAnterior" 
                            variant="outline" 
                            @click="copiarDesdeAnterior"
                            class="bg-background hover:bg-accent/10 border-primary/30 text-primary"
                        >
                            📋 Cargar de Período Anterior
                        </Button>
                        <Button @click="openAddGroupModal">
                            + Instanciar Grupo
                        </Button>
                    </div>
                </div>

                <Card class="border border-muted">
                    <CardContent class="p-0">
                        <div v-if="periodo.grupo_periodos && periodo.grupo_periodos.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs uppercase bg-muted/65 text-muted-foreground border-b">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Grupo</th>
                                        <th class="px-6 py-3 font-semibold">Materia / Asignatura</th>
                                        <th class="px-6 py-3 font-semibold">Docente</th>
                                        <th class="px-6 py-3 font-semibold">Cupo</th>
                                        <th class="px-6 py-3 font-semibold">Horario e Instalaciones</th>
                                        <th class="px-6 py-3 font-semibold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="gp in periodo.grupo_periodos" :key="gp.id" class="hover:bg-accent/5 transition-colors">
                                        <td class="px-6 py-4 font-bold text-foreground font-mono">
                                            {{ gp.grupo?.codigo }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-foreground">{{ gp.grupo?.materia?.nombre }}</div>
                                            <div class="text-xs font-mono text-muted-foreground mt-0.5">{{ gp.grupo?.materia?.codigo }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-foreground font-medium">
                                            {{ gp.docente?.name || 'Sin docente asignado' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono font-medium text-foreground">
                                            {{ gp.cupo_maximo }} estudiantes
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1">
                                                <div v-for="h in gp.horarios" :key="h.id" class="flex items-center gap-2 text-xs bg-muted/50 px-2 py-1 rounded border border-border/40 w-fit">
                                                    <span class="font-semibold text-foreground capitalize">{{ h.dia }}:</span>
                                                    <span class="text-muted-foreground font-mono">{{ formatTime(h.hora_inicio) }} - {{ formatTime(h.hora_fin) }}</span>
                                                    <span class="text-foreground" v-if="h.aula">({{ h.aula.nombre }})</span>
                                                    <button 
                                                        @click="deleteHorario(h.id)" 
                                                        class="text-destructive hover:bg-destructive/10 rounded-full p-0.5 ml-1 transition-all"
                                                        title="Eliminar Horario"
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                                <div v-if="!gp.horarios || gp.horarios.length === 0" class="text-xs text-muted-foreground italic">
                                                    Sin horarios registrados.
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <Button size="sm" variant="outline" @click="openAddHorarioModal(gp)">
                                                    + Horario
                                                </Button>
                                                <Button size="sm" variant="outline" @click="openEditGroupModal(gp)">
                                                    Editar
                                                </Button>
                                                <Button size="sm" variant="ghost" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="deleteGrupoPeriodo(gp.id)">
                                                    Quitar
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-14 border border-dashed rounded-lg">
                            <p class="text-muted-foreground text-sm font-medium">No se han registrado grupos en este período académico.</p>
                            <p class="text-xs text-muted-foreground/80 mt-1">Registra un grupo de forma manual o clónalo desde el ciclo lectivo anterior.</p>
                            <div class="flex items-center justify-center gap-3 mt-4">
                                <Button 
                                    v-if="tienePeriodoAnterior" 
                                    variant="outline" 
                                    @click="copiarDesdeAnterior"
                                    class="bg-background hover:bg-accent/10 border-primary/30 text-primary"
                                >
                                    📋 Cargar de Período Anterior
                                </Button>
                                <Button @click="openAddGroupModal">
                                    + Instanciar Grupo
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Modales -->

            <!-- Modal: Instanciar Grupo -->
            <div v-if="showAddGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <Card class="w-full max-w-lg shadow-2xl border border-muted bg-card">
                    <CardHeader class="border-b">
                        <CardTitle>Instanciar Grupo en el Período</CardTitle>
                        <CardDescription>Crea la oferta académica de un grupo para el período activo.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitAddGroup" class="space-y-4">
                            <div class="space-y-1.5">
                                <Label for="add_grupo_id" class="text-sm font-semibold">Grupo (Catálogo)</Label>
                                <select 
                                    id="add_grupo_id" 
                                    v-model="addGroupForm.grupo_id" 
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900"
                                    required
                                >
                                    <option value="">Seleccionar grupo...</option>
                                    <option v-for="g in availableCatalogGroups" :key="g.id" :value="g.id">
                                        {{ g.codigo }} — Materia: {{ g.materia?.nombre }}
                                    </option>
                                </select>
                                <InputError :message="addGroupForm.errors.grupo_id" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="add_docente_id" class="text-sm font-semibold">Docente Asignado</Label>
                                <select 
                                    id="add_docente_id" 
                                    v-model="addGroupForm.docente_id" 
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900"
                                    required
                                >
                                    <option value="">Seleccionar docente...</option>
                                    <option v-for="d in docentes" :key="d.id" :value="d.id">
                                        {{ d.name }}
                                    </option>
                                </select>
                                <InputError :message="addGroupForm.errors.docente_id" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="add_cupo_maximo" class="text-sm font-semibold">Cupo Máximo</Label>
                                <Input 
                                    id="add_cupo_maximo" 
                                    v-model="addGroupForm.cupo_maximo" 
                                    type="number" 
                                    min="1" 
                                    required 
                                    class="bg-background"
                                />
                                <InputError :message="addGroupForm.errors.cupo_maximo" />
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t mt-4">
                                <Button type="button" variant="outline" @click="showAddGroupModal = false">
                                    Cancelar
                                </Button>
                                <Button type="submit" :disabled="addGroupForm.processing">
                                    {{ addGroupForm.processing ? 'Registrando...' : 'Asignar Grupo' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Modal: Editar Grupo -->
            <div v-if="showEditGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <Card class="w-full max-w-lg shadow-2xl border border-muted bg-card">
                    <CardHeader class="border-b">
                        <CardTitle>Editar Grupo del Período</CardTitle>
                        <CardDescription>
                            Modificá el docente o el cupo del grupo <span class="font-mono font-bold text-primary">{{ selectedGrupoPeriodo?.grupo?.codigo }}</span>.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitEditGroup" class="space-y-4">
                            <div class="space-y-1.5">
                                <Label for="edit_docente_id" class="text-sm font-semibold">Docente Asignado</Label>
                                <select 
                                    id="edit_docente_id" 
                                    v-model="editGroupForm.docente_id" 
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900"
                                    required
                                >
                                    <option value="">Seleccionar docente...</option>
                                    <option v-for="d in docentes" :key="d.id" :value="d.id">
                                        {{ d.name }}
                                    </option>
                                </select>
                                <InputError :message="editGroupForm.errors.docente_id" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="edit_cupo_maximo" class="text-sm font-semibold">Cupo Máximo</Label>
                                <Input 
                                    id="edit_cupo_maximo" 
                                    v-model="editGroupForm.cupo_maximo" 
                                    type="number" 
                                    min="1" 
                                    required 
                                    class="bg-background"
                                />
                                <InputError :message="editGroupForm.errors.cupo_maximo" />
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t mt-4">
                                <Button type="button" variant="outline" @click="showEditGroupModal = false">
                                    Cancelar
                                </Button>
                                <Button type="submit" :disabled="editGroupForm.processing">
                                    {{ editGroupForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Modal: Agregar Horario -->
            <div v-if="showAddHorarioModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <Card class="w-full max-w-lg shadow-2xl border border-muted bg-card">
                    <CardHeader class="border-b">
                        <CardTitle>Agregar Horario</CardTitle>
                        <CardDescription>
                            Registrá un día y bloque de horas para el grupo <span class="font-mono font-bold text-primary">{{ selectedGrupoPeriodo?.grupo?.codigo }}</span>.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitAddHorario" class="space-y-4">
                            <div class="space-y-1.5">
                                <Label for="horario_dia" class="text-sm font-semibold">Día de la Semana</Label>
                                <select 
                                    id="horario_dia" 
                                    v-model="addHorarioForm.dia" 
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900"
                                    required
                                >
                                    <option value="">Seleccionar día...</option>
                                    <option v-for="dia in diasSemana" :key="dia" :value="dia">
                                        {{ dia }}
                                    </option>
                                </select>
                                <InputError :message="addHorarioForm.errors.dia" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label for="horario_inicio" class="text-sm font-semibold">Hora Inicio</Label>
                                    <Input 
                                        id="horario_inicio" 
                                        v-model="addHorarioForm.hora_inicio" 
                                        type="time" 
                                        required 
                                        class="bg-background"
                                    />
                                    <InputError :message="addHorarioForm.errors.hora_inicio" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="horario_fin" class="text-sm font-semibold">Hora Fin</Label>
                                    <Input 
                                        id="horario_fin" 
                                        v-model="addHorarioForm.hora_fin" 
                                        type="time" 
                                        required 
                                        class="bg-background"
                                    />
                                    <InputError :message="addHorarioForm.errors.hora_fin" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="horario_aula" class="text-sm font-semibold">Aula / Instalación</Label>
                                <select 
                                    id="horario_dia" 
                                    v-model="addHorarioForm.aula_id" 
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900"
                                    required
                                >
                                    <option value="">Seleccionar aula...</option>
                                    <option v-for="aula in aulas" :key="aula.id" :value="aula.id">
                                        {{ aula.nombre }} ({{ aula.codigo }})
                                    </option>
                                </select>
                                <InputError :message="addHorarioForm.errors.aula_id" />
                            </div>

                            <div v-if="addHorarioForm.errors.grupo_periodo_id" class="text-sm font-medium text-destructive mt-1">
                                {{ addHorarioForm.errors.grupo_periodo_id }}
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t mt-4">
                                <Button type="button" variant="outline" @click="showAddHorarioModal = false">
                                    Cancelar
                                </Button>
                                <Button type="submit" :disabled="addHorarioForm.processing">
                                    {{ addHorarioForm.processing ? 'Registrando...' : 'Registrar Horario' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

        </div>
    </AppLayout>
</template>
