<script setup lang="ts">
import { usePollingPago } from '@/composables/usePollingPago';
import { ref, watch } from 'vue';

interface Props {
    show: boolean;
    qrImage: string | null;
    transaccionId: string | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    completed: [];
}>();

const { status, startPolling, stopPolling } = usePollingPago();

const isGenerating = ref(false);

watch(
    () => props.transaccionId,
    (newId) => {
        if (newId) {
            startPolling(newId);
        }
    },
);

watch(status, (newStatus) => {
    if (newStatus === 'completado') {
        setTimeout(() => {
            emit('completed');
            emit('close');
        }, 1500);
    }
});

function handleClose() {
    stopPolling();
    emit('close');
}

const statusLabel = ref('');
watch(status, (s) => {
    switch (s) {
        case 'pending':
        case 'polling':
            statusLabel.value = 'Esperando pago...';
            break;
        case 'completado':
            statusLabel.value = 'Pago completado ✓';
            break;
        case 'fallido':
            statusLabel.value = 'Pago fallido';
            break;
        case 'error':
            statusLabel.value = 'Error de conexión';
            break;
        default:
            statusLabel.value = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50" @click="handleClose"></div>

            <!-- Modal -->
            <div class="relative z-10 mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <!-- Close button -->
                <button class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="handleClose">✕</button>

                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Pago con QR</h3>

                <!-- QR Image -->
                <div class="mb-4 flex justify-center">
                    <div v-if="qrImage" class="rounded-lg border border-gray-200 p-4 dark:border-gray-600">
                        <img :src="qrImage" alt="Código QR de pago" class="h-48 w-48" />
                    </div>
                    <div
                        v-else
                        class="flex h-48 w-48 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700"
                    >
                        <span class="text-sm text-gray-500 dark:text-gray-400">Generando QR...</span>
                    </div>
                </div>

                <!-- Transaction ID -->
                <div v-if="transaccionId" class="mb-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">ID de transacción</p>
                    <p class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ transaccionId }}</p>
                </div>

                <!-- Status -->
                <div class="text-center">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium',
                            status === 'completado'
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                : status === 'fallido' || status === 'error'
                                  ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                  : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                        ]"
                    >
                        {{ statusLabel }}
                    </span>
                </div>

                <!-- Polling indicator -->
                <div v-if="status === 'polling' || status === 'pending'" class="mt-4 flex justify-center">
                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
