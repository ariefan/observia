import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { NavItem, NavGroup, SharedData } from '@/types';
import {
    LayoutGrid,
    Home,
    Shield,
    TrendingUp,
    Heart,
    Layers,
    CreditCard,
    Settings,
} from 'lucide-vue-next';
import { IconFileText, IconHorse } from '@tabler/icons-vue';

/**
 * Centralized navigation composable
 * Single source of truth for all navigation items across the application
 */
export function useNavigation() {
    const page = usePage<SharedData>();

    const back = () => window.history.back();

    // Check if user has farm context
    const hasFarmContext = computed(() => {
        return page.props.auth.farms && page.props.auth.user?.current_farm_id;
    });

    // Check if user is super user
    const isSuperUser = computed(() => {
        return page.props.auth.user?.is_super_user ?? false;
    });

    // Check if user has admin access (super user or admin/owner/finance role)
    const hasAdminAccess = computed(() => {
        if (isSuperUser.value) return true;
        const currentFarm = page.props.auth.user?.currentFarm;
        if (!currentFarm) return false;
        return ['admin', 'owner', 'finance'].includes(currentFarm.role ?? '');
    });

    /**
     * Main sidebar navigation items
     * Used in AppSidebar.vue
     */
    const mainNavItems = computed<NavItem[]>(() => {
        const items: NavItem[] = [];

        // Super user items
        if (isSuperUser.value) {
            items.push(
                {
                    title: 'Super Dashboard',
                    href: '/super-dashboard',
                    icon: Shield,
                },
                {
                    title: 'Manajemen Konten',
                    href: '/content-management',
                    icon: Layers,
                }
            );
        }

        // Farm-dependent items
        if (hasFarmContext.value) {
            items.push(
                {
                    title: 'Dashboard',
                    href: '/dashboard',
                    icon: LayoutGrid,
                },
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
                {
                    title: 'Transaksi',
                    href: '/transaksi/paket-layanan',
                    icon: CreditCard,
                }
            );
        }

        // Data menu - available for super users or users with farm context
        if (isSuperUser.value || hasFarmContext.value) {
            items.push({
                title: 'Data',
                href: '/laporan',
                icon: IconFileText,
            });
        }

        // Settings - for admin users
        if (hasAdminAccess.value) {
            items.push({
                title: 'Pengaturan',
                href: '/admin/settings',
                icon: Settings,
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
            items.push(
                {
                    title: 'Dashboard',
                    href: '/dashboard',
                    icon: LayoutGrid,
                },
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
                }
            );
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
        hasFarmContext,
        isSuperUser,
        hasAdminAccess,
        mainNavItems,
        mobileNavItems,
        isActiveRoute,
        isActiveRouteName,
    };
}
