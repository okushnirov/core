# Render: <span style="color:blue">SelectOptionsN</span>

___

## Приклад:

```xml

<tagName render="SelectOptionsN" class="width-100">
  <source class="select-options -source width-100 left no-update" size="" aria-label="Source">
    <container class="width-48 left">
      <label render="TagN">
        <en>Label EN</en>
        <uk>Label UK</uk>
      </label>
    </container>
    <dict id="" order="0|1|2" parent="" parentName="" name="" lang-postfix="true|" prepare="true" value=""/>
    <sql sql="" name="" lang-postfix="true|" prepare="true" value=""/>
    <ws auth="current:user|" method="json|xml|" name="" prepare="true">
      <handler class="" method=""/>
      <request>[request body]</request>
    </ws>
    <filter>[filter values]</filter>
    <option value="" data-order="0">
      <en>Option EN</en>
      <uk>Option UK</uk>
    </option>
  </source>
  <c/>
  <destination class="select-options -dest width-100 right" prepare="true" data-type="0|1" data-separator=";" size="" aria-label="Destination">
    <value xpath="" f-xpath=""/>
    <container class="width-48 right">
      <label render="TagN">
        <en>Label EN</en>
        <uk>Label UK</uk>
      </label>
    </container>
    <option value="" data-order="0">
      <en>Option EN</en>
      <uk>Option UK</uk>
    </option>
  </destination>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - назва елементу

- обробник в циклі перебирає всі атрибути `<tagName>`

___ 

### <span style="color:blue">source</span> - джерело даних

- обробник в циклі перебирає всі атрибути `<source>`


- **container** - контейнер елементів
    - обробник в циклі перебирає всі елементи контейнера `<container>`
    - *class* - клас контейнера `string`


- **dict** - дані довідника
    - *id* - ідентифікатор довідника `int`, шифрується та додається до атрибута таблиці `data-ref=""`
    - *order* - порядок сортування:
        - *0* - сортування за назвою `за замовчуванням`
        - *1* - сортування за ідентифікатором `ID`
        - *інакше* - сортування за полем `Порядок`, за умови `isnull("Порядок", "Назва")`
    - *parent* - значення батьківського елементу
    - *parentName* - назва поля батьківського елементу
    - *name* - назва текстового поля опції
    - *lang-postfix* - мовна локалізація назви текстового поля опції
        - *true* - враховувати мовну локалізацію, наприклад `name_uk` або `name_en`
        - *інакше* - ні, за замовчуванням
    - *prepare* - екранування спецсимволів текстового  `не обов'язково`
        - *true* - так
        - *інакше* - ні, за замовчуванням
    - *value* - назва поля значення


- **sql** - дані запиту до бази даних
    - *sql* - строка запиту до бази даних
    - *name* - назва текстового поля опції
    - *lang-postfix* - мовна локалізація назви текстового поля опції
        - *true* - враховувати мовну локалізацію, наприклад `name_uk` або `name_en`
        - *інакше* - ні, за замовчуванням
    - *prepare* - екранування спецсимволів текстового  `не обов'язково`
        - *true* - так
        - *інакше* - ні, за замовчуванням
    - *value* - назва поля значення


- **ws** - дані запиту до вебсервісу
    - *auth* - тип авторизації:
        - *current:user* - поточний користувач
        - *інакше* - без авторизації або за замовчуванням
    - *method* - тип авторизації:
        - *json* - запит в форматі JSON
        - *xml* - запит в форматі XML
        - *інакше* - типовий запит
    - *name* - назва вебсервісу в налаштуваннях
    - *prepare* - екранування спецсимволів в назвах опцій
    - *handler* - обробник результатів запиту:
        - *class* - назва класу обробника
        - *method* - назва методу обробника
    - *request* - тіло запиту до вебсервісу `не обов'язково`


- **filter** - фільтрування значень `не обов'язково`
    - за наявності дані в за маскою `;value;` будуть відфільтровані, згідно зі строкою фільтра


- **option** - значення опції, коли всі значення джерела вибрані

___

### <span style="color:blue">destination</span> - отримувач даних

- обробник в циклі перебирає всі атрибути `<destination>`


- **prepare** - екранування спецсимволів в назвах опцій


- **data-separator** - символ роздільника значень, за замовчуванням `;`


- **data-type** - тип даних:
    - *0* - значення являють собою строку із роздільниками
    - *1* - значення являють собою строки таблиці бази даних `в розробці`


- **container** - контейнер елементів
    - обробник в циклі перебирає всі елементи контейнера `children`
    - *class* - клас контейнера `string`


- **value** - поточні значення даних
    - *xpath* - шлях до XML даних
    - *f-xpath* - шлях до структури XML даних `не обов'язково`


- **option** - значення опції, коли не вибрано жодного значення

___

### <span style="color:blue">с</span> - середній розділюючий елемент `не обов'язково`

- обробник в циклі перебирає всі атрибути та елементи контейнера `children`

___

## Приклад виконання:

```html

<div class="--cell-10">
  <div class="fieldset-n --in --flex-row width-100">
    <div class="fieldset-n --out --in --flex-col width-48">
      <label class="label-n width-100">Повноваження</label>
      <select class="select-options -options-source width-100 no-update" size="4" data-source-name="dW9uY3VsbmRwZnNhdWQ">
        <option value="" data-order="0" style="display:none;">список пустий</option>
        <option value="плВыборДилера" data-order="1">Вибір дилера</option>
        <option value="плВыборПродукта" data-order="2">Вибір продукту</option>
        <option value="плДетальныйГрафик" data-order="3">Детальний графік</option>
        <option value="плИзменениеГрафиков" data-order="4">Зміна графіків</option>
        <option value="плСменаСостояния" data-order="5">Зміна стану</option>
        <option value="плДилерИндУсловия" data-order="6">Індивідуальні умови</option>
        <option value="плПортфель" data-order="7">Портфель</option>
        <option value="плСчета" data-order="8">Перегляд рахунків</option>
        <option value="плУсловияРасчета" data-order="9">Умови розрахунку</option>
        <option value="плФинальныйГрафик" data-order="10">Фінальний графік</option>
        <option value="плЭкспортЗаявок" data-order="11">Експорт заявок в Excel</option>
      </select>
    </div>
    <div class="--options-center">
      <div class="mif-import-export rotate-90"></div>
    </div>
    <div class="fieldset-n --out --in --flex-col width-48">
      <label class="label-n width-100">Доступні повноваження</label>
      <select class="select-options -options-dest width-100" data-type="0" data-separator=";" size="4" name="dW9uY3VsbmRwZnNhdWQ" maxlength="256">
        <option value="" data-order="0">За замовчуванням</option>
      </select>
    </div>
  </div>
</div>
```