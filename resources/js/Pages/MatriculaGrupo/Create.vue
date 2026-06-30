<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Materia {
    id: number;
    nombre: string;
    codigo: string;
    descripcion: string;
}

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
    dia_semana: string;
    hora_inicio: string;
    hora_fin: string;
    aula?: Aula;
}

interface Grupo {
    id: number;
    codigo: string;
    cupo_maximo: number;
    materia_id: number;
    docente_id: number;
    materia: Materia;
    docente?: Docente;
    horarios: Horario[];
    cumple_prerrequisitos: boolean;
    prerrequisito_mensaje: string;
    inscritos_count: number;
}

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    numero_maximo_materias: number;
}

interface MatriculaPeriodo {
    id: number;
    estado: string;
    matricula_carrera: {
        id: number;
        usuario?: { name: string };
        oferta_academica: OfertaAcademica;
    };
    periodo_academico: PeriodoAcademico;
}

const props = defineProps<{
    matriculasPeriodo: MatriculaPeriodo[];
    grupos: Grupo[];
    matriculaPeriodoId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Matrículas de Grupo', href: '/matriculas-grupo' },
    { title: 'Inscribirse', href: '/matriculas-grupo/create' },
];

const form = useForm({
    matricula_periodo_id: props.matriculaPeriodoId || '',
    grupo_ids: [] as number[],
});

// Reactively load groups when enrollment period is changed
watch(() => form.matricula_periodo_id, (newVal) => {
    if (newVal) {
        router.get('/matriculas-grupo/create', { matricula_periodo_id: newVal }, {
            preserveState: true,
            replace: true,
            onSuccess: () => {
                form.grupo_ids = [];
            }
        });
    }
});

const periodoSeleccionado = computed(() => {
    let matricula = props.matriculasPeriodo;
    console.log("matriculs seleccionado " + matricula)
    return matricula.find(mp => mp.id === Number(form.matricula_periodo_id));
});

const maxMaterias = computed(() => {
    console.log("periodoSeleccionado", periodoSeleccionado.value?.periodo_academico);
    return periodoSeleccionado.value?.periodo_academico?.numero_maximo_materias || 0;
});

const isSelected = (grupoId: number) => form.grupo_ids.includes(grupoId);

const toggleGrupo = (grupo: Grupo) => {
    if (!grupo.cumple_prerrequisitos) return;
    if (grupo.inscritos_count >= grupo.cupo_maximo) return;

    const index = form.grupo_ids.indexOf(grupo.id);
    if (index > -1) {
        form.grupo_ids.splice(index, 1);
    } else {
        // Validar si ya hay otro grupo seleccionado para la misma materia
        const materiasSeleccionadas = props.grupos
            .filter(g => form.grupo_ids.includes(g.id))
            .map(g => g.materia_id);

        if (materiasSeleccionadas.includes(grupo.materia_id)) {
            alert(`Ya seleccionaste un grupo para la materia: ${grupo.materia?.nombre}.`);
            return;
        }

        if (maxMaterias.value && form.grupo_ids.length >= maxMaterias.value) {
            alert(`Límite excedido: Solo podés inscribir un máximo de ${maxMaterias.value} materias en este período.`);
            return;
        }
        form.grupo_ids.push(grupo.id);
    }
};

const formatTime = (time: string) => {
    if (!time) return '';
    return time.substring(0, 5); // Format HH:MM
};

const formatDay = (day: string) => {
    if (!day) return '';
    return day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
};

const submit = () => {
    form.post('/matriculas-grupo', {
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

<template>

    <Head title="Inscribirse en Grupos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Inscribirse en Grupos</h1>
                    <p class="text-sm text-muted-foreground mt-1">Seleccioná tu período de matrícula y los grupos a los
                        que querés inscribirte.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/matriculas-grupo">Volver</Link>
                </Button>
            </div>

            <!-- Período de Matrícula Select -->
            <Card class="border border-muted">
                <CardContent class="pt-6">
                    <div class="space-y-2">
                        <Label for="matricula_periodo_id" class="text-base font-semibold">Período de Matrícula</Label>
                        <select id="matricula_periodo_id" v-model="form.matricula_periodo_id"
                            class="flex h-10 w-full md:w-1/2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            required>
                            <option value="">Seleccionar período de inscripción...</option>
                            <option v-for="mp in matriculasPeriodo" :key="mp.id" :value="mp.id">
                                {{ mp.periodo_academico?.nombre }} ({{ mp.periodo_academico?.tipo }}) — Oferta: {{
                                    mp.matricula_carrera?.oferta_academica?.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.matricula_periodo_id" />
                    </div>
                </CardContent>
            </Card>

            <form @submit.prevent="submit" class="space-y-6" v-if="form.matricula_periodo_id">
                <!-- Status Bar with materia limits -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-accent/40 border border-accent p-4 rounded-lg gap-2">
                    <div>
                        <p class="text-sm font-medium text-foreground">
                            Oferta Seleccionada: <span class="font-bold text-primary">{{
                                periodoSeleccionado?.matricula_carrera?.oferta_academica?.nombre }}</span>
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Materia límite por período: {{ maxMaterias }} materias
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-semibold px-3 py-1 bg-background rounded-full border">
                            Seleccionadas: <span class="text-primary font-bold">{{ form.grupo_ids.length }}</span> / {{
                                maxMaterias }}
                        </span>
                        <Button type="submit" :disabled="form.processing || form.grupo_ids.length === 0">
                            {{ form.processing ? 'Procesando...' : 'Confirmar Inscripciones' }}
                        </Button>
                    </div>
                </div>

                <InputError :message="form.errors.grupo_ids" />

                <!-- Grid of groups cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="grupo in grupos" :key="grupo.id" @click="toggleGrupo(grupo)" :class="[
                        'relative rounded-xl border p-5 transition-all cursor-pointer flex flex-col justify-between shadow-sm select-none',
                        isSelected(grupo.id)
                            ? 'border-primary ring-2 ring-primary/20 bg-primary/[0.02]'
                            : 'border-border bg-card hover:border-muted-foreground/30',
                        (!grupo.cumple_prerrequisitos || grupo.inscritos_count >= grupo.cupo_maximo)
                            ? 'opacity-60 cursor-not-allowed bg-accent/20'
                            : ''
                    ]">
                        <!-- Top header -->
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-lg leading-snug text-foreground">{{ grupo.materia?.nombre
                                        }}</h3>
                                    <p class="text-xs text-muted-foreground font-mono mt-0.5">{{ grupo.materia?.codigo
                                        }} — Grupo: {{ grupo.codigo }}</p>
                                </div>
                                <div v-if="isSelected(grupo.id)"
                                    class="h-6 w-6 rounded-full bg-primary flex items-center justify-center text-primary-foreground font-bold text-xs">
                                    ✓
                                </div>
                            </div>

                            <!-- Group details -->
                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-muted-foreground text-xs font-semibold uppercase tracking-wider w-16">Docente:</span>
                                    <span class="text-foreground font-medium">{{ grupo.docente?.name || 'No asignado'
                                        }}</span>
                                </div>

                                <div class="flex flex-col gap-1 pt-1">
                                    <span
                                        class="text-muted-foreground text-xs font-semibold uppercase tracking-wider">Horario
                                        y Aula:</span>
                                    <div class="pl-2 border-l-2 border-primary/20 space-y-1">
                                        <div v-for="h in grupo.horarios" :key="h.id" class="text-xs text-foreground">
                                            <span class="font-semibold">{{ formatDay(h.dia_semana) }}:</span>
                                            {{ formatTime(h.hora_inicio) }} - {{ formatTime(h.hora_fin) }}
                                            <span class="text-muted-foreground" v-if="h.aula">({{ h.aula.nombre
                                                }})</span>
                                        </div>
                                        <div v-if="grupo.horarios.length === 0"
                                            class="text-xs text-muted-foreground italic">
                                            Sin horarios asignados
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer indicator -->
                        <div class="mt-5 pt-3 border-t border-border flex items-center justify-between">
                            <span :class="[
                                'text-xs px-2.5 py-0.5 rounded-full font-semibold',
                                (grupo.inscritos_count >= grupo.cupo_maximo)
                                    ? 'bg-destructive/10 text-destructive'
                                    : 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400'
                            ]">
                                Cupos: {{ grupo.inscritos_count }} / {{ grupo.cupo_maximo }}
                            </span>

                            <!-- Prerequisites status / Cupos message -->
                            <div class="text-right">
                                <span v-if="!grupo.cumple_prerrequisitos"
                                    class="text-xs font-medium text-destructive flex items-center gap-1">
                                    ⚠️ {{ grupo.prerrequisito_mensaje }}
                                </span>
                                <span v-else-if="grupo.inscritos_count >= grupo.cupo_maximo"
                                    class="text-xs font-medium text-destructive">
                                    Cupos agotados
                                </span>
                                <span v-else class="text-xs text-muted-foreground font-medium">
                                    Habilitado para inscripción
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4">
                    <Button variant="ghost" as-child>
                        <Link href="/matriculas-grupo">Cancelar</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing || form.grupo_ids.length === 0">
                        {{ form.processing ? 'Registrando...' : 'Confirmar Inscripciones' }}
                    </Button>
                </div>
            </form>

            <Card v-else class="border border-dashed">
                <CardContent class="flex flex-col items-center justify-center p-12 text-center">
                    <p class="text-muted-foreground">Seleccioná un período de matrícula activo para ver los grupos
                        disponibles.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
