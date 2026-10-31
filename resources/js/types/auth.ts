/**
 * The authenticated user as shared by HandleInertiaRequests. Personal data
 * (CPF, birth date, addresses) is deliberately not part of it.
 */
export type User = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    avatar_url: string | null;
};

export type Auth = {
    user: User | null;
};
