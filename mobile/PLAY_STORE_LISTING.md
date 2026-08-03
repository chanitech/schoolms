# Shule360 — Google Play Store submission pack

Everything below is copy-paste ready for the Play Console.

---

## App details

| Field | Value |
|---|---|
| App name (30 chars max) | `Shule360` |
| Default language | English (United States) |
| App or game | **App** |
| Free or paid | **Free** |
| Package name | `tz.co.chanitech.shulepro` |
| Category | Education |
| Tags | Education, Parenting |
| Contact email | info@chanitech.co.tz |
| Contact phone | +255 713 209 535 |
| Website | https://schoolms.chanitech.co.tz |
| Privacy policy URL | https://schoolms.chanitech.co.tz/privacy |

---

## Short description (80 characters max)

```
School management in your pocket — for parents, teachers and school staff.
```

---

## Full description (4000 characters max)

```
Shule360 brings the whole school to your pocket — for parents and for school staff.

Shule360 is the mobile app for the Shule360 school management system used by schools in Tanzania. Sign in with the account your school gave you, and you get the part of the system that belongs to your role.

FOR PARENTS AND GUARDIANS

• School fees — see what has been paid, what remains, and when the next payment is due.
• Payment receipts — view every official receipt issued for your payments.
• Examination results — follow your child's marks, grades, subject positions and division as soon as the school publishes them.
• Progress over time — see how your child improves across exams and subjects.
• Several children — one login shows every child registered to you at the school.

FOR TEACHERS AND SCHOOL STAFF

• Academics — enter and review marks, examinations, results and class performance.
• Lesson plans and topic coverage — record the topics and subtopics covered in each session.
• Attendance — mark class sessions and follow teaching attendance.
• Students — registration, enrolment, classes and student records.
• Finance office — school fees, payments, invoices, budgets, staff loans, procurement requests and expenses, each following the school's own approval chain.
• Store and inventory — stock items, stock movements and store requests.
• Library, dormitory, counselling, events and staff leave.
• Reports — printable, signed reports carrying the school's letterhead and an approval trail.

Every user sees only what their role allows, and every school's data is completely separate from every other school's.

SIGNING IN

Parents sign in with the phone number registered at their child's school, together with the school code and password. Staff sign in with their school email address and password using the "Staff / Admin" link on the sign-in screen. Accounts are created by the school — the app has no public sign-up.

FOR SCHOOLS

If your school does not use Shule360 yet and you would like a demonstration, contact Chani Technologies on +255 713 209 535 or info@chanitech.co.tz.

Shule360 is a product of Chani Technologies, Dar es Salaam, Tanzania.
www.chanitech.co.tz
```

## Graphics to upload

| Asset | File | Size |
|---|---|---|
| App icon | `mobile/assets/play-store-icon-512.png` | 512×512 |
| Feature graphic | `mobile/assets/feature-graphic.png` | 1024×500 |
| Phone screenshots | take on your phone (see below) | min 2, max 8 |

### Screenshots to take (on your Android phone, in the app)

1. The login screen
2. The dashboard showing your children
3. The fees page showing balance/paid
4. A results page with marks
5. A payment receipt

Take them with **Power + Volume Down**, then upload the files from your phone or transfer them to your Mac. Play accepts JPEG or 24-bit PNG, 16:9 or 9:16, between 320px and 3840px on each side — normal phone screenshots qualify.

---

## App access (IMPORTANT — the app requires login)

Play Console → **App content** → **App access** → choose
**"All or some functionality is restricted"** → Add instructions:

| Field | Value |
|---|---|
| Name | Guardian login |
| Username | `0700000001` |
| Password | `ReviewDemo2026` |
| Any other instructions | See below |

```
The app opens on the Parent/Guardian sign-in screen. Both roles can be
reviewed from the same app.

PARENT / GUARDIAN LOGIN (the screen the app opens on)
  School code: kitungwa
  Phone number: 0700000001
  Password: ReviewDemo2026
  Shows: one demo student with sample fees, payment receipts and
  examination results.

SCHOOL STAFF LOGIN (tap "Staff / Admin? Login here" at the bottom of the
sign-in screen)
  School code: demo
  Email: demo@demo.ac.tz
  Password: demo1234
  Shows: the school-management side — students, academics, marks,
  attendance, finance, store and reports — in a demo school.

Accounts are created by each school for its own users; the app has no
public sign-up.
```

---

## Data safety answers

Play Console → **App content** → **Data safety**

- Does your app collect or share any of the required user data types? **Yes**
- Is all of the user data collected by your app encrypted in transit? **Yes**
- Do you provide a way for users to request that their data is deleted? **Yes** (contact the school/ info@chanitech.co.tz — this is stated in the privacy policy)

Data types to declare — for each: **Collected = Yes, Shared = No, Processed ephemerally = No, Required = Yes, Purpose = App functionality + Account management**

| Category | Data type |
|---|---|
| Personal info | Name |
| Personal info | Phone number |
| Personal info | Other info (student academic and fee records) |
| Financial info | Purchase history (school fee payment records) |
| App activity | App interactions (basic usage/security logs) |

> Note: the app does **not** collect location, contacts, photos, files, messages, or device identifiers for advertising.

---

## Content rating questionnaire

Category: **Reference, News, or Educational**

Answer **No** to every question about violence, sexuality, profanity, drugs, gambling, and user-generated content sharing. Expected result: **Everyone / PEGI 3**.

---

## Other App content declarations

| Question | Answer |
|---|---|
| Ads | **No, my app does not contain ads** |
| Target audience | Age 18 and over (the app is used by parents, not children) |
| News app | No |
| COVID-19 contact tracing | No |
| Data safety | complete as above |
| Government app | No |
| Financial features | **No** — the app displays fee records but does not process payments |

---

## Release steps (original submission — already done)

1. **Create app** — Play Console → *Create app* → fill App details table above.
2. **Store listing** — Main store listing → short + full description, upload icon, feature graphic, screenshots.
3. **App content** — complete every item: privacy policy, app access, ads, content rating, target audience, data safety.
4. **Closed testing** — Testing → Closed testing → Create new release → upload the AAB.
   - Create an email list of **at least 12 testers** and invite them; they must **opt in via the link and keep the app installed for 14 days**.
5. After 14 days with 12+ opted-in testers → **Apply for production access** → Google reviews → publish to **Production**.

---

## Rename release: ShulePRO → Shule360 (v2 / 1.1)

The app was renamed after submission to avoid a brand collision with an
unrelated, already-operating competitor also using the name "ShulePro"
(shulepro.com, Kenya-based, same market). The package ID
(`tz.co.chanitech.shulepro`) is unchanged — only the display name.

**Upload this update to the same Closed testing → Alpha track:**

1. Testing → Closed testing → Alpha → **Create release**.
2. Upload `mobile/android/app/build/outputs/bundle/release/app-release.aab`
   (already rebuilt as versionCode 2 / 1.1 with the new name — or use
   `~/Downloads/Shule360-release-v2.aab`).
3. Release name: `1.1 (2)`
4. Release notes:
   `App renamed from ShulePRO to Shule360. No other changes — same login, same features.`
5. **Save → Review release → Start rollout.** Your existing 12 opted-in
   testers stay opted in; this does not reset the 14-day clock.
6. **Also update the Main store listing** (separate from the release):
   App name → `Shule360`; paste the updated short/full description from
   this file; replace the feature graphic with the regenerated
   `mobile/assets/feature-graphic.png` (also copied to `~/Downloads/`).
7. **Re-take screenshots** after deploying the web-app changes to
   production (`bash deploy.sh`) — the old screenshots show the
   "ShulePRO" wordmark on the sign-in pages, which no longer matches.
   Say "relaunch capture" or re-run
   `node mobile/assets/shoot-portal-screenshots.js`.

---

## Future updates

Each upload needs a higher version. In `mobile/android/app/build.gradle`:

```gradle
versionCode 3          // increment by 1 every upload — 2 was used for the rename
versionName "1.2"      // human-readable
```

Then rebuild:

```bash
cd mobile/android && JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew bundleRelease
```

## Keep safe forever

- `mobile/android/shulepro-release.jks`
- `mobile/android/key.properties`

Losing these means never being able to update Shule360 on Play again.
