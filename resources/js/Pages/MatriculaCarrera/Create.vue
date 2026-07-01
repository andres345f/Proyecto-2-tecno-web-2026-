<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

interface Usuario {
    id: number;
    name: string;
    email: string;
}

interface OfertaAcademica {
    id: number;
    nombre: string;
    codigo: string;
}

const props = defineProps<{
    usuarios: Usuario[];
    ofertas: OfertaAcademica[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Matrículas de Carrera', href: route('matriculas-carrera.index') },
    { title: 'Crear', href: route('matriculas-carrera.create') },
];

// Manual Enrollment Form
const form = useForm({
    usuario_id: '',
    oferta_academica_id: '',
});

const submitManual = () => {
    form.post(route('matriculas-carrera.store'));
};

// Mass Import Form
const importForm = useForm({
    archivo: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    importForm.archivo = target.files?.[0] || null;
};

const clearFile = () => {
    importForm.archivo = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submitImport = () => {
    importForm.post(route('matriculas-carrera.importar'), {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
        },
    });
};
</script>

<template>

    <Head title="Registrar Matrículas de Carrera" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto w-full">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Inscripción / Matrícula de
                        Carrera</h1>
                    <p class="text-sm text-muted-foreground mt-1">Registra de manera individual o masiva la matrícula de
                        estudiantes.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="route('matriculas-carrera.index')">Volver</Link>
                </Button>
            </div>

            <!-- Double Column Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

                <!-- Manual Creation Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Inscripción Individual</CardTitle>
                        <CardDescription>Selecciona un estudiante existente y asígnale su carrera correspondiente.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitManual" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="usuario_id">Estudiante</Label>
                                <select id="usuario_id" v-model="form.usuario_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                    required>
                                    <option value="">Seleccionar estudiante...</option>
                                    <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                        {{ usuario.name }} ({{ usuario.email }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.usuario_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="oferta_academica_id">Carrera / Oferta Académica</Label>
                                <select id="oferta_academica_id" v-model="form.oferta_academica_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-sidebar-border dark:bg-neutral-900 dark:text-white"
                                    required>
                                    <option value="">Seleccionar carrera...</option>
                                    <option v-for="oferta in ofertas" :key="oferta.id" :value="oferta.id">
                                        {{ oferta.nombre }} ({{ oferta.codigo }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.oferta_academica_id" />
                            </div>

                            <div class="flex items-center gap-4 pt-2 border-t border-border mt-4">
                                <Button type="submit" :disabled="form.processing">
                                    Inscribir Estudiante
                                </Button>
                                <Button variant="ghost" as-child>
                                    <Link :href="route('matriculas-carrera.index')">Cancelar</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Bulk Creation (Excel/CSV) Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Carga Masiva (Excel / CSV)</CardTitle>
                        <CardDescription>Sube un archivo de Excel exportado como CSV para crear las cuentas y
                            matricularlas de una vez.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            class="text-xs text-muted-foreground space-y-2 bg-muted/40 p-3 rounded-lg border border-border/50">
                            <p class="font-bold text-foreground">Instrucciones del archivo:</p>
                            <p>El archivo CSV debe tener las siguientes columnas en su primera fila (cabecera):</p>
                            <ul class="list-disc list-inside font-mono text-[11px] text-foreground space-y-1">
                                <li><span class="font-bold">nombre</span>: Nombre completo del estudiante.</li>
                                <li><span class="font-bold">email</span>: Correo electrónico único.</li>
                                <li><span class="font-bold">codigo_oferta</span>: Código de la carrera (ej. INF-01,
                                    DER-02).</li>
                            </ul>
                            <p class="mt-2 text-[11px]">
                                * El sistema creará el usuario, le asignará el rol <span
                                    class="font-semibold text-foreground">Estudiante</span>,
                                generará un código de estudiante aleatorio de 9 dígitos y establecerá ese mismo código
                                como su contraseña inicial.
                            </p>
                            <div class="pt-2 border-t border-border/40 mt-2">
                                <a :href="route('matriculas-carrera.plantilla')"
                                    class="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline text-[11px]">
                                    📥 Descargar plantilla CSV (.csv)
                                </a>
                            </div>
                        </div>

                        <form @submit.prevent="submitImport" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="csv_file">Selecciona el archivo CSV</Label>
                                <input ref="fileInput" id="csv_file" type="file" accept=".csv"
                                    @change="handleFileSelect" class="hidden" />
                                <div @click="fileInput?.click()"
                                    class="border-2 border-dashed border-input hover:border-primary/50 transition-colors rounded-lg p-8 text-center cursor-pointer bg-muted/10">
                                    <div v-if="!importForm.archivo" class="space-y-2">
                                        <div class="text-3xl">📥</div>
                                        <p class="text-sm font-medium text-foreground">Hacer clic para subir el archivo
                                        </p>
                                        <p class="text-xs text-muted-foreground">Solo archivos delimitados por comas
                                            (.csv)</p>
                                    </div>
                                    <div v-else class="space-y-3">
                                        <div class="text-3xl text-primary">📄</div>
                                        <p class="text-sm font-semibold text-foreground truncate max-w-xs mx-auto">
                                            {{ importForm.archivo.name }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ (importForm.archivo.size / 1024).toFixed(2) }} KB
                                        </p>
                                        <Button variant="ghost" size="sm" type="button" @click.stop="clearFile"
                                            class="text-destructive hover:bg-destructive/10 h-7 px-2">
                                            Quitar archivo
                                        </Button>
                                    </div>
                                </div>
                                <InputError :message="importForm.errors.archivo" />
                            </div>

                            <!-- Import errors display -->
                            <div v-if="$page.props.errors.import_errors && ($page.props.errors.import_errors as any).length > 0"
                                class="space-y-2 mt-4">
                                <Label class="text-destructive font-semibold text-xs">Errores encontrados en el
                                    archivo:</Label>
                                <div
                                    class="max-h-40 overflow-y-auto bg-destructive/5 border border-destructive/20 rounded-lg p-3 text-xs text-destructive space-y-1 font-mono">
                                    <div v-for="(error, index) in ($page.props.errors.import_errors as any)"
                                        :key="index">
                                        • {{ error }}
                                    </div>
                                </div>
                            </div>

                            <Button type="submit" class="w-full mt-2"
                                :disabled="!importForm.archivo || importForm.processing">
                                Importar y Matricular
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
