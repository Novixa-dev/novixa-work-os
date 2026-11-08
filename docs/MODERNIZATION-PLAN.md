# 🛠️ خطة التحديث والتحسين الاحترافي

> المرجع التنفيذي لمرحلة "التحسين الشامل". كل مهمة = Commit + Push مستقل.
> الحالة: ⬜ لم تبدأ · 🔄 جارية · ✅ منتهية

| # | المهمة | الحالة |
|---|--------|:------:|
| T1 | 🧹 نظافة المستودع: حذف الملفات المكررة، تنسيق PHP (Pint)، `.editorconfig`، تثبيت PHP ^8.4 | ✅ |
| T2 | 🤖 CI عبر GitHub Actions (API + Web) + Dependabot + قوالب PR/Issues | ✅ |
| T3 | 🔒 مراجعة أمنية للـ API وإصلاح الثغرات + اختبارات صلاحيات إضافية | ⬜ |
| T4 | 🔬 تحليل ساكن (Larastan) + رفع تغطية الاختبارات | ⬜ |
| T5 | 🎨 الواجهة: ESLint + Prettier + typecheck + اختبارات مكوّنات | ⬜ |
| T6 | 📚 توثيق: README, CONTRIBUTING, SECURITY, CHANGELOG | ⬜ |
| T7 | 📄 تقرير المشروع العربي الشامل (HTML) | ⬜ |
| T8 | 📈 تحديث PROGRESS / TRACEABILITY / TASK-TRACKER | ⬜ |

## معايير القبول العامة
- `php artisan test` أخضر، `pint --test` نظيف، `pnpm test` و`pnpm typecheck` ناجحان.
- لا أسرار في Git. لا كسر لتوافق الواجهة/API.
- رسائل Commit قصيرة بإيموجي (Conventional Commits).

## ملاحظات بيئة
- PHP المحلي الافتراضي 8.4.0 لا يطابق `composer.lock` (يتطلب ≥ 8.4.1)؛ استخدم `~/.config/herd/bin/php84/php.exe` (8.4.16).
