<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Entrega {
    id: number;
    ruta_archivo: string | null;
    fecha_entrega: string;
    nota: string | null;
    retroalimentacion: string | null;
    usuario: { id: number; name: string; email: string };
}

interface Tarea {
    id: number;
    titulo: string;
    descripcion: string | null;
    fecha_vencimiento: string;
    puntaje_maximo: string;
    entregas: Entrega[];
    grupo: {
        id: number;
        codigo: string;
        materia: { nombre: string; codigo: string };
    };
}

interface Grupo {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
}

const props = defineProps<{
    grupo: Grupo;
    tarea: Tarea;
}>();

const uploadForm = useForm({
    tarea_id: props.tarea.id,
    archivo: null as File | null,
});

const gradeForm = useForm({
    nota: '',
    retroalimentacion: '',
});

const gradingEntregaId = ref<number | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grupos', href: '/grupos' },
    { title: props.grupo.codigo, href: `/grupos/${props.grupo.id}` },
    { title: 'Tareas', href: `/grupos/${props.grupo.id}/tareas` },
    { title: props.tarea.titulo, href: `/grupos/${props.grupo.id}/tareas/${props.tarea.id}` },
];

const isPastDeadline = (fecha: string) => new Date(fecha) < new Date();

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    uploadForm.archivo = target.files?.[0] || null;
};

const clearFile = () => {
    uploadForm.archivo = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submitFile = () => {
    uploadForm.post('/entregas', {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.archivo = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const startGrading = (entrega: Entrega) => {
    gradingEntregaId.value = entrega.id;
    gradeForm.nota = entrega.nota || '';
    gradeForm.retroalimentacion = entrega.retroalimentacion || '';
};

const submitGrade = (entregaId: number) => {
    gradeForm.put(`/entregas/${entregaId}/calificar`, {
        preserveScroll: true,
        onSuccess: () => {
            gradingEntregaId.value = null;
        },
    });
};

const downloadFile = (entregaId: number) => {
    window.location.href = `/entregas/${entregaId}/download`;
};
</script>

<template>

    <Head :title="tarea.titulo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto w-full">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">{{ tarea.titulo }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ grupo.codigo }} — {{ grupo.materia?.nombre }}</p>
                </div>
                <Button variant="ghost" as-child>
                    <Link :href="`/grupos/${grupo.id}/tareas`">Volver a tareas</Link>
                </Button>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Section -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Detalles de la Tarea</CardTitle>
                            <CardDescription>Información general y descripción de la asignación.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-muted-foreground">Descripción</h3>
                                <p
                                    class="mt-1 text-sm text-foreground whitespace-pre-line bg-muted/30 p-3 rounded-lg border border-border/50">
                                    {{ tarea.descripcion || 'Sin descripción disponible.' }}
                                </p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-border">
                                <div>
                                    <h3 class="text-sm font-semibold text-muted-foreground">Vencimiento</h3>
                                    <p class="mt-1 text-sm font-medium text-foreground font-mono">
                                        {{ formatDate(tarea.fecha_vencimiento) }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-muted-foreground">Puntaje Máximo</h3>
                                    <p class="mt-1 text-sm font-bold text-foreground">
                                        {{ tarea.puntaje_maximo }} puntos
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-muted-foreground">Estado</h3>
                                    <div class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="[
                                                isPastDeadline(tarea.fecha_vencimiento)
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                            ]">
                                            {{ isPastDeadline(tarea.fecha_vencimiento) ? 'Cerrada' : 'Abierta' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Submit / Upload Form (students only, before deadline) -->
                <div class="lg:col-span-1">
                    <Card v-if="$page.props.auth.user?.is_estudiante">
                        <CardHeader>
                            <CardTitle>Mi Entrega</CardTitle>
                            <CardDescription>Envía tu trabajo antes de la fecha límite.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="isPastDeadline(tarea.fecha_vencimiento)"
                                class="p-4 text-center rounded-lg bg-red-100/50 border border-red-200 dark:bg-red-950/20 dark:border-red-900">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">La fecha límite de entrega
                                    ha pasado.</p>
                            </div>
                            <div v-else-if="tarea.entregas && tarea.entregas.length > 0"
                                class="p-4 text-center rounded-lg bg-green-100/50 border border-green-200 dark:bg-green-950/20 dark:border-green-900">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">¡Ya has realizado una
                                    entrega para esta tarea!</p>
                            </div>
                            <form v-else @submit.prevent="submitFile" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="archivo">Selecciona tu archivo</Label>
                                    <input ref="fileInput" id="archivo" type="file" accept=".pdf,.doc,.docx"
                                        @change="handleFileSelect" class="hidden" />
                                    <div @click="fileInput?.click()"
                                        class="border-2 border-dashed border-input hover:border-primary/50 transition-colors rounded-lg p-6 text-center cursor-pointer bg-muted/20">
                                        <div v-if="!uploadForm.archivo" class="space-y-2">
                                            <div class="text-3xl">📁</div>
                                            <p class="text-sm font-medium text-foreground">Seleccionar Archivo</p>
                                            <p class="text-xs text-muted-foreground">PDF, DOC, DOCX (máx. 10MB)</p>
                                        </div>
                                        <div v-else class="space-y-3">
                                            <div class="text-3xl text-primary">📄</div>
                                            <p class="text-sm font-semibold text-foreground truncate max-w-xs mx-auto">
                                                {{ uploadForm.archivo.name }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ (uploadForm.archivo.size / (1024 * 1024)).toFixed(2) }} MB
                                            </p>
                                            <Button variant="ghost" size="sm" type="button" @click.stop="clearFile"
                                                class="text-destructive hover:bg-destructive/10 h-7 px-2">
                                                Quitar archivo
                                            </Button>
                                        </div>
                                    </div>
                                    <InputError :message="uploadForm.errors.archivo" />
                                    <InputError :message="uploadForm.errors.tarea_id" />
                                </div>
                                <Button type="submit" class="w-full"
                                    :disabled="!uploadForm.archivo || uploadForm.processing">
                                    Enviar Tarea
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Submissions Table Card (all users) -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ $page.props.auth.user?.is_profesor ? 'Entregas Recibidas' : 'Detalles de mi Entrega'
                        }}</CardTitle>
                    <CardDescription>Resumen de archivos subidos y calificaciones obtenidas.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Estudiante</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Fecha de Envío</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Calificación</th>
                                    <th
                                        class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Comentarios</th>
                                    <th
                                        class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="entrega in tarea.entregas" :key="entrega.id"
                                    class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">
                                        {{ entrega.usuario?.name }}
                                        <p class="text-xs font-normal text-muted-foreground">{{ entrega.usuario?.email
                                            }}</p>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground font-mono">
                                        {{ formatDate(entrega.fecha_entrega) }}
                                    </td>
                                    <td class="px-6 py-4 align-middle font-semibold">
                                        <span v-if="entrega.nota !== null" class="text-foreground">
                                            {{ entrega.nota }} / {{ tarea.puntaje_maximo }} pts
                                        </span>
                                        <span v-else
                                            class="text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400 px-2 py-0.5 rounded text-xs">
                                            Sin calificar
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground max-w-xs truncate">
                                        {{ entrega.retroalimentacion || '—' }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-right space-x-2">
                                        <Button v-if="entrega.ruta_archivo" variant="outline" size="sm"
                                            @click="downloadFile(entrega.id)">
                                            Descargar
                                        </Button>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/entregas/${entrega.id}`">Ver Ficha</Link>
                                        </Button>
                                        <Button v-if="$page.props.auth.user?.is_profesor" variant="default" size="sm"
                                            @click="startGrading(entrega)">
                                            Calificar
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!tarea.entregas || tarea.entregas.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay entregas registradas para esta tarea.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Grade Modal (Overlay Dialog) -->
            <div v-if="gradingEntregaId"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm"
                @click.self="gradingEntregaId = null">
                <Card class="w-full max-w-md shadow-lg border border-border">
                    <CardHeader>
                        <CardTitle>Calificar Entrega</CardTitle>
                        <CardDescription>Registra la nota y comentarios de retroalimentación.</CardDescription>
                    </CardHeader>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitGrade(gradingEntregaId!)" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="nota">Nota (0 — {{ tarea.puntaje_maximo }})</Label>
                                <Input id="nota" v-model="gradeForm.nota" type="number" :min="0"
                                    :max="Number(tarea.puntaje_maximo)" step="0.01" required />
                                <InputError :message="gradeForm.errors.nota" />
                            </div>
                            <div class="space-y-2">
                                <Label for="retroalimentacion">Retroalimentación / Comentarios</Label>
                                <textarea id="retroalimentacion" v-model="gradeForm.retroalimentacion" rows="3"
                                    placeholder="Escribe comentarios de retroalimentación..."
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white" />
                                <InputError :message="gradeForm.errors.retroalimentacion" />
                            </div>
                            <div class="flex items-center gap-2 border-t border-border pt-4 mt-6 justify-end">
                                <Button type="submit" :disabled="gradeForm.processing">
                                    Guardar Nota
                                </Button>
                                <Button type="button" variant="outline" @click="gradingEntregaId = null">
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
