export type AdminProduct = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_cents: number;
    stock: number;
    image_url: string | null;
    deleted: boolean;
    created_at: string;
};

export type InventoryMovement = {
    id: number;
    type: string;
    type_label: string;
    quantity: number;
    stock_after: number;
    source: string | null;
    user: string | null;
    note: string | null;
    created_at: string;
};

export type SupplierPurchase = {
    id: number;
    product: string;
    supplier: string;
    quantity: number;
    unit_cost_cents: number;
    total_cents: number;
    purchased_at: string;
};

/** A paginator passed through ->through(), without a resource wrapper. */
export type SimplePaginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
};
