<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    oferta_academica?: { nombre: string; codigo: string };
}

interface PlanPago {
    id: number;
    nombre: string;
    tipo: string;
    monto_matricula: number;
    monto_cuota: number;
    cantidad_cuotas: number;
}

interface MatriculaCarrera {
    id: number;
    usuario: { name: string };
    oferta_academica: { nombre: string; codigo: string };
}

const props = defineProps<{
    matriculaCarrera: MatriculaCarrera;
    matriculasCarrera: MatriculaCarrera[];
    periodos: PeriodoAcademico[];
    planes: PlanPago[];
}>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (user.value.is_estudiante) {
        return [
            { title: 'Dashboard', href: route('dashboard') },
            { title: 'Mis Grupos', href: route('mis-grupos') },
            { title: 'Inscribir Período', href: route('matriculas-periodo.create') },
        ];
    }
    return [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Matrículas de Carrera', href: route('matriculas-carrera.index') },
        { title: 'Inscribir Período', href: route('matriculas-periodo.create') },
    ];
});

const form = useForm({
    matricula_carrera_id: props.matriculaCarrera.id,
    periodo_academico_id: '',
    plan_pago_id: '',
});

const filteredPlanes = computed(() => {
    if (user.value.is_estudiante) {
        const planes_filter = props.planes.filter((p) => p.tipo === 'por_periodo');
        return planes_filter;
    }

    return props.planes;
});

onMounted(() => {
    if (user.value.is_estudiante) {
        const defaultPlan = props.planes.find((p) => p.tipo === 'por_periodo');
        if (defaultPlan) {
            form.plan_pago_id = defaultPlan.id;
        }
    }
});

const handleCarreraChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const selectedId = target.value;
    router.get(
        route('matriculas-periodo.create'),
        { matricula_carrera_id: selectedId },
        {
            preserveState: false,
        },
    );
};

const submit = () => {
    form.post(route('matriculas-periodo.store'));
};
</script>

<template>
    <Head title="Inscribir Período" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full max-w-2xl flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Inscribir Período</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Inscribe a {{ matriculaCarrera.usuario?.name }} en un período académico.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="user.is_estudiante ? route('mis-grupos') : route('matriculas-carrera.show', props.matriculaCarrera.id)">
                        Volver</Link
                    >
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de Inscripción al Período</CardTitle>
                    <CardDescription>Selecciona el período lectivo y el plan de pago respectivo para la generación de cuotas.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2" v-if="matriculasCarrera && matriculasCarrera.length > 1">
                            <Label for="matricula_carrera_id">Carrera / Oferta Académica</Label>
                            <select
                                id="matricula_carrera_id"
                                :value="matriculaCarrera.id"
                                @change="handleCarreraChange"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option v-for="mc in matriculasCarrera" :key="mc.id" :value="mc.id">
                                    {{ mc.oferta_academica?.nombre }} ({{ mc.oferta_academica?.codigo }})
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2" v-else-if="matriculaCarrera">
                            <Label>Carrera / Oferta Académica</Label>
                            <div class="rounded-md border border-border/50 bg-muted/40 px-3 py-2 text-sm font-medium text-muted-foreground">
                                {{ matriculaCarrera.oferta_academica?.nombre }} ({{ matriculaCarrera.oferta_academica?.codigo }})
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="periodo_academico_id">Período Académico</Label>
                            <select
                                id="periodo_academico_id"
                                v-model="form.periodo_academico_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Seleccionar período...</option>
                                <option v-for="periodo in periodos" :key="periodo.id" :value="periodo.id">
                                    {{ periodo.nombre }} ({{ periodo.tipo }}) — Oferta: {{ periodo.oferta_academica?.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.periodo_academico_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="plan_pago_id">Plan de Pago</Label>
                            <select
                                id="plan_pago_id"
                                v-model="form.plan_pago_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Seleccionar plan...</option>
                                <option v-for="plan in filteredPlanes" :key="plan.id" :value="plan.id">
                                    {{ plan.nombre }} — Matrícula: ${{ plan.monto_matricula }} | Cuota: ${{ plan.monto_cuota }} ×
                                    {{ plan.cantidad_cuotas }}
                                </option>
                            </select>
                            <InputError :message="form.errors.plan_pago_id" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing"> Inscribir y Generar Cuotas </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="user.is_estudiante ? route('mis-grupos') : route('matriculas-carrera.show', props.matriculaCarrera.id)">
                                    Cancelar</Link
                                >
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
