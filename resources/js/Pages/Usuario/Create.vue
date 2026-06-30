<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuarios', href: '/usuarios' },
    { title: 'Crear', href: '/usuarios/create' },
];

import { watch } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    codigo_estudiante: '',
    is_propietario: false,
    is_director: false,
    is_secretaria: false,
    is_profesor: false,
    is_estudiante: false,
    is_activo: true,
});

watch(() => form.is_estudiante, (val) => {
    if (!val) {
        form.codigo_estudiante = '';
    }
});

const submit = () => {
    form.post('/usuarios');
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Crear Usuario</h1>
                    <p class="text-sm text-muted-foreground mt-1">Registra una nueva cuenta de usuario asignando sus roles.</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/usuarios">Volver</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles del Usuario</CardTitle>
                    <CardDescription>Completa la información básica y marca los roles de la cuenta.</CardDescription>
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
                            <Label for="password">Contraseña</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Mínimo 8 caracteres"
                                required
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
                                Registrar Usuario
                            </Button>
                            <Button variant="ghost" as-child>
                                <Link href="/usuarios">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
