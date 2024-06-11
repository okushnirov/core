# Render: <span style="color:blue">RadioboxOptionsN</span>

___

## Приклад:

```xml

<tagName render="RadioboxOptionsN">
  <source xpath=""/>
  <dict id="" order="0|1|2" parent="" parentName="" name="" lang-postfix="true|" prepare="true" value=""/>
  <sql sql="" name="" lang-postfix="true|" prepare="true" value=""/>
  <container class=""/>
  <label class="" position="after"/>
  <input type="radio">
    <attr>
      <attrName>
        <en>Text EN</en>
        <uk>Text UK</uk>
      </attrName>
    </attr>
  </input>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - назва елементу контейнера, зазвичай `<fieldset>`

- обробник в циклі перебирає всі атрибути `<tagName>`

___ 

### <span style="color:blue">source</span> - джерело даних

- **xpath** - шлях до XML даних

___

### <span style="color:blue">dict</span> - дані довідника

- **id** - ідентифікатор довідника `int`, шифрується та додається до атрибута таблиці `data-ref=""`


- **order** - порядок сортування:
    - *0* - сортування за назвою `за замовчуванням`
    - *1* - сортування за ідентифікатором `ID`
    - *інакше* - сортування за полем `Порядок`, за умови `isnull("Порядок", "Назва")`


- **parent** - значення батьківського елементу


- **parentName** - назва поля батьківського елементу


- **name** - назва текстового поля опції


- **lang-postfix** - мовна локалізація назви текстового поля опції
    - *true* - враховувати мовну локалізацію, наприклад `name_uk` або `name_en`
    - *інакше* - ні `за замовчуванням`


- **prepare** - екранування спецсимволів текстового  `не обов'язково`
    - *true* - так
    - *інакше** - ні `за замовчуванням`


- **value** - назва поля значення

___

### <span style="color:blue">sql</span> - дані запиту до бази даних

- **sql** - строка запиту до бази даних


- **name** - назва текстового поля опції


- **lang-postfix** - мовна локалізація назви текстового поля опції
    - *true* - враховувати мовну локалізацію, наприклад `name_uk` або `name_en`
    - *інакше* - ні `за замовчуванням`


- **prepare** - екранування спецсимволів текстового  `не обов'язково`
    - *true* - так
    - *інакше* - ні `за замовчуванням`


- **value** - назва поля значення

___

### <span style="color:blue">container</span> - елемент `<fieldset>`

- обробник в циклі перебирає всі атрибути `<container>`

___

### <span style="color:blue">label</span> - елемент `<label>`

- обробник в циклі перебирає всі атрибути `<label>`


- **position** - розташування елемента стосовно  `<input>`:
    - *after* - після `<input>` `за замовчуванням`
    - *before* - перед `<input>`

___

### <span style="color:blue">input</span> - елемент `<input type="radio"/>`

- обробник в циклі перебирає всі атрибути `<input>`


- **attr** - атрибути:
    - *attrName* - назва атрибута
    - *en, uk* - мультимовні атрибути