import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useI18n() {
    const page = usePage();

    const translations = computed(() => page.props.translations ?? {});
    const locale = computed(() => page.props.locale ?? 'fr');
    const supportedLocales = computed(() => page.props.supportedLocales ?? ['en', 'fr']);

    const t = (key, fallback = null) => {
        const parts = key.split('.');
        let current = translations.value;

        for (const part of parts) {
            if (current == null || typeof current !== 'object' || !(part in current)) {
                return fallback ?? key;
            }
            current = current[part];
        }

        return typeof current === 'string' ? current : fallback ?? key;
    };

    return {
        locale,
        supportedLocales,
        t,
    };
}
