export type TrackedJobStatus =
    | 'will apply'
    | 'applied'
    | 'interview'
    | 'no response'
    | 'offer'
    | 'rejected';

export type JobContactStatus =
    | 'will contact'
    | 'message sent'
    | 'replied'
    | 'no response';

export interface Cv {
    id: number;
    user_id: number;
    name: string | null;
    file_path: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface JobContact {
    id: number;
    job_id: number;
    name: string;
    email: string | null;
    position: string | null;
    status: JobContactStatus;
    notes: string | null;
    linkedin_url: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface ContactDraft {
    name: string;
    email: string;
    position: string;
    status: JobContactStatus;
    notes: string;
    linkedin_url: string;
}

export interface JobReminder {
    id: number;
    job_id: number;
    scheduled_at: string;
    sent_at: string | null;
    status: string;
    created_at?: string;
    updated_at?: string;
}

export interface TrackedJob {
    id: number;
    user_id: number;
    jobTitle: string;
    companyName: string;
    jobURL: string | null;
    status: TrackedJobStatus;
    cv_id: number | null;
    notes: string | null;
    appliedAt: string | null;
    created_at?: string;
    updated_at?: string;
    cv?: Cv | null;
    contacts?: JobContact[];
    reminder?: JobReminder | null;
}

export interface DashboardStats {
    total: number;
    willApply: number;
    applied: number;
    interview: number;
    interviews?: number;
    noResponse: number;
    offer: number;
    offers?: number;
    rejected: number;
    pendingReminders: number;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
