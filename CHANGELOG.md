# Changelog

يتبع [Keep a Changelog](https://keepachangelog.com/) و[Semantic Versioning](https://semver.org/).

## [1.1.0] — 2026-11-08
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
