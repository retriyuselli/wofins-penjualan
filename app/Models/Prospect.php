<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Prospect extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name_event',
        'name_cpp',
        'name_cpw',
        'address',
        'phone',
        'date_lamaran',
        'time_lamaran',
        'venue_lamaran',
        'date_akad',
        'time_akad',
        'venue_akad',
        'date_resepsi',
        'time_resepsi',
        'venue',
        'date_pengajian',
        'time_pengajian',
        'venue_pengajian',
        'date_ngunduh_mantu',
        'time_ngunduh_mantu',
        'venue_ngunduh_mantu',
        'total_penawaran',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date_lamaran' => 'date',
        'date_akad' => 'date',
        'date_resepsi' => 'date',
        'date_pengajian' => 'date',
        'date_ngunduh_mantu' => 'date',
        'time_lamaran' => 'datetime:H:i:s',
        'time_akad' => 'datetime:H:i:s',
        'time_resepsi' => 'datetime:H:i:s',
        'time_pengajian' => 'datetime:H:i:s',
        'time_ngunduh_mantu' => 'datetime:H:i:s',
        'total_penawaran' => 'integer',
    ];

    /**
     * Urutan jadwal sama dengan Pasal 2 (Waktu Kegiatan).
     *
     * @return list<array{key: string, label: string, date: string, time: string, venue: string}>
     */
    public static function pasal2EventDefinitions(): array
    {
        return [
            ['key' => 'lamaran', 'label' => 'Lamaran', 'date' => 'date_lamaran', 'time' => 'time_lamaran', 'venue' => 'venue_lamaran'],
            ['key' => 'pengajian', 'label' => 'Pengajian', 'date' => 'date_pengajian', 'time' => 'time_pengajian', 'venue' => 'venue_pengajian'],
            ['key' => 'akad', 'label' => 'Akad / Pemberkatan', 'date' => 'date_akad', 'time' => 'time_akad', 'venue' => 'venue_akad'],
            ['key' => 'resepsi', 'label' => 'Resepsi', 'date' => 'date_resepsi', 'time' => 'time_resepsi', 'venue' => 'venue'],
            ['key' => 'ngunduh_mantu', 'label' => 'Ngunduh Mantu', 'date' => 'date_ngunduh_mantu', 'time' => 'time_ngunduh_mantu', 'venue' => 'venue_ngunduh_mantu'],
        ];
    }

    /**
     * @return list<array{key: string, label: string, date: string, time: ?string, location: ?string, detail: string}>
     */
    public function filledPasal2Events(string $dateFormat = 'd F Y'): array
    {
        $events = [];

        foreach (self::pasal2EventDefinitions() as $event) {
            $date = $this->{$event['date']};

            if (! filled($date)) {
                continue;
            }

            try {
                $parsed = $date instanceof Carbon ? $date : Carbon::parse($date);
            } catch (Exception) {
                continue;
            }

            if ($parsed->year < 1990) {
                continue;
            }

            $location = $this->{$event['venue']};
            $timeLabel = $this->formatPasal2Clock($this->{$event['time']});
            $dateLabel = $parsed->copy()->locale('id')->translatedFormat($dateFormat);
            $parts = array_values(array_filter([
                $dateLabel,
                $timeLabel,
                filled($location) ? (string) $location : null,
            ]));

            $events[] = [
                'key' => $event['key'],
                'label' => $this->pasal2EventLabel($event),
                'date' => $dateLabel,
                'time' => $timeLabel,
                'location' => filled($location) ? (string) $location : null,
                'detail' => implode(' · ', $parts),
            ];
        }

        return $events;
    }

    /**
     * @param  array{key: string, label: string}  $event
     */
    private function pasal2EventLabel(array $event): string
    {
        if ($event['key'] !== 'akad') {
            return $event['label'];
        }

        $name = mb_strtolower((string) $this->name_event);

        if (str_contains($name, 'pemberkatan') && ! str_contains($name, 'akad')) {
            return 'Pemberkatan';
        }

        return $event['label'];
    }

    private function formatPasal2Clock(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $parsed = $value instanceof Carbon ? $value : Carbon::parse($value);

            if ($parsed->format('H:i') === '00:00') {
                return null;
            }

            return 'pukul '.$parsed->format('H.i');
        } catch (Exception) {
            return null;
        }
    }

    protected static function boot()
    {
        parent::boot();

        // Prevent deletion if prospect has associated orders
        static::deleting(function ($prospect) {
            if ($prospect->orders()->exists()) {
                throw new Exception("Cannot delete prospect '{$prospect->name_event}' because it has associated orders.");
            }
        });
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_event', 'name_cpp', 'name_cpw', 'phone', 'date_akad', 'date_resepsi', 'date_lamaran', 'date_pengajian', 'date_ngunduh_mantu', 'venue'])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}")
            ->useLogName('prospect');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
