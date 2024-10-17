# Render: <span style="color:blue">SelectOf</span>

___

## Приклад:

```xml

<tagName render="SelectOf" class="select-of-box">
  <source>
    <container>
      <div class="--source">
        <label render="TagN">
          <en>Label EN</en>
          <uk>Label UK</uk>
        </label>
        <fieldset class="--source-select">
          #source
          <button render="TagN" class="--source-cmd" onclick="" type="button">
            <attr>
              <aria-label>
                <en>Add item EN</en>
                <uk>Add item UK</uk>
              </aria-label>
            </attr>
          </button>
        </fieldset>
      </div>
    </container>
    <dict id="" order="0|1|2" parent="" parentName="" name="" lang-postfix="true|" prepare="true" value=""/>
    <sql sql="" name="" lang-postfix="true|" prepare="true" value=""/>
    <ws auth="current:user|" method="json|xml|" name="" prepare="true">
      <handler class="" method=""/>
      <request>[request body]</request>
    </ws>
    <filter>[filter values]</filter>
    <select data-handler="selectr" data-search="" data-width="100%">
      <option value="" data-order="0">Select option</option>
    </select>
  </source>
  <destination data-type="0">
    <input class="-select-of" type="hidden" data-separator-char=";">
      <value xpath="" f-xpath=""/>
    </input>
    <container>
      <div class="--dest">
        <label render="TagN">
          <en>Label EN</en>
          <uk>Label UK</uk>
        </label>
        #dest:input
        <div render="TagN" class="--dest-items">
          <attr>
            <aria-label>
              <en>Empty items EN</en>
              <uk>Empty items UK</uk>
            </aria-label>
          </attr>
          #dest:items
        </div>
        <div class="--dest-item-clone">
          <div class="--name"/>
          <button class="--dest-move" onclick="" type="button" data-direction="up"/>
          <button class="--dest-move" onclick="" type="button" data-direction="down"/>
          <button class="--dest-cmd" onclick="" type="button"/>
        </div>
      </div>
    </container>
  </destination>
</tagName>

```

## Опис параметрів:

### <span style="color:blue">tagName</span> - назва елементу

- обробник в циклі перебирає всі атрибути `<tagName>`

___ 

### <span style="color:blue">source</span> - джерело даних

- **container** - контейнер елементів
    - обробник в циклі перебирає всі елементи контейнера `<container>`


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


- **select** - елемент `<select>` вибору опцій, обробник в циклі перебирає всі атрибути.
    - *data-handler* - назва плагіну обробки списків
        - *selectr* - JS SelectR
        - *select2* - jQuery Select2
    - **option** - перший елемент списку
        - *value* - значення опції
        - *data-order* - порядковий номер

Результат обробки `<select>` вставляється замість тегу `#source` в контейнер елементів `source`
___

### <span style="color:blue">destination</span> - отримувач даних

- **data-type** - тип даних:
    - *0* - значення являють собою строку із роздільниками
    - *1* - значення являють собою строки таблиці бази даних `в розробці`


- **container** - контейнер елементів
    - обробник в циклі перебирає всі елементи контейнера `children`


- **input** - елемент `<input>` отримувача
    - обробник в циклі перебирає всі атрибути елемента
    - **value** - джерело даних
        - **xpath** - шлях до XML даних
        - **f-xpath** - шлях до структури XML даних `не обов'язково`
        - **lang** - залежність назви поля від мови `bool`:
            - *true* - змінна `$lang` в шляху `xpath` замінюється на поточну
              мову
        - **prepare** - екранування спецсимволів `не обов'язково`
            - *true* - так
            - *інакше* - ні `за замовчуванням`


Результат обробки `<input>` вставляється замість тегу `#dest:input` в контейнер елементів `destination`

Результат обробки обраних значень опцій вставляється замість тегу `#dest:items` в контейнер елементів `destination`
___

## Приклад виконання:

```html

<div class="select-of-box" data-ref="CLIENT">
  <div class="--source">
    <label class="label">Клієнти банка</label>
    <fieldset class="--source-select">
      <div class="selectr-container tas-lease selectr-desktop has-selected --empty --search" style="width: 100%;">
        <div class="selectr-selected" disabled="undefined" tabindex="0" aria-expanded="false">
          <span class="selectr-label">виберіть зі списку</span>
          <div class="selectr-placeholder"></div>
        </div>
        <div class="selectr-options-container">
          <div class="selectr-input-container">
            <input class="selectr-input" tagindex="-1" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="true" role="textbox" type="search">
            <button class="selectr-input-clear" type="button"></button>
          </div>
          <div class="selectr-notice"></div>
          <ul class="selectr-options" role="tree" aria-hidden="true" aria-expanded="false"></ul>
        </div>
        <select data-handler="selectr" data-search="" data-width="100%" class="-select-r selectr-hidden" data-el-type="select" tabindex="-1" aria-hidden="true">
          <option value="" data-order="0" selected="">виберіть зі списку</option>
          <option value="03742096" data-order="5">03742096 - ТОВ "АГРОФІРМА РАССВЕТ"</option>
          <option value="13990808" data-order="8">13990808 - ТОВ "ТОПАЗ-ЛТД"</option>
        </select>
      </div>
      <button class="--source-cmd" onclick="" type="button" aria-label="Додати клієнта"></button>
    </fieldset>
  </div>
  <div class="--dest">
    <label class="label">Список доступних клієнтів</label>
    <input class="-select-of update" type="hidden" data-separator-char=";" name="b2NxY2VxeWlwYWRob2M" maxlength="32767" value=";00130725;03579041;" data-ini-changes="" data-movable="" data-el-type="selectOf" update="">
    <div class="--dest-items" aria-label="не вибрано жодного клієнта">
      <div class="--dest-item --new" data-order="1" data-value="00130725">
        <div class="--name">00130725 - АКЦІОНЕРНЕ ТОВАРИСТВО "ТЕРНОПІЛЬОБЛЕНЕРГО"</div>
        <button class="--dest-move" onclick="" type="button" data-direction="up"></button>
        <button class="--dest-move" onclick="" type="button" data-direction="down"></button>
        <button class="--dest-cmd" onclick="" type="button"></button>
      </div>
      <div class="--dest-item --new" data-order="3" data-value="03579041">
        <div class="--name">03579041 - ТОВ "БЕРЕЗІВКААГРОШЛЯХБУД"</div>
        <button class="--dest-move" onclick="" type="button" data-direction="up"></button>
        <button class="--dest-move" onclick="" type="button" data-direction="down"></button>
        <button class="--dest-cmd" onclick="" type="button"></button>
      </div>
    </div>
    <div class="--dest-item-clone">
      <div class="--name"></div>
      <button class="--dest-move" onclick="" type="button" data-direction="up"></button>
      <button class="--dest-move" onclick="" type="button" data-direction="down"></button>
      <button class="--dest-cmd" onclick="" type="button"></button>
    </div>
  </div>
</div>
```