# Deploy QR-Gen to Vercel (vercel-community/php)

This project is configured for `vercel-php@0.9.0` in [vercel.json](vercel.json).

## 1) Prerequisites

- Vercel account
- Node.js installed
- PHP installed locally (recommended for `vercel dev` and syntax checks)

## 2) Install and login

Run:

```bash
npm i -g vercel
vercel login
```

## 3) Deploy from project root

Run:

```bash
vercel
```

For production:

```bash
vercel --prod
```

## 4) Optional project setting

If Vercel asks for framework preset, choose **Other**.

## 5) Verify key routes

After deploy, test:

- `/` (main app)
- `/ajax/process.php` (POST endpoint used by generator)
- `/pdf` (PDF endpoint)
- `/API` (health JSON endpoint)

## 6) Important runtime note

Vercel Serverless file storage is ephemeral. This repo is adapted so that on Vercel runtime:

- SVG/PNG downloads are generated client-side in browser
- server-side persisted file flow is bypassed for those downloads

This avoids write/read issues on serverless filesystem.

## 7) Local Vercel emulation

```bash
vercel dev
```

If this fails, ensure local PHP is installed and available on PATH.
