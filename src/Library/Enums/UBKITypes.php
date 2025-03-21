<?php

namespace okushnirov\core\Library\Enums;

enum UBKITypes: int
{
  # Контакти
  case Request04 = 4;
  
  # Ідентифікація за номером телефону та фото
  case Request06 = 6;
  
  #  Звіт «Transaction Underwriting Score» (Тимчасово не надається)
  case Request07 = 7;
  
  # Кредитний звіт фізичної особи, підприємця
  case Request10 = 10;
  
  # Кредитний бал
  case Request11 = 11;
  
  # Ідентифікація
  case Request12 = 12;
  
  # Перевірка посвідчення особи
  case Request13 = 13;
  
  # Перевірка посвідчення особи онлайн
  case Request14 = 14;
  
  # Кредитний звіт юридичної особи
  case Request15 = 15;
  
  # AFS UBKI
  case Request16 = 16;
  
  # Фотоверифікація
  case Request17 = 17;
  
  # Верифікація
  case Request21 = 21;
  
  # Досьє підприємця
  case Request22 = 22;
  
  # Індивідуальний скоринг
  case Request25 = 25;
  
  # Публічне досьє
  case Request26 = 26;
  
  # Судове рішення
  case Request28 = 28;
  
  # Повідомлення в ЗМІ
  case Request29 = 29;
  
  # Телеком звіт
  case Request30 = 30;
  
  # Інформація з Державного реєстру обтяжень рухомого майна (ДРОРМ)
  case Request31 = 31;
  
  # Ідентифікація СКІ за паспортом
  case Request32 = 32;
  
  # Інформація з реєстру речових прав на нерухоме майно (РРП)
  case Request33 = 33;
  
  # Ідентифікація СКІ за номером телефону і датою народження
  case Request34 = 34;
  
  # Liveness detection
  case Request35 = 35;
  
  # Інформація про реєстрацію платника податків (Дані із державних реєстрів тимчасово не надаються)
  case Request36 = 36;
  
  # Інформація про зареєстровані транспортні засоби
  case Request37 = 37;
  
  # Інформація про перевипуск sim-карти
  case Request38 = 38;
  
  # Скоринг МСБ
  case Request40 = 40;
  
  # Звіт "Feature Set"
  case Request41 = 41;
  
  # Звіт "Фінансова звітність"
  case Request42 = 42;
  
  # Фотоверифікація LIGHT
  case Request43 = 43;
  
  # Скоринг ФОП
  case Request44 = 44;
  
  # Скоринг FICO
  case Request46 = 46;
  
  # Індивідуальний кредитний звіт
  case Request48 = 48;
  
  # Інформація з Єдиного державного реєстру декларацій
  case Request49 = 49;
  
  # Інформація з Реєстру осіб, яким обмежено доступ до гральних закладів та/або участь в азартних іграх
  case Request50 = 50;
  
  # Ідентифікація за номером телефону та ПІБ
  case Request52 = 52;
  
  # Пов'язані особи
  case Request53 = 53;
}