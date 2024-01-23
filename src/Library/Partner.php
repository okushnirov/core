<?php

namespace okushnirov\core\Library;

final class Partner
{
  public ?int $id;
  
  public string $logo;
  
  public string $name;
  
  public function setValue(array $user):Partner
  {
    $id = trim($user['partnerID'] ?? '');
    $this->id = '' === $id ? null : (int)$id;
    
    $this->logo = $user['partnerLogo'] ?? '';
    
    $this->name = $user['partnerName'] ?? '';
    
    return $this;
  }
}