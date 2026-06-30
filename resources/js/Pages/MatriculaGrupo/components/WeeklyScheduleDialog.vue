<script setup lang="ts">
import WeeklySchedule from '@/components/WeeklySchedule.vue';
import { Calendar } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

defineProps<{
    horarios: Array<{
        dia: string;
        hora_inicio: string;
        hora_fin: string;
        grupo_codigo: string;
        materia_nombre: string;
        aula_nombre: string;
    }>;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline" class="flex items-center gap-2">
                <Calendar class="w-4 h-4" />
                <span>Ver Horario Semanal</span>
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-5xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Horario Semanal</DialogTitle>
                <DialogDescription>
                    Distribución horaria de tus materias inscritas para el ciclo lectivo en curso.
                </DialogDescription>
            </DialogHeader>
            <div class="mt-4">
                <WeeklySchedule
                    title="Distribución de Clases"
                    :horarios="horarios"
                    type="usuario"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
