<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\Contact;
use App\Models\KnowledgePage;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskLabel;
use App\Models\User;
use App\Models\WorkspaceSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Workspace Settings
        WorkspaceSetting::set('workspace_info', [
            'name_ar' => 'نوڤيكسا للبرمجيات والحلول الرقمية',
            'name_en' => 'Novixa Software & Digital Solutions',
            'tagline_ar' => 'نبني التقنية التي تجعل أعمالك أقوى',
            'tagline_en' => 'Engineered for Growth',
            'website' => 'https://novixa.dev/ar',
            'email' => 'info@novixa.dev',
            'phone' => '+967 776 248 526',
            'default_currency' => 'USD',
            'default_timezone' => 'Asia/Riyadh',
        ]);

        // 2. Team Users
        $owner = User::create([
            'name' => 'أحمد الحضرمي (Ahmed Al-Hadrami)',
            'email' => 'ahmed@novixa.dev',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'job_title' => 'Founder & CEO / المؤسس والمدير التنفيذي',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'سارة سالم (Sara Salem)',
            'email' => 'sara@novixa.dev',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'job_title' => 'Operations Lead / مديرة العمليات',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $lead = User::create([
            'name' => 'طارق باوزير (Tariq Ba-Wazir)',
            'email' => 'tariq@novixa.dev',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'job_title' => 'Technical Lead / قائد تقني ومشاريع',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $engineer = User::create([
            'name' => 'عمر بن محفوظ (Omar Bin-Mahfouz)',
            'email' => 'omar@novixa.dev',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'job_title' => 'Senior Fullstack Engineer / مهندس برمجيات أول',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $designer = User::create([
            'name' => 'فاطمة العطاس (Fatima Al-Attas)',
            'email' => 'fatima@novixa.dev',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'job_title' => 'UI/UX & Product Designer / مصممة واجهات',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $guest = User::create([
            'name' => 'سعيد الكندي (Saeed Al-Kindi)',
            'email' => 'saeed@darhadramout.com',
            'password' => Hash::make('password123'),
            'role' => 'guest',
            'job_title' => 'Representative / ممثل دار حضرموت العقارية',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        // 3. Labels
        $labelBackend = TaskLabel::create(['name_ar' => 'الواجهة الخلفية (Backend)', 'name_en' => 'Backend', 'color' => '#2563EB']);
        $labelFrontend = TaskLabel::create(['name_ar' => 'الواجهة الأمامية (Frontend)', 'name_en' => 'Frontend', 'color' => '#14B8A6']);
        $labelDesign = TaskLabel::create(['name_ar' => 'تصميم وتجربة (UI/UX)', 'name_en' => 'UI/UX', 'color' => '#EC4899']);
        $labelSecurity = TaskLabel::create(['name_ar' => 'أمان وحماية (Security)', 'name_en' => 'Security', 'color' => '#EF4444']);
        $labelClient = TaskLabel::create(['name_ar' => 'تسليم عميل (Client Delivery)', 'name_en' => 'Client Delivery', 'color' => '#F59E0B']);

        // 4. Products
        $prodAqar = Product::create([
            'name_ar' => 'نوڤيكسا عقار (Novixa Aqar)',
            'name_en' => 'Novixa Aqar Real Estate OS',
            'slug' => 'novixa-aqar',
            'tagline_ar' => 'المنظومة الرقمية المتكاملة لإدارة المكاتب والشركات العقارية',
            'tagline_en' => 'Integrated Real Estate Operating System',
            'description_ar' => 'منصة عربية بالكامل لإدارة العقارات، الوحدات، عقود الإيجار، تحصيل المستحقات، وتتبع الصيانة مع دعم العملات المتعددة.',
            'description_en' => 'Comprehensive Arabic-first real estate ERP managing properties, tenancy contracts, payment installments, and maintenance.',
            'lifecycle_status' => 'active',
            'owner_id' => $lead->id,
            'repository_url' => 'https://github.com/novixa-dev/novixa-aqar-core',
            'website_url' => 'https://aqar-landing-page.novixa.dev',
            'demo_url' => 'https://aqar-demo2.novixa.dev',
            'documentation_url' => 'https://docs.novixa.dev/aqar',
        ]);

        $prodMuhajim = Product::create([
            'name_ar' => 'مُهاجم (Muhajim Growth Engine)',
            'name_en' => 'Muhajim Outbound Engine',
            'slug' => 'muhajim',
            'tagline_ar' => 'محرك النمو والاستخبارات التسويقية للأعمال والوكالات',
            'tagline_en' => 'Automated Outbound Intelligence Engine',
            'description_ar' => 'أتمتة أبحاث العملاء المحتملين والتواصل المخصص عبر قنوات متعددة وفق أعلى معايير الحوكمة.',
            'description_en' => 'Automated lead research, prospect qualification, and smart email/messaging sequences.',
            'lifecycle_status' => 'active',
            'owner_id' => $owner->id,
            'repository_url' => 'https://github.com/novixa-dev/muhajim-engine',
            'website_url' => 'https://muhajim.novixa.dev',
            'demo_url' => 'https://demo.muhajim.novixa.dev',
            'documentation_url' => 'https://docs.novixa.dev/muhajim',
        ]);

        $prodServe = Product::create([
            'name_ar' => 'نوڤيكسا سيرف (Novixa Serve POS)',
            'name_en' => 'Novixa Serve Restaurant POS',
            'slug' => 'novixa-serve',
            'tagline_ar' => 'نظام نقاط البيع وإدارة الطلبات السريعة للمطاعم والمقاهي',
            'tagline_en' => 'Touch-first POS & Kitchen Display for F&B',
            'description_ar' => 'يعمل بدون اتصال بالإنترنت، ويدعم شاشات المطبخ، والطباعة الحرارية المباشرة باللغة العربية.',
            'description_en' => 'Offline-first POS with multi-printer thermal routing and kitchen display system.',
            'lifecycle_status' => 'beta',
            'owner_id' => $engineer->id,
            'repository_url' => 'https://github.com/novixa-dev/novixa-serve',
            'demo_url' => 'https://serve-demo.novixa.dev',
        ]);

        // 5. Clients & Contacts
        $clientDar = Client::create([
            'name_ar' => 'دار حضرموت العقارية',
            'name_en' => 'Dar Hadramout Real Estate',
            'type' => 'company',
            'industry' => 'Real Estate',
            'status' => 'active_client',
            'website' => 'https://darhadramout.com',
            'phone' => '+967 770 123 456',
            'email' => 'management@darhadramout.com',
            'city' => 'المكلا (Mukalla)',
            'country' => 'Yemen',
            'account_manager_id' => $admin->id,
            'notes' => 'أول عميل رائد لمنظومة نوڤيكسا عقار (باقة المؤسسات الاحترافية $2,500).',
        ]);

        $contactSaeed = Contact::create([
            'client_id' => $clientDar->id,
            'name' => 'المهندس سعيد الكندي',
            'job_title' => 'المدير العام والمالك',
            'email' => 'saeed@darhadramout.com',
            'phone' => '+967 771 987 654',
            'is_primary' => true,
            'notes' => 'المسؤول الرئيسي عن اعتماد المتطلبات الفنية والتدريب.',
        ]);

        $clientRayyan = Client::create([
            'name_ar' => 'شركة الريان للخدمات اللوجستية',
            'name_en' => 'Al-Rayyan Logistics & Transport',
            'type' => 'company',
            'industry' => 'Logistics & Cargo',
            'status' => 'prospect',
            'phone' => '+967 772 345 678',
            'email' => 'info@alrayyan-cargo.com',
            'city' => 'سيئون (Seiyun)',
            'country' => 'Yemen',
            'account_manager_id' => $owner->id,
            'notes' => 'مهتمون بنظام تتبع الشحنات والأسطول الداخلي.',
        ]);

        // 6. Opportunities
        $oppDar = Opportunity::create([
            'client_id' => $clientDar->id,
            'product_id' => $prodAqar->id,
            'title_ar' => 'ترخيص كود نوڤيكسا عقار باقة المؤسسات وتخصيص الهوية',
            'title_en' => 'Novixa Aqar Enterprise Code License & Customization',
            'stage' => 'won',
            'estimated_value' => 2500.00,
            'currency' => 'USD',
            'owner_id' => $admin->id,
            'expected_close_date' => now()->subDays(10),
            'source' => 'Direct Referral / إحالة مباشرة',
            'notes' => 'تم توقيع العقد واستلام الدفعة المقدمة 50%. جاري تنفيذ مشروع التسليم.',
        ]);

        $oppRayyan = Opportunity::create([
            'client_id' => $clientRayyan->id,
            'title_ar' => 'تطوير منصة تتبع الشحنات ولوحة إدارة السائقين',
            'title_en' => 'Cargo Tracking & Driver Dispatch Platform',
            'stage' => 'proposal',
            'estimated_value' => 3800.00,
            'currency' => 'USD',
            'owner_id' => $owner->id,
            'expected_close_date' => now()->addDays(20),
            'next_follow_up_date' => now()->addDays(2),
            'source' => 'Inbound Request / استفسار وارد',
            'notes' => 'تم إرسال العرض المالي والفني يوم الأربعاء. موعد المتابعة لمناقشة التفاصيل.',
        ]);

        // Client Interactions
        ClientInteraction::create([
            'client_id' => $clientDar->id,
            'contact_id' => $contactSaeed->id,
            'user_id' => $admin->id,
            'channel' => 'meeting',
            'summary' => 'اجتماع انطلاق المشروع في مقر المكتب بالمكلا واعتماد نموذج تخصيص هوية دار حضرموت.',
            'interaction_date' => now()->subDays(5),
            'next_follow_up_date' => now()->addDays(3),
        ]);

        ClientInteraction::create([
            'client_id' => $clientRayyan->id,
            'user_id' => $owner->id,
            'channel' => 'call',
            'summary' => 'مكالمة هاتفية مع مدير العمليات لمناقشة متطلبات الربط مع تطبيق واتساب لإشعارات المستلمين.',
            'interaction_date' => now()->subDays(1),
            'next_follow_up_date' => now()->addDays(2),
        ]);

        // 7. Projects
        $projAqarCore = Project::create([
            'code' => 'AQR-CORE',
            'name_ar' => 'تطوير الإصدار الثاني من نوڤيكسا عقار (V2)',
            'name_en' => 'Novixa Aqar V2 Core Platform',
            'description_ar' => 'ترقية المعمارية الأساسية لنوڤيكسا عقار لتشمل تسريع تحميل العقارات وعقود الإيجار المتقدمة وتصدير التقارير بصيغة PDF.',
            'description_en' => 'Core architectural upgrade for Novixa Aqar real estate system.',
            'type' => 'internal_product',
            'status' => 'active',
            'priority' => 'high',
            'lead_id' => $lead->id,
            'product_id' => $prodAqar->id,
            'start_date' => now()->subDays(20),
            'due_date' => now()->addDays(40),
            'progress_percentage' => 45,
        ]);

        $projDarDelivery = Project::create([
            'code' => 'DAR-DLV',
            'name_ar' => 'مشروع تخصيص ونشر دار حضرموت العقارية',
            'name_en' => 'Dar Hadramout Implementation & Delivery',
            'description_ar' => 'تهيئة السيرفر السحابي، وربط النطاق darhadramout.com، وتطبيق الهوية البصرية، واستيراد 120 عقار ووحدة أولية.',
            'description_en' => 'Custom branding, VPS deployment, and initial data migration for Dar Hadramout Real Estate.',
            'type' => 'client_delivery',
            'status' => 'active',
            'priority' => 'urgent',
            'lead_id' => $lead->id,
            'product_id' => $prodAqar->id,
            'client_id' => $clientDar->id,
            'start_date' => now()->subDays(7),
            'due_date' => now()->addDays(14),
            'progress_percentage' => 60,
        ]);

        $oppDar->update(['won_delivery_project_id' => $projDarDelivery->id]);

        // Project Memberships
        $projAqarCore->members()->attach([
            $lead->id => ['role' => 'lead', 'joined_at' => now()],
            $engineer->id => ['role' => 'contributor', 'joined_at' => now()],
            $designer->id => ['role' => 'contributor', 'joined_at' => now()],
        ]);

        $projDarDelivery->members()->attach([
            $lead->id => ['role' => 'lead', 'joined_at' => now()],
            $admin->id => ['role' => 'contributor', 'joined_at' => now()],
            $engineer->id => ['role' => 'contributor', 'joined_at' => now()],
            $guest->id => ['role' => 'reviewer', 'joined_at' => now()],
        ]);

        // Milestones
        $m1 = ProjectMilestone::create([
            'project_id' => $projDarDelivery->id,
            'title_ar' => 'المرحلة 1: تجهيز البنية السحابية وإعداد السيرفر',
            'title_en' => 'Phase 1: Cloud VPS Provisioning & SSL',
            'due_date' => now()->subDays(2),
            'status' => 'completed',
            'completed_at' => now()->subDays(2),
        ]);

        $m2 = ProjectMilestone::create([
            'project_id' => $projDarDelivery->id,
            'title_ar' => 'المرحلة 2: تخصيص الهوية البصرية واستيراد العقارات',
            'title_en' => 'Phase 2: Custom Branding & Data Seeding',
            'due_date' => now()->addDays(5),
            'status' => 'in_progress',
        ]);

        $m3 = ProjectMilestone::create([
            'project_id' => $projDarDelivery->id,
            'title_ar' => 'المرحلة 3: تدريب فريق المكتب والتسليم النهائي',
            'title_en' => 'Phase 3: Team Training & Final Handover',
            'due_date' => now()->addDays(14),
            'status' => 'pending',
        ]);

        // 8. Tasks & Subtasks
        $t1 = Task::create([
            'code' => 'NW-101',
            'title_ar' => 'تهيئة استضافة VPS وتفعيل شهادات SSL لنظام دار حضرموت',
            'title_en' => 'Provision VPS & Configure SSL for Dar Hadramout',
            'description_ar' => 'إعداد سيرفر Ubuntu 24.04 وتثبيت Nginx وDocker وحماية المنافذ وجدار الحماية UFW.',
            'description_en' => 'Setup Ubuntu 24.04, Nginx reverse proxy, and Let\'s Encrypt SSL certificates.',
            'project_id' => $projDarDelivery->id,
            'product_id' => $prodAqar->id,
            'client_id' => $clientDar->id,
            'milestone_id' => $m1->id,
            'status' => 'done',
            'priority' => 'high',
            'assignee_id' => $engineer->id,
            'reporter_id' => $lead->id,
            'is_my_day' => false,
            'start_date' => now()->subDays(6),
            'due_date' => now()->subDays(2),
            'completed_at' => now()->subDays(2),
            'kanban_order' => 1,
        ]);
        $t1->labels()->attach([$labelBackend->id, $labelSecurity->id, $labelClient->id]);

        $t2 = Task::create([
            'code' => 'NW-102',
            'title_ar' => 'تخصيص ألوان وشعار الهوية البصرية لدار حضرموت في الواجهة الأمامية',
            'title_en' => 'Apply Dar Hadramout Branding & Color Palette',
            'description_ar' => 'استبدال الشعار الافتراضي وضبط متغيرات CSS لتعكس هوية المكتب بالأزرق الملكي والذهبي.',
            'description_en' => 'Inject Dar Hadramout logos and CSS branding tokens in Nuxt application.',
            'project_id' => $projDarDelivery->id,
            'product_id' => $prodAqar->id,
            'client_id' => $clientDar->id,
            'milestone_id' => $m2->id,
            'status' => 'in_progress',
            'priority' => 'urgent',
            'assignee_id' => $designer->id,
            'reporter_id' => $lead->id,
            'is_my_day' => true,
            'start_date' => now()->subDays(2),
            'due_date' => now()->addDays(1),
            'kanban_order' => 1,
        ]);
        $t2->labels()->attach([$labelDesign->id, $labelFrontend->id, $labelClient->id]);

        $t3 = Task::create([
            'code' => 'NW-103',
            'title_ar' => 'استيراد سجلات الوحدات والعقود السابقة من ملف Excel',
            'title_en' => 'Migrate Legacy Property Records from Excel',
            'description_ar' => 'تنظيف بيانات 120 عقار ووحدة سكنية ومطابقة المستأجرين مع العقود النشطة.',
            'description_en' => 'Validate and import legacy records into the PostgreSQL database.',
            'project_id' => $projDarDelivery->id,
            'product_id' => $prodAqar->id,
            'client_id' => $clientDar->id,
            'milestone_id' => $m2->id,
            'status' => 'todo',
            'priority' => 'high',
            'assignee_id' => $engineer->id,
            'reporter_id' => $lead->id,
            'is_my_day' => true,
            'due_date' => now()->addDays(3),
            'kanban_order' => 1,
        ]);
        $t3->labels()->attach([$labelBackend->id, $labelClient->id]);

        // Dependency: t3 depends on t2 (design/branding must be ready before demoing imported data)
        $t3->dependencies()->attach($t2->id, ['type' => 'blocks']);

        $t4 = Task::create([
            'code' => 'NW-104',
            'title_ar' => 'تطوير آلية تصدير سندات القبض بصيغة PDF باللغة العربية',
            'title_en' => 'Build Arabic PDF Payment Receipt Generator',
            'description_ar' => 'إنشاء قالب سند قبض رسمي يحتوي على رقم السند والمستأجر والمبلغ بالريال اليمني والسعودي مع تفقيط المبلغ كتابة.',
            'description_en' => 'Generate printable official payment receipts in Arabic with proper typography.',
            'project_id' => $projAqarCore->id,
            'product_id' => $prodAqar->id,
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee_id' => $engineer->id,
            'reporter_id' => $lead->id,
            'is_my_day' => true,
            'due_date' => now()->addDays(4),
            'kanban_order' => 2,
        ]);
        $t4->labels()->attach([$labelBackend->id]);

        $t5 = Task::create([
            'code' => 'NW-105',
            'title_ar' => 'فحص أمان الجلسات وحماية الروابط ضد ثغرات IDOR',
            'title_en' => 'Session Security Audit & IDOR Protection Check',
            'description_ar' => 'التأكد من أن جميع سياسات Laravel تمنع تحميل مرفقات أو بيانات مشاريع غير مصرح بها.',
            'description_en' => 'Verify that all policies reject direct object references for unauthorized users.',
            'project_id' => $projAqarCore->id,
            'status' => 'todo',
            'priority' => 'urgent',
            'assignee_id' => $lead->id,
            'reporter_id' => $owner->id,
            'is_my_day' => false,
            'due_date' => now()->addDays(5),
            'kanban_order' => 2,
        ]);
        $t5->labels()->attach([$labelSecurity->id]);

        // Overdue task for testing alerts
        $tOverdue = Task::create([
            'code' => 'NW-099',
            'title_ar' => 'مراجعة شروط ترخيص البرمجيات وتوثيق العقود المعتمدة',
            'title_en' => 'Review Software License Terms Documentation',
            'description_ar' => 'تحديث بنود اتفاقية ترخيص كود المصدر لنوڤيكسا عقار وفق القوانين التجارية المحلية.',
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee_id' => $owner->id,
            'reporter_id' => $admin->id,
            'is_my_day' => false,
            'due_date' => now()->subDays(3), // Overdue!
            'kanban_order' => 3,
        ]);

        // Comments
        TaskComment::create([
            'task_id' => $t2->id,
            'user_id' => $designer->id,
            'comment' => 'تم استخراج لوحة الألوان المعتمدة من شعار دار حضرموت، وتم إنشاء المتغيرات الخاصة بـ Tailwind.',
        ]);

        TaskComment::create([
            'task_id' => $t2->id,
            'user_id' => $lead->id,
            'comment' => 'ممتاز جداً يا فاطمة، يرجى التأكد من ظهور الشعار بوضوح في كلا الوضعين الليلي والنهاري.',
        ]);

        // 9. Notifications
        Notification::create([
            'user_id' => $designer->id,
            'type' => 'task.assigned',
            'title_ar' => 'تم تعيين مهمة جديدة لك: تخصيص الهوية البصرية',
            'title_en' => 'New task assigned: Apply Dar Hadramout Branding',
            'body_ar' => 'قام طارق باوزير بتعيينك للمهمة NW-102 ضمن مشروع دار حضرموت.',
            'body_en' => 'Tariq Ba-Wazir assigned you to NW-102 in Dar Hadramout project.',
            'link' => '/tasks?code=NW-102',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $owner->id,
            'type' => 'deal.won',
            'title_ar' => 'صفقة رابحة جديدة! 🎉 دار حضرموت العقارية ($2,500)',
            'title_en' => 'Deal Won! 🎉 Dar Hadramout Real Estate ($2,500)',
            'body_ar' => 'تم إغلاق الصفقة بنجاح وتحويلها لمشروع تسليم DAR-DLV.',
            'body_en' => 'Opportunity won and converted to delivery project DAR-DLV.',
            'link' => '/projects?code=DAR-DLV',
            'is_read' => false,
        ]);

        // 10. Knowledge Base SOPs
        KnowledgePage::create([
            'title_ar' => 'إجراءات تشغيل ونشر الأنظمة السحابية (Cloud Deployment SOP)',
            'title_en' => 'Cloud Deployment & Server Hardening SOP',
            'slug' => 'cloud-deployment-sop',
            'category' => 'sop',
            'content_ar' => "## معايير نشر وتثبيت أنظمة نوڤيكسا\n\n1. **إعداد الخادم:** استخدام نظام Ubuntu 24.04 LTS مع تفعيل جدار الحماية UFW والسماح بالمنافذ 22, 80, 443 فقط.\n2. **الحاويات:** تشغيل التطبيقات عبر Docker Compose مع عزل قاعدة بيانات PostgreSQL داخل شبكة Docker الداخلية.\n3. **شهادات الأمان:** تفعيل Let's Encrypt SSL مع التجديد التلقائي عبر Nginx.\n4. **النسخ الاحتياطي:** تشغيل سكريبت `backup.sh` يومياً في تمام الساعة 2:00 صباحاً بتوقيت مكة المكرمة مع تشفير النسخة وتخزينها خارج السيرفر.",
            'content_en' => 'Standard Operating Procedure for deploying Novixa services using Docker Compose, Nginx SSL, and isolated PostgreSQL databases.',
            'author_id' => $lead->id,
            'is_published' => true,
        ]);

        KnowledgePage::create([
            'title_ar' => 'دليل تصميم واجهات الويب ثنائية الاتجاه (Arabic RTL / LTR Guidelines)',
            'title_en' => 'Bilingual RTL/LTR Design & Engineering Guidelines',
            'slug' => 'bilingual-rtl-guidelines',
            'category' => 'architecture',
            'content_ar' => "## إرشادات التجربة العربية الأصيلة\n\n1. **الخصائص المنطقية:** استخدام خصائص CSS Logical Properties (مثل `ms-*`, `me-*`, `ps-*`, `pe-*`) لتجنب عكس المواضع يدوياً.\n2. **الخطوط المعتمدة:** استخدام خط IBM Plex Sans Arabic أو Readex Pro لواجهات المستخدم.\n3. **الأرقام والأكواد:** الحفاظ على اتجاه الأكواد البرمجية (مثل `NW-101`) وأرقام الهواتف من اليسار لليمين `dir=\"ltr\"` لضمان صحة القراءة.",
            'content_en' => 'Engineering guidelines for clean bidirectional Arabic-first and English interfaces.',
            'author_id' => $designer->id,
            'is_published' => true,
        ]);

        // 11. Initial Audit Log
        AuditLog::log('system.seed', null, [
            'message' => 'Initial database seed completed with authentic Novixa products, clients, projects, and users.',
            'users_count' => 6,
            'products_count' => 3,
            'projects_count' => 2,
        ], $owner->id);
    }
}
