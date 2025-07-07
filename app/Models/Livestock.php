<?php

namespace App\Models;

use App\Enum\OriginLivestock;
use App\Enum\StatusLivestock;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Livestock extends Model
{
    use HasFactory;
    use HasUuids;

    protected $appends = ['age_in_year', 'age_in_month'];
    protected $fillable = [
        'name', 'birthdate', 'sex', 'purchase_date', 'origin', 'status', 'tag_type',
        'tag_id', 'birth_weight', 'weight', 'breed_id', 'male_parent_id', 'female_parent_id',
        'farm_id', 'barter_livestock_id', 'barter_from', 'barter_date', 'photo', 'purchase_price',
    ];

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origin' => OriginLivestock::class,
            'status' => StatusLivestock::class,
            'photo' => 'json',
        ];
    }

    protected function purchaseDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? null : Carbon::parse($value)->toDateString(),
        );
    }

    protected function birthdate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? null : Carbon::parse($value)->toDateString(),
        );
    }

    /**
     * Get the age of animal in yeear
     */
    protected function ageInYear(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = now();
                $birthDate = Carbon::parse($this->birthdate);
                return (int) $birthDate->diffInYears($now);
            },
        );
    }

    /**
     * Get the age of animal in yeear
     */
    protected function ageInMonth(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = now();
                $birthDate = Carbon::parse($this->birthdate);
                return (int) $birthDate->diffInMonths($now);
            },
        );
    }

    public static function pedigree(string $id)
    {
        DB::statement("SET @nomor=0");
        $sql = "WITH RECURSIVE FamilyTree AS (
            SELECT
                @nomor:=@nomor+1 AS nomor,
                l.id,
                l.name,
                female_parent_id,
                male_parent_id,
                breed_id,
                sex,
                b.name breed_name,
                l.photo,
                0 AS depth,
                CAST(@nomor AS CHAR(100)) AS path
            FROM
                livestocks l
            JOIN breeds b ON b.id = l.breed_id
            WHERE
                l.id = ?

            UNION ALL

            SELECT
                @nomor:=@nomor+1 AS nomor,
                parent.id,
                parent.name,
                parent.female_parent_id,
                parent.male_parent_id,
                parent.breed_id,
                parent.sex,
                b.name breed_name,
                parent.photo,
                child.depth + 1 AS depth,
                CONCAT(child.path, ',', @nomor) AS path
            FROM
                livestocks parent
            INNER JOIN
                FamilyTree child ON parent.id = child.female_parent_id OR parent.id = child.male_parent_id
            JOIN breeds b ON b.id = parent.breed_id
            WHERE
                child.depth < 3
        )

        SELECT * FROM FamilyTree ORDER BY depth, female_parent_id, male_parent_id, path;
        ";

        return DB::select($sql, [$id]);
    }

    public static function generateAifarmId(int $number): string
    {
        if ($number < 0) {
            return '-' . self::geenerateAifarmId(-$number);
        }

        $digits = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';

        do {
            $remainder = $number % 36;
            $result = $digits[$remainder] . $result;
            $number = floor($number / 36);
        } while ($number > 0);

        return str_pad($result, 6, '0', STR_PAD_LEFT);
    }

    public function milkingSummaries(): HasMany
    {
        return $this->hasMany(MilkingSummary::class);
    }

    public function maleParent(): BelongsTo
    {
        return $this->belongsTo(Livestock::class, 'male_parent_id')->withDefault();
    }

    public function femaleParent(): BelongsTo
    {
        return $this->belongsTo(Livestock::class, 'female_parent_id')->withDefault();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->aifarm_id = generateAifarmId();
        });
    }
}
