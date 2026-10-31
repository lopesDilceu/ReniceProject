-- pgvector HNSW latency benchmark (docs/search.md). Synthetic: 10,000 random
-- 1024-dimension vectors, cosine distance, top 50, with and without the index.
-- Creates and drops its own table. Run against a disposable database:
--   docker compose exec -T pgsql psql -U sail -d testing -q < tests/Benchmarks/pgvector-hnsw.sql
CREATE EXTENSION IF NOT EXISTS vector;
DROP TABLE IF EXISTS bench_vectors;
CREATE TABLE bench_vectors (id serial PRIMARY KEY, embedding vector(1024));
INSERT INTO bench_vectors (embedding)
  SELECT ARRAY(SELECT random()::real - 0.5 FROM generate_series(1, 1024) WHERE g.n > 0)::vector
  FROM generate_series(1, 10000) AS g(n);
\timing on
CREATE INDEX bench_hnsw ON bench_vectors USING hnsw (embedding vector_cosine_ops);
\timing off
ANALYZE bench_vectors;
DO $$
DECLARE
  q vector; t0 timestamptz; times float8[] := '{}'; i int;
BEGIN
  FOR i IN 1..200 LOOP
    SELECT embedding INTO q FROM bench_vectors WHERE id = 1 + floor(random() * 10000)::int;
    t0 := clock_timestamp();
    PERFORM id FROM bench_vectors ORDER BY embedding <=> q LIMIT 50;
    times := times || extract(epoch FROM clock_timestamp() - t0) * 1000;
  END LOOP;
  RAISE NOTICE 'HNSW top-50 over 10000 x 1024: p50=% ms, p95=% ms, max=% ms',
    round((SELECT percentile_cont(0.5) WITHIN GROUP (ORDER BY x) FROM unnest(times) x)::numeric, 2),
    round((SELECT percentile_cont(0.95) WITHIN GROUP (ORDER BY x) FROM unnest(times) x)::numeric, 2),
    round((SELECT max(x) FROM unnest(times) x)::numeric, 2);
END $$;
SET enable_indexscan = off;
DO $$
DECLARE q vector; t0 timestamptz; times float8[] := '{}'; i int;
BEGIN
  FOR i IN 1..50 LOOP
    SELECT embedding INTO q FROM bench_vectors WHERE id = 1 + floor(random() * 10000)::int;
    t0 := clock_timestamp();
    PERFORM id FROM bench_vectors ORDER BY embedding <=> q LIMIT 50;
    times := times || extract(epoch FROM clock_timestamp() - t0) * 1000;
  END LOOP;
  RAISE NOTICE 'Sequential scan (no index), same query: p50=% ms, p95=% ms',
    round((SELECT percentile_cont(0.5) WITHIN GROUP (ORDER BY x) FROM unnest(times) x)::numeric, 2),
    round((SELECT percentile_cont(0.95) WITHIN GROUP (ORDER BY x) FROM unnest(times) x)::numeric, 2);
END $$;
SELECT pg_size_pretty(pg_total_relation_size('bench_vectors')) AS table_and_index_size;
DROP TABLE bench_vectors;
