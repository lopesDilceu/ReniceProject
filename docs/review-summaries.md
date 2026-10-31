# AI review summaries

Each product page can show **"O que os clientes dizem"**: one or two sentences plus up to three pros and three cons, summarized from the product's reviews by an LLM through [`laravel/ai`](https://github.com/laravel/ai). It is labeled **"Resumo gerado por IA"**, says how many reviews it is based on and when it was updated, and the reviews themselves stay right below it.

## Turning it on

```dotenv
REVIEW_SUMMARY_PROVIDER=anthropic
ANTHROPIC_API_KEY=sk-ant-...
# Optional: REVIEW_SUMMARY_MODEL=claude-haiku-5-5
```

Then summarize the existing reviews once:

```bash
docker compose exec laravel.test php artisan reviews:summarize
```

New, edited and removed reviews update the summary by themselves, through the `queue` service. Without a provider there is no summary block and no calls.

## How it works

| | |
|---|---|
| **Trigger** | A review is saved or deleted. `SummarizeReviews` is queued with a 5-minute delay and is unique per product, so a burst of reviews makes one call |
| **Input** | Product name, plus the latest 100 reviews with a comment, as `rating/5: comment`. Authors are left out, so no personal data reaches the provider |
| **Minimum** | 3 reviews with a comment. Below that, the summary is removed, because it would only restate one or two reviews |
| **Skip** | A `source_hash` covers the prompt version, the model and the reviews. If none changed, no call is made |
| **Output** | Structured output (JSON schema): `summary` (≤ 280 characters), `pros` and `cons` (≤ 3 each). It is checked again before storing: an empty summary fails the job, and non-strings and extra points are dropped |
| **Versioning** | `ReviewSummarizer::VERSION` is stored on each summary. Changing the prompt or the schema means bumping it, and every product is summarized again on the next run |
| **Provider down** | The job retries (30 s, then 5 min) and the previous summary stays, since it is still true of the reviews it came from. The product page only reads the database and never waits on the provider |
| **Moderation** | When an admin removes a review, the current summary is deleted at once, so removed content does not live on in it, and a new one is scheduled |
| **Prompt injection** | The instructions tell the model that reviews are customer-written data, and that it must ignore instructions or links inside them and never repeat personal data |

## Testing

`tests/Feature/Reviews/ReviewSummaryTest.php` runs against `laravel/ai`'s fake agent:

- debouncing a burst of reviews;
- the prompt contents, with no author names;
- versioned storage;
- skipping unchanged reviews;
- the minimum of reviews;
- provider failure keeping the old summary;
- removal by moderation;
- validation of the output;
- the page props and the backfill command.

The real provider has not been called yet: that needs an API key.
