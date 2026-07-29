# ShulePRO — Google Play Store submission pack

Everything below is copy-paste ready for the Play Console.

---

## App details

| Field | Value |
|---|---|
| App name (30 chars max) | `ShulePRO` |
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
ShulePRO brings the whole school to your pocket — for parents and for school staff.

ShulePRO is the mobile app for the ShulePRO school management system used by schools in Tanzania. Sign in with the account your school gave you, and you get the part of the system that belongs to your role.

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

If your school does not use ShulePRO yet and you would like a demonstration, contact Chani Technologies on +255 713 209 535 or info@chanitech.co.tz.

ShulePRO is a product of Chani Technologies, Dar es Salaam, Tanzania.
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

## Release steps

1. **Create app** — Play Console → *Create app* → fill App details table above.
2. **Store listing** — Main store listing → short + full description, upload icon, feature graphic, screenshots.
3. **App content** — complete every item: privacy policy, app access, ads, content rating, target audience, data safety.
4. **Closed testing** — Testing → Closed testing → Create new release → upload
   `mobile/android/app/build/outputs/bundle/release/app-release.aab`.
   - Release name: `1.0 (1)`
   - Release notes: `First release of ShulePRO for parents — school fees, payment receipts and examination results.`
   - Create an email list of **at least 12 testers** and invite them; they must **opt in via the link and keep the app installed for 14 days**.
5. After 14 days with 12+ opted-in testers → **Apply for production access** → Google reviews → publish to **Production**.

---

## Future updates

Each upload needs a higher version. In `mobile/android/app/build.gradle`:

```gradle
versionCode 2          // increment by 1 every upload
versionName "1.1"      // human-readable
```

Then rebuild:

```bash
cd mobile/android && JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew bundleRelease
```

## Keep safe forever

- `mobile/android/shulepro-release.jks`
- `mobile/android/key.properties`

Losing these means never being able to update ShulePRO on Play again.
