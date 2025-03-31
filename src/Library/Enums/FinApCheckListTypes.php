<?php

namespace okushnirov\core\Library\Enums;

enum FinApCheckListTypes: string
{
  # 1. Санкції ДСФМУ, РНБО, ООН, Європейський Союз, Велика Британія, США, Канада та інші іноземні санкційні списки
  case Sanctions = "Санкції";
  
  # 2. Реєстр політично значущих осіб
  case PEPs = "Політично значущі особи";
  
  # 3. Реєстр зрадників
  case Zrada = "Зрадники";
  
  # 4. Реєстр окупантів
  case Okupant = "Окупанти";
  
  # 5. Реєстр нерезидентів
  case NonResident = "Нерезиденти";
  
  # 6. Реєстр втрачених/недійсних документів (національні та закордонні паспорти)
  case LostDocs = "Втрачені документи";
  
  # 7. Реєстр виконавчих проваджень
  case Executive = "Виконавчі впровадження";
  
  # 8. Єдиний реєстр боржників
  case Debtor = "Боржники";
  
  # 9. Реєстр банкрутів
  case Bankrupt = "Банкрутство";
  
  # 10. Показник ризиковості крани громадянства або реєстрації серед бенефіціарних власників або засновників
  case CountryOfRisks = "Ризикова країна";
  
  # 11. Реєстр судових справ
  case Justice = "Суди";
  
  # 12. Реєстр розшуку
  case Wanted = "Розшук";
  
  # 13. Реєстр корупціонерів
  case Corrupt = "Корупція";
  
  # 14. Реєстр податкових боржників
  case TaxDebtor = "Податкові боржники";
  
  public function getTags():string
  {
    
    return match ($this) {
      self::Sanctions => 'psanctions',
      self::PEPs => 'peps',
      self::Zrada => 'zrada',
      self::Okupant => 'okupant',
      self::NonResident => 'nonrezedent',
      self::LostDocs => 'lostdocs',
      self::Executive => 'executive',
      self::Debtor => 'debtor',
      self::Bankrupt => 'bankrupt',
      self::Justice => 'justice',
      self::Wanted => 'wanted',
      self::Corrupt => 'corrupt',
      self::TaxDebtor => 'taxdebtor',
      default => ''
    };
  }
  
  public function getLabels():string
  {
    
    return match ($this) {
      self::Sanctions => 'Санкції ДСФМУ, РНБО, ООН, Європейський Союз, Велика Британія, США, Канада та інші іноземні санкційні списки',
      self::PEPs => 'Реєстр політично значущих осіб',
      self::Zrada => 'Реєстр зрадників',
      self::Okupant => 'Реєстр окупантів',
      self::NonResident => 'Реєстр нерезидентів',
      self::LostDocs => 'Реєстр втрачених/недійсних документів (національні та закордонні паспорти)',
      self::Executive => 'Реєстр виконавчих проваджень',
      self::Debtor => 'Єдиний реєстр боржників',
      self::Bankrupt => 'Реєстр банкрутів',
      self::CountryOfRisks => 'Показник ризиковості крани громадянства або реєстрації серед бенефіціарних власників або засновників',
      self::Justice => 'Реєстр судових справ',
      self::Wanted => 'Реєстр розшуку',
      self::Corrupt => 'Реєстр корупціонерів',
      self::TaxDebtor => 'Реєстр податкових боржників'
    };
  }
}