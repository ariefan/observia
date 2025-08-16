import type { LucideIcon } from 'lucide-vue-next';
import type { PageProps } from '@inertiajs/core';
import type { Component } from 'vue';

export interface Auth {
    user: User;
    farms: Farm[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | Component;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
}

export interface Farm {
    id: string;
    name: string;
    address?: string;
    picture?: string;
    users_count?: number;
}

export interface User {
    id: number;
    name: string;
    username?: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    current_farm_id: string | null;
    current_farm?: Farm;
    farms: Farm[] | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Species {
    id: number;
    name: string;
}

export interface Breed {
    id: number;
    name: string;
    species_id: number;
    species: Species;
}

export interface Herd {
    id: string;
    name: string;
    capacity: number;
}

export interface Livestock {
    id?: string;
    name?: string;
    origin?: number;
    status?: number;
    species_id?: number;
    breed_id?: number;
    breed?: Breed;
    herd_id?: string;
    male_parent_id?: string;
    female_parent_id?: string;
    male_parent?: Livestock;
    female_parent?: Livestock;
    sex?: string;
    tag_type?: string;
    tag_id?: string;
    aifarm_id?: string;
    birthdate?: string;
    purchase_date?: string;
    birth_weight?: number;
    weight?: number;
    photo?: File[] | string[];
    barter_livestock_id?: string;
    barter_from?: string;
    barter_date?: string;
    purchase_price?: number;
    purchase_from?: string;
    grant_from?: string;
    grant_date?: string;
    borrowed_from?: string;
    borrowed_date?: string;
    entry_date?: string;
}
