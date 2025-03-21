<?php

namespace okushnirov\core\Library\Enums;

enum CustomerType
{
  case COMPANY;
  
  case BUSINESSMAN;
  
  case PERSON;
  
  public function getLabel(string $lang, string $variant = ''):string
  {
    
    return match ($this) {
      CustomerType::COMPANY => match ($variant) {
        'short' => 'ru' === $lang ? 'ЮЛ' : 'ЮО',
        default => 'ru' === $lang ? 'Юридическое лицо' : 'Юридична особа'
      },
      CustomerType::PERSON => match ($variant) {
        'short' => 'ru' === $lang ? 'ФЛ' : 'ФО',
        default => 'ru' === $lang ? 'Физическое лицо' : 'Фізична особа'
      },
      CustomerType::BUSINESSMAN => match ($variant) {
        'short' => 'ru' === $lang ? 'ФЛП' : 'ФОП',
        'half' => 'ru' === $lang ? 'Предприниматель' : 'Підприємець',
        default => 'ru' === $lang ? 'Физическое лицо-предприниматель' : 'Фізична особа-підприємець'
      }
    };
  }
  
  /**
   * @deprecated
   */
  public function getShort(string $lang):string
  {
    
    return match ($this) {
      CustomerType::COMPANY => 'ru' === $lang ? 'ЮЛ' : 'ЮО',
      CustomerType::PERSON => 'ru' === $lang ? 'ФЛ' : 'ФО',
      CustomerType::BUSINESSMAN => 'ru' === $lang ? 'ФЛП' : 'ФОП'
    };
  }
  
  public static function getType(int | string $type, string $customer = ''):?CustomerType
  {
    $type = 'string' === gettype($type) ? mb_convert_case(trim($type), MB_CASE_UPPER) : $type;
    
    return '' === $customer
      ? match ($type) {
        CustomerFrontID::Company->value, CustomerDocsID::Company->value, CustomerFrontRef::Company->value, CustomerDocsRef::Company->value => CustomerType::COMPANY,
        CustomerFrontID::Businessman->value, CustomerDocsID::Businessman->value, CustomerFrontRef::Businessman->value, CustomerDocsRef::Businessman->value => CustomerType::BUSINESSMAN,
        CustomerFrontID::Person->value, CustomerDocsID::Person->value, CustomerFrontRef::Person->value, CustomerDocsRef::Person->value => CustomerType::PERSON,
        default => null
      }
      : match ($customer) {
        CustomerTKBID::class => match (CustomerTKBID::tryFrom($type)) {
          CustomerTKBID::Businessman => CustomerType::BUSINESSMAN,
          CustomerTKBID::Company => CustomerType::COMPANY,
          CustomerTKBID::Person => CustomerType::PERSON,
          default => null,
        },
        CustomerFrontID::class => match (CustomerFrontID::tryFrom($type)) {
          CustomerFrontID::Businessman => CustomerType::BUSINESSMAN,
          CustomerFrontID::Company => CustomerType::COMPANY,
          CustomerFrontID::Person => CustomerType::PERSON,
          default => null
        },
        CustomerFrontRef::class => match (CustomerFrontRef::tryFrom($type)) {
          CustomerFrontRef::Businessman => CustomerType::BUSINESSMAN,
          CustomerFrontRef::Company => CustomerType::COMPANY,
          CustomerFrontRef::Person => CustomerType::PERSON,
          default => null
        },
        CustomerDocsID::class => match (CustomerDocsID::tryFrom($type)) {
          CustomerDocsID::Businessman => CustomerType::BUSINESSMAN,
          CustomerDocsID::Company => CustomerType::COMPANY,
          CustomerDocsID::Person => CustomerType::PERSON,
          default => null
        },
        CustomerDocsRef::class => match (CustomerDocsRef::tryFrom($type)) {
          CustomerDocsRef::Businessman => CustomerType::BUSINESSMAN,
          CustomerDocsRef::Company => CustomerType::COMPANY,
          CustomerDocsRef::Person => CustomerType::PERSON,
          default => null
        },
        default => null
      };
  }
  
  public function getCustomer(
    string $customer):null | CustomerFrontID | CustomerDocsID | CustomerFrontRef | CustomerDocsRef | CustomerTKBID
  {
    
    return match ($this) {
      CustomerType::COMPANY => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Company,
        CustomerDocsID::class => CustomerDocsID::Company,
        CustomerFrontRef::class => CustomerFrontRef::Company,
        CustomerDocsRef::class => CustomerDocsRef::Company,
        CustomerTKBID::class => CustomerTKBID::Company,
        default => null
      },
      CustomerType::BUSINESSMAN => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Businessman,
        CustomerDocsID::class => CustomerDocsID::Businessman,
        CustomerFrontRef::class => CustomerFrontRef::Businessman,
        CustomerDocsRef::class => CustomerDocsRef::Businessman,
        CustomerTKBID::class => CustomerTKBID::Businessman,
        default => null
      },
      CustomerType::PERSON => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Person,
        CustomerDocsID::class => CustomerDocsID::Person,
        CustomerFrontRef::class => CustomerFrontRef::Person,
        CustomerDocsRef::class => CustomerDocsRef::Person,
        CustomerTKBID::class => CustomerTKBID::Person,
        default => null
      }
    };
  }
}