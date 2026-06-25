<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'question_ar',
        'question_ne',
        'answer',
        'answer_ar',
        'answer_ne',
        'status',
    ];

    public function getQuestionAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"question_{$locale}"} ?? $value;
        }
        return $value;
    }

    public function getAnswerAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"answer_{$locale}"} ?? $value;
        }
        return $value;
    }
}
