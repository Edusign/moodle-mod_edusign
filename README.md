<p align="center">
  <img src="pix/monologo.svg" alt="Edusign" width="110" />
</p>

<h1 align="center">Edusign for Moodle</h1>

<p align="center">
  <strong>The official Moodle plugin to run legally‑compliant digital attendance and e‑signatures inside your courses.</strong>
</p>

<p align="center">
  <a href="https://moodle.org/plugins"><img src="https://img.shields.io/badge/Moodle-4.2%2B-orange?logo=moodle&logoColor=white" alt="Moodle 4.2+"></a>
  <a href="https://github.com/Edusign/moodle-mod_edusign/releases"><img src="https://img.shields.io/github/v/release/Edusign/moodle-mod_edusign?color=success" alt="Latest release"></a>
  <a href="https://github.com/Edusign/moodle-mod_edusign/blob/master/composer.json"><img src="https://img.shields.io/badge/license-MIT-blue" alt="License"></a>
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/status-beta-yellow" alt="Maturity: Beta">
</p>

<p align="center">
  <a href="https://edusign.com">Website</a> ·
  <a href="https://developers.edusign.com">API docs</a> ·
  <a href="https://github.com/Edusign/moodle-mod_edusign/issues">Report a bug</a> ·
  <a href="mailto:support@edusign.com">Contact us</a>
</p>

---

## Why Edusign for Moodle?

Training organizations, universities, and corporate academies waste hours every week chasing paper attendance sheets, rebuilding rosters in two systems, and proving compliance to funders and auditors. **`mod_edusign` plugs [Edusign](https://edusign.com) directly into Moodle** so teachers and students never leave the LMS they already know — while you get airtight, legally recognized electronic signatures and a single source of truth for attendance.

Used by hundreds of French training centers (Qualiopi) and higher‑education institutions across Europe, Edusign is trusted to secure attendance records for thousands of learners every day.

### What you get out of the box

- ✍️ **Legally compliant e‑signatures** — eIDAS-grade electronic signatures, stored with a full audit trail.
- 🎓 **Seamless Moodle experience** — Edusign appears as a native activity module. No extra logins, no context switching.
- 🔄 **Two-way sync** — courses, teachers, and students are automatically mirrored between Moodle and your Edusign school platform.
- 📅 **Flexible session management** — create sessions one by one, in bulk via CSV, or let Edusign generate them from the course schedule.
- ✅ **Powerful completion rules** — mark the activity as complete when students sign *all* sheets or a *specific number* of sheets.
- 📊 **Gradebook integration** — attendance flows straight into Moodle's gradebook.
- 📩 **One-click signature emails** — send individual or grouped signature reminders to students who missed class.
- ⚡ **Real-time webhooks** — presence updates from the Edusign app, the mobile app, or the Edusign email signature flow reach Moodle instantly.
- 🌍 **Multilingual** — ships with English, French and Spanish translations.
- 🕓 **Retroactive sync** — change a roster and past sessions update automatically.

## See it in action

> Screenshots and a short demo video are coming soon. In the meantime, [book a live demo](https://edusign.com) — we'll walk you through the full flow in under 20 minutes.

## How it works

```
   ┌────────────────────────────┐        ┌─────────────────────────────┐
   │          Moodle            │        │      Edusign platform       │
   │                            │        │                             │
   │  Course  ─►  mod_edusign ──┼──API──►│  Training / Sessions        │
   │                            │        │                             │
   │  Student & teacher roster ─┼──Sync─►│  Attendees & instructors    │
   │                            │◄─Web── │  Signature events           │
   │  Gradebook  ◄──completion──│  hooks │  (signed / absent / late)   │
   └────────────────────────────┘        └─────────────────────────────┘
```

Under the hood the plugin:

1. Creates an Edusign **training** when a teacher adds an *Edusign* activity to a course.
2. Keeps the participant list in sync (enrol a student in Moodle → they appear on every Edusign sheet).
3. Turns every session you create into an **Edusign attendance sheet** with QR code + email signing.
4. Listens for signature webhooks and updates Moodle completion and grades in real time.

## Requirements

| Requirement | Version |
|---|---|
| Moodle | **4.2+** (build `2023042408` or higher) |
| PHP | 7.4+ (8.1+ recommended) |
| An Edusign account | [Create one](https://edusign.com) — a valid API key is required |

## Installation

### Option 1 — From a release ZIP (recommended)

1. Download the latest release from the [Releases page](https://github.com/Edusign/moodle-mod_edusign/releases).
2. In Moodle, go to **Site administration → Plugins → Install plugins** and upload the ZIP.
3. Follow the on-screen upgrade process and complete the database installation.

### Option 2 — Manual install

```bash
cd /path/to/your/moodle/mod
git clone https://github.com/Edusign/moodle-mod_edusign.git edusign
```

Then visit **Site administration → Notifications** and follow the Moodle installation wizard.

> ⚠️ The target directory **must** be named `edusign` (not `moodle-mod_edusign`), otherwise Moodle won't pick it up.

## Configuration

1. In Moodle, go to **Site administration → Plugins → Activity modules → Edusign**.
2. Set your **API URL** (`https://ext.edusign.fr` by default) and paste the **API key** from your Edusign admin console.
3. Click *Test API connection* to confirm everything is wired correctly.
4. Copy the **Webhook URL** shown in the *Webhooks* tab and paste it into the `on_student_sign` webhook of your Edusign school. This guarantees email-based signatures are reflected in Moodle instantly. See the [Edusign webhook documentation](https://developers.edusign.com/docs/webhooks-2) for details.

## Usage

### Add an Edusign activity to a course

1. Turn editing on in your course.
2. **Add an activity or resource → Edusign**.
3. Set the training start and end date (pre-filled from the course dates).
4. Choose a completion rule: *Sign all sheets* or *Sign N sheets*.
5. Save — Moodle and Edusign are now in sync.

### Create sessions

- **Manually** — from the activity page, click *Add a session* and pick a date, start time, end time, and title.
- **In bulk via CSV** — import many sessions at once using the provided [CSV template](./sample-import-sessions.csv):

  ```csv
  session_name,start_date,end_date
  Lecture 1,2025-09-01 09:00:00,2025-09-01 12:00:00
  Lecture 2,2025-09-08 09:00:00,2025-09-08 12:00:00
  ```

### Take attendance

From the session row, click **Take attendance** to:

- sign individual students (manual signature, late arrival, early departure, absence with comment),
- trigger a signature email to one or many students at once,
- or open the session on the Edusign app for QR-code signing in class.

Students see a dedicated dashboard with *Today*, *Upcoming*, and *Already signed* tabs, directly on the course page.

## Localization

Ships with:

- 🇬🇧 English (`lang/en`)
- 🇫🇷 French (`lang/fr`)
- 🇪🇸 Spanish (`lang/es`)

We welcome pull requests adding new languages — see the `lang/en/edusign.php` file for the full string list.

## Roadmap highlights

- Deeper reporting inside Moodle (session-level analytics, export to Excel).
- LTI launcher for institutions running Moodle-adjacent LMSes.
- Native Moodle Mobile support.

Have a feature you need? [Open an issue](https://github.com/Edusign/moodle-mod_edusign/issues/new) — we prioritize based on customer demand.

## Contributing

Contributions are very welcome! Please:

1. Open an issue first to discuss large changes.
2. Follow [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle).
3. Use [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `docs:`, …) so they land cleanly in the [CHANGELOG](./CHANGELOG.md).

Releases are automated with [release-please](https://github.com/googleapis/release-please) — a version bump and changelog entry will be generated for you.

## Support

- 🐛 **Bugs & feature requests** → [GitHub Issues](https://github.com/Edusign/moodle-mod_edusign/issues)
- 📚 **Product documentation** → [edusign.com](https://edusign.com)
- 🔌 **API & webhooks** → [developers.edusign.com](https://developers.edusign.com)
- 💬 **Paid support / onboarding** → [support@edusign.com](mailto:support@edusign.com)

## License

Released under the [MIT License](./composer.json). The Moodle plugin wrapper is distributed under the terms of the [GNU GPL v3+](http://www.gnu.org/copyleft/gpl.html) as required by the Moodle plugin policy.

---

<p align="center">
  Built with ❤️ by the <a href="https://edusign.com">Edusign</a> team and contributors.<br/>
  <sub>Edusign is the attendance & e‑signature platform trusted by leading training organizations across Europe.</sub>
</p>
