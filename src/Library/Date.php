<?php

namespace okushnirov\core\Library;

use JetBrains\PhpStorm\ArrayShape;
use okushnirov\core\Library\{Enums\DateEn, Interfaces\DateFormat};

final class Date
{
  public static function getAge(string $birthday):bool | int
  {
    if ('' === $birthday) {
      
      return false;
    }
    
    $birthdayTimestamp = strtotime($birthday);
    
    if (false === $birthdayTimestamp) {
      
      return false;
    }
    
    $age = (int)date('Y') - (int)date('Y', $birthdayTimestamp);
    
    return date('md', $birthdayTimestamp) > date('md') ? $age - 1 : $age;
  }
  
  public static function getDateProp(string $lang = 'uk'):string
  {
    if ('en' === $lang) {
      
      return date('F j, Y');
    }
    
    $month = match ($lang) {
      'ru' => [
        'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'декабря'
      ],
      'de' => [
        'Januar',
        'Februar',
        'März',
        'April',
        'Mai',
        'Juni',
        'Juli',
        'August',
        'September',
        'Oktober',
        'November',
        'Dezember'
      ],
      'pl' => [
        'stycznia',
        'lutego',
        'marca',
        'kwietnia',
        'maja',
        'czerwca',
        'lipca',
        'sierpnia',
        'września',
        'października',
        'listopada',
        'grudnia'
      ],
      default => [
        'січня',
        'лютого',
        'березня',
        'квітня',
        'травня',
        'червня',
        'липня',
        'серпня',
        'вересня',
        'жовтня',
        'листопада',
        'грудня'
      ]
    };
    
    $ltr_year = match ($lang) {
      'ru' => 'года',
      'de' => 'Jahr',
      'pl' => 'rok',
      default => 'року'
    };
    
    $monthIndex = (int)date('n') - 1;
    
    return date('j').' '.$month[$monthIndex].' '.date('Y').' '.$ltr_year;
  }
  
  public static function getFirstQuarterDay(int $monthNumber, ?int $year = null):string
  {
    if (1 > $monthNumber || 12 < $monthNumber) {
      $monthNumber = (int)date('n');
    }
    
    $targetYear = $year ?? (int)date('Y');
    
    return match (intval(($monthNumber + 2) / 3)) {
      1 => $targetYear.'-01-01',
      2 => $targetYear.'-04-01',
      3 => $targetYear.'-07-01',
      4 => $targetYear.'-10-01',
      default => ''
    };
  }
  
  public static function getMonthName(int $month, string $lang):string
  {
    $targetMonth = $month < 1 || $month > 12 ? (int)date('m') : $month;
    
    $names = match ($lang) {
      'ru' => [
        1 => "Январь",
        "Февраль",
        "Март",
        "Апрель",
        "Май",
        "Июнь",
        "Июль",
        "Август",
        "Сентябрь",
        "Октябрь",
        "Ноябрь",
        "Декабрь"
      ],
      'en' => [
        1 => "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
      ],
      default => [
        1 => "Січень",
        "Лютий",
        "Березень",
        "Квітень",
        "Травень",
        "Червень",
        "Липень",
        "Серпень",
        "Вересень",
        "Жовтень",
        "Листопад",
        "Грудень"
      ],
    };
    
    return $names[$targetMonth] ?? '';
  }
  
  public static function getPeriod():array
  {
    $today = date('Y-m-d');
    $currentYear = date('Y');
    $prevYear = (int)$currentYear - 1;
    
    return [
      'периодСегодня' => [
        'periodFrom' => $today,
        'periodTo' => $today,
        'label' => [
          'ru' => 'За сегодня',
          'ua' => 'За сьогодні',
          'uk' => 'За сьогодні'
        ]
      ],
      'периодНеделя' => [
        'periodFrom' => date('Y-m-d', strtotime('monday this week')),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За текущую неделю',
          'ua' => 'За поточний тиждень',
          'uk' => 'За поточний тиждень'
        ]
      ],
      'периодПрошлаяНеделя' => [
        'periodFrom' => date('Y-m-d', strtotime('monday previous week')),
        'periodTo' => date('Y-m-d', strtotime('sunday previous week')),
        'label' => [
          'ru' => 'За прошлую неделю',
          'ua' => 'За минулий тиждень',
          'uk' => 'За минулий тиждень'
        ]
      ],
      'период10Дней' => [
        'periodFrom' => date('Y-m-d', strtotime('-10 day')),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За последние 10 дней',
          'ua' => 'За останній 10 днів',
          'uk' => 'За останній 10 днів'
        ]
      ],
      'период30Дней' => [
        'periodFrom' => date('Y-m-d', strtotime('-30 day')),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За последние 30 дней',
          'ua' => 'За останні 30 днів',
          'uk' => 'За останні 30 днів'
        ]
      ],
      'периодМесяц' => [
        'periodFrom' => date('Y-m-01'),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За текущий month',
          'ua' => 'За поточний місяць',
          'uk' => 'За поточний місяць'
        ]
      ],
      'периодПрошлыйМесяц' => [
        'periodFrom' => date("Y-m-01", strtotime("first day of previous month")),
        'periodTo' => date('Y-m-t', strtotime("last day of previous month")),
        'label' => [
          'ru' => 'За прошлый месяц',
          'ua' => 'За минулий місяць',
          'uk' => 'За минулий місяць'
        ]
      ],
      'периодКвартал' => [
        'periodFrom' => self::getFirstQuarterDay((int)date('n')),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За текущий квартал',
          'ua' => 'За поточний квартал',
          'uk' => 'За поточний квартал'
        ]
      ],
      'период12Месяцев' => [
        'periodFrom' => date('Y-m-d', strtotime('-1 year')),
        'periodTo' => $today,
        'label' => [
          'ru' => 'За последние 12 месяцев',
          'ua' => 'За останні 12 місяців',
          'uk' => 'За останні 12 місяців'
        ]
      ],
      'периодГод' => [
        'periodFrom' => "$currentYear-01-01",
        'periodTo' => $today,
        'label' => [
          'ru' => 'За текущий год',
          'ua' => 'За поточний рік',
          'uk' => 'За поточний рік'
        ]
      ],
      'периодПрошлыйГод' => [
        'periodFrom' => "$prevYear-01-01",
        'periodTo' => "$prevYear-12-31",
        'label' => [
          'ru' => 'За прошлый год',
          'ua' => 'За минулий рік',
          'uk' => 'За минулий рік'
        ]
      ],
      'периодВыбранный' => [
        'label' => [
          'ru' => 'Выбранный период',
          'ua' => 'Обраний період',
          'uk' => 'Обраний період'
        ]
      ]
    ];
  }
  
  public static function getPeriodObject():array
  {
    $todayStr = date('Y-m-d');
    $yesterday = (new \DateTime('yesterday'))->format('Y-m-d');
    $monthPreviousFrom = new \DateTime('first day of previous month');
    $month2AgoFrom = (new \DateTime('first day of this month'))->modify('-2 month');
    $month3AgoFrom = (new \DateTime('first day of this month'))->modify('-3 month');
    $year2AgoFrom = new \DateTime('first day of 2 years ago');
    
    $labelCurrent = [
      'ua' => 'Поточний',
      'uk' => 'Поточний',
      'ru' => 'Текущий',
      'en' => 'Current'
    ];
    
    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');
    $currentQuarter = intval(($currentMonth + 2) / 3);
    
    $prevQuarter = $currentQuarter - 1;
    $prevQuarterYear = $currentYear;
    if ($prevQuarter === 0) {
      $prevQuarter = 4;
      $prevQuarterYear--;
    }
    
    $pqStartMonths = [
      1 => '01-01',
      2 => '04-01',
      3 => '07-01',
      4 => '10-01'
    ];
    $pqEndMonths = [
      1 => '03-31',
      2 => '06-30',
      3 => '09-30',
      4 => '12-31'
    ];
    
    $pqFrom = $prevQuarterYear.'-'.$pqStartMonths[$prevQuarter];
    $pqTo = $prevQuarterYear.'-'.$pqEndMonths[$prevQuarter];
    
    return [
      'Сегодня' => [
        'from' => $todayStr,
        'to' => $todayStr,
        'label' => [
          'ua' => 'Сьогодні',
          'uk' => 'Сьогодні',
          'ru' => 'Сегодня',
          'en' => 'Today'
        ]
      ],
      'Вчера' => [
        'from' => $yesterday,
        'to' => $yesterday,
        'label' => [
          'ua' => 'Вчора',
          'uk' => 'Вчора',
          'ru' => 'Вчера',
          'en' => 'Yesterday'
        ]
      ],
      'Неделя' => [
        'from' => (new \DateTime('monday this week'))->format('Y-m-d'),
        'to' => $todayStr,
        'label' => $labelCurrent
      ],
      'ПрошлаяНеделя' => [
        'from' => (new \DateTime('monday previous week'))->format('Y-m-d'),
        'to' => (new \DateTime('sunday previous week'))->format('Y-m-d'),
        'label' => [
          'ua' => 'Попередній',
          'uk' => 'Попередній',
          'ru' => 'Предыдущая',
          'en' => 'Previous'
        ]
      ],
      '30Дней' => [
        'from' => date('Y-m-d', strtotime('-30 day')),
        'to' => $todayStr,
        'label' => [
          'ua' => '30 днів',
          'uk' => '30 днів',
          'ru' => '30 дней',
          'en' => '30 days before'
        ]
      ],
      'Месяц' => [
        'from' => date('Y-m-01'),
        'to' => $todayStr,
        'label' => $labelCurrent
      ],
      'ПрошлыйМесяц' => [
        'from' => $monthPreviousFrom->format('Y-m-d'),
        'to' => (clone $monthPreviousFrom)->format('Y-m-t'),
        'label' => self::getPeriodLabel($monthPreviousFrom)
      ],
      'ПозапрошлыйМесяц' => [
        'from' => $month2AgoFrom->format('Y-m-d'),
        'to' => (clone $month2AgoFrom)->format('Y-m-t'),
        'label' => self::getPeriodLabel($month2AgoFrom)
      ],
      'ЗаПозапрошлыйМесяц' => [
        'from' => $month3AgoFrom->format('Y-m-d'),
        'to' => (clone $month3AgoFrom)->format('Y-m-t'),
        'label' => self::getPeriodLabel($month3AgoFrom)
      ],
      'Квартал' => [
        'from' => self::getFirstQuarterDay($currentMonth, $currentYear),
        'to' => $todayStr,
        'label' => $labelCurrent
      ],
      'ПрошлыйКвартал' => [
        'from' => $pqFrom,
        'to' => $pqTo,
        'label' => [
          'ua' => "Q$prevQuarter",
          'uk' => "Q$prevQuarter",
          'ru' => "Q$prevQuarter",
          'en' => "Q$prevQuarter"
        ]
      ],
      'Год' => [
        'from' => "$currentYear-01-01",
        'to' => $todayStr,
        'label' => $labelCurrent
      ],
      'ПрошлыйГод' => [
        'from' => ($currentYear - 1).'-01-01',
        'to' => ($currentYear - 1).'-12-31',
        'label' => [
          'ua' => ($currentYear - 1).' рік',
          'uk' => ($currentYear - 1).' рік',
          'ru' => ($currentYear - 1).' год',
          'en' => ($currentYear - 1).' year'
        ]
      ],
      'ПозапрошлыйГод' => [
        'from' => $year2AgoFrom->format('Y-01-01'),
        'to' => $year2AgoFrom->format('Y-12-31'),
        'label' => [
          'ua' => $year2AgoFrom->format('Y').' рік',
          'uk' => $year2AgoFrom->format('Y').' рік',
          'ru' => $year2AgoFrom->format('Y').' год',
          'en' => $year2AgoFrom->format('Y').' year'
        ]
      ],
      'периодВыбранный' => [
        'from' => $todayStr,
        'to' => $todayStr,
        'label' => [
          'ua' => 'За період',
          'uk' => 'За період',
          'ru' => 'За период',
          'en' => 'During the period'
        ]
      ]
    ];
  }
  
  public static function getWeekDay(string $lang = 'uk'):string
  {
    $weekday = match ($lang) {
      'de' => [
        'Sonntag',
        'Montag',
        'Dienstag',
        'Mittwoch',
        'Donnerstag',
        'Freitag',
        'Samstag'
      ],
      'pl' => [
        'Niedziela',
        'Poniedziałek',
        'Wtorek',
        'Środa',
        'Czwartek',
        'Piątek',
        'Sobota'
      ],
      'ru' => [
        'Воскресенье',
        'Понедельник',
        'Вторник',
        'Среда',
        'Четверг',
        'Пятница',
        'Суббота'
      ],
      'en' => [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
      ],
      default => [
        'Неділя',
        'Понеділок',
        'Вівторок',
        'Середа',
        'Четвер',
        "П'ятниця",
        'Субота'
      ]
    };
    
    return $weekday[(int)date('w')];
  }
  
  #[ArrayShape([
    'day' => "string[]",
    'week' => "string[]",
    'month' => "string[]",
    'quarter' => "string[]",
    'year' => "string[]"
  ])]
  public static function setPeriodGroup():array
  {
    
    return [
      'day' => [
        'Сегодня',
        'Вчера'
      ],
      'week' => [
        'Неделя',
        'ПрошлаяНеделя'
      ],
      'month' => [
        'Месяц',
        'ПрошлыйМесяц',
        'ПозапрошлыйМесяц',
        'ЗаПозапрошлыйМесяц'
      ],
      'quarter' => [
        'Квартал'
      ],
      'year' => [
        'Год',
        'ПрошлыйГод',
        'ПозапрошлыйГод'
      ]
    ];
  }
  
  public static function validateDate(string $date, DateFormat $format = DateEn::ISO):bool
  {
    $d = \DateTime::createFromFormat($format->value, $date);
    
    return $d && $d->format($format->value) == $date;
  }
  
  #[ArrayShape([
    'ua' => "string",
    'uk' => "string",
    'ru' => "string",
    'en' => "string"
  ])]
  private static function getPeriodLabel(\DateTime $datetime):array
  {
    $month = (int)$datetime->format('m');
    $year = $datetime->format('Y');
    
    return [
      'ua' => self::getMonthName($month, 'ua').' '.$year,
      'uk' => self::getMonthName($month, 'uk').' '.$year,
      'ru' => self::getMonthName($month, 'ru').' '.$year,
      'en' => self::getMonthName($month, 'en').' '.$year
    ];
  }
}