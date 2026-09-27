# Resume Analyzer AI

AI-powered resume analyzer and job-matching platform. Upload a resume (PDF/DOCX), get a
structured AI analysis (skills, ATS score, strengths/weaknesses, suggestions) from Grok
(xAI), and see how well you match against jobs posted by an admin.

## Tech stack

- PHP 8.1+ (no framework), MySQL
- [Grok API](https://docs.x.ai) (xAI) for resume analysis and job-match explanations, using
  structured JSON output (`response_format: json_schema`)
- Bootstrap 5 + Chart.js (via CDN) for the UI
- `smalot/pdfparser` and `phpoffice/phpword` for text extraction

## How the AI pipeline works

1. User uploads a PDF/DOCX resume.
2. PHP extracts the raw text (`ResumeParser`).
3. The text is sent to Grok with a strict JSON schema (`GrokService::analyzeResume`), so the
   response is always valid, predictable JSON — never a free-text paragraph.
4. The structured result is validated and stored in MySQL (`resume_analysis` table).
5. For job matching, **PHP calculates the match percentage** (`JobMatcher`) by comparing the
   candidate's extracted skills against a job's required skills. Grok is only asked to explain
   the match in plain language — the score itself is deterministic, not up to the AI.

## Setup (XAMPP)

1. Clone this repo into `C:\xampp\htdocs\resume-analyzer-ai`.
2. Start Apache and MySQL in the XAMPP control panel.
3. Install PHP dependencies:
   ```
   composer install
   ```
4. Create the database:
   ```
   mysql -u root -p < database/schema.sql
   ```
5. Copy `.env.example` to `.env` and fill in your Grok API key (get one at
   https://console.x.ai) and your local DB credentials.
6. Create the first admin account:
   ```
   php database/seed_admin.php
   ```
7. Visit `http://localhost/resume-analyzer-ai/public/` in your browser.

## Project structure

```
admin/            Admin panel (jobs, users, resumes, dashboard) - requires admin role
public/           User-facing app (register, login, upload, dashboard, jobs) - webroot
src/               PHP classes (Auth, Database, services/)
src/services/      GrokService, ResumeParser, JobMatcher
config/            config.php (reads .env)
database/          schema.sql, seed_admin.php
```

## Security notes

- The Grok API key lives only in `.env`, which is git-ignored and never sent to the browser.
- Passwords are hashed with `password_hash()` (bcrypt).
- Uploaded files are renamed to random hex names and validated by extension + real MIME type.
- All DB queries use prepared statements (PDO).
