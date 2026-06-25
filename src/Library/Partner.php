<?php

namespace okushnirov\core\Library;

final class Partner
{
  public ?int $id;
  public string $logo;
  public string $name;
  
  public function setValue(array $user):Partner
  {
    $id = trim((string)($user['partnerID'] ?? ''));
    $this->id = '' === $id ? null : (int)$id;
    $this->logo = trim((string)($user['partnerLogo'] ?? ''));
    $this->name = trim((string)($user['partnerName'] ?? ''));
    
    return $this;
  }
}