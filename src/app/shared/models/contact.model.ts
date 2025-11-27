export interface Contact {
    id: number;
    name: string;
    email?: string | null;
    phone?: string | null;
    status?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface PaginatedContacts {
    data: Contact[];
    links?: PaginationLinks;
    meta?: PaginationMeta;
}

export interface ContactResponse {
    data: Contact;
}

export interface PaginationLinks {
    first?: string;
    last?: string;
    prev?: string;
    next?: string;
}

export interface PaginationMeta {
    current_page: number;
    from?: number;
    last_page: number;
    links?: PaginationLink[];
    path?: string;
    per_page: number;
    to?: number;
    total: number;
}

export interface PaginationLink {
    active: boolean;
    label: string;
    url?: string | null;
}

export interface ContactQueryParams {
    q?: string;
    status?: string;
    page?: number;
}

export type ContactPayload = Pick<Contact, 'name' | 'email' | 'phone' | 'status'>;
