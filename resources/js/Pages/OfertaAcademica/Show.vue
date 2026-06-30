<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface MateriaPrerequisito {
    id: number;
    nombre: string;
    codigo: string;
}

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
    pivot: {
        semestre_orden: number;
    };
    prerrequisitos?: MateriaPrerequisito[];
}

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    fecha_inicio: string;
    fecha_fin: string;
}

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
    descripcion: string | null;
    materias: Materia[];
    periodos_academicos?: PeriodoAcademico[];
}

interface MateriaBase {
    id: number;
    nombre: string;
    codigo: string;
}

const props = defineProps<{
    oferta: OfertaAcademica;
    allMaterias: MateriaBase[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ofertas Académicas', href: '/ofertas-academicas' },
    { title: props.oferta.nombre, href: `/ofertas-academicas/${props.oferta.id}` },
];

const form = useForm({
    materia_id: '',
    semestre_orden: 1,
});

const availableMaterias = computed(() => {
    return props.allMaterias.filter(
        (m) => !props.oferta.materias.some((om) => om.id === m.id)
    );
});

const submit = () => {
    form.post(`/ofertas-academicas/${props.oferta.id}/materias`, {
        onSuccess: () => {
            form.reset('materia_id');
        },
    });
};

const groupedBySemestre = (materias: Materia[]) => {
    const groups: Record<number, Materia[]> = {};
    materias.forEach((m) => {
        const semestre = m.pivot.semestre_orden;
        if (!groups[semestre]) groups[semestre] = [];
        groups[semestre].push(m);
    });
    return groups;
};

// Prerequisites state and management
const activeMateriaForPrereq = ref<Materia | null>(null);
const newPrereqId = ref<number | string>('');

const eligiblePrereqMaterias = computed(() => {
    if (!activeMateriaForPrereq.value) return [];
    return props.oferta.materias.filter((m) => {
        // Exclude self
        if (m.id === activeMateriaForPrereq.value!.id) return false;
        // Exclude already added prereqs
        const isAlreadyPrereq = activeMateriaForPrereq.value!.prerrequisitos?.some((p) => p.id === m.id);
        return !isAlreadyPrereq;
    });
});

const openPrereqManager = (materia: Materia) => {
    activeMateriaForPrereq.value = materia;
    newPrereqId.value = '';
};

const addPrereqForm = useForm({
    prerequisito_id: '',
});

const addPrereq = () => {
    if (!activeMateriaForPrereq.value || !newPrereqId.value) return;
    addPrereqForm.prerequisito_id = String(newPrereqId.value);
    addPrereqForm.post(`/ofertas-academicas/${props.oferta.id}/materias/${activeMateriaForPrereq.value.id}/prerrequisitos`, {
        onSuccess: () => {
            const targetMateria = props.oferta.materias.find((m) => m.id === activeMateriaForPrereq.value!.id);
            const addedMateria = props.oferta.materias.find((m) => m.id === Number(newPrereqId.value));
            if (targetMateria && addedMateria) {
                if (!targetMateria.prerrequisitos) {
                    targetMateria.prerrequisitos = [];
                }
                if (!targetMateria.prerrequisitos.some((p) => p.id === addedMateria.id)) {
                    targetMateria.prerrequisitos.push({
                        id: addedMateria.id,
                        nombre: addedMateria.nombre,
                        codigo: addedMateria.codigo,
                    });
                }
            }
            newPrereqId.value = '';
        },
    });
};

const removePrereq = (materia: Materia, prereq: MateriaPrerequisito) => {
    if (!confirm(`¿Remover ${prereq.nombre} como prerrequisito de ${materia.nombre}?`)) return;
    addPrereqForm.delete(`/ofertas-academicas/${props.oferta.id}/materias/${materia.id}/prerrequisitos/${prereq.id}`, {
        onSuccess: () => {
            const targetMateria = props.oferta.materias.find((m) => m.id === materia.id);
            if (targetMateria && targetMateria.prerrequisitos) {
                targetMateria.prerrequisitos = targetMateria.prerrequisitos.filter((p) => p.id !== prereq.id);
            }
        },
    });
};
</script>

<template>
    <Head :title="oferta.nombre" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">{{ oferta.nombre }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de la malla curricular y datos de la carrera.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/grupos?oferta_id=${oferta.id}`">Ver Grupos</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/ofertas-academicas/${oferta.id}/edit`">Editar</Link>
                    </Button>
                    <Button variant="ghost" as-child>
                        <Link href="/ofertas-academicas">Volver</Link>
                    </Button>
                </div>
            </div>

            <!-- Basic Info Card -->
            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-card">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Información de la Oferta</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ oferta.nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ oferta.codigo }}</dd>
                    </div>
                    <div v-if="oferta.descripcion" class="sm:col-span-3">
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ oferta.descripcion }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Add Materia Form -->
            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-card">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Agregar Materia</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Añade una materia a la malla curricular de esta oferta.</p>

                <form @submit.prevent="submit" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="space-y-2">
                        <Label for="materia_id">Materia</Label>
                        <select
                            id="materia_id"
                            v-model="form.materia_id"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                        >
                            <option value="" disabled>Selecciona una materia...</option>
                            <option v-for="materia in availableMaterias" :key="materia.id" :value="materia.id">
                                {{ materia.nombre }} ({{ materia.codigo }})
                            </option>
                        </select>
                        <InputError :message="form.errors.materia_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="semestre_orden">Nivel</Label>
                        <Input
                            id="semestre_orden"
                            v-model="form.semestre_orden"
                            type="number"
                            min="1"
                            required
                        />
                        <InputError :message="form.errors.semestre_orden" />
                    </div>

                    <Button type="submit" class="w-full h-10 animate-in fade-in duration-200" :disabled="form.processing || availableMaterias.length === 0">
                        {{ availableMaterias.length === 0 ? 'No hay materias disponibles' : 'Agregar Materia' }}
                    </Button>
                </form>
            </div>

            <!-- Malla Curricular List -->
            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-card">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-neutral-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Malla Curricular</h2>
                    <span class="text-xs font-medium bg-primary/10 text-primary px-2.5 py-1 rounded-full">{{ oferta.materias.length }} materias</span>
                </div>

                <div v-if="oferta.materias.length === 0" class="mt-6 text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                    No hay materias asignadas a esta oferta.
                </div>

                <div v-else class="mt-6 space-y-6">
                    <div v-for="(materias, semestre) in groupedBySemestre(oferta.materias)" :key="semestre" class="pb-4 last:pb-0 border-b last:border-0 border-gray-100 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-neutral-800 px-3 py-1.5 rounded-md inline-block mb-3">Nivel {{ semestre }}</h3>
                        <ul class="space-y-3">
                            <li v-for="materia in materias" :key="materia.id" class="py-3 px-4 rounded-lg border border-neutral-100 hover:border-neutral-200 dark:border-neutral-800 dark:hover:border-neutral-700/50 hover:bg-gray-50 dark:hover:bg-neutral-800/30 transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ materia.nombre }}</span>
                                        <span class="text-xs font-mono bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 px-1.5 py-0.5 rounded">{{ materia.codigo }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="openPrereqManager(materia)"
                                            class="text-xs text-primary hover:underline font-medium"
                                        >
                                            Prerrequisitos
                                        </button>
                                        <span class="text-neutral-300 dark:text-neutral-700">|</span>
                                        <Link
                                            :href="`/ofertas-academicas/${oferta.id}/materias/${materia.id}`"
                                            method="delete"
                                            as="button"
                                            class="text-xs text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                            @click.prevent="
                                                if (confirm('¿Remover esta materia de la malla?'))
                                                    $inertia.delete(`/ofertas-academicas/${oferta.id}/materias/${materia.id}`);
                                            "
                                        >
                                            Remover
                                        </Link>
                                    </div>
                                </div>

                                <!-- Display prerequisites list -->
                                <div v-if="materia.prerrequisitos && materia.prerrequisitos.length > 0" class="mt-2 flex flex-wrap gap-1.5 items-center">
                                    <span class="text-xs text-muted-foreground mr-1">Prerrequisitos:</span>
                                    <span
                                        v-for="prereq in materia.prerrequisitos"
                                        :key="prereq.id"
                                        class="inline-flex items-center gap-1 text-xs bg-muted text-muted-foreground px-2 py-0.5 rounded-full border border-neutral-200 dark:border-neutral-700"
                                    >
                                        {{ prereq.codigo }}
                                        <button
                                            @click="removePrereq(materia, prereq)"
                                            class="text-[10px] text-muted-foreground hover:text-red-500 font-bold ml-0.5"
                                            title="Quitar prerrequisito"
                                        >
                                            ×
                                        </button>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prerrequisitos Management Modal -->
        <div v-if="activeMateriaForPrereq" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-200">
            <div class="relative w-full max-w-md rounded-xl border border-sidebar-border bg-card p-6 shadow-xl animate-in fade-in zoom-in duration-200 dark:border-neutral-800">
                <h3 class="text-lg font-semibold text-foreground">
                    Gestionar Prerrequisitos
                </h3>
                <p class="text-xs text-muted-foreground mt-1">
                    Establece las materias previas requeridas para cursar <strong>{{ activeMateriaForPrereq.nombre }}</strong>.
                </p>

                <!-- Add prereq form -->
                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <Label for="new_prereq_id">Agregar Materia Prerrequisito</Label>
                        <div class="flex gap-2">
                            <select
                                id="new_prereq_id"
                                v-model="newPrereqId"
                                class="flex h-10 flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                            >
                                <option value="" disabled>Selecciona una materia...</option>
                                <option v-for="m in eligiblePrereqMaterias" :key="m.id" :value="m.id">
                                    {{ m.nombre }} ({{ m.codigo }})
                                </option>
                            </select>
                            <Button @click="addPrereq" :disabled="!newPrereqId || addPrereqForm.processing">
                                Agregar
                            </Button>
                        </div>
                        <InputError :message="addPrereqForm.errors.prerequisito_id" />
                    </div>

                    <!-- Current Prereqs List inside modal -->
                    <div class="space-y-2 pt-2">
                        <Label>Prerrequisitos Actuales</Label>
                        <div v-if="!activeMateriaForPrereq.prerrequisitos || activeMateriaForPrereq.prerrequisitos.length === 0" class="text-sm text-muted-foreground py-2 italic">
                            No tiene prerrequisitos asignados en esta oferta.
                        </div>
                        <ul v-else class="divide-y divide-neutral-100 dark:divide-neutral-800 max-h-40 overflow-y-auto">
                            <li v-for="prereq in activeMateriaForPrereq.prerrequisitos" :key="prereq.id" class="flex items-center justify-between py-2">
                                <span class="text-sm text-foreground">{{ prereq.nombre }} ({{ prereq.codigo }})</span>
                                <button
                                    @click="removePrereq(activeMateriaForPrereq, prereq)"
                                    class="text-xs text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                    :disabled="addPrereqForm.processing"
                                >
                                    Quitar
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <Button variant="outline" @click="activeMateriaForPrereq = null">
                        Cerrar
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
