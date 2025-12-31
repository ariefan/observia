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
    role?: string;
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
    };
    flash: FlashMessages;
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