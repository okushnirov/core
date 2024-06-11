# Render: <span style="color:blue">CheckboxOptionsN</span>

___

## Приклад:

```xml

<tagName render="CheckboxOptionsN">
  <source class="" xpath=""/>
  <dict id="" order="0|1|2" parent="" parentName="" name="" lang-postfix="true|" prepare="true" value=""/>
  <sql sql="" name="" lang-postfix="true|" prepare="true" value=""/>
  <container class=""/>
  <label class="" position="after"/>
  <input name="" data-separator=";"/>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - назва елементу контейнера, зазвичай `<fieldset>`

- обробник в циклі перебирає всі атрибути `<tagName>`

___ 

### <span style="color:blue">source</span> - джерело даних

- **class** - клас елементів `<input type="checkbox"/>`


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

### <span style="color:blue">input</span> - елемент `<input type="hidden"/>`

- обробник в циклі перебирає всі атрибути `<input>`


- **data-separator** - символ роздільника значень, за замовчуванням `;`

___

## Приклад виконання:

```html

<fieldset class="checkbox-container width-100">
  <input type="hidden" name="вопрос_риск_список" data-print="setOf" data-separator=";" required="" aria-label="Запитання про ризикові види діяльності" value=";490;">
  <fieldset class="checkbox-wrapper width-100">
    <input id="911178_492" type="checkbox" class="checkbox-n --set-el" data-name="вопрос_риск_список" value="492">
    <label for="911178_492" class="label-n ml-10 --checkbox">Виробництво та/або торгівля зброєю, боєприпасами, військовою технікою та військовими машинами (її частинами)</label>
  </fieldset>
  <fieldset class="checkbox-wrapper width-100">
    <input id="911196_491" type="checkbox" class="checkbox-n --set-el" data-name="вопрос_риск_список" value="491">
    <label for="911196_491" class="label-n ml-10 --checkbox">Діяльність, якій притаманно високий рівень обігу готівки</label>
  </fieldset>
  <fieldset class="checkbox-wrapper width-100">
    <input id="911201_490" type="checkbox" class="checkbox-n --set-el" data-name="вопрос_риск_список" checked="" value="490">
    <label for="911201_490" class="label-n ml-10 --checkbox">Жоден з наведених</label>
  </fieldset>
</fieldset>

```