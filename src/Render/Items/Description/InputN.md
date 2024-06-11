# Render: <span style="color:blue">InputN</span>

___

## Приклад:

```xml

<tagName render="InputN">
  <value f-xpath="" xpath="" lang="true" prepare="true"/>
  <convert handler="date" format="d.m.Y"/>
  <convert handler="int|number" decimal="" empty="" point="" thousand=" "/>
  <attr>
    <attrName>
      <en>Text EN</en>
      <uk>Text UK</uk>
    </attrName>
  </attr>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - елемент вводу, зазвичай `input` або `<textarea>`

- обробник в циклі перебирає всі атрибути `<tagName>`

___ 

### <span style="color:blue">value</span> - джерело даних

- **xpath** - шлях до XML даних


- **f-xpath** - шлях до структури XML даних `не обов'язково`


- **lang** - залежність назви поля від мови `bool`:
    - *true* - змінна `$lang` в шляху `xpath` замінюється на поточну
      мову


- **prepare** - екранування спецсимволів `не обов'язково`
    - *true* - так
    - *інакше* - ні `за замовчуванням`

___ 

### <span style="color:blue">convert</span> - конвертація (перетворення) даних

- **handler** - обробник даних:
    - *date* - тип дата
        - *format* - формати дати
    - *int, number* - число типу `int` або `float`
        - *decimal* - кількість знаків після крапки
        - *empty* - значення за відсутності значення
        - *point* - символ роздільника, зазвичай `.`
        - *thousand* - символ роздільника тисячних розрядів, зазвичай `пробіл`

___

### <span style="color:blue">attr</span> - атрибути

- **attrName** - назва атрибута
- **en, uk** - мультимовні атрибути