# 🚀 النشر الإنتاجي — novixa-work-os.novixa.dev

> يحل محل وصف `docker-compose.yml` القديم في `17-DEPLOYMENT-AND-DEVOPS.md` (حُذف ذلك الملف لأنه يحجز المنفذين 80/443 المستخدمَين من Mailcow على الخادم).

## المعمارية

```
المتصفح ──► Cloudflare (DNS + Proxy + SSL) ──► mailcow nginx :443 (شهادة Origin *.novixa.dev)
                                                    │  شبكة Docker: workos-edge
                                                    ▼
                                         novixa-work-os-web  (nginx :8080)
                                          ├─ /          صفحة الشركة العامة (ثابتة)
                                          ├─ /app/      واجهة WorkOS (Nuxt SPA ثابتة)
                                          └─ /api/      ──► novixa-work-os-api (php-fpm :9000)
                                                              ├─ novixa-work-os-queue (worker)
                                                              └─ novixa-work-os-db (PostgreSQL 16)
```

- الخادم يستضيف مواقع أخرى وMailcow؛ **لا يُعاد تشغيل أي منها** أثناء النشر.
- لا منافذ عامة جديدة: الوصول عبر شبكة `workos-edge` فقط، ومنفذ محلي `127.0.0.1:3120` للفحص.

## خط النشر (GitHub Actions)

`push → main` ⟶ **CI** (Pint، PHPStan، PHPUnit، ESLint، vue-tsc، Vitest، build) ⟶ **Deploy**:

1. بناء صورتين (`novixa-work-os-api`, `novixa-work-os-web`) ودفعهما إلى GHCR بوسم `sha-<commit>` و`latest`.
2. نسخ `deploy/*` إلى `/opt/novixa-work-os` عبر SSH (مستخدم `workos` بصلاحيات محدودة، مفتاح مخصص، `known_hosts` مثبّت).
3. `remote-deploy.sh`: سحب الصور ⟶ `migrate --force` ⟶ تشغيل ⟶ فحص الصحة ⟶ **تراجع تلقائي** للوسم السابق عند الفشل.
4. تثبيت كتلة nginx عبر `sudo workos-sync-site` (يجري `nginx -t` ويستعيد القديمة عند الفشل).
5. اختبار دخان (`curl`) ثم اختبار متصفح حقيقي `e2e/smoke.mjs`.

تشغيل يدوي: **Actions ▸ Deploy ▸ Run workflow**.

## الأسرار (GitHub ▸ Settings ▸ Secrets)

| السر | الاستخدام |
|------|-----------|
| `DEPLOY_SSH_KEY` | مفتاح SSH الخاص بمستخدم `workos` |
| `DEPLOY_HOST` · `DEPLOY_USER` | عنوان الخادم والمستخدم |
| `DEPLOY_KNOWN_HOSTS` | بصمة الخادم (حماية من MITM) |

أسرار التطبيق (`APP_KEY`، `DB_PASSWORD`) تُولَّد على الخادم في `/opt/novixa-work-os/.env` (chmod 600) ولا تدخل Git.

## الحساب الأول
يُنشأ مالك واحد تلقائياً (بدون بيانات تجريبية) وتُحفظ كلمة مروره في `/opt/novixa-work-os/.owner-credentials`. **اقرأها مرة واحدة ثم احذفها**، وغيّر كلمة المرور من الإعدادات.

## التشغيل والصيانة

```bash
ssh workos@161.97.75.64
cd /opt/novixa-work-os
docker compose ps
docker compose logs -f api web
./backup.sh                      # نسخة احتياطية فورية (يعمل أيضاً يومياً 03:17)
bash restore.sh backups/<file>.sql.gz   # استعادة (للتدريب أولاً على نسخة معزولة)
```

- **التراجع اليدوي:** عدّل `TAG=` في `.env` إلى وسم سابق ثم `docker compose up -d`.
- **DNS:** سجل `A` مُوكَّل (Proxied) يشير إلى IP الخادم، وSSL في Cloudflare بوضع Full.
- **نسخ احتياطي خارجي (موصى به):** انسخ `/opt/novixa-work-os/backups` دورياً خارج الخادم (مثلاً Cloudflare R2).

## ما يُفحص بعد كل نشر
`/up` ⟶ 200 · `/` يحوي Novixa · `/api/v1/settings/workspace` ⟶ JSON · `/app/` ⟶ 200 · متصفح: الصفحة العامة، تحويل `/app/` لتسجيل الدخول، ورفض بيانات دخول خاطئة بـ 422 من `/api/v1/auth/login`.
