# Legacy CRM flow comparison

Reviewed 2026-11-11 against the current workspace. This is a source-code comparison of Users authentication, Customers, Contacts, and their import/export paths. It is not an audit of every controller, Settings, every template, or deployed database contents. No production writes or external API calls were made. No behavior was changed during this review.

The application is not yet behaviorally identical to `app/`. Passing the current regression tests does not establish complete legacy parity.

## Differences that affect customer/contact workflows

1. **DNC integration restored after this review.**
   - Old: `app/Controller/ContactsController.php:393` calls `callDncAPI()` during Add before the contact save; line 469 calls it after successful Edit. The function at line 510 sends phone, fixed lead ID 15, and add/delete mode based on the active disposition's name. Order/DNC/SV-unconfirmed dispositions select add; other dispositions select delete. API response text is included in some flash messages.
   - Follow-up: `src/Controller/ContactsController.php` now resolves the parent phone and active/undeleted disposition name and calls `src/Service/AmeyoDnc.php` with lead ID 15. The shared save action calls it before Add save and after successful Edit save. Endpoint, nested GET query parameters, status-name mode mapping, and response flash messages follow the old implementation. The method is protected so it is not a standalone controller action.
   - Deliberate resilience difference: invalid API responses and transport failures are logged and return an empty message so the contact can still save. Debug logs do not include phone numbers. No live DNC calls were made during implementation; requests were verified with mocks.

2. **Resolved: edit/delete/contact-parent lookups now use direct IDs.**
   - Old: Customer Edit/Delete retrieve by ID without a belong/deleted lookup filter (`app/Controller/CustomersController.php:325`, `:375`). Contact Add reads its parent customer by ID (`app/Controller/ContactsController.php:384`).
   - Follow-up: `legacyForm()` retrieves existing Customers/Contacts directly by ID and retrieves contact parents directly by customer ID. `legacyDelete()` also retrieves by ID. Belong/deleted lookup restrictions no longer block these operations, including edits of soft-deleted records. Editing does not clear the deleted flag. Method/admin checks on Delete remain; validation and parent existence checks remain. List/import query filters are unchanged.

3. **Contacts list still has extra filters; View was subsequently aligned.**
   - Old: Contacts list filters its own deleted flag; no customer-belong filter is present (`app/Controller/ContactsController.php:28`). Contact View/Edit look up the requested contact by ID (`:320`, `:430`).
   - New: `src/Service/LegacyCrm.php:138` excludes deleted contacts and deleted parent customers; line 144 filters parent belong when the user has a non-null belong. These filters remain on Contacts list. View now uses direct ID lookup with Customers loaded, without belong/deleted filters. Its sibling-contact history directly filters Contacts.customer_id and Contacts.deleted IS NULL, even when the parent customer is deleted. Edit/Delete also use direct ID lookups, as recorded above.
   - Effect: some contacts available in the old application are hidden or cannot be opened in the new one.

4. **Resolved: Contact Edit success redirect follows the legacy referrer rule.**
   - Old: `app/Controller/ContactsController.php:481` checks the referrer: a view-origin edit returns to Customers/View; otherwise it returns to Contacts/Index.
   - Follow-up: after successful Contact Edit, the shared action checks whether the local referrer contains `view`, returning to Customers/View in that case and Contacts/Index otherwise. Contact Add continues to return to Customers/View. Missing referrer returns to Contacts/Index. DNC call timing is unchanged.

5. **Resolved: Contact legacy validation now follows the original inquiry rules.**
   - Old: `app/Model/Contact.php:9` defines not-empty validation on submitted inquiry fields including contact_company, contact_tantou, date_want, category_cd, status_cd, correspond_cd. These fields are not explicitly required to be present, so an omitted field and a submitted empty field can behave differently.
   - Follow-up: `src/Model/Table/ContactsTable.php::validationLegacy()` now checks these six inquiry fields only when submitted, rejecting blank/null/whitespace values with the original Japanese messages. Omitted fields remain optional on create/update. The extra customer_id/kekka-required, callback datetime, and numeric/nonnegative product validation rules were removed from this validation set. The controller still requires an existing parent customer, role restrictions still apply, and database types/marshalling may reject values independently of model validation. Product calculations/date normalization remain separate differences below.

6. **Automatic belong/client_cd assignments removed; alias normalization remains.**
   - Old: `app/Controller/CustomersController.php:304` adds audit fields to form data, without assigning belong or generating client_cd there.
   - Follow-up: Customer Add and customer import no longer generate client_cd or copy the current user's belong. These columns are omitted on insert so database defaults apply. Existing values are preserved during edits. The independent jump/autoAdd path was outside this change.
   - Remaining difference: `src/Service/LegacyCrm.php:65` normalizes phone digits and copies legacy values into modern alias columns (name, phone, address aliases).

7. **Correspondence dropdown scoping is not ported.**
   - Old: Customer `setListValue()` filters MtCorrespond.belong_div by user belong (`app/Controller/CustomersController.php:463`). Contacts has its correspondence-loading block commented out; do not treat that commented code as active behavior.
   - New: `src/Controller/LegacyCrmActionsTrait.php:220` loads mt_corresponds using generic deleted/active rules without belong_div conditions.
   - Effect: Customer correspondence choices can include options excluded by the old application.

8. **User option queries differ.**
   - Old: dropdown users are undeleted and ordered by disp_cd (`app/Controller/CustomersController.php:499` and `app/Controller/ContactsController.php:670`).
   - New: `src/Controller/LegacyCrmActionsTrait.php:272` loads user names without that filter/order.
   - Effect: deleted users can appear in operator options and ordering can change.

## Import, export, and list behavior

9. **CSV download restored to the active legacy format and export query rules.**
   - Old: Customers/Contacts download branches construct separate queries and Japanese headings; legacy download templates use SJIS-win output. Those queries do not consistently reproduce the screen's belong/deleted filters. Contacts export contains several inconsistent equality/LIKE conditions and field aliases.
   - Follow-up: `src/Service/LegacyCrmCsvExport.php` now produces SJIS-win without BOM, the original 21 Customer / 38 Contact headings and column order, Japanese filenames with the original timestamp format, and the original Excel MIME type. Data fields are quoted, headings are unquoted, and lines end in LF. Contact result/reason/product/family labels, first-product code/name split, fixed サンプル status, and memo newline-to-& conversion follow the old template. Customer audit names exclude deleted users. Buffered ORM result caching is disabled and CSV output uses a temporary stream.
   - Export queries use the submitted form filters, not saved search/query-string state. They apply no implicit belong/deleted constraints and Contacts uses the old LEFT customer join. Customer filters use literal substring LIKE rather than Search-plugin AND/OR splitting. Contact operators retain legacy equality against literal surrounding percent signs for phone1/address1/address2/address4/AP name, exact age/result/product/reason/AP ID/total_price comparisons, and legacy created/updated date bounds (to-only ignored). Other legacy-unhandled fields remain ignored.
   - Compatibility adaptations: old Contact aliases for customer fields fall back to actual Customers columns when absent. Missing old product, total_price_from, and sakiokuri_date_from columns map to product_name1, total_price, and sakiokuri_datetime, retaining the original comparison operator instead of producing unknown-column SQL errors. Parameterized conditions replace raw date SQL. Commas/embedded quotes are escaped as valid CSV; the old helper did not escape embedded quotes. Missing labels/parents produce empty cells rather than PHP warnings. The existing auth-9 backend export check remains.

10. **Customer import conversions restored; persistence still differs.**
    - Old: `app/Controller/ImportsController.php` converts Japanese male/female labels to 1/2 and decades 20..80 to 2..8; audit user IDs are 0. `app/Model/Import.php:43` uses bulk INSERT/ON DUPLICATE KEY UPDATE, relying on database unique keys.
    - Follow-up: `src/Service/LegacyCrmImporter.php` now converts customer gender 男/女 to 1/2 and decades 20..80 to 2..8 before normalization. Blank, zero, unknown labels/decades, and already-internal codes map to null, as in the original import. Only supplied customer columns are converted; Contacts import and customer form inputs are unaffected.
    - Customer-import follow-up: matching deleted/out-of-scope customers can now be updated. Updates preserve deleted/deleted_user_id, belong, client_cd, and created audit fields, so this does not restore a deleted customer. Contact import's separate scope/parent checks remain.
    - Remaining differences: the new importer explicitly matches customer_name + phone1, validates each row, and uses the current operator for audit fields. It omits belong/client_cd on insert, allowing database defaults. Duplicate/update behavior and performance still depend on a different persistence method. Contacts import is a new supported path, not a direct copy of the active legacy customer importer.

11. **Pagination deliberately avoids total counts.**
    - Old: full paginator, 20 rows, total count/page links.
    - New: `src/Controller/LegacyCrmActionsTrait.php:53` uses SimplePaginator, 20 rows, previous/next navigation without total count. An ID descending tie-breaker is added to updated/created ordering.
    - Effect: page navigation differs. This was introduced to prevent large-table request timeouts; it is an intentional performance change.

12. **Contact totals have server-side normalization.**
    - Old: the controller serializes checkbox values and saves posted form values; price calculations are primarily in the UI.
    - New: `src/Service/LegacyCrm.php:95` additionally derives missing line prices/total_price and normalizes dates. Explicit posted line prices are preserved. Extra numeric/date model validation was subsequently removed as recorded above.
    - Effect: partial submissions/imports can produce calculated values rather than storing exactly the submitted fields.

## Authentication, lead handling, and access behavior

13. **Deleted-user login is excluded in the new application.**
    - Old: `app/Controller/AppController.php:36` configures Form authentication without a deleted-user scope.
    - New: login uses UsersTable.findAuth (`src/Model/Table/UsersTable.php:141`); Home/SSO explicitly filter Users.deleted.
    - Effect: deleted users that could authenticate under the old configuration are rejected now.

14. **User maintenance is not identical.**
    - Old: Users index orders by ID ascending and paginates at 200; User deletion writes deleted_at; User model requires auth and user_name.
    - New: index lists undeleted users ordered by updated/created with no pagination; deletion writes deleted.
    - Edit follow-up: User Edit now uses direct ID lookup, original six-field orange form and only Update, with existing login ID prefilled. Its separate legacyEdit validator requires auth/user_name, leaves disp_cd/loginid/password optional, and uses original messages. POST/PUT updates only displayed fields and trusted updated_user_id, preserving updated/created/deleted metadata. Success returns to Users/Index; failure reloads stored values with validation errors. Plain-password behavior remains, including saving a submitted blank password as blank. Extra Cancel/Delete controls and audit display were removed from Edit. Modern route IDs, CSRF, protected identity fields, and escaping remain.
    - Effect: list order/visibility, validation, and deletion columns differ. No database schema/data reconciliation was attempted in this review.
    - Add follow-up: Add now uses the original blue form, field order, labels, and sole 登　録 button. Add/Edit share `validationLegacy()` (required auth/user_name, other displayed fields optional). Add preserves original blank login/password controls, sets trusted created/updated audit fields, saves plain passwords, returns to Users/Index on success, and retains failed form values/errors with the original messages. The Edit validator name was consolidated from legacyEdit to legacy. Both pages now use the legacy layout styling. No real user records were modified during these tests.

15. **Lead session/empty-value behavior differs.**
    - Old: SSO writes lead_name; Ameyo sets view variables without writing it to session. Customer View uses session fallback when the argument is empty (`app/Controller/CustomersController.php:275`).
    - New: Ameyo writes lead_name (missing becomes empty); Customers Index/View persist any non-null argument. Explicit empty View arguments clear session rather than fall back. Strict empty checks treat string '0' differently from PHP empty() in the old Ameyo.
    - Effect: stale/current list context and the unusual value '0' can behave differently. Ordinary nonempty lead values still populate Contact.list_name.

16. **Delete authorization and request-method checks differ.**
    - Old: Customer/Contact delete actions soft-delete by ID; admin-only controls exist in the UI, but these actions do not themselves enforce auth 9 or POST-only access.
    - New: `src/Controller/LegacyCrmActionsTrait.php:194` checks the method and auth 9 in the action, in addition to lookup filters.
    - Effect: non-admin direct requests and GET delete URLs no longer work.

17. **Framework security/session handling differs.**
    - Old: AppController requires HTTPS and disables CSRF/post validation. Logout redirects to root.
    - New: CSRF middleware is enabled, no equivalent controller-level forced-HTTPS redirect exists, Authentication manages identity persistence, and logout redirects directly to Users/Login.
    - Effect: external POST callers need valid CSRF handling, HTTP behavior differs, and the logout redirect path differs.

## Matching main paths after the recent changes

- Password storage/comparison is plain, matching NonePasswordHasher. No automatic rehash is performed.
- Normal successful form login redirects to Customers/Index.
- Home uses the original per-user password mapping; auth 9 goes to Ameyo and other roles to Users/Blank.
- SSO uses the actual fixed-password behavior in the old source, saves lead context, and redirects to Ameyo for all roles.
- Ameyo uses exact phone1 matching and latest-created customer; no phone goes to Index, no match with blank lead goes to Add, no match with a lead goes to Index. No implicit customer insert occurs. New code logs a generic error without phone/lead values and resolves same-created ties by ID.
- Customer View and Ameyo now query without belong/customer-deleted filters. History still excludes deleted contacts.
- Contact list-name display/storage uses lead_name as the default for contacts.list_name.

## Verification boundary

The original review used the isolated SQLite controller suite. The later full verification passed 90 tests / 1,287 assertions, linted 205 PHP files, and performed read-only real-DB render/schema checks including Settings and Users. See [VERIFICATION_20261111.md](VERIFICATION_20261111.md) for current evidence, remaining differences, and boundaries. No live DNC transaction, real-DB mutation test, or browser/pixel comparison was performed. This is not a comparative run of the CakePHP 2 runtime.
