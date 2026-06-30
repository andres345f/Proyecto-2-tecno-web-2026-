<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { computed, onMounted } from 'vue';
import { type SharedData } from '@/types';

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
    periodos: PeriodoAcademico[];
    planes: PlanPago[];
}>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (user.value.is_estudiante) {
        return [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Mis Grupos', href: '/mis-grupos' },
            { title: 'Inscribir Período', href: '/matriculas-periodo/create' },
        ];
    }
    return [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Matrículas de Carrera', href: '/matriculas-carrera' },
        { title: 'Inscribir Período', href: '/matriculas-periodo/create' },
    ];
});

const form = useForm({
    matricula_carrera_id: props.matriculaCarrera.id,
    periodo_academico_id: '',
    plan_pago_id: '',
});

const filteredPlanes = computed(() => {
    if (user.value.is_estudiante) {
        // console.log("planes", props.planes)
        const planes_filter = props.planes.filter(p => p.tipo === 'por_periodo');
        // console.log("planes filtrados", planes_filter)
        return planes_filter;
    }

    return props.planes;
});

onMounted(() => {
    if (user.value.is_estudiante) {
        const defaultPlan = props.planes.find(p => p.tipo === 'por_periodo');
        if (defaultPlan) {
            form.plan_pago_id = defaultPlan.id;
        }
    }
});

const submit = () => {
    form.post('/matriculas-periodo');
};
</script>

<template>

    <Head title="Inscribir Período" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Inscribir Período</h1>
                    <p class="text-sm text-muted-foreground mt-1">Inscribe a {{ matriculaCarrera.usuario?.name }} en un
                        período académico.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="user.is_estudiante ? '/mis-grupos' : `/matriculas-carrera/${matriculaCarrera.id}`">
                        Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de Inscripción al Período</CardTitle>
                    <CardDescription>Selecciona el período lectivo y el plan de pago respectivo para la generación de
                        cuotas.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="periodo_academico_id">Período Académico</Label>
                            <select id="periodo_academico_id" v-model="form.periodo_academico_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required>
                                <option value="">Seleccionar período...</option>
                                <option v-for="periodo in periodos" :key="periodo.id" :value="periodo.id">
                                    {{ periodo.nombre }} ({{ periodo.tipo }}) — Oferta: {{
                                    periodo.oferta_academica?.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.periodo_academico_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="plan_pago_id">Plan de Pago</Label>
                            <select id="plan_pago_id" v-model="form.plan_pago_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required>
                                <option value="">Seleccionar plan...</option>
                                <option v-for="plan in filteredPlanes" :key="plan.id" :value="plan.id">
                                    {{ plan.nombre }} — Matrícula: ${{ plan.monto_matricula }} | Cuota: ${{
                                        plan.monto_cuota }} × {{ plan.cantidad_cuotas }}
                                </option>
                            </select>
                            <InputError :message="form.errors.plan_pago_id" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                Inscribir y Generar Cuotas
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link
                                    :href="user.is_estudiante ? '/mis-grupos' : `/matriculas-carrera/${matriculaCarrera.id}`">
                                    Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
