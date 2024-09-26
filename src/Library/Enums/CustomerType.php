<?php

namespace okushnirov\core\Library\Enums;

enum CustomerType
{
  case COMPANY;
  
  case BUSINESSMAN;
  
  case PERSON;
  
  public function getLabel(string $lang):string
  {
    
    return match ($this) {
      CustomerType::COMPANY => 'ru' === $lang ? 'Юридическое лицо' : 'Юридична особа',
      CustomerType::PERSON => 'ru' === $lang ? 'Физическое лицо' : 'Фізична особа',
      CustomerType::BUSINESSMAN => 'ru' === $lang ? 'Физическое лицо-предприниматель' : 'Фізична особа-підприємець'
    };
  }
  
  public function getShort(string $lang):string
  {
    
    return match ($this) {
      CustomerType::COMPANY => 'ru' === $lang ? 'ЮЛ' : 'ЮО',
      CustomerType::PERSON => 'ru' === $lang ? 'ФЛ' : 'ФО',
      CustomerType::BUSINESSMAN => 'ru' === $lang ? 'ФЛП' : 'ФОП'
    };
  }
  
  public static function getType(int | string $type):?CustomerType
  {
    
    return match ('string' === gettype($type) ? mb_convert_case(trim($type), MB_CASE_UPPER) : $type) {
      CustomerFrontID::Company->value, CustomerDocsID::Company->value, CustomerFrontRef::Company->value, CustomerDocsRef::Company->value => CustomerType::COMPANY,
      CustomerFrontID::Businessman->value, CustomerDocsID::Businessman->value, CustomerFrontRef::Businessman->value, CustomerDocsRef::Businessman->value => CustomerType::BUSINESSMAN,
      CustomerFrontID::Person->value, CustomerDocsID::Person->value, CustomerFrontRef::Person->value, CustomerDocsRef::Person->value => CustomerType::PERSON,
      default => null
    };
  }
  
  public function getCustomer(
    string $customer):null | CustomerFrontID | CustomerDocsID | CustomerFrontRef | CustomerDocsRef
  {
    
    return match ($this) {
      CustomerType::COMPANY => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Company,
        CustomerDocsID::class => CustomerDocsID::Company,
        CustomerFrontRef::class => CustomerFrontRef::Company,
        CustomerDocsRef::class => CustomerDocsRef::Company,
        default => null
      },
      CustomerType::BUSINESSMAN => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Businessman,
        CustomerDocsID::class => CustomerDocsID::Businessman,
        CustomerFrontRef::class => CustomerFrontRef::Businessman,
        CustomerDocsRef::class => CustomerDocsRef::Businessman,
        default => null
      },
      CustomerType::PERSON => match ($customer) {
        CustomerFrontID::class => CustomerFrontID::Person,
        CustomerDocsID::class => CustomerDocsID::Person,
        CustomerFrontRef::class => CustomerFrontRef::Person,
        CustomerDocsRef::class => CustomerDocsRef::Person,
        default => null
      }
    };
  }
}