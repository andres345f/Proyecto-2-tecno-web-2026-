import { onUnmounted, ref } from 'vue';

export function usePollingPago() {
    const status = ref<string>('pending');
    const intervalId = ref<number | null>(null);

    function startPolling(transaccionId: string) {
        stopPolling();
        status.value = 'polling';

        intervalId.value = window.setInterval(async () => {
            try {
                const response = await fetch(`/api/pagos/estado/${transaccionId}`);
                const data = await response.json();
                status.value = data.status;

                if (data.status === 'completado' || data.status === 'fallido') {
                    stopPolling();
                }
            } catch {
                status.value = 'error';
                stopPolling();
            }
        }, 3000);
    }

    function stopPolling() {
        if (intervalId.value !== null) {
            clearInterval(intervalId.value);
            intervalId.value = null;
        }
    }

    onUnmounted(stopPolling);

    return { status, startPolling, stopPolling };
}
