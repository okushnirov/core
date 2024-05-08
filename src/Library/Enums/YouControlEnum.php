<?php

namespace okushnirov\core\Library\Enums;

enum YouControlEnum: string
{
  /**
   * Відомості про справи про банкрутство (Bankruptcy Information)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%92%D1%96%D0%B4%D0%BE%D0%BC%D0%BE%D1%81%D1%82%D1%96%20%D0%BF%D1%80%D0%BE%20%D1%81%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%20%D0%BF%D1%80%D0%BE%20%D0%B1%D0%B0%D0%BD%D0%BA%D1%80%D1%83%D1%82%D1%81%D1%82%D0%B2%D0%BE%20(Bankruptcy%20Information)/get_v1_secou
   */
  case bankrupt = "Банкрутство";
  
  /**
   * НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)%20%E2%80%94%20beta-testing/get_v1_peps
   */
  case companyPersons = "НПД та суб'єкти декларування пов'язані з компанією";
  
  /**
   * Судові дані (Court data)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D1%83%D0%B4%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Court%20data)/get_v1_courtCaseGroup__contractorCode_
   */
  case courts = "Суди";
  
  /**
   * Виконавчі провадження (Enforcement proceedings)<br>
   * ФО - Виконавчі провадження (Private individual - Enforcement proceedings)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Enforcement%20proceedings)/get_v1_enforcement__contractorCode_
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Private%20individual%20-%20Enforcement%20proceedings)/get_v1_enforcementIndividual
   */
  case executive = "Виконавчі провадження";
  
  /**
   * Приналежність до ФПГ (Affiliation with FIG)<br>
   * ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D1%80%D0%B8%D0%BD%D0%B0%D0%BB%D0%B5%D0%B6%D0%BD%D1%96%D1%81%D1%82%D1%8C%20%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D1%96%D1%97%20%D0%B4%D0%BE%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D1%85%20%D0%B3%D1%80%D1%83%D0%BF%20(%D0%A4%D0%9F%D0%93)%20(Affiliation%20with%20financial-industrial%20groups%20(FIG))/get_v1_fig
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%97%D0%B2%E2%80%99%D1%8F%D0%B7%D0%BE%D0%BA%20%D0%B7%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D0%BC%D0%B8%20%D0%B3%D1%80%D1%83%D0%BF%D0%B0%D0%BC%D0%B8%20(Private%20individual%20-%20Affiliation%20with%20financial-industrial%20groups)/get_v1_individualsFigCompanies
   */
  case fig = "Приналежність до ФПГ";
  
  /**
   * ФО - Перевірка паспорту (Passports check)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%9F%D0%B5%D1%80%D0%B5%D0%B2%D1%96%D1%80%D0%BA%D0%B0%20%D0%BF%D0%B0%D1%81%D0%BF%D0%BE%D1%80%D1%82%D1%83%20(Passports%20check)/get_v1_passports
   */
  case passports = "Перевірка паспорту";
  
  /**
   * НПД скринінг (PEP Screening)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)/get_v1_peps
   */
  case peps = "НПД скринінг";
  
  /**
   * Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)/get_v1_peps_related
   */
  case relatedPersons = "Пов'язані з шуканим НПД особи та компанії";
  
  /**
   * Санкції (Sanctions)<br>
   * ФО - Санкції (Sanctions screening)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions)/get_v1_sanctions
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions%20screening)/get_v1_individualsGlobalSanctionsLists
   */
  case sanctions = "Санкції";
  
  /**
   * Наявність у компанії податкового боргу (Company's tax dept)<br>
   * ФO - Податковий борг (Private individual - Tax debtors)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D0%BE%D0%B4%D0%B0%D1%82%D0%BA%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Tax%20data)/get_v1_taxDebt__contractorCode_
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%BE%D1%80%D0%B6%D0%BD%D0%B8%D0%BA%D0%B8%20(Private%20individual%20-%20Deptors)/get_v1_individualsTaxDebtors
   */
  case taxDebtor = "Податковий борг";
  
  /**
   * ФО - Терористи (Terrorists)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A2%D0%B5%D1%80%D0%BE%D1%80%D0%B8%D1%81%D1%82%D0%B8%20(Terrorists)/get_v1_individualsDsfmuTerrorists
   */
  case terrorists = "Терористи";
  
  /**
   * ФО - Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%B5%D0%B7%D0%B2%D1%96%D1%81%D0%BD%D0%BE%20%D0%B7%D0%BD%D0%B8%D0%BA%D0%BB%D1%96%20%D1%82%D0%B0%20%D1%82%D1%96%2C%20%D1%8F%D0%BA%D1%96%20%D0%BF%D0%B5%D1%80%D0%B5%D1%85%D0%BE%D0%B2%D1%83%D1%8E%D1%82%D1%8C%D1%81%D1%8F%20%D0%B2%D1%96%D0%B4%20%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D1%96%D0%B2%20%D0%B2%D0%BB%D0%B0%D0%B4%D0%B8%20(Missing%20or%20wanted%20persons)
   */
  case wanted = "Безвісно зниклі та ті, які переховуються від органів влади";
}