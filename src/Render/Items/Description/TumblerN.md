# Render: <span style="color:blue">TumblerN</span>

___

## Приклад:

```xml

<tagName render="TumblerN" class="tumbler left">
  <input class="input" type="checkbox" data-on="1" data-on-type="integer" data-off="0" data-off-type="integer"/>
  <value xpath="" f-xpath="">[default value]</value>
  <slider class="slider round red"/>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - зовнішня обгортка елемента, назва тега береться із назви елемента

- обробник в циклі перебирає атрибути `<tagName>`

___

### <span style="color:blue">input</span> - елемент Input

- обробник в циклі перебирає атрибути `<input>`

___

### <span style="color:blue">value</span> - джерело значень

- **xpath** - шлях до XML даних
- **f-xpath** - шлях до структури XML даних


- **default value** - значення за замовчуванням `не обов'язково`:
    - *починається з `$`* - значення змінної php
    - *огорнено в `{}`* - вираз php

___

### <span style="color:blue">slider</span> - внутрішній слайдер

- обробник в циклі перебирає атрибути `<slider>`

___

## Приклад виконання:

```html

<label class="tumbler left">
  <input type="checkbox" data-on="1" data-on-type="integer" data-off="0" data-off-type="integer" value="0"/>
  <span class="slider round red"></span>
</label>

```