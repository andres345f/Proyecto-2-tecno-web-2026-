<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface GrupoAdmin {
    id: number;
    codigo: string;
    materia: { nombre: string; codigo: string };
    horarios: Array<{ dia: string; hora_inicio: string; hora_fin: string; aula: { nombre: string } }>;
    cupo_maximo: number;
    inscritos_count: number;
    periodo_nombre: string;
    carrera_nombre: string;
    aprobados_count: number;
    reprobados_count: number;
    tiene_notas: boolean;
}

const props = defineProps<{
    grupo: GrupoAdmin | null;
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const importFileInput = ref<HTMLInputElement | null>(null);

const importFileForm = useForm({
    archivo: null as File | null,
});

watch(() => props.open, (val) => {
    if (!val) {
        importFileForm.reset();
        if (importFileInput.value) importFileInput.value.value = '';
    }
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    importFileForm.archivo = target.files?.[0] || null;
};

const clearFile = () => {
    importFileForm.archivo = null;
    if (importFileInput.value) importFileInput.value.value = '';
};

const submit = () => {
    if (!props.grupo) return;
    importFileForm.post(`/matriculas-grupo/grupo/${props.grupo.id}/importar-notas`, {
        preserveScroll: true,
        onSuccess: () => {
            importFileForm.reset();
            clearFile();
            emit('update:open', false);
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Carga Masiva de Notas</DialogTitle>
                <DialogDescription>
                    Carga notas de estudiantes de forma masiva mediante un archivo Excel/CSV para el grupo seleccionado.
                </DialogDescription>
            </DialogHeader>
            <div class="mt-4 space-y-4" v-if="grupo">
                <div class="p-3 bg-secondary/50 rounded-lg space-y-1.5 border border-border text-xs">
                    <div><strong class="text-foreground">Grupo:</strong> {{ grupo.codigo }} - {{ grupo.materia?.nombre }}</div>
                    <div><strong class="text-foreground">Carrera:</strong> {{ grupo.carrera_nombre }}</div>
                    <div><strong class="text-foreground">Período:</strong> {{ grupo.periodo_nombre }}</div>
                </div>

                <div class="p-3 bg-secondary/30 rounded-lg flex items-center justify-between text-xs border border-border">
                    <span class="text-foreground">Plantilla pre-rellenada:</span>
                    <Button variant="link" size="sm" as-child class="h-auto p-0 font-bold">
                        <a :href="`/matriculas-grupo/grupo/${grupo.id}/plantilla-notas`" download>
                            📥 Descargar Plantilla
                        </a>
                    </Button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="csv_file">Archivo CSV / Excel</Label>
                        <div class="flex items-center gap-2">
                            <input
                                id="csv_file"
                                type="file"
                                ref="importFileInput"
                                accept=".csv,.txt"
                                @change="handleFileSelect"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium text-muted-foreground"
                            />
                            <Button v-if="importFileForm.archivo" type="button" variant="ghost" size="sm" @click="clearFile">
                                Limpiar
                            </Button>
                        </div>
                        <InputError :message="importFileForm.errors.archivo" />
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <Button type="button" variant="outline" @click="emit('update:open', false)">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="!importFileForm.archivo || importFileForm.processing">
                            Cargar Notas
                        </Button>
                    </div>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>
