<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

interface PeriodoAcademico {
    id: number;
    nombre: string;
    tipo: string;
    fecha_inicio: string;
    fecha_fin: string;
    oferta_academica_id: number;
    fecha_inicio_inscripcion?: string;
    fecha_fin_inscripcion?: string;
    fecha_inicio_cierre?: string;
    fecha_fin_cierre?: string;
    fecha_inicio_retiro?: string;
    fecha_fin_retiro?: string;
    numero_maximo_materias?: number | null;
    estado: string;
}

const props = defineProps<{
    periodo: PeriodoAcademico;
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Períodos Académicos', href: route('periodos-academicos.index') },
    { title: 'Editar', href: route('periodos-academicos.edit', props.periodo.id) },
];

const formatDate = (dateStr: any) => {
    if (!dateStr) return '';
    if (typeof dateStr !== 'string') {
        try {
            return new Date(dateStr).toISOString().split('T')[0];
        } catch (e) {
            return '';
        }
    }
    const match = dateStr.match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
};

const form = useForm({
    oferta_academica_id: props.periodo.oferta_academica_id,
    nombre: props.periodo.nombre,
    tipo: props.periodo.tipo,
    fecha_inicio: formatDate(props.periodo.fecha_inicio),
    fecha_fin: formatDate(props.periodo.fecha_fin),
    fecha_inicio_inscripcion: formatDate(props.periodo.fecha_inicio_inscripcion),
    fecha_fin_inscripcion: formatDate(props.periodo.fecha_fin_inscripcion),
    fecha_inicio_cierre: formatDate(props.periodo.fecha_inicio_cierre),
    fecha_fin_cierre: formatDate(props.periodo.fecha_fin_cierre),
    fecha_inicio_retiro: formatDate(props.periodo.fecha_inicio_retiro),
    fecha_fin_retiro: formatDate(props.periodo.fecha_fin_retiro),
    numero_maximo_materias: props.periodo.numero_maximo_materias || '',
    estado: props.periodo.estado || 'inscripcion',
});

const submit = () => {
    form.put(route('periodos-academicos.update', props.periodo.id));
};
</script>

<template>
    <Head title="Editar Período Académico" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Editar Período Académico</h1>
                    <p class="text-sm text-muted-foreground mt-1">Actualiza los detalles y la configuración del ciclo académico.</p>
                </div>
                <Button variant="ghost" as-child>
                    <Link :href="route('periodos-academicos.index')">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Información del Período</CardTitle>
                    <CardDescription>Modifica los campos del período, límites de inscripción y estados del ciclo.</CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Oferta Académica y Nombre -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="oferta_academica_id">Oferta Académica</Label>
                                <select
                                    id="oferta_academica_id"
                                    v-model="form.oferta_academica_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                    required
                                >
                                    <option value="" disabled>Seleccionar oferta...</option>
                                    <option v-for="oferta in ofertas" :key="oferta.id" :value="oferta.id">{{ oferta.nombre }} ({{ oferta.codigo }})</option>
                                </select>
                                <InputError :message="form.errors.oferta_academica_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre">Nombre del Período</Label>
                                <Input
                                    id="nombre"
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                />
                                <InputError :message="form.errors.nombre" />
                            </div>
                        </div>

                        <!-- Tipo, Inicio y Fin general -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="tipo">Tipo</Label>
                                <select
                                    id="tipo"
                                    v-model="form.tipo"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                    required
                                >
                                    <option value="" disabled>Seleccionar tipo...</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                                <InputError :message="form.errors.tipo" />
                            </div>

                            <div class="space-y-2">
                                <Label for="fecha_inicio">Fecha Inicio</Label>
                                <Input
                                    id="fecha_inicio"
                                    v-model="form.fecha_inicio"
                                    type="date"
                                    required
                                />
                                <InputError :message="form.errors.fecha_inicio" />
                            </div>

                            <div class="space-y-2">
                                <Label for="fecha_fin">Fecha Fin</Label>
                                <Input
                                    id="fecha_fin"
                                    v-model="form.fecha_fin"
                                    type="date"
                                    required
                                />
                                <InputError :message="form.errors.fecha_fin" />
                            </div>
                        </div>

                        <!-- Fechas de Procesos (Opcionales) -->
                        <div class="border-t border-border pt-6">
                            <h3 class="text-lg font-semibold text-foreground mb-4">Fechas de Procesos (Opcionales)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label for="fecha_inicio_inscripcion">Inicio Inscripción</Label>
                                        <Input
                                            id="fecha_inicio_inscripcion"
                                            v-model="form.fecha_inicio_inscripcion"
                                            type="date"
                                        />
                                        <InputError :message="form.errors.fecha_inicio_inscripcion" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="fecha_fin_inscripcion">Fin Inscripción</Label>
                                        <Input
                                            id="fecha_fin_inscripcion"
                                            v-model="form.fecha_fin_inscripcion"
                                            type="date"
                                        />
                                        <InputError :message="form.errors.fecha_fin_inscripcion" />
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label for="fecha_inicio_cierre">Inicio Cierre</Label>
                                        <Input
                                            id="fecha_inicio_cierre"
                                            v-model="form.fecha_inicio_cierre"
                                            type="date"
                                        />
                                        <InputError :message="form.errors.fecha_inicio_cierre" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="fecha_fin_cierre">Fin Cierre</Label>
                                        <Input
                                            id="fecha_fin_cierre"
                                            v-model="form.fecha_fin_cierre"
                                            type="date"
                                        />
                                        <InputError :message="form.errors.fecha_fin_cierre" />
                                    </div>
                                </div>

                                <div class="space-y-4 md:col-span-2">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="fecha_inicio_retiro">Inicio Retiro</Label>
                                            <Input
                                                id="fecha_inicio_retiro"
                                                v-model="form.fecha_inicio_retiro"
                                                type="date"
                                            />
                                            <InputError :message="form.errors.fecha_inicio_retiro" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="fecha_fin_retiro">Fin Retiro</Label>
                                            <Input
                                                id="fecha_fin_retiro"
                                                v-model="form.fecha_fin_retiro"
                                                type="date"
                                            />
                                            <InputError :message="form.errors.fecha_fin_retiro" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Límites y Estado -->
                        <div class="border-t border-border pt-6">
                            <h3 class="text-lg font-semibold text-foreground mb-4">Límites y Estado</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="numero_maximo_materias">Nº Máximo de Materias</Label>
                                    <Input
                                        id="numero_maximo_materias"
                                        v-model="form.numero_maximo_materias"
                                        type="number"
                                        min="1"
                                        placeholder="Sin límite"
                                    />
                                    <InputError :message="form.errors.numero_maximo_materias" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="estado">Estado del Período</Label>
                                    <select
                                        id="estado"
                                        v-model="form.estado"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                        required
                                    >
                                        <option value="inscripcion">Inscripción</option>
                                        <option value="cierre">Cierre</option>
                                        <option value="retiro">Retiro</option>
                                        <option value="terminado">Terminado</option>
                                    </select>
                                    <InputError :message="form.errors.estado" />
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4 border-t border-border pt-6">
                            <Button type="submit" :disabled="form.processing">
                                Actualizar Período
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="route('periodos-academicos.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
