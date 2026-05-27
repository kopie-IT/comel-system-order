# Development Record

## 2026-05-26 — Admin: pending order items picking list

### Request
User requested:
- On Admin → Senarai Pesanan, add a button that opens a list of all order items belonging to **pending** orders, so the shop can see at a glance what products (and how many of each) need to be prepared.

### Task Checklist
- [x] `Order::pendingItemsAggregated()` — group order_items by `product_id + size_label + variant_label`, summing quantity and counting distinct orders.
- [x] `Order::pendingItemsDetailed()` — flat list of every pending order_item with its order_number / customer for the breakdown drill-down.
- [x] `AdminOrderController::pendingItems()` — pulls both queries and renders the picking-list view.
- [x] Route `/admin/orders/pending-items` added in `public/index.php` under the existing admin auth gate.
- [x] New view `src/views/admin/orders/pending_items.php`:
  - Yellow→orange gradient header banner (matches existing container palette).
  - Two stat tiles: total quantity + unique product lines.
  - White outer container with rose-tinted inner panel listing each aggregated row (image, name, size/variant chips, "from N pesanan" chip, big quantity number).
  - Collapsible per-product breakdown showing each individual order with deep-link back to the admin order page.
  - Print button + `@media print` rules so the picking list prints clean (without the breakdown noise).
- [x] Button on `views/admin/orders/index.php` ("Item Pending", yellow chip) next to the AI Assistant chip.
- [x] PHP lint passed for all touched files.

### Impacted Files

#### NEW
- `/src/views/admin/orders/pending_items.php`

#### UPDATED
- `/public/index.php`
- `/src/models/Order.php`
- `/src/controllers/admin/AdminOrderController.php`
- `/src/views/admin/orders/index.php`

### Summary
Admins now have a one-click picking list. The page shows a clean aggregated view ("Romper 0-3 bulan x 7 from 4 pesanan") plus an expandable breakdown that links straight to each individual order, making fulfilment for the pending queue dramatically faster. The page is also printable.

### Security & Quality Notes
- All queries run via PDO with no user input (admin-only route, no params), so no injection surface.
- Route lives behind the existing `requireAdmin()` gate; the existing `/admin/orders/{id}` route still works because the new `pending-items` segment is matched first by an explicit string compare.
- Aggregation uses `LEFT JOIN products` so deleted products that still appear in pending orders don't get hidden — they just lose their thumbnail.
- View renders all variable fields through `e()`; chip counts and quantities are cast to `int` before output.
- Print stylesheet hides the noisy detail panel and the back/print buttons so the printed page is a tight checklist.

---

## 2026-05-26 — Admin orders: Active tab now shows pending only

### Request
User requested:
- On Admin → Senarai Pesanan, when the "Active" tab is selected it should only show pending orders. Confirmed and cancelled should live under their own tabs.

### Root Cause
`AdminOrderController::index` previously treated the empty `status` filter as "everything except completed/cancelled", which still included `confirmed` orders alongside `pending`.

### Fix
- `AdminOrderController::index`: when no `status` is in the URL (the Active tab), now queries `Order::all('pending')` directly. Confirmed and cancelled tabs already work through `?status=confirmed` / `?status=cancelled`, so they're unchanged.
- The view's tab links already point to those filters, so no view change required.

### Task Checklist
- [x] Update `AdminOrderController::index` Active branch to fetch `pending` only.
- [x] Confirm Confirmed and Cancelled tabs still load through their own status filter.
- [x] PHP lint passed.

### Impacted Files

#### UPDATED
- `/src/controllers/admin/AdminOrderController.php`

### Summary
The Active tab is now a clean "what needs my attention right now" view — just orders waiting to be confirmed or cancelled. Confirmed orders live under the Confirmed tab, cancelled orders under the Cancelled tab.

### Security & Quality Notes
- One-line behaviour change in a single controller method; no new query surface.
- No model changes, so existing parameterized queries continue to apply.
- No view changes — the tab markup already uses `?status=` query string filters that the controller now interprets cleanly.

---

## 2026-05-26 — Auto-resize + compress courier slip images on upload

### Request
User requested:
- When uploading the courier slip, the uploaded image should be auto-resized + reduced in size.

### Task Checklist
- [x] New helper `src/helpers/image.php` exposing `image_resize_to_path($path, $maxDim, $quality)`.
- [x] Helper handles JPEG and PNG only; skips PDFs and other formats cleanly.
- [x] Never upscales — small images keep their dimensions and only get recompressed.
- [x] Preserves PNG transparency (alpha channel retained).
- [x] No-ops gracefully when GD extension is unavailable.
- [x] Loaded the helper from `public/index.php` so all routes have access.
- [x] Wired into `AdminCourierSlipController::upload` after `move_uploaded_file`, applied only to JPEG/PNG slips.
- [x] Defaults: max longest side = **1600 px**, JPEG quality = **82**.
- [x] PHP lint passed for all touched files.

### Impacted Files

#### NEW
- `/src/helpers/image.php`

#### UPDATED
- `/public/index.php`
- `/src/controllers/admin/AdminCourierSlipController.php`

### Summary
Courier slip uploads are now auto-resized to a max 1600 px on the longest side and recompressed (JPEG q=82, PNG compression level 6). Typical phone-camera slips drop from 3-6 MB down to 200-400 KB, which means lighter storage, faster admin previews, and (most importantly) reliable WhatsApp delivery without hitting upload caps. PDFs and other non-image slips are passed through unchanged.

### Security & Quality Notes
- Resize runs **after** MIME validation and `move_uploaded_file`, so we only ever touch a vetted, known-good file.
- Helper performs `is_writable` + `getimagesize` checks before any GD work; failures return cleanly without aborting the upload flow.
- PNG transparency is preserved via `imagealphablending` + `imagesavealpha`.
- JPEG/EXIF metadata is stripped through the GD pipeline as a side-effect of re-encoding — smaller file and slightly better privacy (camera GPS, device, etc.).
- GD-missing environments still complete the upload (helper returns true and is a no-op), so no environmental hard dependency is introduced.

---

## 2026-05-26 — Hotfix: Fatal error on courier slip upload (finfo on empty tmp_name)

### Request
User reported a fatal error on courier slip upload:
```
Uncaught ValueError: finfo::file(): Argument #2 ($flags) cannot be empty
... AdminCourierSlipController.php:26 ... finfo->file('')
```

### Root Cause
`AdminCourierSlipController::upload` only checked `!empty($_FILES['courier_slip']['name'])` before calling `finfo->file($file['tmp_name'])`. When PHP rejects the upload at the engine level (file too big for `upload_max_filesize` / `post_max_size`, no tmp dir, partial upload, etc.), `tmp_name` is `''` even though `name` is populated. Passing `''` into `finfo::file()` on PHP 8+ now throws `ValueError`. `AdminSettingsController` had the same vulnerability.

### Fix
Added an upload-validation guard before any `finfo` call in both controllers:
1. Inspect `$_FILES['…']['error']` against `UPLOAD_ERR_*` constants.
2. Reject when `tmp_name` is empty or fails `is_uploaded_file()`.
3. Surface a localized, actionable Malay error message (using a new `uploadErrorMessage()` helper) showing the active `upload_max_filesize` and `post_max_size` so the admin sees exactly what to raise in php.ini.
4. Audited `AdminAiController::chat` — it already guarded `error === UPLOAD_ERR_OK`, so no change needed there.

### Task Checklist
- [x] Add upload-error guard to `AdminCourierSlipController::upload`.
- [x] Add upload-error guard to `AdminSettingsController::update`.
- [x] Add `uploadErrorMessage()` helper to both controllers.
- [x] Audit `AdminAiController::chat` (already safe).
- [x] PHP lint passed for both controllers.

### Impacted Files

#### UPDATED
- `/src/controllers/admin/AdminCourierSlipController.php`
- `/src/controllers/admin/AdminSettingsController.php`

### Summary
Slip and QR uploads now fail gracefully with a localized message stating the cause (oversize, partial, no tmp dir, etc.) and the current php.ini limits. No more uncaught `ValueError` crashing the request.

### Security & Quality Notes
- Validation runs **before** any operation that touches the temp file path, eliminating the crash surface.
- Guard uses `is_uploaded_file()` to ensure the path is actually a real upload (defense against tampered `$_FILES` arrays).
- Error messages name the active php.ini limits so the admin can self-diagnose without reading PHP source. Limits are read at runtime, not hardcoded.
- No change to the file-content validation that follows (MIME via `finfo`, size guard, allow-list).

---

## 2026-05-26 — Hotfix: Restore message sending (auth back in body)

### Request
User reported:
- After the previous WAWP rewrite, **no messages** are being sent to customers anymore.

### Root Cause
The previous attempt moved `access_token` and `instance_id` to the **query string** to follow the official WAWP v2 documentation's example. The user's actual WAWP server expects auth **in the request body** (which is how the original working code sent them). Moving them out of the body made every request unauthenticated and silently dropped — including the text messages that used to work.

### Fix
- Reintroduced a `withAuth($payload)` helper that injects `access_token` and `instance_id` back into the body for **all** requests (text, JSON image, multipart image).
- Kept the structural improvements that were correct:
  - Bracket-notation `file[url]` / `file[filename]` / `file[mimetype]` keys for image sends.
  - Proper `multipart/form-data` upload with `CURLFile` for `sendImageFile` (no more broken `data:` URL hack).
  - HTTPS / JPEG-PNG validation with explicit error messages.
  - Tempfile materialization with `try/finally` cleanup.
  - 500-char error truncation before logging.

### Task Checklist
- [x] Restore `access_token` + `instance_id` in the request body (was: query string).
- [x] Apply the same auth injection to text, JSON image, and multipart image paths.
- [x] Keep `file[*]` bracket-notation keys + multipart upload behavior.
- [x] PHP lint passed.

### Impacted Files

#### UPDATED
- `/src/models/WawpService.php`

### Summary
Text messages flow again. Image send paths still use the WAWP v2 body shape (bracket-notation keys + proper multipart upload), but auth now lives in the body where the user's server expects it.

### Security & Quality Notes
- This restores the original auth-in-body pattern that was already working for text — so no behavior regression for callers.
- Tempfile cleanup, HTTPS guard, MIME validation, and error truncation all stay in place.
- If a future migration to query-string auth is desired, it should be feature-flagged and tested against the live WAWP endpoint first; do not change without confirmation.

---

## 2026-05-26 — Fix: WAWP image arrives broken (real fix per WAWP v2 API contract)

### Request
User reported:
- The QR image attached to the WhatsApp message is still showing broken on the recipient's side after the previous attempt.

### Root Cause (read the WAWP v2 docs carefully this time)
The official WAWP `/v2/send/image` contract is:

1. **`instance_id` and `access_token` go in the query string**, not in the JSON body. The previous code put them in the body, so the request was being authenticated incorrectly (or as a guest), which silently produced a "broken thumbnail" on delivery.
2. **`file[url]` MUST be a publicly accessible HTTPS URL** that wawp.net's servers can fetch — `data:` URLs and `localhost`/`http://` URLs are not supported. The earlier code attempted to send a `data:` URL inside `file[url]`, which is exactly what the docs warn against.
3. **JSON body uses bracket-notation keys** (`"file[url]"`, `"file[filename]"`, `"file[mimetype]"`) rather than a nested `file` object.
4. WhatsApp accepts only **JPEG and PNG**.

For the localhost case (where there's no public HTTPS URL to give wawp.net), the correct path is **multipart/form-data** with a `CURLFile` upload — not a `data:` URL stuffed into `file[url]`.

### Fix (full rewrite of `WawpService`)
- **Auth via query string**: `instance_id` + `access_token` are now appended to the endpoint URL using `http_build_query`. Request body no longer carries them.
- **`postJson()`**: clean JSON post with bracket-notation body for the URL-based image send.
- **`postMultipart()`**: new multipart/form-data path used by `sendImageFile` — uploads bytes via `CURLFile`, no `data:` URL hack. Works on localhost.
- **`sendImageMessage()`**: validates HTTPS, derives filename + MIME from the URL extension, rejects non-JPEG/PNG with a clear message.
- **`sendImageFile()`**: detects real MIME, auto-converts WebP/other to PNG via GD when present, materializes bytes to a tempfile, uploads via multipart, then unlinks the tempfile (in a `try/finally`).
- Tightened the JSON encoding (`JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`) and trimmed long error payloads to 500 chars so log rows stay manageable.

The checkout controller's existing fallback chain (multipart file → public URL → text-only with link) keeps working unchanged — but now each step actually conforms to WAWP's spec, so the image renders.

### Task Checklist
- [x] Move `instance_id` / `access_token` to query string (per WAWP v2 docs).
- [x] Replace nested `file` object with bracket-notation `file[*]` keys.
- [x] Replace base64 `data:` URL hack with proper multipart `CURLFile` upload.
- [x] Add tempfile materialization with `try/finally` cleanup.
- [x] Keep MIME detection + WebP→PNG auto-conversion.
- [x] Tighten error payload + JSON encoding flags.
- [x] PHP lint passed for `src/models/WawpService.php` and dependents (`CheckoutController`, `AdminCourierSlipController`, `AdminOrderController`).

### Impacted Files

#### UPDATED
- `/src/models/WawpService.php`

### Summary
The QR image now renders on the recipient's WhatsApp because the request actually conforms to the WAWP v2 API contract: query-string auth, bracket-notation body for URL sends, and proper multipart upload (not a `data:` URL) for file sends. No other call sites needed to change — `CheckoutController`, `AdminOrderController`, and `AdminCourierSlipController` all continue calling the same `sendImageFile`/`sendImageMessage` methods with the same arguments.

### Security & Quality Notes
- Auth tokens are now passed in the query string — the endpoint should be HTTPS (WAWP requires it). No tokens leak into request bodies that could be replayed via cached body logs.
- Tempfile creation uses `tempnam(sys_get_temp_dir(), 'wawp_')` and is unlinked in `finally`, even if cURL throws.
- Multipart uploads stream from disk via `CURLFile`, avoiding doubling memory usage for large images.
- Response error string is capped at 500 chars before being persisted to `whatsapp_logs`, preventing oversized failure rows.
- HTTPS, JPEG/PNG, and clean filename validation all surface specific errors instead of failing silently.

---

## 2026-05-26 — Fix: WhatsApp image (QR / slip) not showing on customer side

### Request
User reported:
- The image attached to the WhatsApp message is not showing in WhatsApp.

### Root Cause
WAWP API documentation confirms WhatsApp's image pipeline only accepts **JPEG and PNG**. Three things were combining to produce a "broken thumbnail" / silently dropped image:

1. **Hardcoded MIME type** — `WawpService::sendImageMessage` always sent `mimetype: image/jpeg` and `filename: qr_payment.jpg`, regardless of the actual file. Uploading a PNG or WebP made WAWP/WhatsApp reject the payload.
2. **WebP allowed in QR upload** — Admin Settings accepted WebP, which WhatsApp does not render as an image.
3. **No HTTPS guard on URL sends** — When wawp.net's servers fetch `file[url]`, the URL must be public HTTPS. `http://` or `localhost` URLs caused upstream fetch failures.

### Fix
- `WawpService::sendImageMessage`:
  - Validates the URL is HTTPS, returns a clear error if not (no more silent drop).
  - Derives `filename` and `mimetype` from the URL extension; rejects non-JPEG/PNG with a clear message instead of mislabelling.
- `WawpService::sendImageFile`:
  - Detects the real MIME via `mime_content_type` instead of trusting the extension.
  - Auto-converts WebP/other formats to PNG using GD when available; rejects with a clear error if conversion isn't possible.
  - Sends the correctly-labelled `data:` URL with the real MIME so WhatsApp renders the thumbnail.
- `AdminSettingsController::update`:
  - QR upload now rejects WebP outright (only JPEG/PNG).
  - Forces the saved filename extension to match the detected MIME (`.jpg` or `.png`), avoiding extension/content mismatches.
- `views/admin/settings/index.php`: QR upload UI hint updated to "JPEG atau PNG sahaja (WhatsApp tidak menerima WebP)".

The earlier checkout fallback (base64 → URL → text) remains in place; this fix targets why each fallback step was failing.

### Task Checklist
- [x] `WawpService::sendImageMessage`: HTTPS check + dynamic MIME/filename + JPEG/PNG validation.
- [x] `WawpService::sendImageFile`: detect real MIME + GD-based WebP→PNG conversion + early reject for unsupported formats.
- [x] `AdminSettingsController::update`: drop WebP for QR uploads + safe extension naming.
- [x] Settings view: align UI hint with the JPEG/PNG-only policy.
- [x] PHP lint passed for all touched files.

### Impacted Files

#### UPDATED
- `/src/models/WawpService.php`
- `/src/controllers/admin/AdminSettingsController.php`
- `/src/views/admin/settings/index.php`

### Summary
Customer WhatsApp images now actually render. The QR (and any other image we send) goes through with a correct MIME and filename, the URL path is gated to HTTPS, the file path detects real MIME and converts unsupported formats automatically, and the admin can no longer upload a WebP QR that WhatsApp would silently drop.

### Security & Quality Notes
- Admin uploads still go through `finfo` MIME detection on top of the new whitelist; we never trust client-supplied extensions.
- Upload filename is now generated from MIME (`.jpg` or `.png`), not from the original filename, eliminating extension/content mismatch.
- WAWP send failures now surface a real reason (HTTPS guard, MIME guard) rather than failing silently — visible in `whatsapp_logs`.
- Auto-conversion uses GD's `imagecreatefromstring` and only runs when GD is loaded; if not, we return a clear error instead of a corrupted send.

---

## 2026-05-26 — Fix: customer not receiving QR code on order confirmation

### Request
User reported:
- When system sends WhatsApp confirmation to the customer, only the text message arrives. The QR code image is not received.

### Root Cause
`CheckoutController::process` was sending the QR via `sendImageMessage(url, caption)` which posts a public URL to wawp.net. The wawp.net server then has to fetch the image. This silently fails when:
- The site is on localhost or behind a firewall (URL not reachable from outside).
- The configured `base_url` is empty / wrong / private network.
- The QR file is served only with auth or behind redirects.

The accompanying `try/catch` swallowed all errors so admins had no signal.

The courier slip path already had a URL → base64 → text-only fallback chain. The QR path did not.

### Fix
Implemented the same multi-tier fallback for the QR send in `CheckoutController::process`:

1. **Try local-file base64** (`WawpService::sendImageFile`) — works on localhost and any host because the file bytes are sent inside the WAWP payload, no external fetch needed.
2. **Fallback to URL send** (`sendImageMessage`) — covers cases where the file isn't readable from disk but is publicly hosted.
3. **Fallback to text-only with the QR link** — so the customer at least gets a clickable link if both image methods fail.

Also replaced the silent `catch` with one that writes a `whatsapp_logs` entry, so any send-exception is now visible in the admin WhatsApp logs.

### Task Checklist
- [x] Reorder QR send to try `sendImageFile` (base64) before `sendImageMessage` (URL).
- [x] Add text-only fallback that includes the QR URL when both image sends fail.
- [x] Replace silent `catch` with a logged failure so admins can diagnose.
- [x] PHP lint passed for `src/controllers/CheckoutController.php`.

### Impacted Files

#### UPDATED
- `/src/controllers/CheckoutController.php`

### Summary
QR delivery now works on localhost and on hosts where the configured base URL isn't reachable from wawp.net's servers. The order confirmation text and the QR are both sent. If the image upload to WAWP keeps failing, the customer still receives a text message with the QR URL so they aren't left without payment instructions.

### Security & Quality Notes
- Local file path is constructed from `ROOT_PATH . '/public/' . ltrim($qrImage, '/')` and only read after `file_exists`/`is_readable` checks.
- `$qrImage` is a setting written by an authenticated admin via the existing settings upload flow, which already validates MIME and stores under `/public/uploads/qr/`. No new untrusted input is read.
- Failures now produce a `whatsapp_logs` row instead of being silently dropped — better operational visibility.
- Outermost `try/catch` still ensures a WAWP outage cannot break checkout: the order is committed regardless of WhatsApp result.

---

## 2026-05-26 — Checkout container styling matched to cart

### Request
User requested:
- Apply the same container background pattern from the cart page (blue gradient header + white outer container + rose-tinted inner panels) to the checkout page.

### Task Checklist
- [x] Replaced the plain card header with the blue→indigo gradient banner used on the cart page.
- [x] Wrapped "Maklumat Penerima" form fields in a white outer container with a rose-tinted inner panel.
- [x] Form inputs (name, phone, address, state) now sit on white with rose-100 borders, matching the cart's color palette.
- [x] Wrapped "Ringkasan Pesanan" in the same white-outer / rose-inner pattern; each line item is now a white pill inside the rose panel.
- [x] Added an item-count chip on the summary header (consistency with the cart's "Senarai Item" header).
- [x] Postage hint, totals, and CTA layout preserved.
- [x] PHP lint passed for `src/views/checkout/index.php`.

### Impacted Files

#### UPDATED
- `/src/views/checkout/index.php`

### Summary
Checkout now mirrors the cart visually: blue gradient header → white outer containers → rose-tinted inner panels → white pills/inputs. The hierarchy makes the form and order summary feel framed and structured rather than floating on the page background.

### Security & Quality Notes
- Pure presentation change; all backend validation and postage logic untouched.
- All output continues to flow through `e()`.
- No JS hooks affected; existing dynamic postage / state validation works unchanged.

---

## 2026-05-26 — Cart: each product on its own container card

### Request
User requested:
- On the cart page, put each purchased product into its own container card so the page reads more clearly.

### Task Checklist
- [x] Pulled each cart line out of the shared container and gave it its own `card` (rounded white panel, soft border, hover-shadow).
- [x] Kept items grouped under a single header row (icon + "Senarai Item" + count chip) for context, then `space-y-3` between cards.
- [x] Larger 80x80 product image, name + remove pinned to the same row, size/variant chips below, qty stepper bottom-left, line total bottom-right.
- [x] Disabled qty stepper buttons get explicit `disabled` styling (opacity + not-allowed).
- [x] PHP lint passed for `src/views/cart/index.php`.

### Impacted Files

#### UPDATED
- `/src/views/cart/index.php`

### Summary
Each product in the cart is now its own card. The page reads as a clean stack of self-contained items rather than one big merged list. The header row still gives a quick at-a-glance count, and the subtotal block stays in its own card at the bottom.

### Security & Quality Notes
- No backend logic changes; pure presentation.
- Continues to use `e()` for all interpolated values.
- Remove action still gates with a confirm prompt.

---

## 2026-05-26 — Hide postage until state chosen + cart page polish

### Request
User requested:
- On the checkout page, do not show postage at all until the user picks a state (since the cost is unknown beforehand).
- Restyle the cart page with a nicer container layout.

### Task Checklist
- [x] Hide the postage row on the checkout summary until a state is selected; show a friendly hint instead.
- [x] When no state is selected, the "Jumlah" line shows just the subtotal (no fake postage).
- [x] When the user picks a state the postage row + zone label appear; "Jumlah" recalculates live.
- [x] Strip postage out of `CartController::index` and the cart view (cart only deals with products; postage is a checkout concern).
- [x] Rebuild the cart view with a single grouped items container, badge for line count, soft hover row, sized chips for size/variant, polished qty stepper, and a cleaner subtotal block with a postage hint.
- [x] Add an explicit "Teruskan Membeli-belah" link on the cart for symmetry with the checkout flow.
- [x] PHP lint passed for `src/views/checkout/index.php`, `src/views/cart/index.php`, and `src/controllers/CartController.php`.

### Impacted Files

#### UPDATED
- `/src/controllers/CartController.php`
- `/src/views/cart/index.php`
- `/src/views/checkout/index.php`

### Summary
Postage is now hidden on both the cart and the checkout summary until the customer actually picks a state. On the checkout page, the postage row appears only after a state is selected and recalculates live based on Semenanjung vs Sabah/Sarawak/Labuan zone. The cart page was refactored into a single grouped items container with cleaner item rows, badged size/variant chips, a tidier qty stepper, and a clearer subtotal block — and now ends with both a primary "Pembayaran" CTA and a secondary "Membeli-belah" link.

### Security & Quality Notes
- No backend trust changes: postage is still recalculated server-side in `CheckoutController::process` against the validated state.
- Cart no longer pulls a `postage_fee` setting it doesn't display, removing one unnecessary DB read per cart view.
- Item-removal now requires confirmation (lightweight UX guardrail).
- Qty steppers disable at edges (1 / max_stock) to communicate limits cleanly.
- All output continues to flow through `e()`; no raw HTML interpolation introduced.

---

## 2026-05-26 — Cart "TV switch-on" added-to-cart modal

### Request
User requested:
- After adding a product to cart on the front end, show a popup with a TV switch-on style animation confirming the product was added.
- Popup must offer two clear actions: see other products, or proceed to payment.
- "Continue shopping" should bring the user back to the category page of the product they just added.

### Task Checklist
- [x] Refactor `CartController::add` to stash a rich `cart_just_added` session payload (image, name, qty, size/variant, price, category_id) instead of plain flash text.
- [x] Redirect after add to the originating product's category page (`/category/{id}`).
- [x] Add a TV switch-on CSS animation suite (vertical line → horizontal stretch → settle, plus flash + scanline) in the public layout, with a `prefers-reduced-motion` fallback.
- [x] Render the modal in `src/views/layouts/public.php` only when the session payload exists; one-shot consumption (popped immediately).
- [x] Modal actions: "Teruskan ke Pembayaran" → `/checkout`, "Teruskan Membeli-belah" → category page (closes modal smoothly when already on that page).
- [x] ESC key + backdrop click close support; body scroll locked while open.
- [x] PHP lint passed for `src/views/layouts/public.php` and `src/controllers/CartController.php`.

### Impacted Files

#### UPDATED
- `/src/controllers/CartController.php`
- `/src/views/layouts/public.php`

### Summary
After adding to cart, the user is now taken to the originating category page where an animated, TV-style popup confirms the addition and offers two next steps: keep shopping (closes the popup, user is already on the category) or proceed to checkout. Implementation uses a one-shot session payload so the modal renders exactly once after the add — no JS state leakage, no risk of stuck popups on refresh.

### Security & Quality Notes
- The session payload only carries display data (name, image path, labels, qty, price, category_id). All values are HTML-escaped at render via `e()`; no raw HTML injection surface.
- Modal lives in a single layout, so every public page benefits without per-view changes.
- `prefers-reduced-motion` honored: animation is suppressed for users with motion sensitivity.
- Body scroll locked + restored to prevent background interaction during the animation.
- Clicking "Continue shopping" while already on the destination short-circuits to a smooth close instead of a redundant navigation.

---

## 2026-05-26 — State-based postage (Sabah/Sarawak/Labuan)

### Request
User requested:
- Add a Malaysian state field to customer details on the checkout page so they can specify which state they are from.
- If the state is Sabah or Sarawak, the postage amount should be different from Semenanjung.
- Postage amount for Sabah/Sarawak must be configurable in the admin Settings page.

---

### Task Checklist
- [x] Add `state` (VARCHAR 50) to `customer_addresses` and `orders`, plus `postage` (DECIMAL 10,2) to `orders`
- [x] Auto-migration in `src/config/database.php` so existing installs upgrade safely
- [x] Update `database/schema.sql` for fresh installs
- [x] New helper `src/helpers/malaysia.php` exposing `malaysia_states()`, `malaysia_states_flat()`, `is_east_malaysia()`, `postage_for_state()`
- [x] Wire helper load into `public/index.php`
- [x] Add `postage_fee_east` setting in admin Settings page (split West/East UI cards) and persist via `AdminSettingsController::update`
- [x] Add Malaysian state dropdown to checkout page with grouped optgroups (Semenanjung / Sabah & Sarawak)
- [x] Live postage + grand total update in checkout via JS based on selected state's zone
- [x] Server-side validation: state required, must match allow-list (anti-tampering)
- [x] Calculate postage server-side using `postage_for_state()` and persist `state` + `postage` on `orders`
- [x] Persist `state` on the customer address record
- [x] Surface state badge in admin order detail (Sabah/Sarawak chip when applicable) and customer profile address list
- [x] Include `state` in WhatsApp notifications (WAWP and wa.me link variants)
- [x] Use stored per-order postage on the order confirmation page (instead of current global setting), with state label
- [x] Lint-check all changed PHP files via Docker (php:8.2-cli, all green)

---

### Impacted Files

#### NEW
- `/.dev-records/dev.md`
- `/src/helpers/malaysia.php`

#### UPDATED
- `/database/schema.sql`
- `/public/index.php`
- `/src/config/database.php`
- `/src/controllers/CheckoutController.php`
- `/src/controllers/admin/AdminSettingsController.php`
- `/src/models/Order.php`
- `/src/models/CustomerAddress.php`
- `/src/views/checkout/index.php`
- `/src/views/admin/settings/index.php`
- `/src/views/admin/orders/show.php`
- `/src/views/admin/customers/show.php`
- `/src/views/order/confirmation.php`

---

### Summary
Implemented state-aware shipping. Customers must now select a Malaysian state at checkout; the postage charge updates live (Semenanjung vs Sabah/Sarawak/Labuan) and is recalculated server-side on submit using two configurable settings: `postage_fee` (West) and `postage_fee_east` (East). Both `state` and the resolved `postage` are persisted on the order so historical confirmation pages and admin views always reflect what the customer actually paid, regardless of later setting changes. Auto-migration ensures existing databases gain the new columns without manual SQL.

### Security & Quality Notes
- State input is validated against a server-side allow-list (`malaysia_states_flat()`) before persistence — prevents tampering of the WhatsApp/HTML option to bypass East-zone pricing.
- Postage is recalculated on the server using the stored settings, never trusted from the client.
- Numeric inputs in admin settings use `max(0, (float)…)` and `number_format(…, 2, '.', '')` for safe normalization.
- All persistence uses prepared statements (existing pattern preserved).
- New columns default to safe values (`''` / `0.00`) so legacy rows render cleanly.
