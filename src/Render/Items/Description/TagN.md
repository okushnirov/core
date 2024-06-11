# Render: <span style="color:blue">TagN</span>

___

## Приклад:

```xml

<tagName render="TagN" crypt="true|" method="" method-name="after|before|" position="after|" target="crypt|" xpath="">
  [default value]
  <en>Tag text EN</en>
  <uk>Tag text UA</uk>
  <attr>
    <attrName>Attr text</attrName>
    <attrName>
      <en>Attr text EN</en>
      <uk>Attr text UA</uk>
    </attrName>
  </attr>
  <children/>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - назва елемента

- обробник в циклі перебирає атрибути `<tagName>`


- **crypt** - шифрування значення та додає атрибут `data-crypt-id=""` [не обов'язково]:
    - *true* - шифрує значення
    - *за замовчанням* - виводить значення як є


- **method** - назва методу `не обов'язково`:
- **method-name** - позиція виводу текстову назву методу з бази даних `не обов'язково`:
    - *пусте* - не виводити назву методу
    - *after* - після вмісту елемента
    - *before* - перед вмістом `за замовчанням`


- **target** - вивід часової мітки в форматі `YmdHis` та додає атрибут `data-target=""`:
    - *crypt* - шифрує значення мітки
    - *інакше* - виводить значення як є `за замовчанням`


- **position** - позиція виводу значення:
    - *after* - значення після вмісту елемента
    - *інакше* - виводить значення всередині `за замовчанням`


- **xpath** - шлях до XML даних
- **default value** - значення за замовчуванням `не обов'язково`:
    - *починається з `$`* - значення змінної php
    - *огорнено в `{}`* - вираз php

___ 

### <span style="color:blue">en, uk</span> - значення елемента для мовної локалізації

___

### <span style="color:blue">attr</span> - атрибути

- **attrName** - назва атрибута
- **en, uk** - мультимовні атрибути

___

### <span style="color:blue">children</span> - внутрішні елементи

- обробник в циклі перебирає всі вкладені елементи