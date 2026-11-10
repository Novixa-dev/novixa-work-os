# Changelog

يتبع [Keep a Changelog](https://keepachangelog.com/) و[Semantic Versioning](https://semver.org/).

## [1.3.0] — 2026-11-11
### Fixed
- Projects, products, CRM, knowledge and settings lists rendered empty (wrong response shape); `primary-*` colors were undefined (invisible progress bars); inline API errors read the wrong field.
- Auth guard ran after pages mounted (401 race); API is now token-only (no CSRF 419 for SPA).
- Demo credentials were pre-filled/shown on the production login page (now dev-only).
### Added
- Toast feedback for every mutation/error, confirm dialogs (archive project, deactivate user), accessible modals (focus trap/restore, Escape, scroll lock), first-run onboarding checklist, per-page titles, password show/hide, login redirect, "Load more" pagination.
- API `per_page` clamped to 1..100; seeded Chrome journey tests (CI job `e2e`).

## [1.2.0] — 2026-11-09

### Security
- إصلاح مسار `notifications/{id}/read` (كان يرجع 500).
- ترويسات أمان، تهريب محارف LIKE، انتهاء صلاحية الرموز، سياسة كلمات مرور أقوى.
- إلزام `APP_KEY` و`DB_PASSWORD` في `docker-compose.yml`.
### Added
- GitHub Actions CI، Dependabot، قوالب PR/Issues.
- Larastan (المستوى 5)، `composer lint|analyse`، اختبارات أمان (20 اختباراً للـ API).
- التقرير العربي الشامل `docs/PROJECT-REPORT.ar.html`.
### Changed
- تنسيق PHP بالكامل بـ Pint؛ PHP ^8.4؛ إصلاح أخطاء `vue-tsc`؛ اختبارات ترجمة تستورد الكود الحقيقي.
### Removed
- ملفات وثائق مكررة في الجذر.

## [1.0.0] — 2026-11-08
- الإصدار الأول: API، الواجهة، Docker، الوثائق.
