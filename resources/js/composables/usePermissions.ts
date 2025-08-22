// composables/usePermissions.ts
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();
    const permissions = page.props.auth.permissions as string[];

    const can = (perm: string): boolean => permissions.includes(perm);

    return { can };
}
