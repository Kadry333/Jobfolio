import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (data) {
            toast[data.type](data.message);
        }
    });

    router.on('success', (event) => {
        const page = event.detail.page;
        const flash = (page.props as Record<string, any>)?.flash;
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    });

    router.on('httpException', (event) => {
        const response = event.detail.response;
        if (response?.status === 429) {
            toast.error(
                'Too many requests. Please slow down and try again in a moment.',
            );
        }
    });
}
