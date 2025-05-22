<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Models\Recipe;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $protein_amount
 * @property float|null $fat_amount
 * @property float|null $carbohydrates_amount
 * @property float|null $amount_per_day
 * @property float|null $height
 * @property float|null $weight
 * @property float|null $age
 * @property string|null $sex
 * @property string|null $activity
 * @property string|null $way
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAmountPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCarbohydratesAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFatAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProteinAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWeight($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        'protein_amount',
        'fat_amount',
        'carbohydrates_amount',

        'amount_per_day',
        'height',
        'weight',
        'activity',
        'age',
        'way',
        'sex',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mealPlan() {
        $this->belongsToMany(Recipe::class, 'meal_plan')
            ->withPivot('day', 'time')
            ->withTimestamps()
            ->orderBy('day')
            ->orderBy('time');
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Проверка обязательных полей
            if (
                is_null($user->weight) ||
                is_null($user->height) ||
                is_null($user->age) ||
                is_null($user->sex) ||
                is_null($user->activity) ||
                is_null($user->way)
            ) {
                return;
            }

            // Базовая калорийность (BMR) по формуле Mifflin – St Jeor
            $bmr = $user->sex === 'male'
                ? 10 * $user->weight + 6.25 * $user->height - 5 * $user->age + 5
                : 10 * $user->weight + 6.25 * $user->height - 5 * $user->age - 161;

            // Коэффициенты активности
            $activityLevels = [
                'low' => 1.2,
                'light' => 1.375,
                'moderate' => 1.55,
                'high' => 1.725,
                'very_high' => 1.9,
            ];

            $activityFactor = $activityLevels[$user->activity] ?? 1.2;

            // Итоговая калорийность (TDEE)
            $tdee = $bmr * $activityFactor;

            // Соотношение БЖУ по цели
            $ratios = match (Str::lower($user->way)) {
                'lose_weight' => ['p' => 0.4, 'f' => 0.3, 'c' => 0.3],
                'gain_weight' => ['p' => 0.25, 'f' => 0.25, 'c' => 0.5],
                default => ['p' => 0.3, 'f' => 0.3, 'c' => 0.4],
            };

            // Расчёт грамм
            $user->amount_per_day = round($tdee); // калории
            $user->protein_amount = round(($tdee * $ratios['p']) / 4, 1); // белки
            $user->fat_amount = round(($tdee * $ratios['f']) / 9, 1);     // жиры
            $user->carbohydrates_amount = round(($tdee * $ratios['c']) / 4, 1); // углеводы
        });
    }
}
