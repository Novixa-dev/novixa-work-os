# 🤝 دليل المساهمة

## سير العمل
1. أنشئ فرعاً: `feat/…` أو `fix/…`.
2. نفّذ شريحة صغيرة كاملة (migration + policy + validation + UI + tests).
3. شغّل بوابات الجودة قبل الدفع:
   ```bash
   cd apps/api && composer lint && composer analyse && php artisan test
   pnpm typecheck:web && pnpm test:web && pnpm build:web
   ```
4. افتح Pull Request باستخدام القالب.

## رسائل Commit (Conventional Commits + إيموجي)
`✨ feat` · `🐛 fix` · `🔒 security` · `🧹 chore` · `📚 docs` · `🧪 test` · `♻️ refactor` · `👷 ci`

## قواعد الجودة
- العربية RTL والإنجليزية LTR في كل واجهة؛ أضف المفتاح في `ar.json` و`en.json` معاً (يتحقق الاختبار من التطابق).
- لا منطق صلاحيات في الواجهة فقط؛ كل مسار محمي بـ Policy.
- لا أسرار ولا ملفات `.env` في Git.
- ملاحظة بيئة: `composer.lock` يتطلب PHP ≥ 8.4.1.
