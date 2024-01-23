<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\{Enums\DateEn, Interfaces\DateFormat};
use JetBrains\PhpStorm\ArrayShape;

final class Date
{
  public static function getAge(string $birthday):bool | int | string
  {
    if (empty($birthday)) {
      
      return false;
    }
    
    $birthdayTimestamp = strtotime($birthday);
    
    if (false === $birthdayTimestamp) {
      
      return false;
    }
    
    $age = date('Y') - date('Y', $birthdayTimestamp);
    
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
        'luty',
        'marsz',
        'kwietnia',
        'może',
        'czerwca',
        'lipiec',
        'sierpień',
        'wrzesień',
        'październik',
        'listopad',
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
    
    return date('j').' '.$month[date('n') - 1].' '.date('Y').' '.$ltr_year;
  }
  
  public static function getFirstQuarterDay(int $monthNumber):string
  {
    
    return match (intval(($monthNumber + 2) / 3)) {
      1 => date('Y-01-01'),
      2 => date('Y-04-01'),
      3 => date('Y-07-01'),
      4 => date('Y-10-01'),
      default => ''
    };
    
  }
  
  public static function getMonthName(int $month, string $lang):string
  {
    $name = match ($lang) {
      'ru' => [
        false,
        "Январь",
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
        false,
        "January",
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
        false,
        "Січень",
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
    
    return $name[empty($month) ? (int)date('m') : $month];
  }
  
  public static function getPeriod():array
  {
    
    return [
      'периодСегодня' => [
        'periodFrom' => date('Y-m-d'),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За сегодня',
          'ua' => 'За сьогодні',
          'uk' => 'За сьогодні'
        ]
      ],
      'периодНеделя' => [
        'periodFrom' => date('Y-m-d', strtotime('monday this week')),
        'periodTo' => date('Y-m-d'),
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
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За последние 10 дней',
          'ua' => 'За останній 10 днів',
          'uk' => 'За останній 10 днів'
        ]
      ],
      'период30Дней' => [
        'periodFrom' => date('Y-m-d', strtotime('-30 day')),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За последние 30 дней',
          'ua' => 'За останні 30 днів',
          'uk' => 'За останні 30 днів'
        ]
      ],
      'периодМесяц' => [
        'periodFrom' => date('Y-m-01'),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За текущий месяц',
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
        'periodFrom' => self::getFirstQuarterDay(date('n')),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За текущий квартал',
          'ua' => 'За поточний квартал',
          'uk' => 'За поточний квартал'
        ]
      ],
      'период12Месяцев' => [
        'periodFrom' => date('Y-m-d', strtotime('-1 year')),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За последние 12 месяцев',
          'ua' => 'За останні 12 місяців',
          'uk' => 'За останні 12 місяців'
        ]
      ],
      'периодГод' => [
        'periodFrom' => date('Y-01-01'),
        'periodTo' => date('Y-m-d'),
        'label' => [
          'ru' => 'За текущий год',
          'ua' => 'За поточний рік',
          'uk' => 'За поточний рік'
        ]
      ],
      'периодПрошлыйГод' => [
        'periodFrom' => date('Y-01-01', strtotime('first day of previous year')),
        'periodTo' => date('Y-12-t', strtotime('last day of previous year')),
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
    $yesterday = (new \DateTime('yesterday'));
    $monthPreviousFrom = (new \DateTime('first day of previous month'));
    $month2AgoFrom = (new \DateTime('first day of 2 months ago'));
    $month3AgoFrom = (new \DateTime('first day of 3 months ago'));
    $yearPreviousFrom = (new \DateTime('first day of previous year'));
    $year2AgoFrom = (new \DateTime('first day of 2 years ago'));
    $labelCurrent = [
      'ua' => 'Поточний',
      'uk' => 'Поточний',
      'ru' => 'Текущий',
      'en' => 'Current'
    ];
    
    return [
      'Сегодня' => [
        'from' => date('Y-m-d'),
        'to' => date('Y-m-d'),
        'label' => [
          'ua' => 'Сьогодні',
          'uk' => 'Сьогодні',
          'ru' => 'Сегодня',
          'en' => 'Today'
        ]
      ],
      'Вчера' => [
        'from' => $yesterday->format('Y-m-d'),
        'to' => $yesterday->format('Y-m-d'),
        'label' => [
          'ua' => 'Вчора',
          'uk' => 'Вчора',
          'ru' => 'Вчера',
          'en' => 'Yesterday'
        ]
      ],
      'Неделя' => [
        'from' => (new \DateTime('monday this week'))->format('Y-m-d'),
        'to' => date('Y-m-d'),
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
        'to' => date('Y-m-d'),
        'label' => [
          'ua' => '30 днів',
          'uk' => '30 днів',
          'ru' => '30 дней',
          'en' => '30 days before'
        ]
      ],
      'Месяц' => [
        'from' => date('Y-m-01'),
        'to' => date('Y-m-d'),
        'label' => $labelCurrent
      ],
      'ПрошлыйМесяц' => [
        'from' => $monthPreviousFrom->format('Y-m-d'),
        'to' => (new \DateTime('last day of previous month'))->format('Y-m-d'),
        'label' => self::_getPeriodLabel($monthPreviousFrom)
      ],
      'ПозапрошлыйМесяц' => [
        'from' => $month2AgoFrom->format('Y-m-d'),
        'to' => (new \DateTime('last day of 2 months ago'))->format('Y-m-d'),
        'label' => self::_getPeriodLabel($month2AgoFrom)
      ],
      'ЗаПозапрошлыйМесяц' => [
        'from' => $month3AgoFrom->format('Y-m-d'),
        'to' => (new \DateTime('last day of 3 months ago'))->format('Y-m-d'),
        'label' => self::_getPeriodLabel($month3AgoFrom)
      ],
      'Квартал' => [
        'from' => self::getFirstQuarterDay(date('n')),
        'to' => date('Y-m-d'),
        'label' => $labelCurrent
      ],
      'ПрошлыйКвартал' => [
        'from' => self::getFirstQuarterDay(date('n')),
        'to' => date('Y-m-d'),
        'label' => [
          'ua' => 'Q ',
          'uk' => 'Q ',
          'ru' => 'Q ',
          'en' => 'Q '
        ]
      ],
      'Год' => [
        'from' => date('Y-01-01'),
        'to' => date('Y-m-d'),
        'label' => $labelCurrent
      ],
      'ПрошлыйГод' => [
        'from' => $yearPreviousFrom->format('Y-01-01'),
        'to' => (new \DateTime('last day of previous year'))->format('Y-12-t'),
        'label' => [
          'ua' => $yearPreviousFrom->format('Y').' рік',
          'uk' => $yearPreviousFrom->format('Y').' рік',
          'ru' => $yearPreviousFrom->format('Y').' год',
          'en' => $yearPreviousFrom->format('Y').' year'
        ]
      ],
      'ПозапрошлыйГод' => [
        'from' => $year2AgoFrom->format('Y-01-01'),
        'to' => (new \DateTime('last day of 2 years ago'))->format('Y-12-t'),
        'label' => [
          'ua' => $year2AgoFrom->format('Y').' рік',
          'uk' => $year2AgoFrom->format('Y').' рік',
          'ru' => $year2AgoFrom->format('Y').' год',
          'en' => $year2AgoFrom->format('Y').' year'
        ]
      ],
      'периодВыбранный' => [
        'from' => date('Y-m-d'),
        'to' => date('Y-m-d'),
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
      ],
    };
    
    return $weekday[date('w')];
  }
  
  #[ArrayShape([
    'day' => "string[]",
    'week' => "string[]",
    'month' => "string[]",
    'quarter' => "string[]",
    'year' => "string[]"
  ])] public static function setPeriodGroup():array
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
  ])] private static function _getPeriodLabel(\DateTime $datetime):array
  {
    $month = $datetime->format('m');
    $year = $datetime->format('Y');
    
    return [
      'ua' => self::getMonthName($month, 'ua').' '.$year,
      'uk' => self::getMonthName($month, 'uk').' '.$year,
      'ru' => self::getMonthName($month, 'ru').' '.$year,
      'en' => self::getMonthName($month, 'en').' '.$year
    ];
  }
}