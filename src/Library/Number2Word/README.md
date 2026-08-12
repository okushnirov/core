# Number2Word

Легка, розширювана PHP-бібліотека для конвертації чисел і грошових сум у текстовий пропис українською та російською мовами з урахуванням граматичних правил відмінювання.

Designed for PHP 8.2+.

---

## **Особливості**

* **Підтримка мов:** Українська (`ua`), Російська (`ru`).
* **Валюти з коробки:** UAH (гривня), USD (долар), EUR (євро), а також режим без валюти (`-1`).
* **Гнучке форматування:**
    * Вивід копійок/центів цифрами (`12 копійок`) або словами (`дванадцять копійок`).
    * Зміна регістру тексту (original, UPPERCASE, Capitalize).
    * Автоматична обробка розділювачів пробілів та ком у числах (наприклад, `"1 234,56"`).
* **SOLID & ООП:** Модульна архітектура на базе чистих класів та Enums без статичного стану, готова до Unit-тестування та Dependency Injection.

---

## **Приклади використання**

* Через HTTP-контролер (Number2Word).<br>
   Зручний спосіб для швидкої обробки входів з $_POST або REST API:

```php
use okushnirov\core\Library\Number2Word;

$params = [
    'num' => '1234.56', # Число
    'cur' => 'UAH',     # Валюта: UAH, USD, EUR або -1
    'lang' => 'ua',     # Мова: ua, ru
    'dec' => 1,         # Включати копійки: 1 (так), 0 (ні)
    'dec2word' => 1,    # Копійки словами: 1 (так), 0 (цифрами)
    'reg' => 2,         # Регістр: 0 (з Ordinary), 1 (UPPERCASE), 2 (Capitalize)
];

$number2Word = new Number2Word($params);

echo $number2Word->run(); 
# Результат: "Одна тисяча двісті тридцять чотири гривні п'ятдесят шість копійок"
```

* Прямий виклик через сервіс (Service)<br>
   Ідеально підходить для використання в бізнес-логіці, сервісах або чергах:

```php
use okushnirov\core\Library\Number2Word\{Service,LocaleUkrainian,LocaleRussian,TextCase};

# Ініціалізація сервісу з потрібною локаллю
$service = new Service(new LocaleUkrainian());

# Базовий виклик (за замовчуванням UAH, копійки цифрами)
echo $service->convert(1000); 
# Результат: "одна тисяча гривень 00 копійок"

# Копійки словами + Capitalize
echo $service->convert(
    number: 1.01, 
    currency: 'UAH', 
    includeDecimals: true, 
    decimalsAsWords: true, 
    textCase: TextCase::CAPITALIZE
); 
# Результат: "Одна гривня одна копійка"

# Без валюти та з UPPERCASE
echo $service->convert(
    number: 5, 
    currency: '-1', 
    includeDecimals: false, 
    textCase: TextCase::UPPERCASE
); 
# Результат: "П'ЯТЬ"

# Змінити мову на російську
$serviceRu = new Service(new LocaleRussian());

echo $serviceRu->convert(1000, 'USD'); 
# Результат: "одна тысяча долларов США 00 центов"
```