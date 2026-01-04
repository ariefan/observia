import type { NavItem, Permissions, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { IconFileText, IconHorse } from '@tabler/icons-vue';
import { BarChart3, CreditCard, Heart, Home, LayoutGrid, Shield, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

/**
 * Centralized navigation composable
 * Single source of truth for all navigation items across the application
 *
 * Uses permissions from backend (HandleInertiaRequests) for consistency
 */
export function useNavigation() {
    const page = usePage<SharedData>();

    const back = () => window.history.back();

    // Get permissions from backend
    const permissions = computed<Permissions>(() => {
        return (
            page.props.auth.permissions ?? {
                isSuperUser: false,
                canAccessFinance: false,
                canModifyFinance: false,
                canAccessOperations: false,
                canModifyOperations: false,
                canManageMembers: false,
                canAccessSettings: false,
                isViewOnly: false,
            }
        );
    });

    // Check if user has farm context
    const hasFarmContext = computed(() => {
        return !!(page.props.auth.farms?.length && page.props.auth.user?.current_farm_id);
    });

    // Check if user is super user (from permissions)
    const isSuperUser = computed(() => permissions.value.isSuperUser);

    // Check if user can access operations (owner, admin, farmer)
    const canAccessOperations = computed(() => permissions.value.canAccessOperations);

    // Check if user can access finance (owner, admin, investor)
    const canAccessFinance = computed(() => permissions.value.canAccessFinance);

    // Check if user can modify finance (owner, admin)
    const canModifyFinance = computed(() => permissions.value.canModifyFinance);

    // Check if user can access settings (owner, admin)
    const canAccessSettings = computed(() => permissions.value.canAccessSettings);

    // Check if user is view only (investor)
    const isViewOnly = computed(() => permissions.value.isViewOnly);

    // Get current farm role
    const currentFarmRole = computed(() => {
        return page.props.auth.user?.currentFarm?.role ?? null;
    });

    // Get current farm role label
    const currentFarmRoleLabel = computed(() => {
        return page.props.auth.user?.currentFarm?.role_label ?? null;
    });

    /**
     * Main sidebar navigation items
     * Used in AppSidebar.vue
     */
    const mainNavItems = computed<NavItem[]>(() => {
        const items: NavItem[] = [];

        // Super user items
        if (isSuperUser.value) {
            items.push({
                title: 'Super Dashboard',
                href: '/super-dashboard',
                icon: Shield,
            });
        }

        // Farm-dependent items - Dashboard always visible if has farm context
        if (hasFarmContext.value) {
            items.push({
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            });

            // Operational items - only for users who can access operations (not investors)
            if (canAccessOperations.value) {
                items.push(
                    {
                        title: 'Populasi',
                        href: '/livestocks',
                        icon: IconHorse,
                    },
                    {
                        title: 'Produktivitas',
                        href: '/productivity',
                        icon: TrendingUp,
                    },
                    {
                        title: 'Kesehatan',
                        href: '/health-records',
                        icon: Heart,
                    },
                );
            }

            // Transaksi - for operational users
            if (canAccessOperations.value) {
                items.push({
                    title: 'Transaksi',
                    href: '/transaksi/paket-layanan',
                    icon: CreditCard,
                });
            }
        }

        // Data menu - available for super users or users with farm context
        if (isSuperUser.value || hasFarmContext.value) {
            items.push({
                title: 'Data',
                href: '/laporan',
                icon: IconFileText,
            });
        }

        // Home fallback for users without farm context
        if (!isSuperUser.value && !hasFarmContext.value) {
            items.push({
                title: 'Home',
                href: '/home',
                icon: Home,
            });
        }

        return items;
    });

    /**
     * Bottom navigation items for mobile
     * Used in BottomNavigation.vue
     * Limited to essential items for mobile UX
     */
    const mobileNavItems = computed<NavItem[]>(() => {
        const items: NavItem[] = [];

        if (isSuperUser.value) {
            items.push({
                title: 'Super',
                href: '/super-dashboard',
                icon: Shield,
            });
        }

        if (hasFarmContext.value) {
            items.push({
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            });

            // Only show operational items if user can access operations
            if (canAccessOperations.value) {
                items.push(
                    {
                        title: 'Populasi',
                        href: '/livestocks',
                        icon: IconHorse,
                    },
                    {
                        title: 'Produktivitas',
                        href: '/productivity',
                        icon: TrendingUp,
                    },
                );
            }

            // Finance for investors and admins
            if (canAccessFinance.value) {
                items.push({
                    title: 'Keuangan',
                    href: '/payments/finance',
                    icon: BarChart3,
                });
            }
        }

        if (isSuperUser.value || hasFarmContext.value) {
            items.push({
                title: 'Data',
                href: '/rations',
                icon: IconFileText,
            });
        }

        if (!isSuperUser.value && !hasFarmContext.value) {
            items.push({
                title: 'Home',
                href: '/home',
                icon: Home,
            });
        }

        return items;
    });

    /**
     * Check if a route is currently active
     */
    const isActiveRoute = (href: string): boolean => {
        const currentUrl = page.url;
        if (currentUrl === href) return true;
        // Check if current URL starts with the href (for nested routes)
        const normalizedHref = href.replace(/\/$/, '');
        return currentUrl.startsWith(normalizedHref + '/') || currentUrl === normalizedHref;
    };

    /**
     * Check if a route name matches current route
     */
    const isActiveRouteName = (routeName: string): boolean => {
        try {
            return route().current(routeName);
        } catch {
            return false;
        }
    };

    return {
        back,
        permissions,
        hasFarmContext,
        isSuperUser,
        canAccessOperations,
        canAccessFinance,
        canModifyFinance,
        canAccessSettings,
        isViewOnly,
        currentFarmRole,
        currentFarmRoleLabel,
        mainNavItems,
        mobileNavItems,
        isActiveRoute,
        isActiveRouteName,
    };
}
