# ប្រព័ន្ធគ្រប់គ្រងសាលារៀន

ប្រព័ន្ធនេះបង្កើតដោយ Laravel សម្រាប់គ្រប់គ្រងព័ត៌មានសិស្ស គ្រូ ថ្នាក់ មុខវិជ្ជា វត្តមាន ពិន្ទុ ការបង់ថ្លៃសិក្សា និងរបាយការណ៍។

## មុខងារសំខាន់ៗ

- ចូលប្រើប្រព័ន្ធ និងចាកចេញតាមតួនាទី
- ផ្ទាំងគ្រប់គ្រងសង្ខេបស្ថិតិ
- គ្រប់គ្រងសិស្ស គ្រូ ថ្នាក់ មុខវិជ្ជា
- កត់ត្រាវត្តមាន/អវត្តមាន
- បង្កើតការប្រឡង និងបញ្ចូលពិន្ទុ
- កត់ត្រាការបង់ប្រាក់ និងបោះពុម្ពបង្កាន់ដៃ
- បង់ថ្លៃសិក្សា KHQR Bakong និងបញ្ជាក់តាម Bakong MD5 ឬបញ្ជាក់ប្រាក់ផ្ទាល់
- របាយការណ៍សិស្ស គ្រូ វត្តមាន ពិន្ទុ ការបង់ប្រាក់ និងចំណូលប្រចាំខែ
- នាំចេញរបាយការណ៍ជា CSV ដែល Excel អាចបើកបាន និងទំព័របោះពុម្ព/PDF
- គ្រប់គ្រងអ្នកប្រើប្រាស់តាមតួនាទី Admin, Teacher, Accountant, Student, Student/Parent
- សិស្ស និងគ្រូអាចស្នើបង្កើតគណនីដោយខ្លួនឯង ហើយ Admin ជាអ្នកអនុម័ត

## គណនីសាកល្បង

ពាក្យសម្ងាត់គ្រប់គណនី៖ `password`

| តួនាទី | អ៊ីមែល |
| --- | --- |
| អ្នកគ្រប់គ្រង | `admin@sms.test` |
| គ្រូ | `teacher@sms.test` |
| គណនេយ្យករ | `accountant@sms.test` |
| សិស្ស/អាណាព្យាបាល | `parent@sms.test` |

## គណនី និងសិទ្ធិ

- សិស្ស និងគ្រូអាចចូលទៅ `/register` ដើម្បីស្នើបង្កើតគណនីដោយប្រើលេខសម្គាល់ `student_code` ឬ `teacher_code`។
- គណនីដែលស្នើដោយខ្លួនឯងនឹងមានស្ថានភាព `ផ្អាក` ជាមុន។ Admin ត្រូវចូលទៅ **អ្នកប្រើប្រាស់** ហើយបើក `អនុញ្ញាតឱ្យចូលប្រើ`។
- Role `student` អាចមើលតែព័ត៌មានសិស្ស វត្តមាន បង្កាន់ដៃ និងរបាយការណ៍របស់ខ្លួន។
- Role `teacher` អាចមើលសិស្ស/គ្រូ/ថ្នាក់/មុខវិជ្ជា និងគ្រប់គ្រងវត្តមាន ការប្រឡង ពិន្ទុតាមសិទ្ធិដែលកំណត់។
- Admin បង្កើត/import/edit/delete សិស្ស គ្រូ និងកំណត់ role/status របស់អ្នកប្រើប្រាស់។

## របៀបដំណើរការ

```bash
composer install
php artisan migrate:fresh --seed
php artisan serve
```

បើប្រើ MySQL ក្នុង XAMPP សូមកែ `.env`៖

For Vercel / MongoDB Atlas:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-app-key
APP_URL=https://your-vercel-domain.vercel.app

DB_CONNECTION=mongodb
DB_URI=mongodb+srv://USER:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority
DB_DATABASE=school_management_system

SESSION_DRIVER=cookie
CACHE_STORE=array
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
```

Vercel uses `api/index.php` with the community `vercel-php` runtime. Keep `.env` out of Git and configure Atlas through Vercel environment variables.

បន្ទាប់មកបង្កើត database ឈ្មោះ `school_management_system` ហើយរត់ `php artisan migrate:fresh --seed` ម្តងទៀត។

## KHQR Bakong

កំណត់តម្លៃខាងក្រោមក្នុង `.env`៖

```env
KHQR_API_TOKEN=
KHQR_BAKONG_ACCOUNT_ID=vuthy_reaksa@bkrt
KHQR_ACCOUNT_NAME="VUTHY REAKSA"
KHQR_MERCHANT_CITY="PHNOM PENH"
KHQR_CURRENCY=USD
KHQR_DYNAMIC_QR_EXPIRES_IN=600
```

Student/Parent អាចចូលទៅ **បង់ប្រាក់** → **បង់ថ្លៃសិក្សា KHQR** ដើម្បីបង្កើត QR។ បន្ទាប់ពីស្កេនបង់ប្រាក់ អាចចុច **ពិនិត្យ Bakong** ដើម្បីបញ្ជាក់តាម MD5។ Admin/Accountant ក៏អាចចុច **បញ្ជាក់ប្រាក់ផ្ទាល់** ប្រសិនបើបានទទួលប្រាក់ផ្ទាល់។

QR ថ្មីនឹងមាន expiration timestamp នៅក្នុង KHQR payload និងកំណត់អតិបរមា 10 នាទីតាមការណែនាំ Bakong dynamic QR។
