<?php

namespace okushnirov\core\Library;

final class Partner
{
  public ?int $id;
  
  public string $logo;
  
  public string $name;
  
  public function setValue(array $user):Partner
  {
    $ID = trim($user['partnerID'] ?? '');
    $this->id = '' === $ID ? null : (int)$ID;
    
    $this->logo = $user['partnerLogo'] ?? '';
    
    $this->name = $user['partnerName'] ?? '';

    return $this;
  }
}