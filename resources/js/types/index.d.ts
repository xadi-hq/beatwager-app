export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type BadgeCategory = 'events' | 'challenges' | 'wagers' | 'disputes' | 'special';
export type BadgeTier = 'standard' | 'bronze' | 'silver' | 'gold' | 'platinum';

export interface Badge {
    id: string;
    slug: string;
    name: string;
    description: string;
    category: BadgeCategory;
    tier: BadgeTier;
    is_shame: boolean;
    image_url: string;
    small_image_url: string;
}

export interface UserBadge {
    id: string;
    badge: Badge;
    group_id: string | null;
    group_name: string | null;
    awarded_at: string;
    is_pinned?: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};
