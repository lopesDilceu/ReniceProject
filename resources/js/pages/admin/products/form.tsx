import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import ProductImage from '@/components/catalog/product-image';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { centsToInput } from '@/lib/format';
import products from '@/routes/admin/products';
import type { AdminProduct } from '@/types';

export default function AdminProductForm({
    product,
}: {
    product: AdminProduct | null;
}) {
    const editing = product !== null;
    const action = editing
        ? products.update.form(product.id)
        : products.store.form();

    return (
        <>
            <Head
                title={
                    editing
                        ? `Editar ${product.name} · Admin`
                        : 'Novo produto · Admin'
                }
            />

            <div className="max-w-2xl space-y-6">
                <Link
                    href={products.index()}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Produtos
                </Link>

                <h1 className="text-2xl font-semibold">
                    {editing ? 'Editar produto' : 'Novo produto'}
                </h1>

                <Form
                    {...action}
                    className="space-y-5 rounded-xl border bg-background p-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={product?.name}
                                    required
                                    maxLength={100}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Descrição</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows={4}
                                    maxLength={2000}
                                    defaultValue={product?.description ?? ''}
                                    className="rounded-md border bg-background px-3 py-2 text-sm"
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid max-w-48 gap-2">
                                <Label htmlFor="price">Preço (R$)</Label>
                                <Input
                                    id="price"
                                    name="price"
                                    inputMode="decimal"
                                    placeholder="11,25"
                                    defaultValue={
                                        product
                                            ? centsToInput(product.price_cents)
                                            : ''
                                    }
                                    required
                                />
                                <InputError
                                    message={errors.price ?? errors.price_cents}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">Imagem</Label>
                                <div className="flex items-center gap-4">
                                    <ProductImage
                                        src={product?.image_url ?? null}
                                        alt=""
                                        className="size-16 rounded-md border"
                                    />
                                    <Input
                                        id="image"
                                        name="image"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                    />
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    JPG, PNG ou WebP até 3 MB.
                                </p>
                                <InputError message={errors.image} />
                            </div>

                            {!editing && (
                                <p className="text-sm text-muted-foreground">
                                    O estoque começa em zero. Registre uma
                                    compra de fornecedor para abastecer.
                                </p>
                            )}

                            <Button disabled={processing}>
                                {processing && <Spinner />}
                                {editing
                                    ? 'Salvar alterações'
                                    : 'Cadastrar produto'}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
