<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

interface Usuario {
    id: number;
    name: string;
    email: string;
    codigo_estudiante: string | null;
    is_propietario: boolean;
    is_director: boolean;
    is_secretaria: boolean;
    is_profesor: boolean;
    is_estudiante: boolean;
    is_activo: boolean;
}

const props = defineProps<{
    usuario: Usuario;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Usuarios', href: route('usuarios.index') },
    { title: 'Editar', href: route('usuarios.edit', props.usuario.id) },
];

import { watch } from 'vue';

const form = useForm({
    name: props.usuario.name,
    email: props.usuario.email,
    password: '',
    codigo_estudiante: props.usuario.codigo_estudiante || '',
    is_propietario: props.usuario.is_propietario,
    is_director: props.usuario.is_director,
    is_secretaria: props.usuario.is_secretaria,
    is_profesor: props.usuario.is_profesor,
    is_estudiante: props.usuario.is_estudiante,
    is_activo: props.usuario.is_activo,
});

watch(() => form.is_estudiante, (val) => {
    if (!val) {
        form.codigo_estudiante = '';
    }
});

const submit = () => {
    form.put(route('usuarios.update', props.usuario.id));
};
</script>

<template>
    <Head title="Editar Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Editar Usuario</h1>
                    <p class="text-sm text-muted-foreground mt-1">Actualiza los datos y permisos de {{ usuario.name }}.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="route('usuarios.index')">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Editar Detalles de la Cuenta</CardTitle>
                    <CardDescription>Actualiza la información. Deja la contraseña en blanco para no modificarla.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name input -->
                        <div class="space-y-2">
                            <Label for="name">Nombre Completo</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="ej. Juan Pérez"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Email input -->
                        <div class="space-y-2">
                            <Label for="email">Correo Electrónico</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="ej. juan.perez@universidad.edu"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password input -->
                        <div class="space-y-2">
                            <Label for="password">Contraseña (Opcional)</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Escribe una nueva contraseña si deseas cambiarla"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Roles checkboxes -->
                        <div class="space-y-3">
                            <Label class="text-base font-semibold">Roles del Sistema</Label>
                            <Card class="border-border/50 bg-muted/20">
                                <CardContent class="grid grid-cols-2 gap-4 p-4">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_estudiante"
                                            type="checkbox"
                                            v-model="form.is_estudiante"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                        />
                                        <label for="is_estudiante" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Estudiante</label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_profesor"
                                            type="checkbox"
                                            v-model="form.is_profesor"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                        />
                                        <label for="is_profesor" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Docente / Profesor</label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_secretaria"
                                            type="checkbox"
                                            v-model="form.is_secretaria"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                        />
                                        <label for="is_secretaria" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Secretaria</label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_director"
                                            type="checkbox"
                                            v-model="form.is_director"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                        />
                                        <label for="is_director" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Director</label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_propietario"
                                            type="checkbox"
                                            v-model="form.is_propietario"
                                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                        />
                                        <label for="is_propietario" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Propietario / Admin Supremo</label>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Student Code input (only visible when is_estudiante is checked) -->
                        <div v-if="form.is_estudiante" class="space-y-2">
                            <Label for="codigo_estudiante">Código de Estudiante *</Label>
                            <Input
                                id="codigo_estudiante"
                                v-model="form.codigo_estudiante"
                                type="text"
                                placeholder="ej. EST-2026-0001"
                                :required="form.is_estudiante"
                            />
                            <InputError :message="form.errors.codigo_estudiante" />
                        </div>

                        <!-- Active status -->
                        <div class="flex items-center space-x-2 pt-2">
                            <input
                                id="is_activo"
                                type="checkbox"
                                v-model="form.is_activo"
                                class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                            />
                            <Label for="is_activo" class="text-sm font-medium leading-none">Cuenta Activa (Habilitar inicio de sesión)</Label>
                        </div>

                        <!-- Form actions -->
                        <div class="flex items-center gap-4 pt-4 border-t border-border">
                            <Button type="submit" :disabled="form.processing">
                                Guardar Cambios
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link :href="route('usuarios.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
