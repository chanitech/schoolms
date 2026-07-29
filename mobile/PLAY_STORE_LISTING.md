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
Parents: check school fees, results and receipts anytime, from your phone.
```

---

## Full description (4000 characters max)

```
ShulePRO brings your child's school to your pocket.

Built for parents and guardians of students in Tanzanian schools, ShulePRO gives you secure, instant access to the school information that matters most — no more waiting for the end of term or a trip to the school office.

WHAT YOU CAN DO

• School fees — see exactly what has been paid, what remains, and when the next payment is due.
• Payment receipts — view and keep every official receipt issued for your payments.
• Academic results — follow your child's marks, grades, subject positions and overall performance as soon as the school publishes them.
• Progress over time — see how your child is improving across exams and subjects.
• Multiple children — one login shows every child you are registered with at the school.

SIMPLE AND SECURE SIGN-IN

Log in with the phone number you registered at your child's school, together with your school code and password. You see only your own children's records, and nothing else.

FOR SCHOOLS

ShulePRO is the parent app for the ShulePRO school management system, used by schools to manage students, academics, examinations, fees, staff, stores and reporting. If your school does not use ShulePRO yet and you would like a demonstration, contact Chani Technologies on +255 713 209 535 or info@chanitech.co.tz.

NOTE

You need an account created by your child's school to use this app. If you cannot sign in, please contact your school office to confirm your registered phone number.

ShulePRO is a product of Chani Technologies, Dar es Salaam, Tanzania.
www.chanitech.co.tz
```

---

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
The app opens on the Parent/Guardian login screen.

School code: kitungwa
Phone number: 0700000001
Password: ReviewDemo2026

This demo account belongs to a demo parent with one demo student and
contains sample fees, payments and examination results so the reviewer
can see all app features. Accounts are normally created by each school
for its own registered parents.
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
