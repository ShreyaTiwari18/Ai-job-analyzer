# Resume Analyzer AI

AI-powered resume analyzer and job-matching platform. Upload a resume (PDF/DOCX), get a
structured AI analysis (skills, ATS score, strengths/weaknesses, suggestions) from Groq,
and see how well you match against jobs posted by an admin.

## Tech stack

- PHP 8.1+ (no framework), MySQL
- [Groq API](https://console.groq.com/docs) for resume analysis and job-match explanations,
  using structured JSON output (`response_format: json_schema`)
- Bootstrap 5 + Chart.js (via CDN) for the UI
- `smalot/pdfparser` and `phpoffice/phpword` for text extraction

## How the AI pipeline works

1. User uploads a PDF/DOCX resume.
2. PHP extracts the raw text (`ResumeParser`).
3. The text is sent to Groq with a strict JSON schema (`AiService::analyzeResume`), so the
   response is always valid, predictable JSON — never a free-text paragraph. Structured
   output in strict mode only works on a few Groq models, so this uses `openai/gpt-oss-120b`.
4. The structured result is validated and stored in MySQL (`resume_analysis` table).
5. For job matching, **PHP calculates the match percentage** (`JobMatcher`) by comparing the
   candidate's extracted skills against a job's required skills. The AI is only asked to
   explain the match in plain language — the score itself is deterministic, not up to the AI.

## Setup (XAMPP)

1. Clone this repo into `C:\xampp\htdocs\Ai-job-analyzer`.
2. Start Apache and MySQL in the XAMPP control panel.
3. In `C:\xampp\php\php.ini`, uncomment (remove the leading `;` from) these two lines,
   then restart Apache — they're required by the DOCX parser:
   ```
   extension=gd
   extension=zip
   ```
4. Install PHP dependencies:
   ```
   composer install
   ```
5. Create the database:
   ```
   mysql -u root -p < database/schema.sql
   ```
6. Copy `.env.example` to `.env` and fill in your Groq API key (get one at
   https://console.groq.com/keys) and your local DB credentials.
7. Create the first admin account:
   ```
   php database/seed_admin.php
   ```
8. Visit `http://localhost/Ai-job-analyzer/public/` in your browser.

## Project structure

```
admin/            Admin panel (jobs, users, resumes, dashboard) - requires admin role
public/           User-facing app (register, login, upload, dashboard, jobs) - webroot
src/               PHP classes (Auth, Database, services/)
src/services/      AiService, ResumeParser, JobMatcher
config/            config.php (reads .env)
database/          schema.sql, seed_admin.php
```

## Security notes

- The Groq API key lives only in `.env`, which is git-ignored and never sent to the browser.
- Passwords are hashed with `password_hash()` (bcrypt).
- Uploaded files are renamed to random hex names and validated by extension + real MIME type.
- All DB queries use prepared statements (PDO).
