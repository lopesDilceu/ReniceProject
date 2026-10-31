import { Form } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { store } from '@/routes/reviews';

export default function ReviewForm({ productSlug }: { productSlug: string }) {
    const [rating, setRating] = useState(0);

    return (
        <Form
            {...store.form(productSlug)}
            options={{ preserveScroll: true }}
            className="space-y-3 rounded-lg bg-muted/50 p-4"
        >
            {({ processing, errors }) => (
                <>
                    <fieldset>
                        <legend className="mb-1 text-sm font-medium">
                            Sua nota
                        </legend>
                        <div className="flex gap-1">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <button
                                    key={star}
                                    type="button"
                                    onClick={() => setRating(star)}
                                    aria-label={`${star} ${star === 1 ? 'estrela' : 'estrelas'}`}
                                    aria-pressed={rating === star}
                                    className="rounded focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <Star
                                        className={cn(
                                            'size-6',
                                            star <= rating
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'text-muted-foreground/40',
                                        )}
                                    />
                                </button>
                            ))}
                        </div>
                        <input
                            type="hidden"
                            name="rating"
                            value={rating || ''}
                        />
                        <InputError message={errors.rating} />
                    </fieldset>
                    <textarea
                        name="comment"
                        rows={3}
                        maxLength={1000}
                        placeholder="Conte o que achou (opcional)"
                        aria-label="Comentário"
                        className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                    />
                    <InputError message={errors.comment} />
                    <Button size="sm" disabled={processing || rating === 0}>
                        {processing && <Spinner />}
                        Enviar avaliação
                    </Button>
                </>
            )}
        </Form>
    );
}
