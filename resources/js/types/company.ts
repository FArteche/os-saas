export type CompanyRole = 'owner' | 'attendant' | 'technician';

/** The company the user is working on (shared on every page). */
export type CurrentCompany = {
    id: number;
    name: string;
    role: CompanyRole | null;
    roleLabel: string | null;
};

/** A company the user can switch to. */
export type CompanyOption = {
    id: number;
    name: string;
};
