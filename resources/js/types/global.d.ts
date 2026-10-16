import type { Auth } from '@/types/auth';
import type { CompanyOption, CurrentCompany } from '@/types/company';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            currentCompany: CurrentCompany | null;
            companies: CompanyOption[];
            [key: string]: unknown;
        };
    }
}
