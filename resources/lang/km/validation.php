<?php

return [
    'required' => 'វាល :attribute ត្រូវតែបំពេញ។',
    'email' => 'វាល :attribute ត្រូវតែជាអ៊ីមែលត្រឹមត្រូវ។',
    'unique' => 'វាល :attribute មានរួចហើយ។',
    'exists' => 'វាល :attribute មិនត្រឹមត្រូវ។',
    'date' => 'វាល :attribute ត្រូវតែជាកាលបរិច្ឆេទត្រឹមត្រូវ។',
    'numeric' => 'វាល :attribute ត្រូវតែជាចំនួនលេខ។',
    'integer' => 'វាល :attribute ត្រូវតែជាចំនួនគត់។',
    'min' => [
        'numeric' => 'វាល :attribute ត្រូវតែយ៉ាងហោចណាស់ :min។',
        'string' => 'វាល :attribute ត្រូវតែមានយ៉ាងហោចណាស់ :min តួអក្សរ។',
    ],
    'max' => [
        'numeric' => 'វាល :attribute មិនអាចលើស :max បានទេ។',
        'string' => 'វាល :attribute មិនអាចលើស :max តួអក្សរ បានទេ។',
    ],
    'in' => 'វាល :attribute មិនត្រឹមត្រូវ។',
    'boolean' => 'វាល :attribute ត្រូវតែជាតម្លៃពិត ឬមិនពិត។',

    'attributes' => [
        'name' => 'ឈ្មោះ',
        'email' => 'អ៊ីមែល',
        'password' => 'ពាក្យសម្ងាត់',
        'role' => 'តួនាទី',
        'student_code' => 'លេខសម្គាល់សិស្ស',
        'teacher_code' => 'លេខសម្គាល់គ្រូ',
        'first_name' => 'នាមខ្លួន',
        'last_name' => 'នាមត្រកូល',
        'class_id' => 'ថ្នាក់',
        'subject_id' => 'មុខវិជ្ជា',
        'attendance_date' => 'កាលបរិច្ឆេទវត្តមាន',
        'status' => 'ស្ថានភាព',
        'amount' => 'ទឹកប្រាក់',
        'receipt_no' => 'លេខបង្កាន់ដៃ',
    ],
];
