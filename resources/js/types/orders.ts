export type CartItem = {
    id: number;
    quantity: number;
    subtotal_cents: number;
    max_quantity: number;
    product: {
        name: string;
        slug: string;
        price_cents: number;
        image_url: string | null;
        available: boolean;
    };
};

export type Address = {
    id?: number;
    cep: string;
    street: string;
    number: string;
    complement: string | null;
    neighborhood: string;
    city: string;
    state: string;
};

export type OrderItem = {
    id: number;
    product_id: number;
    product_slug: string | null;
    product_name: string;
    unit_price_cents: number;
    quantity: number;
    subtotal_cents: number;
};

export type Order = {
    id: number;
    status: string;
    status_label: string;
    total_cents: number;
    shipping_address: Address;
    created_at: string;
    /** Only while the order waits for payment. */
    payment_due_at: string | null;
    items: OrderItem[];
    timeline: OrderStatusChange[];
};

export type OrderStatusChange = {
    id: number;
    status: string;
    status_label: string;
    reason: string | null;
    created_at: string;
    /** Admin pages only; null means the system made the change. */
    actor?: string | null;
};
