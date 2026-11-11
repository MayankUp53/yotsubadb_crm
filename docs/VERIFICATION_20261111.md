# Verification of the current CRM against app/

Date: 2026-11-11. Result: the implemented flows pass the current tests, but the new application is **not exactly identical** to the legacy application.

## Checks completed

| Check | Result |
| --- | --- |
| Full repository test cases, using the isolated bootstrap | 90 tests / 1,287 assertions passed |
| PHP syntax across src/, config/, templates/ | 205 files passed |
| Actual configured DB, Customers/Contacts GET screen rendering | 8 screens passed |
| Actual configured DB, five Settings masters GET screen rendering | 20 screens passed |
| Actual configured DB, Settings dashboard rendering | Passed |
| Actual configured DB, Users Index/Add/Edit/View rendering | 4 screens passed |
| Phone-list lookup/download | Generated successfully; contents suppressed |
| Customer import template and phone template | XLSX generation passed |
| Actual Customers Index, pages 1/2, 20 rows | 0.233s / 0.089s; no total COUNT |
| Actual Contacts Index, pages 1/2, 20 rows | 0.109s / 0.069s; no total COUNT |

Timings are single local smoke runs, not load-test guarantees. Queries used the active/updated indexes. Schema inspection confirmed customers.belong has default 0 and customers has a unique constraint on phone1 + customer_name.

Command for the complete test run:

```text
php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap tests/bootstrap_isolated.php tests/TestCase
```

The normal tests/bootstrap.php runs migrations. It was intentionally not used on the configured legacy database. All CRUD test writes used isolated SQLite. Real-database checks were read-only; no real users/customers/contacts were saved or deleted. No live DNC calls were made.

## Functional coverage

- Normal login: plain password matching, invalid credentials, successful Customers redirect.
- Home/SSO: password rules, role redirect, session identity and lead context, invalid/missing/deleted users.
- Ameyo: missing phone, exact phone match, latest-created match, no-match branches, no implicit creation, direct-ID navigation across deleted/belong records.
- Customers: Add/Edit/View/soft Delete, validation, duplicate matching, list search/pagination, DB-default belong/client code behavior.
- Contacts: Add/Edit/View/soft Delete, immutable customer linkage, inquiry validation, dropdown/text/date prefill, multi-checkbox HTML and bracket-string persistence, totals, redirects, selected-record versus undeleted history behavior.
- DNC: request method/endpoint/nested query/lead ID/status-name mapping, Add-before-save and Edit-after-success ordering, invalid response/outage handling, no calls during GET/navigation/Delete.
- Import: CSV/workbook upload, gender/decade conversions, existing-customer update, deleted/out-of-scope customer update without restore, failed row reports, contact import paths.
- CSV: original Customer/Contact headings compared against active legacy source; 21/38 columns, SJIS-win, filenames, label mappings, formatting, old operators, left joins, and independent export filtering.
- Settings: five master CRUD flows, validation, active flags, protected product price edit, sort validation and ranks, routes/templates/permissions, phone lookup/export and templates.
- Users: Add/Edit form layout/fields/buttons, required name/auth, optional credentials, plain password save, trusted audit fields, preserved Edit timestamp, failure form behavior, missing callback-table regression.
- Framework: application bootstrap, protected Pages redirect, authenticated rendering, missing templates, directory traversal and CSRF tests.

The four originally failing Pages tests assumed public access, but Pages is protected by Authentication. They were updated to authenticate for page-rendering tests; a separate unauthenticated redirect test was added. The HTML assertion now allows the actual html element's attributes. Application behavior was not changed to satisfy those tests.

## Remaining differences from the legacy application

These are actual source differences, not a claim that these areas failed their current-behavior tests.

1. **User Edit password prefill remains different.** `templates/Users/edit.php:34` explicitly uses an empty password value. Legacy Edit binds the stored password into a password-type input, displaying dots. The current field is blank, and submitting it blank saves a blank password. This is the most visible unresolved form difference.
2. **Contacts list still has additional parent/scope filters.** `LegacyCrm::search()` excludes deleted parent customers and restricts belong. Legacy Contacts list does not apply those parent filters. Contact View/Edit/Delete and customer-parent lookup have already been separated from them.
3. **Contact import still uses scoped/undeleted lookups.** Customer import restrictions were removed as requested; contact import's parent/record lookups still use LegacyCrm::search(). The legacy active importer imports customers; the new contact importer has no fully equivalent active legacy flow.
4. **Customer import audit and execution differ.** Legacy bulk ON DUPLICATE KEY UPDATE uses audit user ID 0 and database unique constraints. New import validates/saves per row and records the actual operator ID. Name+phone matching corresponds to the unique columns present in the current DB, but bulk execution, validation failures, and performance are not identical.
5. **Settings phone export query differs.** Old matching is a shared phones-table LEFT JOIN with customers.deleted_user_id IS NULL. New lookup does not mutate a shared staging table, uses customer.deleted plus belong filtering, and preserves upload rows in memory. These can produce different rows if deletion flags disagree or scope differs.
6. **Customer validation/normalization has extra behavior.** New validation checks numeric phone1 and nonnegative age/dob/nendai. Legacy Customer rules focus on nonempty fields and composite uniqueness. Phone normalization removes all nondigits and populates alias columns; this is not just saving submitted legacy form data.
7. **Contact calculation/normalization differs.** The numeric/date validation rules were aligned, but new server normalization still derives missing prices/totals and normalizes date separators. The old controller primarily saves submitted values after checkbox serialization.
8. **Master/operator choices differ in some screens.** New generic correspondence options do not reproduce Customer MtCorrespond.belong_div scoping. New generic operator options do not filter deleted users/order by disp_cd the same way. Missing/inactive/deleted master options can prevent some historical values from appearing as selectable options; the normal active-option prefill path was tested.
9. **Settings master implementation is not identical.** New View/Edit/Delete exclude already-deleted masters; old direct-ID actions do not. New Add generates max(disp_cd)+1, trims names, validates active values/product amounts, and writes consistent timestamps. Sorting validates a complete unique ID set, stages ranks for unique constraints, and updates audit fields; old sort directly writes submitted ranks. Failed Edit retains submitted values in the new implementation, while old actions commonly reload database values. Product unit_price is immutable in the new controller; the old UI omits its edit input, but the old controller could accept a crafted posted value.
10. **Users list/View/Delete and deleted-user login differ.** New Users list filters deleted records and orders by updated/created; old list paginates 200 in ID order. New User View filters deleted records. Old delete writes deleted_at; new delete writes deleted. Authentication now rejects deleted users explicitly. Add/Edit were aligned separately.
11. **Pagination differs intentionally.** SimplePaginator has no total count/last-page links, preventing the previously observed large-table timeouts. New queries have ID tie-breakers absent from some legacy ordering.
12. **Access/security and failure handling differ intentionally.** New backend permission/method checks, CSRF, safe escaping, protected identity/audit fields, and DNC transport-error recovery remain. Legacy controller-level forced HTTPS is not reproduced. Logout redirects directly to login rather than root. The legacy unsafe direct GET mutation paths were not restored.
13. **Lead context empty-value behavior differs.** New Ameyo/Index/View persist session lead_name differently; explicit empty input clears context where old View can fall back to the session. String '0' differs under strict empty checks versus old empty().
14. **CSV has explicit compatibility adaptations.** Original headings/format/operators were restored, but nonexistent old aliases map to valid columns; embedded quotes receive valid CSV escaping rather than the old malformed output; backend export permissions remain. Several odd old export equality operators against %value% were deliberately preserved, so export filtering can differ from normal list search.

## Limits of this verification

- This is not a browser/pixel-perfect or manual mouse/keyboard interaction test. DOM/render tests do not prove every JS interaction or browser autofill behavior.
- No writes to the real MySQL database were used to verify save/delete/import. SQLite test success does not prove every MySQL schema/sql-mode edge case.
- No live SSO provider or DNC server transaction was exercised; request contracts/session rules were tested locally with mocks.
- The old CakePHP 2 runtime was not executed side by side. Comparisons use active legacy source and original CSV headings. Passing tests proves the tested current behavior, not complete parity.
- The review covers the CRM/authentication/five active Settings sections discussed in this conversation. Other legacy controllers such as MtArticles were not exhaustively migrated or certified by this run.

Application logic was not changed during this verification pass. Test expectations for authenticated Pages were corrected. See LEGACY_FLOW_REVIEW.md for the original comparison and subsequent fixes.
