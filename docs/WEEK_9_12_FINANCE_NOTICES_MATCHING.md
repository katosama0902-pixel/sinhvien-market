# Weeks 9–12: residence finance, notices, and marketplace matching

## What is included

- Additive MySQL migration for fee categories, invoices, immutable invoice lines,
  payment records, residence announcements/events/rules, product embeddings, and
  matching evaluation records.
- Session/permission-protected JSON APIs and Next.js screens for student invoices
  and payment submissions, dorm notices, manager invoice/payment workflows, and
  marketplace product retrieval/evaluation.
- Matching reuses only active products from the existing `products` and `categories`
  tables. Basic structured filters cover category, price, condition, and listing type.
- Evaluation accepts manually judged relevant product IDs and stores P@5, Recall@5,
  MRR@5, and NDCG@5 alongside the query and ranked product IDs.

## Migration and permissions

The migration is
`database/migrations/20261113_week9_12_finance_notices_matching.sql`. Apply it
manually **after** `20261104_ktx_foundation.sql` and `20261104_ktx_operations.sql`
when a project owner authorizes a database change. It has not been run as part of
this implementation. It only creates new tables and seeds new permissions through
`INSERT IGNORE`; it does not alter or remove existing tables/data.

Students receive invoice read/payment submission, active notices, and matching search.
Dorm managers and admins receive fee/invoice/notice management, payment review,
matching indexing, and evaluation access. New financial/payment records are
operational records only; the payment flow does not claim to verify a bank transfer
or connect to an external payment gateway.

## Local matching baseline and limitations

`LocalMarketplaceEmbeddingService` uses deterministic 256-dimensional signed feature
hashing over text tokens and adjacent token pairs. Product vectors are persisted as
JSON and versioned with model key `local-feature-hash-v1-256`; content hashes trigger
refresh after product text changes. Cosine ranking and product lookup run inside
the application/MySQL workflow, with no external model, vector database, API, or
third-party data dependency.

This is a reproducible **lexical baseline**, not a trained multilingual model or
semantic embedding. It will not reliably recognize synonyms or image meaning.
Candidate scan is capped at 500 active products for search (1,000 for explicit
indexing); this simple scan is intended for a small evaluation dataset. A future
model/vector backend should be selected only after local evaluation and documented
as a separate, explicit dependency.

Evaluation is only as meaningful as the manually curated relevance labels. Use
identical query/label sets when comparing future baselines; the application does
not claim measured quality based on unlabeled production traffic.

## API routes

- `GET /api/residence/fee-categories`, `/api/residence/invoices`,
  `/api/residence/payments`, `/api/residence/notices`
- `POST /api/residence/payments` (student records a pending payment)
- `POST /api/admin/residence/fee-categories`,
  `PUT /api/admin/residence/fee-categories/{id}`,
  `POST /api/admin/residence/invoices`,
  `POST /api/admin/residence/payments/{id}/review`,
  `POST /api/admin/residence/notices`,
  `POST /api/admin/residence/notices/{id}/archive`
- `GET /api/marketplace/matching/search?q=...` (plus optional structured filters)
- `POST /api/admin/marketplace/matching/index`,
  `GET|POST /api/admin/marketplace/matching/evaluations`

Mutations use the existing session CSRF token. Payment amounts are bounded by the
invoice balance, pending submissions reserve balance, and confirmation rechecks
the confirmed amount under a transaction/row lock.

## Validation

Run the PHPUnit unit tests and frontend `npm run lint`, `npm test`, and `npm run
build` from `sinhvien-market-next/frontend`. The PHP unit tests for hashing/metrics
do not require a database. DB-backed verification needs a separately configured
test database and the migration above; it must not be run against any database
without explicit authorization.
