export type Product = {
    id: number;
    name: string;
    slug: string;
    excerpt: string;
    description?: string | null;
    price_cents: number;
    image_url: string | null;
    available: boolean;
    low_stock: boolean;
    rating: number | null;
    reviews_count: number;
};

export type Review = {
    id: number;
    rating: number;
    comment: string | null;
    author: string;
    created_at: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** A Laravel paginated API resource collection. */
export type Paginated<T> = {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };
};
