# Counselling public API (v1)

Read-only JSON API for the student website, plus "follow" and alerts. It returns **published data only**.
Base URL: `https://<your-backend>/api/v1`. No login, no cookies. Dates are ISO 8601 with the cycle's time zone
(for example `2026-10-20T10:00:00+05:30`).

## Conventions

| | |
|---|---|
| Visibility | A counselling is returned only if it is **public and active**. A cycle only if its **public** switch is on (the "current" cycle is not automatically public). Drafts, deleted records and unpublished exams are never returned. |
| Cancelled / archived | They keep their page: `marked` is `"cancelled"` or `"archived"` (do not 404 them). A cancelled or postponed cycle sets `status_bar.state`. |
| Links | Only absolute external `http(s)` links are returned. Files are returned as full URLs. |
| Caching | Reads are cached for 60 s on the server (`CNS_CACHE_TTL`) and sent with `Cache-Control: public, max-age=60` and an `ETag`; send `If-None-Match` to get `304`. |
| CORS | `api/v1/*` allows any origin by default. Set `CORS_ALLOWED_ORIGINS=https://your-site.com` to lock it down. |
| Rate limits | Reads: 120 / minute / IP. Follow: 6 / minute / IP and 5 / hour per email or phone. `429` when exceeded. |
| Errors | `404 {"message": "..."}`, `422 {"message", "errors": {field: [..]}}`, `401` for an invalid inbox token, `429`. |
| Pagination | `page`, `per_page` (default 20-30, max 50-100). Response has `meta: {page, per_page, total, last_page}`. |
| Money / numbers | Numbers are JSON numbers (`25000`, not `"25000.00"`). Ranks and percentiles are numbers too. |

## Pages you need on the website

| Route on the website | Uses |
|---|---|
| `/counselling` | `GET /counselling` |
| `/counselling/{slug}` | `GET /counselling/{slug}` (current public cycle) |
| `/counselling/{slug}/{year}` | `GET /counselling/{slug}?year={year}` |
| `/exams/{slug}` | the exam's own data, plus the block `GET /exams/{slug}/counselling` |
| college page | the block `GET /organisations/{id or slug}/admission-through` |
| `/alerts?token=...` | the inbox / manage screens (see Follow) |

`FRONTEND_URL` (server setting) is used for canonical URLs and for the links inside alerts, so these routes must exist.

## Endpoints

### `GET /counselling`
Filters: `q` (name, short name, alias), `level` (`national|state|university|institute`), `state`, `program_level` (id), `featured=1`, `page`, `per_page` (max 50).
Each item: `id, name, short_name, slug, level, states[], mode, conducting_body, logo_url, is_featured, is_archived, program_levels[], exams[{name, slug|null}], current_cycle{year, session_label, status}, current_round, url, api_url`.
`exams[].slug` is `null` when that exam is not published (show the name, do not link it).

### `GET /counselling/{slug}?year=&session=`
The whole page for one cycle. `year` is optional: without it you get the current public cycle (or the latest public one). `404` if the counselling is not public, or the year is not public.

| Key | Contents |
|---|---|
| `counselling` | identity: name, aliases, level, states, mode, `conducting_body{name,type,website}`, program levels, course categories, website, portal, helpline, `logo_url`, `cover_url`, `about`, flags |
| `exams[]` | mapped exams: `name`, `slug|null`, `role` (`primary|additional`), valid years, `seat_scope_note` |
| `available_years[]` | public years, newest first (build the year switcher from this) |
| `cycle` | `year, session_label, title, status, status_note, timezone, is_current, information_bulletin{url, pdf_url}, reservation_policy, seat_allotment_method, willingness_options[], source_url, last_verified_at, exam_cycles[]`. `null` when no cycle is public yet |
| `status_bar` | `state` (`scheduled|postponed|cancelled|to_be_announced`), `label`, `current_round{id,name}`, `items[{label, state, text}]` (Registration, Choice filling, Allotment result, Reporting). **Render this at the top.** |
| `dates` | `cycle[]` and `rounds[{round_id, name, status, events[]}]`. Event: `type{slug,name}, label, quota, start_at, end_at, extended_end_at, date_precision, date_text, status, status_text, link_url, source_url, note, verified, last_verified_at`. Use `date_text`/`status_text` for display; `status` is `upcoming|open|closed|to_be_announced` (or the manual status for results) |
| `eligibility[]` | per quota (`null` = all): class, subjects, nationality, `age{min,max,as_of}`, `domicile{rule,label,states,study_years_required,notes}`, `minimum_scores[{category, exam, min_percentile, min_score, max_rank, min_qualifying_percent}]` |
| `process[]` | numbered timeline: `step_no, title, description, link_url, date{text,status,start_at,end_at}|null` |
| `fees[]`, `documents[]`, `rules[]` | cycle-level defaults |
| `rounds[]` | per round: `status`, `quotas`, `fresh_registration_allowed`, `willingness_options`, `instructions`, `process[]`, **`rules[]` and `fees[]` as they apply in this round** (each has `source`: `inherited | overridden | round`), `extra_documents[]`, `seat_matrix{live, changes_since_previous, versions[]}`, `results[]`, `cutoffs{count, api_url}` |
| `institutes`, `cutoffs` | counts and the URL of the explorer endpoints |
| `seo` | `title, description, canonical, robots`. Use as-is in `<head>`. The current cycle is canonical at `/counselling/{slug}`, past years at `/counselling/{slug}/{year}` |
| `marked` | `null | "cancelled" | "archived"`: show a banner |
| `last_updated_at` | the "Last updated on" line |

Deposit rules (`rules[]`): `action`, `quota`, `institute_type`, `deposit{outcome, label, refund_percent, refund_amount}`, `seat_outcome`, `eligible_for_next_round`, `penalty_note`, `source_url`. Render one table per round from `rounds[].rules`.

### `GET /counselling/{slug}/institutes`
Filters: `q`, `quota` (id), `institute_type`, `state` (a state of any of the college's campuses), `year`, `page`, `per_page` (max 100).
Row: `college{id,name,slug,logo_url,city,state}, course{id,name,slug}, quota, institute_type, authority_code, annual_tuition_fee, other_fees, bond_details, total_seats`. `filters` lists the choices that exist.

### `GET /counselling/{slug}/cutoffs`  (cutoffs explorer)
Filters: `round` (id), `exam` (id or slug), `quota`, `category` (ids), `gender` (`neutral|male|female`), `pwd` (`0|1`), `institute_type`, `state`, `course` (id), `rank_type` (`air|category_rank|state_merit_rank|score|percentile`), `q` (college or course), `year`.
Sort: `sort=closing_asc` (default) `| closing_desc | college`. Pagination as above.
Row: `college, course, round{id,name}, exam, quota, category, gender, pwd, institute_type, rank_type, rank_type_label, opening_value, closing_value`.
`filters` returns only values that exist in this cycle (build your filter UI from it). Only the cycle's own rounds are searched. Do not mix `rank_type`s when sorting: filter by one.

### Blocks for other pages

**`GET /exams/{slug}/counselling`**: "Counselling after this exam". `{exam, counsellings[{name, slug, level, role, seat_scope_note, status{state,text}, current_cycle, current_round, url}]}`. `404` unless the exam is published.

**`GET /organisations/{id or slug}/admission-through`**: "Admission through" for a college. Per public counselling: its public cycle and, per course, the quotas with seats / fee / bond, plus `latest_cutoffs[]` (the **latest round that has a cutoff** wins for each course + quota + category + gender + PwD + rank type). Empty list if the college is in no counselling.

Both accept `?format=html` and then return a ready-made, **unstyled** HTML fragment (classes `cns-*`) that you can drop into a page and style yourself. The Blade sources are in `resources/views/public/counselling/blocks/`.

## Follow and alerts

There are no student accounts. A follower is an **email and/or phone that has been confirmed**. Nothing is sent to an unconfirmed address; each follow is confirmed on its own (so nobody can start alerts to someone else's email); every channel can be switched off separately.

```
POST /follow  ──►  confirmation email (signed link)  and/or  WhatsApp code
                          │                                      │
             GET link (page shown)                      POST /follow/verify-phone
                          └──────────────►  inbox_token  ◄───────┘   (keep it in the browser)
```

**`POST /follow`** body:
`type` (`counselling|exam`), `slug`, `email`?, `phone`?, `channels[]` (`email|whatsapp|inapp`), `whatsapp_opt_in` (must be `true` for WhatsApp), `alert_types[]`? (omit = all), `token`? (see below), `website` (honeypot: leave empty).
Phones: `+<country><number>`; a 10-digit number is treated as Indian (`+91`).
Answers `202` with `pending_confirmation {email, phone}`. The same answer is returned whether or not the address was known.
Passing the visitor's `token` proves they are the same confirmed person, and the follow is active immediately without another email.

**`GET <link in the email>`**: confirms that follow and the address; shows a small page (send `Accept: application/json` to get `{confirmed, inbox_token, manage_url}`). Links expire after 3 days. The page links to `FRONTEND_URL/alerts?token=...`.

**`POST /follow/verify-phone`** `{phone, code}`: the 6-digit code sent over WhatsApp (valid 10 min, 5 tries). On success records the WhatsApp opt-in and returns `{confirmed: true, inbox_token}`.

**Unsubscribe**: every email has a signed `GET /unsubscribe?...` link (`ch=email|whatsapp|inapp|all`, optionally one follow) and `List-Unsubscribe` headers.

With the **inbox token** (`token` query/body parameter; revoked server-side by bumping the subscriber's `token_version`):

| Call | Purpose |
|---|---|
| `GET /inbox?token=&unread=1&page=` | in-app alerts: `unread_count`, `data[{id, kind, title, body, link_url, source_url, created_at, read}]` |
| `POST /inbox/read` `{token, ids?[]}` | mark read (no `ids` = all) |
| `GET /follows?token=` | `identity` and the follows with their channel switches |
| `POST /follows/{id}/channels` `{token, email?, whatsapp?, inapp?, alert_types?[]}` | change channels / alert kinds |
| `DELETE /follows/{id}?token=` | unfollow |

Alert kinds (`alert_types`): `opens`, `closing` (3 days and 1 day before the deadline, extended deadlines included), `declared`, `date_changed` (also "dates announced"), `postponed` (postponed / cancelled), `seats_revised`.

### What triggers an alert
Only **verified** data. An event fires only when a staff member with the verify permission has marked it verified **after** its last change, so a typo that is corrected before verification never reaches students. Each alert is created once (no repeats), news older than 3 days is ignored, and only public exams / counsellings / cycles are announced. Every alert carries the official source link.

## Server setup checklist

1. **Cron**, every minute: `php artisan schedule:run` (alerts are created and sent by `cns:dispatch-alerts` every 10 minutes; run it by hand any time, or use *Counselling → Alerts → Run now*).
2. **Mail**: set `MAIL_MAILER` and the SMTP settings (the default `log` only writes to the log file, nothing is delivered), plus `MAIL_FROM_ADDRESS`.
3. `FRONTEND_URL` = the website's address; `CORS_ALLOWED_ORIGINS` = the website's origin.
4. **WhatsApp** is off by default (`WHATSAPP_DRIVER=none`: deliveries are marked *skipped*, never faked). To enable, set `WHATSAPP_DRIVER=cloud`, `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, and have two **approved templates** in the WhatsApp Business account, named as in `WHATSAPP_TEMPLATE_OTP` (default `cns_verify_code`, body parameter: the code) and `WHATSAPP_TEMPLATE_ALERT` (default `cns_alert`, body parameters: title, link). `WHATSAPP_DRIVER=log` only writes messages to the log, for testing. Messages are billed by the provider.
5. `php artisan storage:link` so uploaded files (bulletins, templates, results) have public URLs.
6. Run the seeders once after migrating: `php artisan db:seed --class=CounsellingSeeder --force`.
