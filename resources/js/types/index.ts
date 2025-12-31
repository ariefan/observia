import type { Component } from 'vue';

// Navigation types
export interface NavItem {
    title: string;
    href: string;
    icon: Component;
    badge?: string | number;
    isActive?: boolean;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
    collapsible?: boolean;
    defaultOpen?: boolean;
}

// Breadcrumb types
export interface BreadcrumbItem {
    title: string;
    href: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Farm Role types
export type FarmRoleValue = 'owner' | 'admin' | 'farmer' | 'investor';

export interface FarmRoleOption {
    value: FarmRoleValue;
    label: string;
    description: string;
}

// User types
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at?: string;
    is_super_user?: boolean;
    current_farm_id?: string | null;
    currentFarm?: Farm | null;
    created_at: string;
    updated_at: string;
}

// Farm types
export interface Farm {
    id: string;
    name: string;
    picture?: string | null;
    users_count?: number;
    role?: FarmRoleValue | string;
    role_label?: string;
}

// Permission types - matches what HandleInertiaRequests sends
export interface Permissions {
    isSuperUser: boolean;
    canAccessFinance: boolean;
    canModifyFinance: boolean;
    canAccessOperations: boolean;
    canModifyOperations: boolean;
    canManageMembers: boolean;
    canAccessSettings: boolean;
    isViewOnly: boolean;
}

// Flash message types
export interface FlashMessages {
    success?: string | null;
    error?: string | null;
}

// Shared data from Inertia
export interface SharedData {
    name: string;
    quote: {
        message: string;
        author: string;
    };
    auth: {
        user: User | null;
        farms: Farm[];
        permissions: Permissions;
    };
    flash: FlashMessages;
    farmRoles: FarmRoleOption[];
}

// Livestock types
export interface LivestockDetail {
    id: string;
    name: string;
    aifarm_id: string;
    photo: string | null;
    species: string;
    average_litre_per_day?: number;
    total_volume?: number;
    lactation_days?: number;
    current_weight?: number;
    national_rank?: number;
    barn_rank?: number;
    total_national_livestock?: number;
    farm?: {
        name: string;
        image?: string;
    };
}